<?php

namespace App\Livewire\User;

use App\Models\AppSetting;
use App\Models\MetadataGeneration;
use App\Services\AI\AIService;
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
    public string $selectedModel = ''; // Selected AI model
    
    // Results
    public array $results = [];

    // AI Provider
    protected AIService $aiService;

    public function boot(): void
    {
        $this->aiService = app(AIService::class);
    }

    public function mount(): void
    {
        // Set default model based on provider
        $provider = $this->aiService->getDefaultProvider();
        $this->selectedModel = $this->aiService->getDefaultModelForProvider($provider);
    }

    public function updatedSelectedModel(): void
    {
        // Model changed, no additional action needed
    }

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
            $provider = $this->aiService->getDefaultProvider();
            
            // Build prompt
            $prompt = $this->buildPrompt();
            
            // Use AIService to generate metadata
            $result = $this->aiService->generateMetadata(
                $base64Data,
                $mimeType,
                $prompt,
                $this->mode,
                $provider,
                $this->selectedModel ?: null
            );

            // Validate and trim keywords to match user-specified count
            $keywords = $this->validateKeywordCount($result['keywords']);

            // Save to database for generation count tracking
            MetadataGeneration::create([
                'user_id' => auth()->id(),
                'filename' => $filename,
                'title' => $result['title'],
                'keywords' => $keywords,
                'ai_model' => $this->selectedModel ?: $provider,
            ]);
            
            // Update queue
            $this->imageQueue[$index]['status'] = 'completed';
            $this->imageQueue[$index]['title'] = $result['title'];
            $this->imageQueue[$index]['keywords'] = $keywords;
            
            // Store in results (use placeholder for image since it's in IndexedDB)
            $this->results[$index] = [
                'filename' => $filename,
                'title' => $result['title'],
                'keywords' => $keywords,
            ];
            
            // Dispatch event to save to IndexedDB history
            $this->dispatch('save-to-history', [
                'filename' => $filename,
                'title' => $result['title'],
                'keywords' => $result['keywords'],
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

    /**
     * Validate and trim keywords to match user-specified count.
     * Keywords can be equal to or less than the limit, but never more.
     */
    protected function validateKeywordCount(string $keywords): string
    {
        // Split keywords by comma
        $keywordArray = array_map('trim', explode(',', $keywords));
        
        // Remove empty values
        $keywordArray = array_filter($keywordArray, fn($k) => !empty($k));
        
        // Trim to max count if exceeded
        if (count($keywordArray) > $this->keywordCount) {
            $keywordArray = array_slice($keywordArray, 0, $this->keywordCount);
        }
        
        return implode(', ', $keywordArray);
    }

    protected function buildPrompt(): string
    {
        $additionalContext = $this->description ? "\n\nAdditional context from user: {$this->description}" : '';
        
        if ($this->mode === 'slow') {
            // Detailed, high-quality mode with comprehensive prompt
            return <<<PROMPT
You are an expert Senior Metadata Editor and SEO strategist for a top-tier microstock agency like Getty Images or Adobe Stock. Your primary goal is to maximize the commercial discoverability and sales potential of an image.

**Analysis & Strategy:**
Analyze the provided image and generate a highly marketable title and a list of exactly {$this->keywordCount} strategically chosen keywords.

**1. Title Generation (SEO-Optimized, Descriptive):**
- Create a HIGHLY DESCRIPTIVE, SEO-optimized title in English (120-200 characters).
- The title must tell a complete story about the image - WHO, WHAT, WHERE, HOW.
- Structure: [Main Subject] + [Action/State] + [Context/Setting] + [Descriptive Details]
- Include specific descriptive words: colors, lighting, mood, environment, style.
- Think from a buyer's perspective: What detailed search query would find this exact image?

**Title Examples:**
- BAD: "Woman working on laptop" (too short, not descriptive)
- GOOD: "Professional young businesswoman working on laptop in modern bright office with large windows, focused and confident expression, corporate lifestyle concept"
- BAD: "Beautiful sunset" (generic, not SEO)
- GOOD: "Dramatic golden sunset over calm ocean with silhouette of palm trees, tropical paradise vacation destination, warm orange and purple sky"

**2. Keyword Generation (Exactly {$this->keywordCount} Keywords):**

**CRITICAL RULES:**
- Each keyword MUST be a SINGLE WORD only. No phrases, no compound words with spaces.
- Examples of VALID keywords: business, laptop, woman, success, professional, office, technology
- Examples of INVALID keywords: "business woman", "office desk", "working from home" - DO NOT USE THESE
- Order keywords from MOST relevant to LEAST relevant based on the image content and generated title.
- The first keywords should directly describe the main subject visible in the image.

- **Prioritization is Key:** The first 10-15 keywords must be the most powerful, high-intent terms that a commercial buyer would use.
- **Keyword Strategy Mix:**
    - **Primary Concepts:** The absolute core subject and theme (e.g., 'business', 'technology', 'success').
    - **Literal Objects & Subjects:** Clearly visible elements (e.g., 'laptop', 'desk', 'woman').
    - **Action & Emotion:** What is happening and the mood (e.g., 'working', 'smiling', 'focus').
    - **Composition & Style:** How the shot is composed (e.g., 'copyspace', 'closeup', 'minimalist').
    - **Abstract & Metaphorical:** Ideas the image represents (e.g., 'growth', 'innovation', 'strategy').
- **What to AVOID:**
    - Multi-word phrases (NEVER use phrases like "office worker" - use "office" and "worker" separately)
    - Do not use subjective or overly generic words ('beautiful', 'nice', 'great').
    - Avoid spammy or irrelevant terms.
{$additionalContext}

Output keywords as comma-separated lowercase SINGLE words only, ordered by relevance to the image.
PROMPT;
        } else {
            // Fast mode - optimized prompt for speed
            return <<<PROMPT
You are a stock photo metadata expert for Adobe Stock and Shutterstock. Analyze this image and generate optimized metadata.

**Title (SEO-Optimized, Descriptive):**
- Create a HIGHLY DESCRIPTIVE title (120-200 characters) that tells the complete story.
- Include: main subject, action/state, setting/context, mood, colors, style.
- Structure: [Subject] + [Action] + [Context] + [Descriptive Details]
- Example: "Professional businesswoman typing on laptop in modern office, focused expression, bright natural lighting, corporate workplace concept"
- AVOID short generic titles like "Woman working" - be SPECIFIC and DETAILED.

**Keywords:** Generate exactly {$this->keywordCount} comma-separated lowercase keywords.

**CRITICAL RULES:**
- Each keyword MUST be a SINGLE WORD only. NO phrases allowed.
- VALID: business, laptop, woman, success, office, technology, professional
- INVALID: "business woman", "office desk", "home office" - NEVER use multi-word phrases
- Order from MOST relevant to LEAST relevant based on image content and title.
- First keywords = main visible subjects. Last keywords = abstract concepts.
{$additionalContext}
PROMPT;
        }
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
        $provider = $this->aiService->getDefaultProvider();
        $availableModels = $this->aiService->getModelsForProvider($provider);
        
        return view('livewire.user.metadata-generator', [
            'isSubscribed' => $user->isSubscribed(),
            'remainingGenerations' => $user->getRemainingGenerations(),
            'dailyLimit' => $user->getDailyLimit(),
            'canGenerate' => $user->canGenerate(),
            'currentProvider' => $provider,
            'availableModels' => $availableModels,
        ]);
    }
}
