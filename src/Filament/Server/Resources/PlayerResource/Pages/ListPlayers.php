<?php

namespace R0B0TB0SS\GamePlayerManager\Filament\Server\Resources\PlayerResource\Pages;

use Filament\Resources\Pages\ListRecords;
use R0B0TB0SS\GamePlayerManager\Filament\Server\Resources\PlayerResource;
use R0B0TB0SS\GamePlayerManager\Services\MinecraftPlayerProvider;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;



class ListPlayers extends ListRecords
{
    protected static string $resource = PlayerResource::class;

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('minecraft-player-manager::messages.pages.list');
    }

    protected ?array $cachedPlayers = null;

    protected function getCachedPlayers(): array
    {
        if ($this->cachedPlayers !== null) {
            return $this->cachedPlayers;
        }

        $server = Filament::getTenant();
        $serverId = $server->uuid ?? 'server-1';
        
        $provider = new MinecraftPlayerProvider();
        $this->cachedPlayers = $provider->getPlayers($serverId);

        return $this->cachedPlayers;
    }

    public function getTabs(): array
    {
        $players = collect($this->getCachedPlayers());

        return [
            'all' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->label(__('minecraft-player-manager::messages.filters.all'))
                ->badge($players->count()),
            'online' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->label(__('minecraft-player-manager::messages.filters.online'))
                ->badge($players->where('online', true)->count())
                ->badgeColor('success'),
            'offline' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->label(__('minecraft-player-manager::messages.filters.offline'))
                ->badge($players->where('online', false)->count())
                ->badgeColor('gray'),
            'op' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->label(__('minecraft-player-manager::messages.filters.op'))
                ->badge($players->where('is_op', true)->count())
                ->badgeColor('warning'),
            'banned' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->label(__('minecraft-player-manager::messages.filters.banned'))
                ->badge($players->where('is_banned', true)->count())
                ->badgeColor('danger'),
        ];
    }

    public function getTableRecords(): \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Contracts\Pagination\CursorPaginator
    {
        $data = $this->getCachedPlayers();
        $collection = collect($data)->map(fn ($item) => new \R0B0TB0SS\GamePlayerManager\Models\Player($item));

        $activeTab = $this->activeTab ?? 'all';

        if ($activeTab === 'online') {
            $collection = $collection->where('online', true);
        } elseif ($activeTab === 'offline') {
            $collection = $collection->where('online', false);
        } elseif ($activeTab === 'op') {
            $collection = $collection->where('is_op', true);
        } elseif ($activeTab === 'banned') {
            $collection = $collection->where('is_banned', true);
        }

        // Apply Search
        $search = $this->getTableSearch();
        if (filled($search)) {
            $collection = $collection->filter(fn ($record) => str_contains(strtolower($record->name), strtolower($search)));
        }

        return $collection;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \R0B0TB0SS\GamePlayerManager\Filament\Server\Widgets\PlayerCountWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }
    
    public function getTableRecordKey(Model|array $record): string
    {
        return $record['id'];
    }

    public function resolveTableRecord(?string $key): ?Model
    {
        $server = Filament::getTenant();
        $serverId = $server->uuid ?? 'server-1';
        
        $provider = new MinecraftPlayerProvider();
        
        $players = $provider->getPlayers($serverId);
        $recordRaw = collect($players)->firstWhere('id', $key);
        
        if (!$recordRaw) {
            return null;
        }

        $details = $provider->getPlayerDetails($serverId, $key);
        $data = array_merge($recordRaw, $details);
        
        return new \R0B0TB0SS\GamePlayerManager\Models\Player($data);
    }
}
