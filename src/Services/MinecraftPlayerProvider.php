<?php


namespace R0B0TB0SS\GamePlayerManager\Services;

use App\Models\Server;
use Illuminate\Support\Facades\Log;
use App\Repositories\Wings\DaemonCommandRepository;
use App\Repositories\Daemon\DaemonFileRepository;

use R0B0TB0SS\GamePlayerManager\Services\Nbt\NbtService;

class MinecraftPlayerProvider implements GamePlayerService
{ 
    private $nbtService;

    public function __construct() {
        $this->nbtService = new NbtService();
    }
    public function sendRconCommand(string $serverId, string $command): ?string
    {
        $server = Server::where('uuid', $serverId)->first();
        if (!$server) return null;

        try {
        // Retourne le r�sultat brut (ex: la liste des joueurs pour la commande "list")
            return $server->send($command);
        } catch (\Exception $e) {
            Log::error("RCON Error: " . $e->getMessage());
            return null;
        }
    }

    public function dossierExiste(Server $server, string $dossier): bool
    {
        /** @var DaemonFileRepository $repository */
        $repository = app(DaemonFileRepository::class)->setServer($server);

        try {
            $repository->getDirectory($dossier);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getPlayers(string $serverId): array
    {
        // Use 'where' for UUID lookup and eager load allocation
        $server = Server::where('uuid', $serverId)->with('allocation')->first();
        if (!$server) {
            return [];
        }

        $allPlayers = [];

        /** @var \App\Repositories\Daemon\DaemonFileRepository $fileRepository */
        $fileRepository = app(\App\Repositories\Daemon\DaemonFileRepository::class);
        $fileRepository->setServer($server);

        // 1. Get List of OPs from ops.json
        $opNames = [];
        try {
            $opsContent = $fileRepository->getContent('ops.json');
            $ops = json_decode($opsContent, true);
            if (is_array($ops)) {
                foreach ($ops as $op) {
                    $name = $op['name'] ?? null;
                    if ($name) {
                        $lowerName = strtolower($name);
                        $opNames[$lowerName] = true;
                        // Add to all players list
                        if (!isset($allPlayers[$lowerName])) {
                            $allPlayers[$lowerName] = [
                                'id' => $name,
                                'name' => $name,
                                'online' => false,
                                'is_op' => true,
                                'is_banned' => false,
                            ];
                        } else {
                            $allPlayers[$lowerName]['is_op'] = true;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore file read errors
        }

        // 2. Get List of Banned Players from banned-players.json
        try {
            $bannedContent = $fileRepository->getContent('banned-players.json');
            $banned = json_decode($bannedContent, true);
            if (is_array($banned)) {
                foreach ($banned as $ban) {
                    $name = $ban['name'] ?? null;
                    if ($name) {
                        $lowerName = strtolower($name);
                        // Add to all players list
                        if (!isset($allPlayers[$lowerName])) {
                            $allPlayers[$lowerName] = [
                                'id' => $name,
                                'name' => $name,
                                'online' => false,
                                'is_op' => isset($opNames[$lowerName]),
                                'is_banned' => true,
                            ];
                        } else {
                            $allPlayers[$lowerName]['is_banned'] = true;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore file read errors
        }

        // 3. Get List of Known Players from usercache.json (Historical/Offline)
        try {
            $cacheContent = $fileRepository->getContent('usercache.json');

            $cachedPlayers = json_decode($cacheContent, true);
            if (is_array($cachedPlayers)) {
                foreach ($cachedPlayers as $player) {
                    $name = $player['name'] ?? null;
                    if ($name) {
                        $lowerName = strtolower($name);
                        // Add to all players list if not exists
                        // If exists, it means they are OP or Banned already, so we just skip updating status
                        if (!isset($allPlayers[$lowerName])) {
                            $allPlayers[$lowerName] = [
                                'id' => $name,
                                'name' => $name,
                                'online' => false,
                                'is_op' => isset($opNames[$lowerName]),
                                'is_banned' => false,
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // usercache.json might not exist on new servers
        }

        // 4. Online Players via Query
        $onlinePlayers = $this->getOnlinePlayersViaQuery($server);
        foreach ($onlinePlayers as $playerName) {
            $lowerName = strtolower($playerName);
    
            // On force le statut � online si le joueur est trouv� par Query
            if (isset($allPlayers[$lowerName])) {
                $allPlayers[$lowerName]['online'] = true;
            } else {
            // Si le joueur n'�tait ni OP, ni Banni, ni dans le cache, on l'ajoute
                $allPlayers[$lowerName] = [
                    'id' => $playerName,
                    'name' => $playerName,
                    'online' => true,
                    'is_op' => isset($opNames[$lowerName]),
                    'is_banned' => false,
                ];
            }
        }

        return array_values($allPlayers);
    }

    private function getOnlinePlayersViaQuery(Server $server): array
    {
        $players = [];
        try {
            $ip = $server->allocation->alias ?? $server->allocation->ip;
            $port = $server->allocation->port;

             $socket = fsockopen("udp://$ip", $port, $errno, $errstr, 2);
             if ($socket) {
                 stream_set_timeout($socket, 2);
                 
                 // Request Challenge
                 $sessionId = rand(1, 99999999) & 0x0F0F0F0F;
                 $payload = pack('c', 0xFE) . pack('c', 0xFD) . pack('c', 0x09) . pack('N', $sessionId);
                 fwrite($socket, $payload);
                 
                 $response = fread($socket, 4096);
                 if ($response) {
                     $challengeStr = substr($response, 5);
                     $challengeStr = trim($challengeStr); 
                     $challenge = (int) $challengeStr;

                     // Full Stat Request
                     $payload2 = pack('c', 0xFE) . pack('c', 0xFD) . pack('c', 0x00) . 
                                 pack('N', $sessionId) . 
                                 pack('N', $challenge) . 
                                 pack('N', 0x00); 
                     
                     fwrite($socket, $payload2);
                     $fullResponse = fread($socket, 4096);
                     
                     if ($fullResponse) {
                         $body = substr($fullResponse, 16);
                         $split = explode("\x00\x01player_\x00\x00", $body);
                         
                         if (count($split) > 1) {
                            $playerSection = $split[1];
                            $rawPlayers = explode("\x00", $playerSection);

                            foreach ($rawPlayers as $playerName) {
                                if (!empty($playerName)) {
                                    $players[] = $playerName;
                                }
                            }
                        }
                     }
                 }
                 fclose($socket);
             }
        } catch (\Exception $e) {
            // Query failed
        }
        return $players;
    }

    /**
     * Récupère l'armure du joueur en essayant d'abord la nouvelle méthode
     * (tag "equipment", format post-25w03a), puis en repliant sur l'ancienne
     * méthode (slots réservés dans Inventory) si le nouveau tag n'existe pas.
     *
     * Retour : ['head' => id|null, 'chest' => id|null, 'legs' => id|null, 'feet' => id|null, 'offhand' => id|null]
     */
    private function fetchArmorData($rcon, string $playerId): array
    {
        $armor = [
            'head'    => null,
            'chest'   => null,
            'legs'    => null,
            'feet'    => null,
            'offhand' => null,
        ];

        // Aide interne pour extraire l'id d'un item depuis un fragment NBT
        // (ex: '{id: "minecraft:netherite_helmet", count: 1}')
        $extractId = function (string $raw): ?string {
            if (preg_match('/[{,]\s*id:\s*"?([a-zA-Z0-9_:\.\-]+)"?/i', $raw, $kv)) {
                return trim($kv[1]);
            }
            return null;
        };

        // --- 1. Nouvelle façon : une seule requête sur le tag "equipment" complet ---
        // On évite de faire 5 requêtes séparées (une par slot) car les messages
        // d'erreur pour "slot vide" vs "chemin inexistant" varient selon les
        // versions/forks du serveur et rendent la détection peu fiable.
        // Ici on récupère tout le compound "equipment" d'un coup et on parse
        // localement, comme dans : "<joueur> has the following entity data:
        // {head: {id: "minecraft:netherite_helmet", count: 1}, ...}"
        $res = $rcon->sendCommand("data get entity $playerId equipment");

        Log::debug("fetchArmorData raw response for $playerId: " . json_encode($res));

        if (!$res || str_contains($res, 'No entity was found')) {
            // Joueur hors ligne / uuid invalide : le reste du flux basculera
            // sur getOfflineDetailsFromNbt(), inutile d'aller plus loin.
            return $armor;
        }

        $pathMissing = str_contains($res, 'no matching element')
            || str_contains($res, 'Unknown or incomplete command')
            || str_contains($res, 'Expected an NBT')
            || str_contains($res, 'is not present')
            || str_contains($res, 'Found no elements')
            || str_contains($res, 'No such element');

        if (!$pathMissing) {
            // On isole le contenu après "data: " si présent
            $dataPos = strpos($res, 'data: ');
            $body = ($dataPos !== false) ? substr($res, $dataPos + 6) : $res;

            $slotsNew = ['head', 'chest', 'legs', 'feet', 'offhand'];
            $foundAny = false;

            foreach ($slotsNew as $slot) {
                // Cherche "<slot>: {" puis prend le texte jusqu'à la première "}"
                // rencontrée. Comme "id" est toujours le premier champ du compound
                // d'un item, il est capturé même si la compound est tronquée par
                // des sous-structures imbriquées (components, enchantments, etc.)
                if (preg_match('/\b' . $slot . '\s*:\s*\{(.*?)\}/is', $body, $m)) {
                    $id = $extractId($m[1]);
                    if ($id !== null) {
                        $armor[$slot] = $id;
                        $foundAny = true;
                    }
                }
            }

            // Le champ "equipment" existe : que le joueur porte ou non une armure,
            // on considère la nouvelle méthode comme valide et on ne bascule pas
            // sur l'ancienne (un joueur nu doit rester avec des slots à null).
            if ($foundAny || str_contains($body, ':')) {
                return $armor;
            }
        }

        // --- 2. Ancienne façon (fallback) : slots réservés dans Inventory ---
        // 103 = casque, 102 = torse, 101 = jambes, 100 = pieds, -106 = main secondaire
        $legacySlotMap = [
            103  => 'head',
            102  => 'chest',
            101  => 'legs',
            100  => 'feet',
            -106 => 'offhand',
        ];

        foreach ($legacySlotMap as $slotId => $slotName) {
            $res = $rcon->sendCommand("data get entity $playerId Inventory[{Slot:{$slotId}b}]");

            if (!$res
                || str_contains($res, 'No such element')
                || str_contains($res, 'Found no elements')
                || str_contains($res, 'No entity was found')
            ) {
                continue;
            }

            $dataPos = strpos($res, 'data: ');
            $itemStr = ($dataPos !== false) ? substr($res, $dataPos + 6) : $res;

            $id = $extractId($itemStr);
            if ($id !== null) {
                $armor[$slotName] = $id;
            }
        }

        return $armor;
    }

    public function getPlayerDetails(string $serverId, string $playerId): array
    {
        $server = Server::where('uuid', $serverId)->orWhere('uuid_short', $serverId)->first();
        if (!$server) return [];
        
        // Resolve UUID from Username early (Need UUID for stats file and UI)
        $uuid = null;
        /** @var \App\Repositories\Daemon\DaemonFileRepository $fileRepository */
        $fileRepository = app(\App\Repositories\Daemon\DaemonFileRepository::class);
        $fileRepository->setServer($server);
        
        try {
            $cacheContent = $fileRepository->getContent('usercache.json');
            $cache = json_decode($cacheContent, true);
            if (is_array($cache)) {
                foreach ($cache as $entry) {
                    if (strtolower($entry['name']) === strtolower($playerId)) {
                        $uuid = $entry['uuid'];
                        break;
                    }
                }
            }
        } catch (\Exception $e) { /* Ignore */ }

        $details = [
            'name' => $playerId,
            'uuid' => $uuid ?? 'Unknown',
            'status' => 'Offline', // Default
            'is_op' => false,
            'is_banned' => false,
            'raw_stats' => 'RCON disabled or unavailable.',
            'health' => null,
            'food' => null,
            'level' => null,
            'gamemode' => null,
            'inventory_summary' => null,
            'play_time' => null,
            'walk_distance' => null,
            'mobs_killed' => null,
            'deaths' => null,
        ];

        // Check OP status
        try {
            $opsContent = $fileRepository->getContent('ops.json');
            $ops = json_decode($opsContent, true);
            if (is_array($ops)) {
                foreach ($ops as $op) {
                    if (strtolower($op['name'] ?? '') === strtolower($playerId)) {
                        $details['is_op'] = true;
                        break;
                    }
                }
            }
        } catch (\Exception $e) { /* Ignore */ }

        // Check Banned status
        try {
            $bannedContent = $fileRepository->getContent('banned-players.json');
            $banned = json_decode($bannedContent, true);
            if (is_array($banned)) {
                foreach ($banned as $ban) {
                    if (strtolower($ban['name'] ?? '') === strtolower($playerId)) {
                        $details['is_banned'] = true;
                        break;
                    }
                }
            }
        } catch (\Exception $e) { /* Ignore */ }
        
        
        // 1. Get RCON Credentials
        $rconPort = null;
        $rconPassword = null;
        $enableRcon = false;
        $propsContent = null;

        try {
            $propsContent = $fileRepository->getContent('server.properties');
            
            // Regex Parsing for robustness
            if (preg_match('/^\s*enable-rcon\s*=\s*(true|false)/m', $propsContent, $m)) {
                $enableRcon = $m[1] === 'true';
            }
            if (preg_match('/^\s*rcon\.port\s*=\s*(\d+)/m', $propsContent, $m)) {
                $rconPort = (int)$m[1];
            }
            if (preg_match('/^\s*rcon\.password\s*=\s*(.*)$/m', $propsContent, $m)) {
                $rconPassword = trim($m[1]);
            }
            
        } catch (\Exception $e) {
            // Failed to read properties
        }

        $primaryHost = $server->allocation->alias ?? $server->allocation->ip;
        $hasPass = !empty($rconPassword);


        
        // Check setting
        $rconEnabled = config('rbs-minecraft-player-manager.rcon_enabled');

        if (!$rconEnabled) {
             // Try to get status via Query even if RCON is disabled
             $onlinePlayers = $this->getOnlinePlayersViaQuery($server);
             $isOnline = false;
             foreach ($onlinePlayers as $p) {
                 if (strtolower($p) === strtolower($playerId)) {
                     $isOnline = true;
                     break;
                 }
             }
             
             $details['status'] = $isOnline ? 'Online' : 'Offline';
             $details['raw_stats'] = $isOnline 
                ? 'Online (RCON disabled)' 
                : 'Offline';

             // Always try NBT if RCON is disabled (User request)
             $nbtDetails = $this->getOfflineDetailsFromNbt($server, $uuid ?? $playerId);
             if ($nbtDetails) {
                 $details = array_merge($details, $nbtDetails);
                 $details['raw_stats'] = 'RconDisabled';
             }
        } elseif (!$enableRcon || !$rconPort || !$hasPass) {
            $details['raw_stats'] = 'RCON is not enabled in server.properties.';
            $details['status'] = 'Config Error';
        } else {
             // 2. Connect RCON
            $hostsToTry = array_unique([$primaryHost, '127.0.0.1', 'localhost']);
            
            $connected = false;
            $rcon = null; // Initialize $rcon outside the loop

            foreach ($hostsToTry as $host) {
                if (empty($host)) continue;
                
                \Illuminate\Support\Facades\Log::info("RCON Attempt: Host={$host}, Port={$rconPort}");
                
                $rcon = new RconService($host, $rconPort, $rconPassword, 3); // Changed timeout to 3
                if ($rcon->connect()) {
                    $connected = true;
                    \Illuminate\Support\Facades\Log::info("RCON Connected successfully to {$host}");
                    break;
                }
                // Log failure only if all fail, or rely on RconService logs
            }

            // FIX: on ne rappelle plus $rcon->connect() une seconde fois ici.
            // La boucle ci-dessus a déjà établi la connexion (si possible) et
            // renseigné $connected. Rappeler connect() sur un socket déjà
            // ouvert échouait systématiquement, ce qui sautait tout le bloc
            // de récupération Health/Food/XP/Gamemode/Inventory/EnderChest
            // pour les joueurs connectés.
            if ($connected) {
                    // Check if online first by checking root
                    // But actually, asking for a specific path directly is fine. If entity not found, it returns "No entity..."
                    
                    // 3. Fetch Data Individually for robustness
                    $details['status'] = 'Online'; // Assume online if connected and commands work, will verify below
                    
                    // Helper to fetch and parse simpler numbers
                    $fetchInt = function($path) use ($rcon, $playerId) {
                        $res = $rcon->sendCommand("data get entity $playerId $path");
                        // Response: "<uuid> has the following entity data: 123" or just "123" depending on server
                        // Regex to grab the last number in the string
                        if (preg_match('/(-?\d+)[bfsdL]?$/', trim($res), $m)) return (int)$m[1];
                        return null;
                    };
                    $fetchFloat = function($path) use ($rcon, $playerId) {
                        $res = $rcon->sendCommand("data get entity $playerId $path");
                        if (preg_match('/([\d\.]+)[bfsdL]?$/', trim($res), $m)) return (float)$m[1];
                        return null;
                    };

                    // Health
                    // Try "Health" first, then "HealF"
                    $h = $fetchFloat('Health');
                    if ($h === null) $h = $fetchFloat('HealF'); // fallback
                    if ($h !== null) $details['health'] = $h;
                    
                    // Food
                    // Try "foodLevel" (camel), then "FoodLevel" (Pascal)
                    $f = $fetchInt('foodLevel');
                    if ($f === null) $f = $fetchInt('FoodLevel');
                    if ($f !== null) $details['food'] = $f;

                    // XP Level
                    $xl = $fetchInt('XpLevel'); 
                    if ($xl === null) $xl = $fetchInt('xpLevel');
                    if ($xl !== null) $details['level'] = $xl;

                    // Gamemode
                    $gmVal = $fetchInt('playerGameType');
                    if ($gmVal !== null) {
                        $gmById = [0 => 'Survival', 1 => 'Creative', 2 => 'Adventure', 3 => 'Spectator'];
                        $details['gamemode'] = $gmById[$gmVal] ?? 'Unknown';
                    }

                    // Inventory
                    // Fetch items iteratively to handle RCON truncation limits (4KB)
                    // Large inventories with NBT tags often exceed the limit, causing data loss.
                    // We loop through Inventory[0], Inventory[1]... until we get an error or empty result.
                    
                    $inventory = [];
                    $maxItems = 50; // Safety limit (Players typically have max 41 items: 36 main + 4 armor + 1 offhand)
                    
                    for ($i = 0; $i < $maxItems; $i++) {
                        $itemRes = $rcon->sendCommand("data get entity $playerId Inventory[$i]");
                        
                        // Break if index doesn't exist (e.g. "No such element", "Found no elements")
                        if (!$itemRes || str_contains($itemRes, 'No such element') || str_contains($itemRes, 'Found no elements') || str_contains($itemRes, 'No entity was found')) {
                            break;
                        }

                        // Response format: "<player> has the following entity data: {id:..., Count:...}"
                        // extract the part after "data: "
                        $dataPos = strpos($itemRes, 'data: ');
                        $itemStr = ($dataPos !== false) ? substr($itemRes, $dataPos + 6) : $itemRes;
                        
                        $item = [];
                        
                        // ID
                        // FIX: l'ancien regex exigeait un "}" juste avant "id:", ce qui
                        // n'arrive quasiment jamais dans la réponse réelle du serveur
                        // (format habituel: "{Slot: 0b, id: "minecraft:x", Count: 1b}").
                        // On matche désormais "id:" précédé d'une "{" ou d'une ",",
                        // ce qui fonctionne aussi bien en NBT classique (<1.20.5)
                        // qu'avec le nouveau format "components" (>=1.20.5).
                        if (preg_match('/[{,]\s*id:\s*"?([a-zA-Z0-9_:\.\-]+)"?/i', $itemStr, $kv)) {
                            $item['id'] = trim($kv[1]);
                        }
                        
                        // Count (le flag /i gère "Count:" comme "count:" selon la version)
                        if (preg_match('/count:\s*(\d+)b?/i', $itemStr, $kv)) {
                            $item['count'] = (int)$kv[1];
                        } else {
                            $item['count'] = 1;
                        }
                        
                        // Slot
                        if (preg_match('/slot:\s*(\d+)b?/i', $itemStr, $kv)) {
                            $item['slot'] = (int)$kv[1];
                        }

                        if (isset($item['id']) && isset($item['slot'])) {
                            $inventory[] = $item;
                            // Debug log for checking correct parsing
                             if ($i < 3) \Illuminate\Support\Facades\Log::info("DEBUG INV Correctly Parsed Index $i: {$item['id']} (Slot {$item['slot']})");
                        }
                    }

                    // Armure : nouvelle méthode (equipment) puis ancienne méthode (Inventory slots réservés) en fallback
                    $armorData = $this->fetchArmorData($rcon, $playerId);
                    \Illuminate\Support\Facades\Log::info("PARSED ARMOR: " . json_encode($armorData));

                    // IMPORTANT : le template Blade d'affichage de l'inventaire (inv-armor)
                    // cherche l'armure directement dans $inventory_data aux slots réservés
                    // 103/102/101/100/-106 (ancien format legacy). Comme sur les versions
                    // récentes l'armure vit dans le tag "equipment" et n'apparaît plus du
                    // tout dans la liste "Inventory", on réinjecte ici des entrées pseudo-slot
                    // pour rester 100% compatible avec le template sans avoir à le modifier.
                    $armorLegacySlotMap = [
                        'head'    => 103,
                        'chest'   => 102,
                        'legs'    => 101,
                        'feet'    => 100,
                        'offhand' => -106,
                    ];
                    foreach ($armorLegacySlotMap as $armorKey => $slotNum) {
                        if (!empty($armorData[$armorKey])) {
                            // On évite les doublons si l'ancienne méthode (fallback) avait déjà
                            // rempli ce slot directement depuis Inventory
                            $alreadyPresent = false;
                            foreach ($inventory as $existing) {
                                if (($existing['slot'] ?? null) === $slotNum) {
                                    $alreadyPresent = true;
                                    break;
                                }
                            }
                            if (!$alreadyPresent) {
                                $inventory[] = [
                                    'id'    => $armorData[$armorKey],
                                    'count' => 1,
                                    'slot'  => $slotNum,
                                ];
                            }
                        }
                    }

                    $details['inventory_data'] = $inventory;
                    $details['armor_data'] = $armorData;
                    \Illuminate\Support\Facades\Log::info("PARSED INVENTORY ITEMS (Iterative, armure incluse): " . count($inventory));

                    $enderchest = [];
                    $maxItemsEC = 36; // Safety limit (Players typically have max 41 items: 36 main + 4 armor + 1 offhand)
                    
                    for ($i = 0; $i < $maxItemsEC; $i++) {
                        $itemRes = $rcon->sendCommand("data get entity $playerId EnderItems[$i]");
                        
                        // Break if index doesn't exist (e.g. "No such element", "Found no elements")
                        if (!$itemRes || str_contains($itemRes, 'No such element') || str_contains($itemRes, 'Found no elements') || str_contains($itemRes, 'No entity was found')) {
                            break;
                        }

                        // Response format: "<player> has the following entity data: {id:..., Count:...}"
                        // extract the part after "data: "
                        $dataPos = strpos($itemRes, 'data: ');
                        $itemStr = ($dataPos !== false) ? substr($itemRes, $dataPos + 6) : $itemRes;
                        
                        $item = [];
                        
                        // ID (voir commentaire équivalent dans la boucle Inventory ci-dessus)
                        if (preg_match('/[{,]\s*id:\s*"?([a-zA-Z0-9_:\.\-]+)"?/i', $itemStr, $kv)) {
                            $item['id'] = trim($kv[1]);
                        }
                        
                        // Count
                        if (preg_match('/count:\s*(\d+)b?/i', $itemStr, $kv)) {
                            $item['count'] = (int)$kv[1];
                        } else {
                            $item['count'] = 1;
                        }
                        
                        // Slot
                        if (preg_match('/slot:\s*(\d+)b?/i', $itemStr, $kv)) {
                            $item['slot'] = (int)$kv[1];
                        }

                        if (isset($item['id']) && isset($item['slot'])) {
                            $enderchest[] = $item;
                            // Debug log for checking correct parsing
                             if ($i < 3) \Illuminate\Support\Facades\Log::info("DEBUG ENDER CHEST Correctly Parsed Index $i: {$item['id']} (Slot {$item['slot']})");
                        }
                    }

                    $details['enderchest_data'] = $enderchest;
                    \Illuminate\Support\Facades\Log::info("PARSED ENDER CHEST ITEMS (Iterative): " . count($enderchest));

                    
                    // Verify if we actually got data, otherwise might be offline (entity not found)
                    // If all values are null/empty, likely offline or bad UUID
                    if ($h === null && $f === null && $gmVal === null) {
                         // Check raw response of one command to see if "No entity"
                         $check = $rcon->sendCommand("data get entity $playerId Health");
                         if (str_contains($check, 'No entity was found')) {
                             $details['status'] = 'Offline'; 
                             $details['raw_stats'] = "Player is Offline";
                             
                             // Fallback to NBT
                             $nbtDetails = $this->getOfflineDetailsFromNbt($server, $uuid ?? $playerId);
                             if ($nbtDetails) {
                                 $details = array_merge($details, $nbtDetails);
                                 $details['raw_stats'] = 'Offline (Data from Save File)';
                             }
                         }
                    }

                    $rcon->disconnect();
            } else {
                     // Connect failed (logged in loop)
                     $details['status'] = 'RCON Error';
            }
        }

        // 4. Fetch JSON Stats
        if ($uuid) {
            try {
                $levelName = '/world';
                if (isset($propsContent) && preg_match('/level-name=(.*)/', $propsContent, $lm)) {
                    $levelName = '/'.trim($lm[1]);
                }
                if($this->dossierExiste($server, $levelName . "/players/stats/")){
                    $statsPath = "$levelName/players/stats/$uuid.json";
                }else{
                    $statsPath = "$levelName/stats/$uuid.json";
                }

                $statsContent = $fileRepository->getContent($statsPath);
                $statsJson = json_decode($statsContent, true);
                
                if ($statsJson && isset($statsJson['stats'])) {
                    $s = $statsJson['stats'];
                    $ticks = $s['minecraft:custom']['minecraft:play_time'] ?? 0;
                    $details['play_time'] = floor($ticks / 20 / 60) . ' ' . __('rbs-minecraft-player-manager::messages.units.mins');
                    $details['mobs_killed'] = $s['minecraft:custom']['minecraft:mob_kills'] ?? 0;
                    $details['deaths'] = $s['minecraft:custom']['minecraft:deaths'] ?? 0;
                    $cm = $s['minecraft:custom']['minecraft:walk_one_cm'] ?? 0;
                    $details['walk_distance'] = floor($cm / 100) . ' m';
                }
            } catch (\Exception $e) { /* Stat file parse error */ }
        }

        return $details;
    }

    public function kick(string $serverId, string $playerId, string $reason = ''): bool
    {
        $cmd = trim("kick $playerId $reason");
        return $this->sendRconCommand($serverId, $cmd) !== null;
    }

    public function ban(string $serverId, string $playerId, string $reason = ''): bool
    {
        $cmd = "ban $playerId";
        if (!empty($reason)) {
            $cmd .= " $reason";
        }
        return $this->sendRconCommand($serverId, $cmd) !== null;
    }

    public function pardon(string $serverId, string $playerId): bool
    {
        return $this->sendRconCommand($serverId, "pardon $playerId") !== null;
    }

    public function op(string $serverId, string $playerId): bool
    {
        return $this->sendRconCommand($serverId, "op $playerId") !== null;
    }

    public function deop(string $serverId, string $playerId): bool
    {
        return $this->sendRconCommand($serverId, "deop $playerId") !== null;
    }

    public function clearInventory(string $serverId, string $playerId): bool
    {
        return $this->sendRconCommand($serverId, "clear $playerId") !== null;
    }

    public function getServerProperties(string $serverId): array
    {
        $server = Server::where('uuid', $serverId)->orWhere('uuid_short', $serverId)->first();
        if (!$server) return [];

        /** @var \App\Repositories\Daemon\DaemonFileRepository $fileRepository */
        $fileRepository = app(\App\Repositories\Daemon\DaemonFileRepository::class);
        $fileRepository->setServer($server);

        $props = [
            'max_players' => 20,
            'motd' => 'A Minecraft Server',
            'level_name' => 'world',
        ];

        try {
            $content = $fileRepository->getContent('server.properties');
            
            if (preg_match('/^\s*max-players\s*=\s*(\d+)/m', $content, $m)) {
                $props['max_players'] = (int)$m[1];
            }
            if (preg_match('/^\s*motd\s*=\s*(.*)$/m', $content, $m)) {
                $rawMotd = trim($m[1]);
                // Handle basic unicode escape if present
                $props['motd'] = json_decode('"'.$rawMotd.'"') ?? $rawMotd;
            }
            if (preg_match('/^\s*level-name\s*=\s*(.*)$/m', $content, $m)) {
                $props['level_name'] = trim($m[1]);
            }

        } catch (\Exception $e) { /* Ignore */ }

        return $props;
    }

    private function getOfflineDetailsFromNbt(Server $server, string $uuid): ?array
    {
        if (empty($uuid) || $uuid === 'Unknown') return null;

        // Locate File
        // Usually world/playerdata/<uuid>.dat
        // We need to know the level-name
        
        /** @var \App\Repositories\Daemon\DaemonFileRepository $fileRepository */
        $fileRepository = app(\App\Repositories\Daemon\DaemonFileRepository::class);
        $fileRepository->setServer($server);

        $levelName = '/world';
        try {
            $props = $fileRepository->getContent('server.properties');
            if (preg_match('/^\s*level-name\s*=\s*(.*)$/m', $props, $matches)) {
                $levelName = '/'.trim($matches[1]);
            }
        } catch (\Exception $e) {}

        // Construct path relative to server root
        // Note: DaemonFileRepository usually works with relative paths for content, but we need absolute path for NbtService?
        // Actually, NbtService needs a local file system path or raw content.
        // DaemonFileRepository::getContent returns string content. 
        // Our NbtService::parseFile expects a PATH. 
        // However, we are in a plugin on the panel. The file might be on a remote node (Wings).
        // Standard Pelican Panel plugins run on the Panel side. Files are fetched from Wings via API/Repository.
        // We CANNOT directly access disk unless node is local.
        // So we should fetch CONTENT using DaemonFileRepository (which handles Wings API) and pass CONTENT to NbtParser.
        

        if($this->dossierExiste($server, $levelName . "/players/data/")){
            $path = "$levelName/players/data/$uuid.dat";
        }else{
            $path = "$levelName/playerdata/$uuid.dat";
        }
        
        try {
            // This pulls the raw binary content strings (API response)
            // Warning: large files might be an issue, but player.dat is small (few KB).
            $content = $fileRepository->getContent($path);
            
            // adapt NbtService to accept content string
            $data = $this->nbtService->parseString($content);
            
            if (empty($data)) return null;

            $result = [];

            // Extract Health
            if (isset($data['Health'])) $result['health'] = $data['Health']; // Float on NBT
            
            // Extract Food
            if (isset($data['foodLevel'])) $result['food'] = $data['foodLevel']; // Int

            // Extract XP
            if (isset($data['XpLevel'])) $result['level'] = $data['XpLevel'];

            // Extract Gamemode
            if (isset($data['playerGameType'])) {
                 $gmById = [0 => 'Survival', 1 => 'Creative', 2 => 'Adventure', 3 => 'Spectator'];
                 $result['gamemode'] = $gmById[$data['playerGameType']] ?? 'Unknown';
            }

            // Extract Inventory
            // NBT Inventory structure: List of Compounds {Slot:byte, id:string, Count:byte, tag:{...}}
            if (isset($data['Inventory']) && is_array($data['Inventory'])) {
                $inv = [];
                foreach ($data['Inventory'] as $item) {
                     // Normalize keys to match RCON format
                     $slot = $item['Slot'] ?? $item['slot'] ?? null;
                     $id = $item['id'] ?? $item['Id'] ?? null;
                     $count = $item['Count'] ?? $item['count'] ?? 1;

                     // NBT Slot is Byte (signed?), RCON is int.
                     // Filter out invalid items
                     if ($id && $slot !== null) {
                         $inv[] = [
                             'slot' => $slot,
                             'id' => $id,
                             'count' => $count,
                         ];
                     }
                }
                $result['inventory_data'] = $inv;
            }

            if (isset($data['EnderItems']) && is_array($data['EnderItems'])) {
                $inv = [];
                foreach ($data['EnderItems'] as $item) {
                     // Normalize keys to match RCON format
                     $slot = $item['Slot'] ?? $item['slot'] ?? null;
                     $id = $item['id'] ?? $item['Id'] ?? null;
                     $count = $item['Count'] ?? $item['count'] ?? 1;

                     // NBT Slot is Byte (signed?), RCON is int.
                     // Filter out invalid items
                     if ($id && $slot !== null) {
                         $inv[] = [
                             'slot' => $slot,
                             'id' => $id,
                             'count' => $count,
                         ];
                     }
                }
                $result['enderchest_data'] = $inv;
            }

            return $result;
            
        } catch (\Exception $e) {
            // File not found or read error
            return null;
        }
    }
}