<div>
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold" style="color: var(--text-primary);">App Settings</h1>
        <p class="mt-2" style="color: var(--text-secondary);">Configure your application settings</p>
    </div>

    @if($message)
        <div class="mb-6 p-4 bg-emerald-500/20 border border-emerald-500/30 rounded-xl text-emerald-400">
            {{ $message }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-8">
        @foreach($groupedSettings as $group => $groupSettings)
            <div class="card">
                <h2 class="text-lg font-semibold mb-6 capitalize" style="color: var(--text-primary);">
                    {{ str_replace('_', ' ', $group) }} Settings
                </h2>

                <div class="space-y-6">
                    @foreach($groupSettings as $setting)
                        <div>
                            <label class="label">{{ $setting['label'] }}</label>
                            
                            @if($setting['type'] === 'textarea')
                                <textarea 
                                    wire:model="settings.{{ $setting['key'] }}"
                                    class="input min-h-[100px]"
                                    placeholder="{{ $setting['label'] }}"
                                ></textarea>
                            @elseif($setting['type'] === 'number')
                                <input 
                                    type="number" 
                                    wire:model="settings.{{ $setting['key'] }}"
                                    class="input"
                                    placeholder="{{ $setting['label'] }}"
                                >
                            @elseif($setting['type'] === 'boolean')
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        wire:model="settings.{{ $setting['key'] }}"
                                        class="w-5 h-5 rounded"
                                    >
                                    <span style="color: var(--text-secondary);">Enabled</span>
                                </label>
                            @elseif($setting['type'] === 'select' && $setting['key'] === 'ai_provider')
                                <select 
                                    wire:model="settings.{{ $setting['key'] }}"
                                    class="input"
                                >
                                    <option value="gemini">Google Gemini</option>
                                    <option value="groq">Groq AI</option>
                                    <option value="mistral">Mistral AI</option>
                                </select>
                            @else
                                <input 
                                    type="text" 
                                    wire:model="settings.{{ $setting['key'] }}"
                                    class="input"
                                    placeholder="{{ $setting['label'] }}"
                                >
                            @endif

                            @if($setting['description'])
                                <p class="mt-2 text-sm" style="color: var(--text-muted);">{{ $setting['description'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="btn-primary">
                <span wire:loading.remove wire:target="save">Save Settings</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
