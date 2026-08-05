<?php

namespace R0B0TB0SS\GamePlayerManager\Filament\Server\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use R0B0TB0SS\GamePlayerManager\Services\MinecraftPlayerProvider;
use R0B0TB0SS\GamePlayerManager\Models\Player;
use Filament\Facades\Filament;
use Filament\Actions\Action;

class OpPlayersTableWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = 'OP Players';

    protected static bool $isCollapsible = true;

    public function table(Table $table): Table
    {
        return $table
            ->query(Player::query()->where('id', 'impossible'))
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('')
                    ->state(function ($record) {
                        $name = $record['name'];
                        if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
                            return "https://minotar.net/avatar/MHF_Steve/32";
                        }
                        return "https://minotar.net/avatar/{$name}/32";
                    })
                    ->circular(),
                Tables\Columns\TextColumn::make('name')->label('Name'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->state(fn ($record) => $record['online'] ? 'Online' : 'Offline')
                    ->color(fn ($state) => $state === 'Online' ? 'success' : 'gray'),
            ])
            ->actions([
                Action::make('deop')
                    ->label('Deop')
                    ->color('warning')
                    ->button()
                    ->action(function ($record) {
                        $server = Filament::getTenant();
                        if (!$server) return;
                        app(MinecraftPlayerProvider::class)->sendRconCommand($server->uuid, "deop {$record->name}");
                        \Filament\Notifications\Notification::make()->title('De-opped')->success()->send();
                    })
                    ->requiresConfirmation(),
            ]);
    }

    public function getTableRecords(): \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Contracts\Pagination\CursorPaginator
    {
        $server = Filament::getTenant();
        $serverId = $server->uuid ?? 'server-1';
        $provider = new MinecraftPlayerProvider();
        $all = $provider->getPlayers($serverId);
        
        // Filter OP
        $ops = array_filter($all, fn($p) => $p['is_op']);
        
        return collect($ops)->map(fn($item) => new Player($item));
    }
}
