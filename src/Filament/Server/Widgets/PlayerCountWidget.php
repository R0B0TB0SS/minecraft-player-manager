<?php

namespace R0B0TB0SS\GamePlayerManager\Filament\Server\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Server\Components\SmallStatBlock;
use R0B0TB0SS\GamePlayerManager\Services\MinecraftPlayerProvider;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class PlayerCountWidget extends BaseWidget
{
    /**
     * Implémentation manuelle du protocole "Server List Ping" (status).
     * Fonctionne sans enable-query, comme le fait le client Minecraft.
     */
    protected function getPlayerNum(string $host, int $port = 25565): int
    {
        try {
            $socket = @fsockopen($host, $port, $errno, $errstr, 3);

            if (! $socket) {
                Log::warning("Impossible de se connecter à {$host}:{$port} - {$errstr} ({$errno})");
                return 0;
            }

            stream_set_timeout($socket, 3);

            // --- Handshake packet ---
            $handshake = $this->packVarInt(0x00)
                . $this->packVarInt(-1) // protocol version (any)
                . $this->packVarInt(strlen($host)) . $host
                . pack('n', $port)
                . $this->packVarInt(0x01); // next state: status

            $this->writePacket($socket, $handshake);

            // --- Status request packet ---
            $statusRequest = $this->packVarInt(0x00);
            $this->writePacket($socket, $statusRequest);

            // --- Lecture de la réponse ---
            $length = $this->readVarInt($socket);
            $packetId = $this->readVarInt($socket);
            $jsonLength = $this->readVarInt($socket);

            $jsonData = '';
            while (strlen($jsonData) < $jsonLength) {
                $chunk = fread($socket, $jsonLength - strlen($jsonData));
                if ($chunk === false || $chunk === '') {
                    break;
                }
                $jsonData .= $chunk;
            }

            fclose($socket);

            $data = json_decode($jsonData, true);

            if (! is_array($data)) {
                Log::warning("Réponse JSON invalide du serveur Minecraft: {$jsonData}");
                return 0;
            }

            return (int) ($data['players']['online'] ?? 0);
        } catch (Throwable $e) {
            Log::warning("Erreur getPlayerNum: {$e->getMessage()}");
            return 0;
        }
    }

    protected function packVarInt(int $value): string
    {
        $value &= 0xFFFFFFFF;
        $bytes = '';

        do {
            $temp = $value & 0b01111111;
            $value >>= 7;
            if ($value !== 0) {
                $temp |= 0b10000000;
            }
            $bytes .= chr($temp);
        } while ($value !== 0);

        return $bytes;
    }

    protected function readVarInt($socket): int
    {
        $numRead = 0;
        $result = 0;

        do {
            $byte = fread($socket, 1);
            if ($byte === false || $byte === '') {
                throw new \RuntimeException('Impossible de lire le VarInt');
            }
            $value = ord($byte);
            $result |= ($value & 0b01111111) << (7 * $numRead);

            $numRead++;
            if ($numRead > 5) {
                throw new \RuntimeException('VarInt trop long');
            }
        } while (($value & 0b10000000) !== 0);

        return $result;
    }

    protected function writePacket($socket, string $payload): void
    {
        $length = $this->packVarInt(strlen($payload));
        fwrite($socket, $length . $payload);
    }

    protected function getStats(): array
    {
        try {
            $server = Filament::getTenant();

            if (
                ! $server ||
                ! $server->egg ||
                ! in_array('minecraft', $server->egg->tags ?? [])
            ) {
                return [];
            }

            $serverId = $server->uuid ?? 'server-1';

            $provider = new MinecraftPlayerProvider();
            $properties = $provider->getServerProperties($serverId);

            $maxPlayers = $properties['max_players'] ?? 20;
            $motd = $properties['motd'] ?? '';
            $levelName = $properties['level_name'] ?? 'world';

            $allocation = $server->allocation;
            $host = $allocation?->ip ?? '127.0.0.1';
            $port = $allocation?->port ?? 25565;

            $onlineCount = Cache::remember(
                "minecraft.playercount.{$serverId}",
                10,
                fn () => $this->getPlayerNum($host, $port)
            );

            return [
                SmallStatBlock::make(__('rbs-minecraft-player-manager::messages.widget.online_players'), "{$onlineCount} / {$maxPlayers}"),
                SmallStatBlock::make(__('rbs-minecraft-player-manager::messages.widget.motd'), $motd),
                SmallStatBlock::make(__('rbs-minecraft-player-manager::messages.widget.map'), $levelName),
            ];
        } catch (Throwable $e) {
            Log::error("PlayerCountWidget getStats() erreur: {$e->getMessage()}");
            Log::error($e->getTraceAsString());
            return [];
        }
    }
}