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

            // Validate and enforce title length limit (max 200 characters)
            $title = $this->validateTitleLength($result['title'], 200);

            // Get category
            $category = $result['category'] ?? 'Graphic Resources';

            // Save to database for generation count tracking
            MetadataGeneration::create([
                'user_id' => auth()->id(),
                'filename' => $filename,
                'title' => $title,
                'keywords' => $keywords,
                'ai_model' => $this->selectedModel ?: $provider,
            ]);
            
            // Update queue
            $this->imageQueue[$index]['status'] = 'completed';
            $this->imageQueue[$index]['title'] = $title;
            $this->imageQueue[$index]['category'] = $category;
            $this->imageQueue[$index]['keywords'] = $keywords;
            
            // Store in results (use placeholder for image since it's in IndexedDB)
            $this->results[$index] = [
                'filename' => $filename,
                'title' => $title,
                'category' => $category,
                'keywords' => $keywords,
            ];
            
            // Dispatch event to save to IndexedDB history
            $this->dispatch('save-to-history', [
                'filename' => $filename,
                'title' => $title,
                'category' => $category,
                'keywords' => $keywords,
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
    protected function validateKeywordCount(string|array $keywords): string
    {
        // Handle if keywords is already an array
        if (is_array($keywords)) {
            $keywordArray = array_map('trim', $keywords);
        } else {
            // Split keywords by comma
            $keywordArray = array_map('trim', explode(',', $keywords));
        }
        
        // Remove empty values
        $keywordArray = array_filter($keywordArray, fn($k) => !empty($k));
        
        // Trim to max count if exceeded
        if (count($keywordArray) > $this->keywordCount) {
            $keywordArray = array_slice($keywordArray, 0, $this->keywordCount);
        }
        
        return implode(', ', $keywordArray);
    }

    /**
     * Validate and enforce title length limit.
     * Title must not exceed 180 characters.
     */
    protected function validateTitleLength(string $title, int $maxLength = 180): string
    {
        $title = trim($title);
        
        if (mb_strlen($title) <= $maxLength) {
            return $title;
        }
        
        // Truncate at last complete word before the limit
        $truncated = mb_substr($title, 0, $maxLength);
        $lastSpace = mb_strrpos($truncated, ' ');
        
        if ($lastSpace !== false && $lastSpace > $maxLength * 0.7) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }
        
        return rtrim($truncated, ' ,.');
    }

    protected function buildPrompt(): string
    {
        $additionalContext = $this->description ? "\n\nAdditional context from user: {$this->description}" : '';
        
        $adobeCategories = [
            'Animals', 'Buildings and Architecture', 'Business', 'Drinks', 'The Environment',
            'States of Mind', 'Food', 'Graphic Resources', 'Hobbies and Leisure', 'Industry',
            'Landscapes', 'Lifestyle', 'People', 'Plants and Flowers', 'Culture and Religion',
            'Science', 'Social Issues', 'Sports', 'Technology', 'Transport', 'Travel'
        ];
        $categoriesList = implode("\n- ", $adobeCategories);

        if ($this->mode === 'slow') {
            // Detailed mode - comprehensive Adobe Stock SEO prompt
            return <<<PROMPT
You are a TOP-TIER Adobe Stock SEO Specialist with 10+ years of experience optimizing stock photography metadata for maximum sales and discoverability. You have deep knowledge of:
- Adobe Stock search algorithm and ranking factors
- Buyer search behavior and trending keywords
- Commercial licensing requirements
- High-converting metadata patterns

Your metadata consistently achieves top search rankings and high sales conversion rates.

Analyze this image and generate PROFESSIONAL, SALES-OPTIMIZED metadata in English:

## TITLE (MOST CRITICAL - This determines 70% of discoverability):
Requirements:
- Length: 80-200 characters (optimal for Adobe Stock algorithm)
- Structure: [Primary Subject] + [Action/State] + [Setting/Context] + [Mood/Atmosphere] + [Style/Quality]
- MUST start with the most searchable, specific noun (not articles like "A" or "The")
- Include 3-5 high-value descriptive adjectives (professional, modern, elegant, vibrant, authentic, etc.)
- Add emotional/mood qualifiers (happy, confident, peaceful, dynamic, etc.)
- Specify demographics if people are present (young, senior, diverse, professional, etc.)
- Include setting details (office, outdoor, studio, urban, nature, etc.)
- Add technical/style descriptors when relevant (aerial view, close-up, wide angle, minimalist, etc.)
- Use commercially valuable terms buyers actually search for
- NEVER use: "image", "photo", "picture", "stock", "illustration" (these waste characters)
- NEVER start with articles (A, An, The)

EXCELLENT TITLE EXAMPLES:
✓ "Confident young African American businesswoman presenting financial data on digital screen in modern corporate boardroom"
✓ "Fresh organic vegetables and fruits arranged on rustic wooden table with morning sunlight streaming through kitchen window"
✓ "Aerial drone view of turquoise ocean waves crashing on pristine white sand tropical beach at golden hour sunset"

POOR TITLE EXAMPLES (AVOID):
✗ "A woman in an office" (too generic, no value)
✗ "Photo of food on table" (wastes characters on "photo")
✗ "Beautiful landscape" (no specificity)

## CATEGORY:
Select the SINGLE most accurate category:
- {$categoriesList}

## KEYWORDS ({$this->keywordCount} keywords for maximum reach):
Rules:
- EXACTLY {$this->keywordCount} single-word keywords, comma-separated
- Each keyword = ONE word only (no spaces, no hyphens, no phrases)
- Lowercase only
- Priority order: main subject → actions → objects → setting → mood → colors → concepts → style → use cases
- Include synonyms of title words
- Add related concepts buyers might search
- Include both specific and broad terms
- Add trending/commercial terms when relevant
{$additionalContext}

RESPOND IN THIS EXACT FORMAT:
TITLE: [your professional SEO title]
CATEGORY: [single category name]
KEYWORDS: [keyword1, keyword2, keyword3, ...]
PROMPT;
        } else {
            // Fast mode - simpler prompt for quick generation
            return <<<PROMPT
You are an expert Senior Metadata Editor and SEO strategist for a top-tier microstock agency like Getty Images or Adobe Stock. Your primary goal is to maximize the commercial discoverability and sales potential of an image.

**Analysis & Strategy:**
Analyze the provided image and generate a highly marketable title, category, and a list of exactly {$this->keywordCount} strategically chosen keywords.

**1. Title Generation (SEO-Optimized, Descriptive):**
- Create a HIGHLY DESCRIPTIVE, SEO-optimized title in English (120-180 characters).
- The title must tell a complete story about the image - WHO, WHAT, WHERE, HOW.
- Structure: [Main Subject] + [Action/State] + [Context/Setting] + [Descriptive Details]
- Include specific descriptive words: colors, lighting, mood, environment, style.
- Think from a buyer's perspective: What detailed search query would find this exact image?

**Title Examples:**
- BAD: "Woman working on laptop" (too short, not descriptive)
- GOOD: "Professional young businesswoman working on laptop in modern bright office with large windows, focused and confident expression, corporate lifestyle concept"
- BAD: "Beautiful sunset" (generic, not SEO)
- GOOD: "Dramatic golden sunset over calm ocean with silhouette of palm trees, tropical paradise vacation destination, warm orange and purple sky"

**2. Category Selection:**
Select the SINGLE most accurate category from this list:
- {$categoriesList}

**3. Keyword Generation (Exactly {$this->keywordCount} Keywords):**

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

RESPOND IN THIS EXACT FORMAT:
TITLE: [your SEO-optimized title]
CATEGORY: [single category name]
KEYWORDS: [keyword1, keyword2, keyword3, ...]
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
