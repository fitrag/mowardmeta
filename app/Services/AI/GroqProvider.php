<?php

namespace App\Services\AI;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;

class GroqProvider implements AIProviderInterface
{
    // Fallback models when rate limited
    protected array $fallbackModels = [
        'llama-3.2-11b-vision-preview',
        'llama-3.2-90b-vision-preview',
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

                \Log::info("Groq model {$currentModel} rate limited, trying fallback...");
                continue;
            }
        }

        throw new \Exception('All Groq models exhausted. Last error: ' . $lastError);
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
            ->post('https://api.groq.com/openai/v1/chat/completions', $payload);

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
            throw new \Exception('Empty response from Groq AI.');
        }

        $text = preg_replace('/^```json\s*/m', '', $text);
        $text = preg_replace('/^```\s*$/m', '', $text);
        $text = trim($text);

        $data = json_decode($text, true);

        if (!$data || !isset($data['title']) || !isset($data['keywords'])) {
            throw new \Exception('Failed to parse Groq response.');
        }

        return [
            'title' => $data['title'],
            'keywords' => $data['keywords'],
        ];
    }

    public function getProviderName(): string
    {
        return 'groq';
    }

    public function getAvailableModels(): array
    {
        return [
            'meta-llama/llama-4-scout-17b-16e-instruct' => 'Llama 4 Scout 17B',
            'meta-llama/llama-4-maverick-17b-128e-instruct' => 'Llama 4 Maverick 17B',
            'llama-3.2-90b-vision-preview' => 'Llama 3.2 90B Vision',
            'llama-3.2-11b-vision-preview' => 'Llama 3.2 11B Vision',
        ];
    }

    public function getDefaultModel(): string
    {
        return 'meta-llama/llama-4-scout-17b-16e-instruct';
    }
}
