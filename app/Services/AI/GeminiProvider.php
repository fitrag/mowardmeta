<?php

namespace App\Services\AI;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;

class GeminiProvider implements AIProviderInterface
{
    public function generateMetadata(
        ApiKey $apiKey,
        string $imageData,
        string $mimeType,
        string $prompt,
        string $mode = 'fast',
        ?string $model = null
    ): array {
        $model = $model ?? $this->getDefaultModel();

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $imageData,
                            ],
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 1024,
                'topP' => 0.8,
                'topK' => 20,
                'responseMimeType' => 'application/json',
                'responseSchema' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'title' => [
                            'type' => 'STRING',
                            'description' => 'A highly marketable, descriptive title in English',
                        ],
                        'keywords' => [
                            'type' => 'STRING',
                            'description' => 'Comma-separated keywords',
                        ],
                    ],
                    'required' => ['title', 'keywords'],
                ],
            ],
        ];

        if ($mode === 'fast') {
            $payload['generationConfig']['thinkingConfig'] = [
                'thinkingBudget' => 0,
            ];
        }

        $response = Http::timeout(30)->connectTimeout(10)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey->api_key}",
            $payload
        );

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? 'Failed to generate metadata';
            throw new \Exception($error);
        }

        return $this->parseResponse($response->json());
    }

    protected function parseResponse(array $response): array
    {
        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($text)) {
            throw new \Exception('Empty response from Gemini AI.');
        }

        $text = preg_replace('/^```json\s*/m', '', $text);
        $text = preg_replace('/^```\s*$/m', '', $text);
        $text = trim($text);

        $startPos = strpos($text, '{');
        if ($startPos !== false) {
            $depth = 0;
            $endPos = $startPos;
            for ($i = $startPos; $i < strlen($text); $i++) {
                if ($text[$i] === '{') $depth++;
                if ($text[$i] === '}') $depth--;
                if ($depth === 0) {
                    $endPos = $i;
                    break;
                }
            }
            $text = substr($text, $startPos, $endPos - $startPos + 1);
        }

        $data = json_decode($text, true);

        if (!$data || !isset($data['title']) || !isset($data['keywords'])) {
            throw new \Exception('Failed to parse Gemini response.');
        }

        return [
            'title' => $data['title'],
            'keywords' => $data['keywords'],
        ];
    }

    public function getProviderName(): string
    {
        return 'gemini';
    }

    public function getAvailableModels(): array
    {
        return [
            'gemini-2.5-flash' => 'Gemini 2.5 Flash',
            'gemini-2.0-flash' => 'Gemini 2.0 Flash',
            'gemini-1.5-flash' => 'Gemini 1.5 Flash',
            'gemini-1.5-pro' => 'Gemini 1.5 Pro',
        ];
    }

    public function getDefaultModel(): string
    {
        return 'gemini-2.5-flash';
    }
}
