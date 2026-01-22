<?php

namespace App\Services\AI;

use App\Models\ApiKey;

interface AIProviderInterface
{
    /**
     * Generate metadata for an image
     */
    public function generateMetadata(
        ApiKey $apiKey,
        string $imageData,
        string $mimeType,
        string $prompt,
        string $mode = 'fast',
        ?string $model = null
    ): array;

    /**
     * Get the provider name
     */
    public function getProviderName(): string;

    /**
     * Get available models for this provider
     */
    public function getAvailableModels(): array;

    /**
     * Get the default model
     */
    public function getDefaultModel(): string;
}
