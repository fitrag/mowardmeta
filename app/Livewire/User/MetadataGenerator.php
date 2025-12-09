<?php

namespace App\Livewire\User;

use App\Models\ApiKey;
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
            // Get random active API key
            $apiKey = ApiKey::getRandomActive('gemini');
            
            if (!$apiKey) {
                throw new \Exception('No active API key available. Please contact administrator.');
            }

            // Call Gemini API with base64 data directly
            $response = $this->callGeminiApi($apiKey, $base64Data, $mimeType);
            
            // Parse response
            $parsed = $this->parseGeminiResponse($response);

            // Increment API key usage
            $apiKey->incrementUsage();
            
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
        }
        
        $this->processedCount++;
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

    protected function callGeminiApi(ApiKey $apiKey, string $imageData, string $mimeType): array
    {
        $prompt = $this->buildPrompt();

        $response = Http::timeout(60)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey->api_key}",
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
                    'maxOutputTokens' => 1024,
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
        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Remove markdown code blocks if present
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);
        $text = trim($text);

        $data = json_decode($text, true);

        if (!$data || !isset($data['title']) || !isset($data['keywords'])) {
            throw new \Exception('Failed to parse AI response. Please try again.');
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
        return view('livewire.user.metadata-generator');
    }
}
