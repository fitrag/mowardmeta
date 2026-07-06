<?php

namespace App\Services\AI;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KieProvider implements AIProviderInterface
{
    protected string $baseUrl = 'https://api.kie.ai';

    public function generateMetadata(
        ApiKey $apiKey,
        string $imageData,
        string $mimeType,
        string $prompt,
        string $mode = 'fast',
        ?string $model = null
    ): array {
        $model = $model ?? $this->getDefaultModel();

        $dataUrl = "data:{$mimeType};base64,{$imageData}";

        $payload = [
            'stream' => false,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt,
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $dataUrl,
                            ],
                        ],
                    ],
                ],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'metadata',
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => [
                                'type' => 'string',
                                'description' => 'A highly marketable, descriptive title in English',
                            ],
                            'keywords' => [
                                'type' => 'string',
                                'description' => 'Comma-separated keywords',
                            ],
                        ],
                        'required' => ['title', 'keywords'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];

        if ($mode === 'fast') {
            $payload['reasoning_effort'] = 'low';
        } else {
            $payload['reasoning_effort'] = 'high';
        }

        $endpoint = "{$this->baseUrl}/gemini-3-flash/v1/chat/completions";

        $response = Http::timeout(60)
            ->connectTimeout(10)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey->api_key,
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint, $payload);

        $rawBody = $response->body();

        if (! $response->successful()) {
            $error = $response->json('error.message')
                ?? $response->json('msg')
                ?? $rawBody
                ?? 'Failed to generate metadata';
            Log::error('Kie AI HTTP error', [
                'status' => $response->status(),
                'body' => substr($rawBody, 0, 2000),
            ]);
            throw new \Exception($error);
        }

        Log::debug('Kie AI raw response', [
            'content_type' => $response->header('Content-Type'),
            'body_length' => strlen($rawBody),
            'body_preview' => substr($rawBody, 0, 3000),
        ]);

        $json = json_decode($rawBody, true);

        if (! is_array($json)) {
            Log::error('Kie AI invalid JSON', [
                'raw_preview' => substr($rawBody, 0, 2000),
            ]);
            throw new \Exception('Invalid JSON response from Kie AI.');
        }

        // Handle Kie AI wrapper: {"code": 200, "data": {...}}
        if (isset($json['data']) && is_array($json['data'])) {
            $json = $json['data'];
        }

        // Handle Kie AI error wrapper: {"code": 422, "msg": "...", "data": null}
        if (isset($json['code']) && $json['code'] !== 200) {
            $msg = $json['msg'] ?? 'Unknown error';
            Log::error('Kie AI error response', ['code' => $json['code'], 'msg' => $msg]);
            throw new \Exception($msg);
        }

        Log::debug('Kie AI parsed JSON', [
            'top_keys' => array_keys($json ?? []),
        ]);

        // --- OpenAI-compatible format: choices[0].message.content ---
        if (isset($json['choices'])) {
            $content = $json['choices'][0]['message']['content'] ?? '';

            if (empty($content)) {
                Log::error('Kie AI empty content', ['response' => $json]);
                throw new \Exception('Empty response from Kie AI.');
            }

            return $this->extractJsonFromText($content);
        }

        // --- Fallback: try Gemini format ---
        if (isset($json['candidates'])) {
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (! empty($text)) {
                return $this->extractJsonFromText($text);
            }
        }

        Log::error('Kie AI unrecognized response format', [
            'response' => $json,
            'raw_preview' => substr($rawBody, 0, 2000),
        ]);
        throw new \Exception('Empty or unrecognized response from Kie AI.');
    }

    protected function extractJsonFromText(string $text): array
    {
        $text = preg_replace('/^```json\s*/m', '', $text);
        $text = preg_replace('/^```\s*$/m', '', $text);
        $text = trim($text);

        $startPos = strpos($text, '{');
        if ($startPos !== false) {
            $depth = 0;
            $endPos = $startPos;
            for ($i = $startPos; $i < strlen($text); $i++) {
                if ($text[$i] === '{') {
                    $depth++;
                }
                if ($text[$i] === '}') {
                    $depth--;
                }
                if ($depth === 0) {
                    $endPos = $i;
                    break;
                }
            }
            $text = substr($text, $startPos, $endPos - $startPos + 1);
        }

        $data = json_decode($text, true);

        if (! $data || ! isset($data['title']) || ! isset($data['keywords'])) {
            Log::error('Kie AI JSON parse failed', ['text' => $text]);
            throw new \Exception('Failed to parse Kie AI response.');
        }

        return [
            'title' => $data['title'],
            'keywords' => $data['keywords'],
        ];
    }

    public function getProviderName(): string
    {
        return 'kie';
    }

    public function getAvailableModels(): array
    {
        return [
            'gemini-3-flash' => 'Gemini 3 Flash (Kie AI)',
        ];
    }

    public function getDefaultModel(): string
    {
        return 'gemini-3-flash';
    }
}
