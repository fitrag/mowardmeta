<?php

namespace App\Services\AI;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;

class CustomProvider implements AIProviderInterface
{
    public function generateMetadata(
        ApiKey $apiKey,
        string $imageData,
        string $mimeType,
        string $prompt,
        string $mode = 'fast',
        ?string $model = null
    ): array {
        $baseUrl = rtrim($apiKey->base_url, '/');
        $endpoint = str_ends_with($baseUrl, '/chat/completions')
            ? $baseUrl
            : $baseUrl.'/chat/completions';

        $model = $model ?? $this->getDefaultModelForApiKey($apiKey);

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt."\n\nRespond with valid JSON only: {\"title\": \"...\", \"keywords\": \"...\"}",
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
            ->post($endpoint, $payload);

        if (! $response->successful()) {
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
            throw new \Exception('Empty response from AI provider.');
        }

        $text = preg_replace('/^```json\s*/m', '', $text);
        $text = preg_replace('/^```\s*$/m', '', $text);
        $text = trim($text);

        $data = json_decode($text, true);

        if (! $data || ! isset($data['title']) || ! isset($data['keywords'])) {
            throw new \Exception('Failed to parse AI response.');
        }

        return [
            'title' => $data['title'],
            'keywords' => $data['keywords'],
        ];
    }

    public function getProviderName(): string
    {
        return 'custom';
    }

    public function getAvailableModels(): array
    {
        return [];
    }

    public function getDefaultModel(): string
    {
        return '';
    }

    public function getDefaultModelForApiKey(ApiKey $apiKey): string
    {
        $models = $apiKey->models;
        if (is_string($models)) {
            $models = json_decode($models, true) ?? [];
        }
        if (is_array($models) && ! empty($models)) {
            return $models[0]['name'] ?? $models[0] ?? '';
        }

        return '';
    }

    public function getModelsForApiKey(ApiKey $apiKey): array
    {
        $models = $apiKey->models;
        if (is_string($models)) {
            $models = json_decode($models, true) ?? [];
        }
        if (is_array($models)) {
            $result = [];
            foreach ($models as $model) {
                if (is_array($model)) {
                    $result[$model['name']] = $model['label'] ?? $model['name'];
                } else {
                    $result[$model] = $model;
                }
            }

            return $result;
        }

        return [];
    }
}
