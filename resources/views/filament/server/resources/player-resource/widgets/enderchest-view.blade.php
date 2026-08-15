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

<div class="ec-center">
    <div class="ec-wrapper p-2 sm:p-4 border border-gray-200 dark:border-white/10 rounded-xl bg-gray-50 dark:bg-white/5 w-fit max-w-full text-gray-900 dark:text-white">
        <div class="flex flex-col gap-1.5 sm:gap-2 shrink-0">
            {{-- Ender Chest (27 slots : 3 lignes x 9 colonnes) --}}
            @for ($row = 0; $row < 3; $row++)
                <div class="flex ec-row">
                    @for ($col = 0; $col < 9; $col++)
                        @php 
                            $slotId = ($row * 9) + $col;
                            $item = $getItem($slotId);
                            $name = $getName($item);
                        @endphp
                        <div class="ec-slot relative flex items-center justify-center bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm transition hover:ring-2 hover:ring-primary-500 hover:border-primary-500 group shrink-0"
                             title="{{ $name ?? 'Vide' }}">
                            @if ($item && isset($item['id']))
                                @php 
                                    $p = explode(':', $item['id']); 
                                    $m = (count($p) > 1) ? $p[0] : 'minecraft'; 
                                    $i = (count($p) > 1) ? $p[1] : $p[0];
                                @endphp
                                <img src="{{ $textureurl }}assets/{{ $m }}/textures/item/{{ $i }}.png" 
                                     class="ec-img rendering-pixelated select-none"
                                     onerror="this.onerror=null;this.src='{{ $textureurl }}assets/{{ $m }}/textures/block/{{ $i }}.png'" />

                                @if (($item['count'] ?? 1) > 1)
                                    <span class="ec-count absolute bottom-0 right-0 font-bold rounded-tl-sm shadow-sm leading-none">
                                        {{ $item['count'] }}
                                    </span>
                                @endif

                                <div class="absolute bottom-full mb-1 hidden group-hover:block z-30 whitespace-nowrap bg-gray-900 text-white text-xs px-2 py-1 rounded shadow-lg pointer-events-none">
                                    {{ $name }}
                                </div>
                            @endif
                        </div>
                    @endfor
                </div>
            @endfor
        </div>
    </div>
</div>

<style>
    /* Structure de base de l'Ender Chest */
    .ec-center {
        width: 100%;
        display: flex;
        justify-content: center;
        container-type: inline-size; /* Adapte la taille selon la carte parente */
        overflow-x: auto;
        padding: 0.25rem 0;
    }

    .ec-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        max-width: 100%;
    }

    .ec-row {
        gap: 0.5rem;
    }

    /* Tailles des slots par défaut */
    .ec-slot {
        width: 2.5rem;
        height: 2.5rem;
    }

    .ec-img {
        width: 1.75rem;
        height: 1.75rem;
    }

    .ec-count {
        font-size: 10px;
        padding: 1px 3px;
        color: #1f2937;
        background-color: rgba(229, 231, 235, 0.9);
    }

    .dark .ec-count {
        color: #ffffff;
        background-color: rgba(0, 0, 0, 0.7);
    }

    /* --- RESPONSIVE EN FONCTION DE LA CARTE (CONTAINER QUERIES) --- */

    /* Carte de largeur moyenne (< 450px) */
    @container (max-width: 450px) {
        .ec-slot {
            width: 2rem;
            height: 2rem;
        }
        .ec-img {
            width: 1.35rem;
            height: 1.35rem;
        }
        .ec-count {
            font-size: 8px;
            padding: 0.5px 2px;
        }
        .ec-row {
            gap: 0.25rem;
        }
    }

    /* Carte étroite / Smartphone (< 360px) */
    @container (max-width: 360px) {
        .ec-slot {
            width: 1.6rem;
            height: 1.6rem;
            border-radius: 0.25rem;
        }
        .ec-img {
            width: 1.1rem;
            height: 1.1rem;
        }
        .ec-count {
            font-size: 7px;
            padding: 0px 1px;
        }
        .ec-row {
            gap: 0.15rem;
        }
    }

    .rendering-pixelated {
        image-rendering: pixelated;
        image-rendering: -moz-crisp-edges;
        image-rendering: crisp-edges;
    }
</style>