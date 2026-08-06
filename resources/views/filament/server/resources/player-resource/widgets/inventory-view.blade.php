@php
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
        
        // Gestion des IDs simples (ex: "stone" au lieu de "minecraft:stone")
        $modid = (count($parts) > 1) ? $parts[0] : 'minecraft';
        $itemName = (count($parts) > 1) ? $parts[1] : $parts[0];

        if (!isset($langCache[$modid])) {
            // URL dynamique selon si c'est Minecraft Vanilla ou un Mod
            $url = "https://cdn.robotboss.org/modded_textures/assets/$modid/lang/en_us.json";
            
            $jsonRaw = @file_get_contents($url);
            $langCache[$modid] = $jsonRaw ? json_decode($jsonRaw, true) : [];
        }

        $d = $langCache[$modid];
        
        // Minecraft Vanilla utilise souvent juste "item.minecraft.iron_sword"
        // On teste les formats standards de clés de traduction
        return $d["item.$modid.$itemName"] ?? 
               $d["block.$modid.$itemName"] ?? 
               $d["item.minecraft.$itemName"] ?? 
               $d["block.minecraft.$itemName"] ?? 
               ucfirst(str_replace('_', ' ', $itemName)); // Nom par défaut propre
    };
@endphp
<div class="inv-center">
<div class="inv-wrapper p-4 border border-gray-200 dark:border-white/10 rounded-xl bg-gray-50 dark:bg-white/5 w-fit max-w-full text-gray-900 dark:text-white">
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
                    <img src="https://cdn.robotboss.org/modded_textures/assets/{{ $m }}/textures/item/{{ $i }}.png"
                         class="inv-img rendering-pixelated"
                         onerror="this.onerror=null;this.src='https://cdn.robotboss.org/modded_textures/assets/{{ $m }}/textures/block/{{ $i }}.png'" />
                    @if ($c > 1)
                        <span class="inv-count absolute bottom-0 right-0 font-bold px-1 pt-0.5 rounded-tl-sm shadow-sm leading-none bg-gray-200/80 dark:bg-black/60 text-[10px]">
                            {{ $c }}
                        </span>
                    @endif
                @else
                   <span class="text-gray-300 dark:text-gray-600 text-xs text-center leading-none opacity-50">O</span>
                @endif
            </div>
    </div>
    {{-- Inventory --}}
    <div class="inv-inv border-gray-200 dark:border-white/10 flex flex-col gap-2 shrink-0">
        {{-- Slots internes (Lignes 1-3) --}}
        @for ($row = 0; $row < 3; $row++)
            <div class="flex gap-2 slot-row">
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
                            @endphp
                            <img src="https://cdn.robotboss.org/modded_textures/assets/{{ $m }}/textures/item/{{ $i }}.png" 
                                 class="inv-img rendering-pixelated"
                                 onerror="this.onerror=null;this.src='https://cdn.robotboss.org/modded_textures/assets/{{ $m }}/textures/block/{{ $i }}.png'" />
                            
                            @if (($item['count'] ?? 1) > 1)
                                <span class="inv-count absolute bottom-0 right-0 font-bold px-1 pt-0.5 rounded-tl-sm shadow-sm leading-none bg-gray-200/80 dark:bg-black/60 text-[10px]">
                                    {{ $item['count'] }}
                                </span>
                            @endif

                            <div class="absolute bottom-full mb-1 hidden group-hover:block z-20 whitespace-nowrap bg-gray-900 text-white text-xs px-2 py-1 rounded shadow-lg">
                                {{ $name }}
                            </div>
                        @endif
                    </div>
                @endfor
            </div>
        @endfor

        {{-- Hotbar --}}
        <div class="flex gap-2 mt-2 slot-row">
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
                        @endphp
                        <img src="https://cdn.robotboss.org/modded_textures/assets/{{ $m }}/textures/item/{{ $i }}.png" 
                             class="inv-img rendering-pixelated"
                             onerror="this.onerror=null;this.src='https://cdn.robotboss.org/modded_textures/assets/{{ $m }}/textures/block/{{ $i }}.png'" />
                        @if (($item['count'] ?? 1) > 1)
                            <span class="inv-count absolute bottom-0 right-0 font-bold px-1 pt-0.5 rounded-tl-sm shadow-sm leading-none bg-gray-200/80 dark:bg-black/60 text-[10px]">
                                {{ $item['count'] }}
                            </span>
                        @endif
                        <div class="absolute bottom-full mb-1 hidden group-hover:block z-20 whitespace-nowrap bg-gray-900 text-white text-xs px-2 py-1 rounded shadow-lg">
                             {{ $name }}
                        </div>
                    @endif
                </div>
            @endfor
        </div>
    </div>

    {{-- Armor & Offhand --}}
    <div class="inv-armor flex gap-2 border-gray-200 dark:border-white/10 shrink-0">
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
                    @endphp
                    <img src="https://cdn.robotboss.org/modded_textures/assets/{{ $m }}/textures/item/{{ $i }}.png"
                         class="inv-img rendering-pixelated"
                         onerror="this.onerror=null;this.src='https://cdn.robotboss.org/modded_textures/assets/{{ $m }}/textures/block/{{ $i }}.png'" />
                @else
                   <span class="text-gray-300 dark:text-gray-600 text-xs text-center leading-none opacity-50">{{ strtoupper(substr($placeholder, 0, 1)) }}</span>
                @endif
            </div>
        @endforeach
    </div>
</div>
</div>

<style>
    .inv-offhand {
        height: 100%;
        display: flex;
        align-content: center;
        flex-wrap: wrap;
    }
    .inv-center{
        width: 100%;
        display: flex;
        justify-content: center;
    }
    .inv-wrapper {
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        gap: 1rem;
        overflow: visible;
    }
    .inv-slot {
        width: 2.5rem;
        height: 2.5rem;
    }
    .inv-img {
        width: 1.75rem;
        height: 1.75rem;
    }
    .inv-count {
        font-size: 9px;
        color: #1f2937;
        background-color: #e5e7eb;
    }
    .dark .inv-count {
        color: #ffffff;
        background-color: rgba(0, 0, 0, 0.6);
    }
    .inv-off-text {
        font-size: 10px;
    }
    .inv-armor {
        flex-direction: column;
        padding-left: 1rem;
        border-left-width: 1px;
    }

    .inv-inv{
        padding-left: 1rem;
        border-left-width: 1px;
    }

    .offhand-slot {
        margin-top: 0.5rem;
        margin-left: 0;
    }
    .slot-row {
        gap: 0.5rem;
    }

    @media (max-width: 640px) {
        .inv-wrapper {
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 0.75rem;
        }
        .inv-slot {
            width: 2rem;
            height: 2rem;
        }
        .inv-img {
            width: 1.5rem;
            height: 1.5rem;
        }
        .inv-armor {
            flex-direction: row;
            padding-left: 0;
            border-left-width: 0;
            padding-top: 0.5rem;
            border-top-width: 1px;
            margin-top: 0.5rem;
            justify-content: center;
            width: 100%;
        }
        .offhand-slot {
            margin-top: 0;
            margin-left: 0.5rem;
        }
        .slot-row {
            gap: 0.25rem;
        }
    }

    .rendering-pixelated {
        image-rendering: pixelated;
        image-rendering: -moz-crisp-edges;
        image-rendering: crisp-edges;
    }
</style>