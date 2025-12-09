<?php

namespace App\Livewire\User;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Keyword Generator')]
class KeywordGenerator extends Component
{
    public string $title = '';
    public int $keywordCount = 35;
    public bool $isGenerating = false;
    public ?string $error = null;
    public ?string $generatedKeywords = null;

    // Available Gemini models
    protected array $geminiModels = [
        'gemini-2.5-flash',
    ];

    public function generateKeywords(): void
    {
        $this->validate([
            'title' => 'required|min:5|max:500',
            'keywordCount' => 'required|integer|min:10|max:50',
        ]);

        // Keyword generator is free for all users - no limit check

        $this->isGenerating = true;
        $this->error = null;
        $this->generatedKeywords = null;

        try {
            $user = auth()->user();
            $apiKeys = collect();
            
            // First, try user's personal API key if they have one
            if ($user->hasPersonalApiKey()) {
                $personalKey = new ApiKey([
                    'api_key' => $user->gemini_api_key,
                    'provider' => 'gemini',
                    'is_active' => true,
                ]);
                $personalKey->is_personal = true;
                $apiKeys->push($personalKey);
            }
            
            // Add shared API keys as fallback
            $sharedKeys = ApiKey::where('provider', 'gemini')
                ->where('is_active', true)
                ->inRandomOrder()
                ->get();
            
            $apiKeys = $apiKeys->concat($sharedKeys);

            if ($apiKeys->isEmpty()) {
                throw new \Exception('No active API key available. Please contact administrator.');
            }

            $lastError = null;
            $response = null;
            $usedApiKey = null;
            $usedModel = null;

            // Try each API key with each model
            foreach ($apiKeys as $apiKey) {
                foreach ($this->geminiModels as $model) {
                    try {
                        $response = $this->callGeminiApi($apiKey, $model);
                        $usedApiKey = $apiKey;
                        $usedModel = $model;
                        break 2;
                    } catch (\Exception $e) {
                        $lastError = $e->getMessage();
                        
                        if (str_contains(strtolower($lastError), 'quota') || 
                            str_contains(strtolower($lastError), 'rate') ||
                            str_contains(strtolower($lastError), 'limit') ||
                            str_contains(strtolower($lastError), '429') ||
                            str_contains(strtolower($lastError), 'exceeded') ||
                            str_contains(strtolower($lastError), 'resource')) {
                            continue;
                        }
                        
                        throw $e;
                    }
                }
            }

            if (!$response || !$usedApiKey) {
                throw new \Exception('All API keys exhausted. Last error: ' . ($lastError ?? 'Unknown error'));
            }

            // Parse response
            $this->generatedKeywords = $this->parseResponse($response);

            // Increment API key usage (only for shared keys)
            if (!isset($usedApiKey->is_personal) || !$usedApiKey->is_personal) {
                $usedApiKey->incrementUsage();
            }

            // Note: Keyword generator is free - no generation record saved

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    protected function callGeminiApi(ApiKey $apiKey, string $model): array
    {
        $prompt = $this->buildPrompt();

        $response = Http::timeout(60)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey->api_key}",
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1024,
                ],
            ]
        );

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? 'Failed to generate keywords';
            throw new \Exception($error);
        }

        return $response->json();
    }

    protected function buildPrompt(): string
    {
        return <<<PROMPT
You are a stock photography SEO expert. Generate EXACTLY {$this->keywordCount} single-word keywords based on this title:

Title: {$this->title}

STRICT REQUIREMENTS:
1. Generate EXACTLY {$this->keywordCount} keywords - no more, no less
2. Each keyword MUST be a SINGLE WORD only (no phrases, no spaces, no hyphens)
3. All keywords must be lowercase
4. Separate each keyword with a comma
5. Be relevant to the title
6. Include related concepts, emotions, actions, colors, and descriptive terms
7. Order by relevance (most relevant first)

RULES:
- Do NOT use phrases like "young woman" → use separate words: "young", "woman"
- Do NOT use hyphenated words like "well-dressed" → use: "elegant", "stylish"
- Count your keywords and make sure there are EXACTLY {$this->keywordCount}

OUTPUT FORMAT: word1, word2, word3, ... (exactly {$this->keywordCount} words)
PROMPT;
    }

    protected function parseResponse(array $response): string
    {
        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        if (empty($text)) {
            throw new \Exception('Empty response from AI.');
        }

        // Clean up the keywords
        $text = trim($text);
        $text = preg_replace('/\n+/', ', ', $text);
        $text = preg_replace('/,\s*,/', ',', $text);
        $text = trim($text, ', ');

        return strtolower($text);
    }

    public function copyKeywords(): void
    {
        if ($this->generatedKeywords) {
            $this->dispatch('copy-to-clipboard', [
                'text' => $this->generatedKeywords,
                'type' => 'keywords',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.user.keyword-generator');
    }
}
