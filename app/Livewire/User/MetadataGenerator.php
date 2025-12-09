<?php

namespace App\Livewire\User;

use App\Models\ApiKey;
use App\Models\MetadataGeneration;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Generate Metadata')]
class MetadataGenerator extends Component
{
    public string $description = '';
    public bool $isProcessing = false;
    public bool $isRegenerating = false; // Flag for single image retry
    public ?string $error = null;
    
    // Queue management (synced from frontend)
    public array $imageQueue = [];
    public int $currentIndex = -1;
    public int $totalImages = 0;
    public int $processedCount = 0;
    
    // Settings
    public int $delaySeconds = 3;
    public string $mode = 'fast'; // 'fast' or 'slow'
    public int $keywordCount = 35;
    
    // Results
    public array $results = [];

    // Available Gemini models to try (in order of preference)
    protected array $geminiModels = [
        'gemini-2.5-flash',
        'gemini-2.0-flash',
        'gemini-1.5-flash',
    ];

    public function setImageQueue(array $queue): void
    {
        $this->imageQueue = $queue;
        $this->totalImages = count($queue);
        $this->error = null;
        $this->results = [];
        $this->currentIndex = -1;
        $this->processedCount = 0;
    }

    public function startProcessing(): void
    {
        if (empty($this->imageQueue)) {
            $this->error = 'Please upload at least one image.';
            return;
        }

        // Check subscription limit for free users
        $user = auth()->user();
        if (!$user->canGenerate()) {
            $this->error = 'You have reached your daily limit of ' . $user->getDailyLimit() . ' generations. Please upgrade to a subscription for unlimited access.';
            return;
        }

        // Check if user has enough remaining generations for all images
        if (!$user->isSubscribed()) {
            $remaining = $user->getRemainingGenerations();
            if ($remaining < count($this->imageQueue)) {
                $this->error = 'You can only generate ' . $remaining . ' more image(s) today. Please reduce the number of images or upgrade to a subscription.';
                return;
            }
        }

        $this->isProcessing = true;
        $this->error = null;
        $this->processedCount = 0;
        $this->currentIndex = 0;
        
        // Dispatch event to frontend to send first image
        $this->dispatch('request-image-data', ['index' => 0]);
    }

    public function processImage(int $index, string $filename, string $base64Data, string $mimeType): void
    {
        if ($index !== $this->currentIndex) return;
        
        // Update status
        $this->imageQueue[$index]['status'] = 'processing';
        
        try {
            $user = auth()->user();
            $apiKeys = collect();
            
            // First, try user's personal API key if they have one
            if ($user->hasPersonalApiKey()) {
                // Create a temporary ApiKey-like object for personal key
                $personalKey = new ApiKey([
                    'api_key' => $user->gemini_api_key,
                    'provider' => 'gemini',
                    'is_active' => true,
                ]);
                $personalKey->is_personal = true; // Flag to identify personal key
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

            // Try each API key with each model until one works
            foreach ($apiKeys as $apiKey) {
                foreach ($this->geminiModels as $model) {
                    try {
                        $response = $this->callGeminiApi($apiKey, $base64Data, $mimeType, $model);
                        $usedApiKey = $apiKey;
                        $usedModel = $model;
                        break 2; // Success, exit both loops
                    } catch (\Exception $e) {
                        $lastError = $e->getMessage();
                        
                        // Check if it's a quota/rate limit error - try next model/key
                        if (str_contains(strtolower($lastError), 'quota') || 
                            str_contains(strtolower($lastError), 'rate') ||
                            str_contains(strtolower($lastError), 'limit') ||
                            str_contains(strtolower($lastError), '429') ||
                            str_contains(strtolower($lastError), 'exceeded') ||
                            str_contains(strtolower($lastError), 'resource')) {
                            continue; // Try next model
                        }
                        
                        // For other errors, throw immediately
                        throw $e;
                    }
                }
            }

            // If no API key/model combination worked
            if (!$response || !$usedApiKey) {
                throw new \Exception('All API keys and models exhausted. Last error: ' . ($lastError ?? 'Unknown error'));
            }
            
            // Parse response
            $parsed = $this->parseGeminiResponse($response);

            // Increment API key usage
            $usedApiKey->incrementUsage();

            // Save to database for generation count tracking
            MetadataGeneration::create([
                'user_id' => auth()->id(),
                'filename' => $filename,
                'title' => $parsed['title'],
                'keywords' => $parsed['keywords'],
                'ai_model' => $usedModel,
            ]);
            
            // Update queue
            $this->imageQueue[$index]['status'] = 'completed';
            $this->imageQueue[$index]['title'] = $parsed['title'];
            $this->imageQueue[$index]['keywords'] = $parsed['keywords'];
            
            // Store in results (use placeholder for image since it's in IndexedDB)
            $this->results[$index] = [
                'filename' => $filename,
                'title' => $parsed['title'],
                'keywords' => $parsed['keywords'],
            ];
            
            // Dispatch event to save to IndexedDB history
            $this->dispatch('save-to-history', [
                'filename' => $filename,
                'title' => $parsed['title'],
                'keywords' => $parsed['keywords'],
            ]);

        } catch (\Exception $e) {
            $this->imageQueue[$index]['status'] = 'failed';
            $this->imageQueue[$index]['error'] = $e->getMessage();
            // Also set main error so it displays in the UI
            $this->error = 'Generation failed: ' . $e->getMessage();
        }
        
        $this->processedCount++;
        
        // If regenerating a single image, stop here
        if ($this->isRegenerating) {
            $this->isProcessing = false;
            $this->isRegenerating = false;
            $this->currentIndex = -1;
            return;
        }
        
        $this->currentIndex++;
        
        // Continue to next image or finish
        if ($this->currentIndex < $this->totalImages) {
            // Dispatch event to frontend with delay
            $this->dispatch('process-next-delay', ['delay' => $this->delaySeconds * 1000, 'nextIndex' => $this->currentIndex]);
        } else {
            $this->isProcessing = false;
            $this->currentIndex = -1;
        }
    }

    /**
     * Regenerate a failed image
     */
    public function regenerateImage(int $index): void
    {
        // Only allow regenerating failed images
        if (!isset($this->imageQueue[$index]) || $this->imageQueue[$index]['status'] !== 'failed') {
            return;
        }

        // Check if user can still generate (for free users)
        $user = auth()->user();
        if (!$user->canGenerate()) {
            $this->error = 'You have reached your daily limit. Please upgrade to continue.';
            return;
        }

        // Reset the image status
        $this->imageQueue[$index]['status'] = 'pending';
        $this->imageQueue[$index]['error'] = null;
        $this->error = null;

        // Set this as current index and mark as processing
        $this->currentIndex = $index;
        $this->isProcessing = true;
        $this->isRegenerating = true; // Mark as single image regeneration

        // Request image data from frontend for this specific index
        $this->dispatch('request-image-data', ['index' => $index]);
    }

    protected function callGeminiApi(ApiKey $apiKey, string $imageData, string $mimeType, string $model = 'gemini-2.0-flash'): array
    {
        $prompt = $this->buildPrompt();

        $response = Http::timeout(60)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey->api_key}",
            [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $imageData,
                                ],
                            ],
                            [
                                'text' => $prompt,
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 2048,
                ],
            ]
        );

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? 'Failed to generate metadata';
            throw new \Exception($error);
        }

        return $response->json();
    }

    protected function buildPrompt(): string
    {
        $additionalContext = $this->description ? "\n\nAdditional context from user: {$this->description}" : '';
        
        if ($this->mode === 'slow') {
            // Detailed, descriptive mode
            return <<<PROMPT
You are an expert stock photography metadata specialist for Adobe Stock, Shutterstock, and Getty Images. Analyze this image thoroughly and generate highly optimized metadata.

1. **Title**: Create a compelling, highly descriptive SEO title (100-180 characters). The title MUST:
   - Start with the most important subject/element in the image
   - Include 2-3 descriptive adjectives (colors, emotions, qualities)
   - Describe the action, setting, or context clearly
   - Use natural sentence structure with high-value search keywords
   - Be specific and unique (avoid generic titles)
   - Include relevant qualifiers (e.g., "professional", "modern", "authentic", "aerial view", "close-up")
   
   GOOD EXAMPLES:
   - "Young professional businesswoman working on laptop in modern coworking space with natural light"
   - "Golden retriever puppy playing with colorful ball in sunny backyard garden during summer"
   - "Aerial view of turquoise ocean waves crashing on white sandy tropical beach at sunset"
   - "Fresh organic vegetables and fruits arranged on rustic wooden table in farmhouse kitchen"
   
   BAD EXAMPLES (too generic):
   - "Woman working" 
   - "Dog playing"
   - "Beach view"

2. **Keywords**: Generate exactly {$this->keywordCount} relevant keywords separated by commas. Keywords should:
   - Be single words or short phrases (2-3 words max)
   - Start with the most specific/important terms
   - Include: main subjects, actions, emotions, colors, styles, compositions, moods, settings
   - Add synonyms and related search terms
   - Be highly relevant to stock photography buyers
   - Be in lowercase
   - Be ordered by relevance (most relevant first)
   - NOT include camera/technical terms
{$additionalContext}

Respond in this exact JSON format:
{
    "title": "Your highly descriptive title here",
    "keywords": "keyword1, keyword2, keyword3, ..."
}

Only respond with the JSON, no additional text.
PROMPT;
        } else {
            // Fast, SEO-focused mode
            return <<<PROMPT
You are a stock photography metadata expert. Analyze this image and generate SEO-optimized metadata.

1. **Title**: Create a descriptive, SEO-friendly title (80-150 characters). The title should:
   - Start with the main subject
   - Include 1-2 descriptive adjectives
   - Describe what's happening in the image
   - Use natural keyword-rich language
   - Be specific (avoid generic descriptions)
   
   EXAMPLES:
   - "Happy young couple enjoying coffee date in cozy cafe with warm lighting"
   - "Modern minimalist home office workspace with laptop and indoor plants"
   - "Colorful autumn leaves falling in peaceful forest path at golden hour"

2. **Keywords**: Generate exactly {$this->keywordCount} relevant keywords separated by commas. Include subjects, actions, emotions, colors, and style. All lowercase, ordered by relevance.
{$additionalContext}

Respond in this exact JSON format:
{
    "title": "Your descriptive title here",
    "keywords": "keyword1, keyword2, keyword3, ..."
}

Only respond with the JSON, no additional text.
PROMPT;
        }
    }

    protected function parseGeminiResponse(array $response): array
    {
        // Try to get text from response - handle different response structures
        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // If no text found, check for other possible structures
        if (empty($text)) {
            \Log::error('Gemini response structure:', $response);
            throw new \Exception('Empty response from AI.');
        }
        
        // Remove markdown code blocks if present (various formats)
        $text = preg_replace('/^```json\s*/m', '', $text);
        $text = preg_replace('/^```\s*$/m', '', $text);
        $text = trim($text);

        // Find the JSON object - look for opening { and find matching closing }
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

        if (!$data) {
            \Log::error('Failed to parse JSON. Raw text:', ['text' => $text]);
            throw new \Exception('Failed to parse AI response. Please try again.');
        }

        if (!isset($data['title']) || !isset($data['keywords'])) {
            \Log::error('Missing required fields:', $data);
            throw new \Exception('AI response missing title or keywords.');
        }

        return [
            'title' => $data['title'],
            'keywords' => $data['keywords'],
        ];
    }

    public function copyToClipboard(int $index, string $type): void
    {
        $result = $this->results[$index] ?? null;
        if (!$result) return;
        
        $text = $type === 'title' ? $result['title'] : $result['keywords'];
        
        $this->dispatch('copy-to-clipboard', [
            'text' => $text,
            'type' => $type,
        ]);
    }

    public function resetForm(): void
    {
        $this->description = '';
        $this->error = null;
        $this->imageQueue = [];
        $this->currentIndex = -1;
        $this->processedCount = 0;
        $this->totalImages = 0;
        $this->results = [];
        
        // Tell frontend to clear IndexedDB
        // $this->dispatch('clear-images'); // Removed to prevent infinite loop with clearAllImages()
    }

    public function render()
    {
        $user = auth()->user();
        
        return view('livewire.user.metadata-generator', [
            'isSubscribed' => $user->isSubscribed(),
            'remainingGenerations' => $user->getRemainingGenerations(),
            'dailyLimit' => $user->getDailyLimit(),
            'canGenerate' => $user->canGenerate(),
        ]);
    }
}
