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
        'heart_full' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAYAAADgkQYQAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAZdEVYdFNvZnR3YXJlAFBhaW50Lk5FVCA1LjEuMTITAUd0AAAAuGVYSWZJSSoACAAAAAUAGgEFAAEAAABKAAAAGwEFAAEAAABSAAAAKAEDAAEAAAACAAAAMQECABEAAABaAAAAaYcEAAEAAABsAAAAAAAAAGAAAAABAAAAYAAAAAEAAABQYWludC5ORVQgNS4xLjEyAAADAACQBwAEAAAAMDIzMAGgAwABAAAAAQAAAAWgBAABAAAAlgAAAAAAAAACAAEAAgAEAAAAUjk4AAIABwAEAAAAMDEwMAAAAADZp5qVybcLXwAAAFVJREFUKFOFjsENwDAIA01W8P5j5JWuxAzuI9DSpFVPQrLMCQHcKGbNsCxFzsIdNQOwdgm9TzuEkmX1yhvmjgYAx7oJ1l6DlMoM8vH8Jn4Jya+QbMIJ9LowOrzh5PMAAAAASUVORK5CYII=',
        
        // half.png
        'heart_half' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAMAAADXT/YiAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAASUExURQAAAP8TEygoKP/IyLsTEwAAAJuR1IsAAAAGdFJOU///////ALO/pL8AAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAZdEVYdFNvZnR3YXJlAFBhaW50Lk5FVCA1LjEuMTITAUd0AAAAuGVYSWZJSSoACAAAAAUAGgEFAAEAAABKAAAAGwEFAAEAAABSAAAAKAEDAAEAAAACAAAAMQECABEAAABaAAAAaYcEAAEAAABsAAAAAAAAAGAAAAABAAAAYAAAAAEAAABQYWludC5ORVQgNS4xLjEyAAADAACQBwAEAAAAMDIzMAGgAwABAAAAAQAAAAWgBAABAAAAlgAAAAAAAAACAAEAAgAEAAAAUjk4AAIABwAEAAAAMDEwMAAAAADZp5qVybcLXwAAADBJREFUGFc1ywEKAEAIAsHVzv9/+TAIQkYwEuiFIGG3aSTbgHR6p5S7S+n9LTdz8QEc9ADGPb6/cQAAAABJRU5ErkJggg==',
        
        // container.png (empty heart)
        'heart_empty' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAYAAADgkQYQAAAAM0lEQVR4XmNgQID/UIzOhoP/GhoaYIzOxlCADcMUYkiQrQinQmQFWBViUwADBBXAAIYCAEOqRbsmUy3lAAAAAElFTkSuQmCC',
        
        // food_full.png
        'food_full' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAMAAADXT/YiAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAhUExURQAAANQqKrIYGN+xj7iEWJ1tQ2E8G3tRLeLVqv/33AAAAIa/kXEAAAALdFJOU/////////////8ASk8B8gAAAAlwSFlzAAAOwwAADsMBx2+oZAAAABl0RVh0U29mdHdhcmUAUGFpbnQuTkVUIDUuMS4xMhMBR3QAAAC4ZVhJZklJKgAIAAAABQAaAQUAAQAAAEoAAAAbAQUAAQAAAFIAAAAoAQMAAQAAAAIAAAAxAQIAEQAAAFoAAABphwQAAQAAAGwAAAAAAAAAYAAAAAEAAABgAAAAAQAAAFBhaW50Lk5FVCA1LjEuMTIAAAMAAJAHAAQAAAAwMjMwAaADAAEAAAABAAAABaAEAAEAAACWAAAAAAAAAAIAAQACAAQAAABSOTgAAgAHAAQAAAAwMTAwAAAAANmnmpXJtwtfAAAANElEQVQYVz3MQRIAEAzF0JRW+fc/sClGVm8VJFCFsHYI1s2L0MyjiBgZcVTMKwGTdxDrC21TLAHpPxuvbwAAAABJRU5ErkJggg==',
        
        // food_half.png
        'food_half' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAMAAADXT/YiAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAnUExURQAAACgoKLIYGOjEpNQqKteXeriEWJ1tQ2E8G3tRLeLVqv/33AAAAL36wlgAAAANdFJOU////////////////wA96CKGAAAACXBIWXMAAA7DAAAOwwHHb6hkAAAAGXRFWHRTb2Z0d2FyZQBQYWludC5ORVQgNS4xLjEyEwFHdAAAALhlWElmSUkqAAgAAAAFABoBBQABAAAASgAAABsBBQABAAAAUgAAACgBAwABAAAAAgAAADEBAgARAAAAWgAAAGmHBAABAAAAbAAAAAAAAABgAAAAAQAAAGAAAAABAAAAUGFpbnQuTkVUIDUuMS4xMgAAAwAAkAcABAAAADAyMzABoAMAAQAAAAEAAAAFoAQAAQAAAJYAAAAAAAAAAgABAAIABAAAAFI5OAACAAcABAAAADAxMDAAAAAA2aealcm3C18AAAA3SURBVBhXPcxZCsAwDAPRkeumm+5/3hDXRF8PBIMNXsMoiqAjiyCdOeg3rpLR/fwy8NIF823hCV8+AjL88T/dAAAAAElFTkSuQmCC',
        
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
