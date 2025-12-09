<?php

namespace App\Livewire\User;

use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Settings')]
class Settings extends Component
{
    public string $geminiApiKey = '';
    public bool $showApiKey = false;
    public ?string $message = null;
    public ?string $error = null;

    public function mount(): void
    {
        $user = auth()->user();
        if ($user->gemini_api_key) {
            // Show masked version
            $this->geminiApiKey = $user->gemini_api_key;
        }
    }

    public function saveApiKey(): void
    {
        $this->message = null;
        $this->error = null;

        $user = auth()->user();

        // Only subscribers can use personal API key
        if (!$user->isSubscribed()) {
            $this->error = 'You need an active subscription to use your own API key.';
            return;
        }

        if (empty(trim($this->geminiApiKey))) {
            // Clear API key
            $user->update(['gemini_api_key' => null]);
            $this->geminiApiKey = '';
            $this->message = 'API key removed. You will now use the shared API.';
            return;
        }

        // Validate the API key by making a test request
        try {
            $response = Http::timeout(10)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . trim($this->geminiApiKey),
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => 'Say "hello" in one word.'],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => 10,
                    ],
                ]
            );

            if (!$response->successful()) {
                $errorMessage = $response->json('error.message') ?? 'Invalid API key';
                $status = $response->json('error.status') ?? '';
                
                // Quota errors mean the key IS valid, just quota exceeded
                if (str_contains(strtolower($errorMessage), 'quota') || 
                    str_contains(strtolower($errorMessage), 'rate') ||
                    str_contains(strtolower($errorMessage), 'exceeded') ||
                    $response->status() === 429) {
                    // API key is valid but quota exceeded - still save it
                    $user->update(['gemini_api_key' => trim($this->geminiApiKey)]);
                    $this->message = 'API key saved! Note: Your quota is currently exceeded, but the key is valid.';
                    return;
                }
                
                // Real authentication/permission errors
                $this->error = 'Invalid API key: ' . $errorMessage;
                return;
            }

            // API key is valid, save it
            $user->update(['gemini_api_key' => trim($this->geminiApiKey)]);
            $this->message = 'API key saved successfully! Your generations will now use your personal API key.';

        } catch (\Exception $e) {
            $this->error = 'Failed to validate API key: ' . $e->getMessage();
        }
    }

    public function toggleShowApiKey(): void
    {
        $this->showApiKey = !$this->showApiKey;
    }

    public function render()
    {
        $user = auth()->user();
        
        return view('livewire.user.settings', [
            'isSubscribed' => $user->isSubscribed(),
            'hasApiKey' => $user->hasPersonalApiKey(),
        ]);
    }
}
