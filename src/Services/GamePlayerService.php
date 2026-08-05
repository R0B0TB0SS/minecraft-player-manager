<?php

namespace R0B0TB0SS\GamePlayerManager\Services;

interface GamePlayerService
{
    public function getPlayers(string $serverId): array;
    public function getPlayerDetails(string $serverId, string $playerId): array;
    public function kick(string $serverId, string $playerId, string $reason = ''): bool;
    public function ban(string $serverId, string $playerId, string $reason = ''): bool;
    public function pardon(string $serverId, string $playerId): bool;
    public function op(string $serverId, string $playerId): bool;
    public function deop(string $serverId, string $playerId): bool;
    public function clearInventory(string $serverId, string $playerId): bool;
}
