<?php

namespace R0B0TB0SS\GamePlayerManager\Filament\Server\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Server\Components\SmallStatBlock;
use R0B0TB0SS\GamePlayerManager\Services\MinecraftPlayerProvider;
use Filament\Facades\Filament;

class PlayerCountWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $server = Filament::getTenant();

        if (
            ! $server ||
            ! in_array('minecraft', $server->egg->tags ?? [])
        ) {
            return [];
        }

        $serverId = $server->uuid ?? 'server-1';
        
        $provider = new MinecraftPlayerProvider();
        $players = $provider->getPlayers($serverId);
        
        $onlineCount = count(array_filter($players, fn($p) => $p['online'] ?? false));
        
        $properties = $provider->getServerProperties($serverId);
        $maxPlayers = $properties['max_players'] ?? 20;
        $motd = $properties['motd'] ?? '';
        $levelName = $properties['level_name'] ?? 'world';

        return [
            SmallStatBlock::make(__('rbs-minecraft-player-manager::messages.widget.online_players'), "{$onlineCount} / {$maxPlayers}"), 
            SmallStatBlock::make(__('rbs-minecraft-player-manager::messages.widget.motd'), $motd),
            SmallStatBlock::make(__('rbs-minecraft-player-manager::messages.widget.map'), $levelName),
        ];
    }
}
