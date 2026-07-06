<?php

namespace App\Services\AI;

use App\Models\ApiKey;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;

class AIService
{
    protected array $providers = [];

    public function __construct()
    {
        $this->providers = [
            'gemini' => new GeminiProvider,
            'groq' => new GroqProvider,
            'mistral' => new MistralProvider,
            'kie' => new KieProvider,
            'custom' => new CustomProvider,
        ];
    }

    /**
     * Get the default AI provider from settings
     */
    public function getDefaultProvider(): string
    {
        return AppSetting::get('ai_provider', 'gemini');
    }

    /**
     * Get provider instance
     */
    public function getProvider(string $name): ?AIProviderInterface
    {
        if (str_starts_with($name, 'custom_')) {
            return $this->providers['custom'] ?? null;
        }

        return $this->providers[$name] ?? null;
    }

    /**
     * Get all available providers
     */
    public function getAvailableProviders(): array
    {
        $providers = [
            'gemini' => 'Google Gemini',
            'groq' => 'Groq AI',
            'mistral' => 'Mistral AI',
            'kie' => 'Kie AI',
        ];

        $customProviders = Cache::remember('custom_ai_providers', 300, function () {
            return ApiKey::where('is_custom', true)
                ->where('is_active', true)
                ->get(['id', 'name', 'provider']);
        });

        foreach ($customProviders as $custom) {
            $providers['custom_'.$custom->id] = $custom->name;
        }

        return $providers;
    }

    /**
     * Get available models for a provider
     */
    public function getModelsForProvider(string $providerName): array
    {
        if (str_starts_with($providerName, 'custom_')) {
            $keyId = str_replace('custom_', '', $providerName);
            $apiKey = ApiKey::find($keyId);
            if ($apiKey) {
                return (new CustomProvider)->getModelsForApiKey($apiKey);
            }

            return [];
        }

        $provider = $this->getProvider($providerName);

        return $provider ? $provider->getAvailableModels() : [];
    }

    /**
     * Get default model for a provider
     */
    public function getDefaultModelForProvider(string $providerName): string
    {
        if (str_starts_with($providerName, 'custom_')) {
            $keyId = str_replace('custom_', '', $providerName);
            $apiKey = ApiKey::find($keyId);
            if ($apiKey) {
                return (new CustomProvider)->getDefaultModelForApiKey($apiKey);
            }

            return '';
        }

        $provider = $this->getProvider($providerName);

        return $provider ? $provider->getDefaultModel() : '';
    }

    /**
     * Get cached active API keys for a provider (5 min cache)
     */
    protected function getCachedApiKeys(string $providerName): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            "ai_api_keys_{$providerName}",
            300,
            fn () => ApiKey::where('provider', $providerName)
                ->where('is_active', true)
                ->inRandomOrder()
                ->get()
        );
    }

    /**
     * Invalidate API key cache for a provider
     */
    public static function invalidateApiKeyCache(string $providerName): void
    {
        Cache::forget("ai_api_keys_{$providerName}");
        Cache::forget('custom_ai_providers');
    }

    /**
     * Generate metadata using the specified or default provider
     */
    public function generateMetadata(
        string $imageData,
        string $mimeType,
        string $prompt,
        string $mode = 'fast',
        ?string $providerName = null,
        ?string $model = null
    ): array {
        $providerName = $providerName ?? $this->getDefaultProvider();
        $provider = $this->getProvider($providerName);

        if (! $provider) {
            throw new \Exception("AI provider '{$providerName}' not found.");
        }

        // Handle custom providers - get specific API key
        if (str_starts_with($providerName, 'custom_')) {
            $keyId = str_replace('custom_', '', $providerName);
            $apiKey = ApiKey::where('id', $keyId)
                ->where('is_custom', true)
                ->where('is_active', true)
                ->first();

            if (! $apiKey) {
                throw new \Exception('Custom provider API key not found or inactive.');
            }

            $result = $provider->generateMetadata(
                $apiKey,
                $imageData,
                $mimeType,
                $prompt,
                $mode,
                $model
            );

            $apiKey->incrementUsage();

            return [
                'title' => $result['title'],
                'keywords' => $result['keywords'],
                'provider' => $providerName,
                'api_key' => $apiKey,
            ];
        }

        // Get cached active API keys for this provider
        $apiKeys = $this->getCachedApiKeys($providerName);

        if ($apiKeys->isEmpty()) {
            throw new \Exception("No active API key available for {$providerName}.");
        }

        $lastError = null;
        $maxRetries = 3;
        $usedKeyIds = [];

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            // Re-fetch keys on retry in case cache was invalidated
            if ($attempt > 1) {
                $apiKeys = $this->getCachedApiKeys($providerName);
            }

            foreach ($apiKeys as $apiKey) {
                // Skip keys already tried in this attempt
                if (isset($usedKeyIds[$apiKey->id])) {
                    continue;
                }
                $usedKeyIds[$apiKey->id] = true;

                try {
                    $result = $provider->generateMetadata(
                        $apiKey,
                        $imageData,
                        $mimeType,
                        $prompt,
                        $mode,
                        $model
                    );

                    $apiKey->incrementUsage();

                    return [
                        'title' => $result['title'],
                        'category' => $result['category'] ?? 'Graphic Resources',
                        'keywords' => $result['keywords'],
                        'provider' => $providerName,
                        'api_key' => $apiKey,
                    ];
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();

                    $isRetryable = str_contains(strtolower($lastError), 'quota') ||
                        str_contains(strtolower($lastError), 'rate') ||
                        str_contains(strtolower($lastError), 'limit') ||
                        str_contains(strtolower($lastError), '429') ||
                        str_contains(strtolower($lastError), 'exceeded') ||
                        str_contains(strtolower($lastError), 'resource') ||
                        str_contains(strtolower($lastError), 'overloaded') ||
                        str_contains(strtolower($lastError), '503');

                    if (! $isRetryable) {
                        throw $e;
                    }
                }
            }
        }

        throw new \Exception('All API keys exhausted after '.$maxRetries.' retries. Last error: '.($lastError ?? 'Unknown error'));
    }
}
