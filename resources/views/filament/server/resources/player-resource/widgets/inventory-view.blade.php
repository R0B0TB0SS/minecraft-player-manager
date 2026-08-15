@php

    if(env('MC_PLAYER_MANAGER_CUSTOM_FILES') != '') {
        $textureurl= env('MC_PLAYER_MANAGER_CUSTOM_FILES');
    }else{
        $textureurl= "https://cdn.robotboss.org/modded_textures/";
    }

    $inventory = $getState() ?? [];

    // Cache statique pour les performances
    static $langCache = [];

    $getItem = function($slot) use ($inventory) {
        foreach ($inventory as $item) {
            if (isset($item['slot']) && $item['slot'] == $slot) return $item;
        }
        return null;
    };

    $getName = function($item) use (&$langCache) {
        if (!$item || !isset($item['id'])) return null;

        $id = $item['id'];
        $parts = explode(":", $id);

        $modid = (count($parts) > 1) ? $parts[0] : 'minecraft';
        $itemName = (count($parts) > 1) ? $parts[1] : $parts[0];

        if (!isset($langCache[$modid])) {
            $url = "https://cdn.robotboss.org/modded_textures/assets/$modid/lang/en_us.json";

            $jsonRaw = @file_get_contents($url);
            $langCache[$modid] = $jsonRaw ? json_decode($jsonRaw, true) : [];
        }

        $d = $langCache[$modid];

        return $d["item.$modid.$itemName"] ?? 
               $d["block.$modid.$itemName"] ?? 
               $d["item.minecraft.$itemName"] ?? 
               $d["block.minecraft.$itemName"] ?? 
               ucfirst(str_replace('_', ' ', $itemName));
    };
@endphp

<div class="inv-center">
    <div class="inv-wrapper p-3 sm:p-4 border border-gray-200 dark:border-white/10 rounded-xl bg-gray-50 dark:bg-white/5 max-w-full text-gray-900 dark:text-white">
        
        {{-- Offhand --}}
        <div class="inv-offhand flex gap-2 shrink-0">
            @php 
                $item = $getItem(-106); 
                $name = $getName($item);
            @endphp
            <div class="inv-slot relative flex items-center justify-center bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-md shadow-inner group shrink-0"
                 title="{{ $name ?? 'Offhand' }}">
                @if ($item && isset($item['id']))
                    @php 
                        $p = explode(':', $item['id']); 
                        $m = (count($p) > 1) ? $p[0] : 'minecraft'; 
                        $i = (count($p) > 1) ? $p[1] : $p[0];
                        $c = $item['count'] ?? 1;
                    @endphp
                    <img src="{{ $textureurl }}assets/{{ $m }}/textures/item/{{ $i }}.png"
                         class="inv-img rendering-pixelated"
                         onerror="this.onerror=null;this.src='{{ $textureurl }}assets/{{ $m }}/textures/block/{{ $i }}.png'" />
                    @if ($c > 1)
                        <span class="inv-count absolute bottom-0 right-0 font-bold px-1 rounded-tl-sm shadow-sm leading-none bg-gray-200/80 dark:bg-black/60">
                            {{ $c }}
                        </span>
                    @endif
                @else
                   <span class="text-gray-300 dark:text-gray-600 text-xs text-center leading-none opacity-50">O</span>
                @endif
            </div>
        </div>

        {{-- Inventory Grid --}}
        <div class="inv-inv flex flex-col gap-1.5 sm:gap-2 shrink-0">
            {{-- Slots internes (Lignes 1-3) --}}
            @for ($row = 0; $row < 3; $row++)
                <div class="flex gap-1.5 sm:gap-2 slot-row">
                    @for ($col = 0; $col < 9; $col++)
                        @php 
                            $slotId = 9 + ($row * 9) + $col;
                            $item = $getItem($slotId);
                            $name = $getName($item);
                        @endphp
                        <div class="inv-slot relative flex items-center justify-center bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm transition hover:ring-2 hover:ring-primary-500 hover:border-primary-500 group shrink-0"
                             title="{{ $name ?? 'Vide' }}">
                            @if ($item && isset($item['id']))
                                @php 
                                    $p = explode(':', $item['id']); 
                                    $m = (count($p) > 1) ? $p[0] : 'minecraft'; 
                                    $i = (count($p) > 1) ? $p[1] : $p[0];
                                    $it = explode('waxed_', $i)[1] ?? $i;
                                @endphp
                                <img src="{{ $textureurl }}assets/{{ $m }}/textures/item/{{ $it }}.png" 
                                     class="inv-img rendering-pixelated"
                                     onerror="this.onerror=null;this.src='{{ $textureurl }}assets/{{ $m }}/textures/block/{{ $it }}.png'" />

                                @if (($item['count'] ?? 1) > 1)
                                    <span class="inv-count absolute bottom-0 right-0 font-bold px-1 rounded-tl-sm shadow-sm leading-none bg-gray-200/80 dark:bg-black/60">
                                        {{ $item['count'] }}
                                    </span>
                                @endif

                                <div class="absolute bottom-full mb-1 hidden group-hover:block z-20 whitespace-nowrap bg-gray-900 text-white text-xs px-2 py-1 rounded shadow-lg pointer-events-none">
                                    {{ $name }}
                                </div>
                            @endif
                        </div>
                    @endfor
                </div>
            @endfor

            {{-- Hotbar --}}
            <div class="flex gap-1.5 sm:gap-2 mt-1 sm:mt-2 slot-row">
                @for ($col = 0; $col < 9; $col++)
                    @php 
                        $item = $getItem($col);
                        $name = $getName($item);
                    @endphp
                    <div class="inv-slot relative flex items-center justify-center bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-md shadow-sm transition hover:ring-2 hover:ring-primary-500 hover:border-primary-500 group shrink-0"
                          title="{{ $name ?? 'Vide' }}">
                        @if ($item && isset($item['id']))
                            @php 
                                $p = explode(':', $item['id']); 
                                $m = (count($p) > 1) ? $p[0] : 'minecraft'; 
                                $i = (count($p) > 1) ? $p[1] : $p[0];
                                $it = explode('waxed_', $i)[1] ?? $i;
                            @endphp
                            <img src="{{ $textureurl }}assets/{{ $m }}/textures/item/{{ $it }}.png" 
                                 class="inv-img rendering-pixelated"
                                 onerror="this.onerror=null;this.src='{{ $textureurl }}assets/{{ $m }}/textures/block/{{ $it }}.png'" />
                            @if (($item['count'] ?? 1) > 1)
                                <span class="inv-count absolute bottom-0 right-0 font-bold px-1 rounded-tl-sm shadow-sm leading-none bg-gray-200/80 dark:bg-black/60">
                                    {{ $item['count'] }}
                                </span>
                            @endif
                            <div class="absolute bottom-full mb-1 hidden group-hover:block z-20 whitespace-nowrap bg-gray-900 text-white text-xs px-2 py-1 rounded shadow-lg pointer-events-none">
                                 {{ $name }}
                            </div>
                        @endif
                    </div>
                @endfor
            </div>
        </div>

        {{-- Armor --}}
        <div class="inv-armor flex gap-1.5 sm:gap-2 shrink-0">
            @foreach ([103, 102, 101, 100] as $armorSlot)
                @php 
                    $item = $getItem($armorSlot); 
                    $name = $getName($item);
                    $icons = [103 => 'helmet', 102 => 'chestplate', 101 => 'leggings', 100 => 'boots'];
                    $placeholder = $icons[$armorSlot] ?? 'armor';
                @endphp
                <div class="inv-slot relative flex items-center justify-center bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-md shadow-inner group shrink-0"
                     title="{{ $name ?? ucfirst($placeholder) }}">
                    @if ($item && isset($item['id']))
                        @php 
                            $p = explode(':', $item['id']); 
                            $m = (count($p) > 1) ? $p[0] : 'minecraft'; 
                            $i = (count($p) > 1) ? $p[1] : $p[0];
                            $it = explode('waxed_', $i)[1] ?? $i;
                        @endphp
                        <img src="{{ $textureurl }}assets/{{ $m }}/textures/item/{{ $it }}.png"
                             class="inv-img rendering-pixelated"
                             onerror="this.onerror=null;this.src='{{ $textureurl }}assets/{{ $m }}/textures/block/{{ $it }}.png'" />
                    @else
                       <span class="text-gray-300 dark:text-gray-600 text-xs text-center leading-none opacity-50">{{ strtoupper(substr($placeholder, 0, 1)) }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
    /* Activer le Responsive basé sur le Conteneur Parent (Container Queries) */
    .inv-center {
        width: 100%;
        display: flex;
        justify-content: center;
        container-type: inline-size;
        container-name: inventory;
    }

    .inv-wrapper {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        width: fit-content;
        box-sizing: border-box;
    }

    /* Tailles fluides des slots (Min 1.4rem ~ Max 2.5rem) */
    .inv-slot {
        width: clamp(1.4rem, 5.2cqw, 2.5rem);
        height: clamp(1.4rem, 5.2cqw, 2.5rem);
    }

    .inv-img {
        width: 70%;
        height: 70%;
        object-fit: contain;
    }

    .inv-count {
        font-size: clamp(7px, 1.8cqw, 10px);
        padding-top: 1px;
        color: #1f2937;
    }

    .dark .inv-count {
        color: #ffffff;
    }

    .inv-offhand {
        align-items: center;
        justify-content: center;
    }

    .inv-inv {
        padding-left: 0.75rem;
        border-left: 1px solid rgba(156, 163, 175, 0.3);
    }

    .inv-armor {
        flex-direction: column;
        padding-left: 0.75rem;
        border-left: 1px solid rgba(156, 163, 175, 0.3);
    }

    /* Rendu des pixels Minecraft */
    .rendering-pixelated {
        image-rendering: pixelated;
        image-rendering: -moz-crisp-edges;
        image-rendering: crisp-edges;
    }

    /* --- BREAKPOINTS BASÉS SUR LE CONTENEUR --- */
    
    /* Si la carte fait moins de 520px de large : Basculer l'Offhand et l'Armure au-dessus/en-dessous */
    @container inventory (max-width: 520px) {
        .inv-wrapper {
            flex-direction: column;
            gap: 0.5rem;
        }

        .inv-inv {
            padding-left: 0;
            border-left: none;
        }

        .inv-offhand {
            width: 100%;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(156, 163, 175, 0.3);
        }

        .inv-armor {
            flex-direction: row;
            padding-left: 0;
            border-left: none;
            padding-top: 0.5rem;
            border-top: 1px solid rgba(156, 163, 175, 0.3);
            width: 100%;
            justify-content: center;
        }
    }

    /* Si le conteneur est très petit (< 320px) : Réduire les espacements */
    @container inventory (max-width: 320px) {
        .slot-row {
            gap: 0.2rem !important;
        }
        .inv-wrapper {
            padding: 0.5rem !important;
        }
    }
</style>