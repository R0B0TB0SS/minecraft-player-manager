@php
    $record = $getRecord();
    $health = $record->health ?? 0;
    $food = $record->food ?? 0;
    
    // Safety clamp 
    $health = min(20, max(0, $health));
    $food = min(20, max(0, $food));
    
    // Embed Base64 Textures directly to enable "portable" display without publishing assets
    $icons = [
        // full.png
        'heart_full' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAYAAADgkQYQAAAAOUlEQVR4XmNgIAX8Fxb+D8LobFQFJ06AJdAxqiIcGK5oNxZJEAaJwxVhU4ihAAZgCnEqgAGCCnABALrRSFdXao4/AAAAAElFTkSuQmCC',
        
        // half.png
        'heart_half' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAYAAADgkQYQAAAAOElEQVR4XmNgIAX8Fxb+D8Lo4nAAVnDiBFgRToUwSbyKdhOjCASQFaLLoQCYQnRxDABSiC5GFAAADKIrK1YPy9UAAAAASUVORK5CYII=',
        
        // container.png (empty heart)
        'heart_empty' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAYAAADgkQYQAAAAM0lEQVR4XmNgQID/UIzOhoP/GhoaYIzOxlCADcMUYkiQrQinQmQFWBViUwADBBXAAIYCAEOqRbsmUy3lAAAAAElFTkSuQmCC',
        
        // food_full.png
        'food_full' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAYAAADgkQYQAAAAUUlEQVR4XmNgIAVc0dL6v0lC4j+6OByAFNzf2P8fRO9oicCuEGQCTMHcXGfcChNtpP9XB+qCFYEwujwcwBSCMLocVvDo6iriFP7/foc4hSAAAGW1LqFA6WVMAAAAAElFTkSuQmCC',
        
        // food_half.png
        'food_half' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAYAAADgkQYQAAAAS0lEQVR4XmNgIBVskpD4jy6GAl4cWfL/ipbWf4IKr0+vAivc0RKBWyHIFBCem+uMWxEIJNpI/68O1MWvCAYeXV1FnML/3+8QpxAEAK9qIcitcVwOAAAAAElFTkSuQmCC',
        
        // food_empty.png
        'food_empty' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAYAAADgkQYQAAAAOElEQVR4XmNgQID/UIwT/NfQ0ABjEBtdEgTgCvApxFBEtEJsikCAKEUgAJNEVohTMQgQNBEE4AoAExkyOMREVxIAAAAASUVORK5CYII=',
    ];
@endphp

<div class="grid grid-cols-[auto_1fr_auto] gap-x-3 gap-y-2 p-2 items-center">
    {{-- Health Row --}}
    <span class="text-sm font-bold text-gray-500 w-16 text-left select-none">{{ __('rbs-minecraft-player-manager::messages.stats.health') }}</span>
    <div class="flex gap-1" title="{{ __('rbs-minecraft-player-manager::messages.stats.health') }}: {{ $health }}/20">
        @for ($i = 1; $i <= 10; $i++)
            @php
                $threshold = $i * 2;
                if ($health >= $threshold) {
                    $state = 'heart_full';
                } elseif ($health >= ($threshold - 1)) {
                    $state = 'heart_half';
                } else {
                    $state = 'heart_empty';
                }
            @endphp
            <img src="{{ $icons[$state] }}" class="w-6 h-6 rendering-pixelated drop-shadow-sm" alt="heart" />
        @endfor
    </div>
    <span class="text-sm text-gray-400 font-mono">({{ $health }})</span>

    {{-- Food Row --}}
    <span class="text-sm font-bold text-gray-500 w-16 text-left select-none">{{ __('rbs-minecraft-player-manager::messages.stats.food') }}</span>
    <div class="flex gap-1" title="{{ __('rbs-minecraft-player-manager::messages.stats.food') }}: {{ $food }}/20">
             @for ($i = 1; $i <= 10; $i++)
                @php
                    $threshold = $i * 2;
                    if ($food >= $threshold) {
                        $state = 'food_full';
                    } elseif ($food >= ($threshold - 1)) {
                        $state = 'food_half';
                    } else {
                        $state = 'food_empty';
                    }
                @endphp
                <img src="{{ $icons[$state] }}" class="w-6 h-6 rendering-pixelated drop-shadow-sm" alt="food" />
             @endfor
    </div>
    <span class="text-sm text-gray-400 font-mono">({{ $food }})</span>
</div>

<style>
    .rendering-pixelated {
        image-rendering: pixelated;
        image-rendering: -moz-crisp-edges;
        image-rendering: crisp-edges;
    }
</style>
