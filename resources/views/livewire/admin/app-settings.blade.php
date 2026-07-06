<div>
    <div class="section-header">
        <h1 class="text-sm font-semibold" style="color: var(--text-primary);">App Settings</h1>
        <p class="text-xs" style="color: var(--text-muted);">Configure your application settings</p>
    </div>

    @if($message)
        <div class="mb-4 p-3 rounded-lg text-xs" style="background-color: var(--success-muted); border: 1px solid var(--success); color: var(--success);">
            {{ $message }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-4">
        @foreach($groupedSettings as $group => $groupSettings)
            <div class="card">
                <h2 class="text-xs font-medium uppercase tracking-wide mb-4 capitalize" style="color: var(--text-muted);">
                    {{ str_replace('_', ' ', $group) }} Settings
                </h2>

                <div class="space-y-4">
                    @foreach($groupSettings as $setting)
                        <div>
                            <label class="label">{{ $setting['label'] }}</label>

                            @if($setting['type'] === 'textarea')
                                <textarea
                                    wire:model="settings.{{ $setting['key'] }}"
                                    class="input text-sm min-h-[80px]"
                                    placeholder="{{ $setting['label'] }}"
                                ></textarea>
                            @elseif($setting['type'] === 'number')
                                <input
                                    type="number"
                                    wire:model="settings.{{ $setting['key'] }}"
                                    class="input text-sm"
                                    placeholder="{{ $setting['label'] }}"
                                >
                            @elseif($setting['type'] === 'boolean')
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        wire:model="settings.{{ $setting['key'] }}"
                                        class="w-4 h-4 rounded"
                                        style="border-color: var(--border-color);"
                                    >
                                    <span class="text-sm" style="color: var(--text-secondary);">Enabled</span>
                                </label>
                            @elseif($setting['type'] === 'select' && $setting['key'] === 'ai_provider')
                                <select
                                    wire:model="settings.{{ $setting['key'] }}"
                                    class="input text-sm"
                                >
                                    <option value="gemini">Google Gemini</option>
                                    <option value="groq">Groq AI</option>
                                    <option value="mistral">Mistral AI</option>
                                </select>
                            @else
                                <input
                                    type="text"
                                    wire:model="settings.{{ $setting['key'] }}"
                                    class="input text-sm"
                                    placeholder="{{ $setting['label'] }}"
                                >
                            @endif

                            @if($setting['description'])
                                <p class="mt-1.5 text-xs" style="color: var(--text-muted);">{{ $setting['description'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="btn-primary text-sm">
                <span wire:loading.remove wire:target="save">Save Settings</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
