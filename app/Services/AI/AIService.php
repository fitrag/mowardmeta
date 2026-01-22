<?php

namespace App\Services\AI;

use App\Models\ApiKey;
use App\Models\AppSetting;

class AIService
{
    protected array $providers = [];

    public function __construct()
    {
        $this->providers = [
            'gemini' => new GeminiProvider(),
            'groq' => new GroqProvider(),
            'mistral' => new MistralProvider(),
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
        return $this->providers[$name] ?? null;
    }

    /**
     * Get all available providers
     */
    public function getAvailableProviders(): array
    {
        return [
            'gemini' => 'Google Gemini',
            'groq' => 'Groq AI',
            'mistral' => 'Mistral AI',
        ];
    }

    /**
     * Get available models for a provider
     */
    public function getModelsForProvider(string $providerName): array
    {
        $provider = $this->getProvider($providerName);
        return $provider ? $provider->getAvailableModels() : [];
    }

    /**
     * Get default model for a provider
     */
    public function getDefaultModelForProvider(string $providerName): string
    {
        $provider = $this->getProvider($providerName);
        return $provider ? $provider->getDefaultModel() : '';
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

        if (!$provider) {
            throw new \Exception("AI provider '{$providerName}' not found.");
        }

        // Get active API keys for this provider
        $apiKeys = ApiKey::where('provider', $providerName)
            ->where('is_active', true)
            ->inRandomOrder()
            ->get();

        if ($apiKeys->isEmpty()) {
            throw new \Exception("No active API key available for {$providerName}.");
        }

        $lastError = null;
        $maxRetries = 3;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            foreach ($apiKeys as $apiKey) {
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

                    if (!$isRetryable) {
                        throw $e;
                    }
                }
            }

            if ($attempt < $maxRetries) {
                $waitSeconds = pow(2, $attempt);
                sleep($waitSeconds);
            }
        }

        throw new \Exception('All API keys exhausted after ' . $maxRetries . ' retries. Last error: ' . ($lastError ?? 'Unknown error'));
    }
}
