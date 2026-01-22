<?php

namespace App\Services\AI;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;

class MistralProvider implements AIProviderInterface
{
    // Fallback models when rate limited
    protected array $fallbackModels = [
        'pixtral-12b-latest',
        'mistral-small-latest',
    ];

    public function generateMetadata(
        ApiKey $apiKey,
        string $imageData,
        string $mimeType,
        string $prompt,
        string $mode = 'fast',
        ?string $model = null
    ): array {
        $model = $model ?? $this->getDefaultModel();
        $modelsToTry = [$model, ...$this->fallbackModels];
        $modelsToTry = array_unique($modelsToTry);

        $lastError = null;

        foreach ($modelsToTry as $currentModel) {
            try {
                return $this->callApi($apiKey, $imageData, $mimeType, $prompt, $currentModel);
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                
                // Check if rate limited, try next model
                $isRateLimited = str_contains(strtolower($lastError), 'rate') ||
                    str_contains(strtolower($lastError), 'limit') ||
                    str_contains(strtolower($lastError), '429') ||
                    str_contains(strtolower($lastError), 'quota') ||
                    str_contains(strtolower($lastError), 'exceeded');

                if (!$isRateLimited) {
                    throw $e;
                }

                \Log::info("Mistral model {$currentModel} rate limited, trying fallback...");
                continue;
            }
        }

        throw new \Exception('All Mistral models exhausted. Last error: ' . $lastError);
    }

    protected function callApi(ApiKey $apiKey, string $imageData, string $mimeType, string $prompt, string $model): array
    {
        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt . "\n\nRespond with valid JSON only: {\"title\": \"...\", \"keywords\": \"...\"}",
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64,{$imageData}",
                            ],
                        ],
                    ],
                ],
            ],
            'temperature' => 0.4,
            'max_tokens' => 1024,
            'response_format' => ['type' => 'json_object'],
        ];

        $response = Http::timeout(60)
            ->connectTimeout(15)
            ->withHeaders([
                'Authorization' => "Bearer {$apiKey->api_key}",
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.mistral.ai/v1/chat/completions', $payload);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->json('error') ?? 'Failed to generate metadata';
            if (is_array($error)) {
                $error = json_encode($error);
            }
            throw new \Exception($error);
        }

        return $this->parseResponse($response->json());
    }

    protected function parseResponse(array $response): array
    {
        $text = $response['choices'][0]['message']['content'] ?? '';

        if (empty($text)) {
            throw new \Exception('Empty response from Mistral AI.');
        }

        $text = preg_replace('/^```json\s*/m', '', $text);
        $text = preg_replace('/^```\s*$/m', '', $text);
        $text = trim($text);

        $data = json_decode($text, true);

        if (!$data || !isset($data['title']) || !isset($data['keywords'])) {
            throw new \Exception('Failed to parse Mistral response.');
        }

        return [
            'title' => $data['title'],
            'keywords' => $data['keywords'],
        ];
    }

    public function getProviderName(): string
    {
        return 'mistral';
    }

    public function getAvailableModels(): array
    {
        return [
            'mistral-small-latest' => 'Mistral Small',
            'mistral-medium-latest' => 'Mistral Medium',
            'mistral-large-latest' => 'Mistral Large',
            'pixtral-12b-latest' => 'Pixtral 12B (Vision)',
        ];
    }

    public function getDefaultModel(): string
    {
        return 'pixtral-12b-latest';
    }
}
