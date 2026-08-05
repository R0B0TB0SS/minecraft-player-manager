<?php

namespace R0B0TB0SS\GamePlayerManager\Filament\Server\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use R0B0TB0SS\GamePlayerManager\Services\MinecraftPlayerProvider;
use R0B0TB0SS\GamePlayerManager\Models\Player;
use Filament\Facades\Filament;
use Filament\Actions\Action;

class OnlinePlayersTableWidget extends BaseWidget
{
    public static function canView(): bool
    {
        /** @var \App\Models\Server $server */
        $server = Filament::getTenant();

        return in_array('minecraft', $server->egg->tags ?? []);
    }
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = 'Online Players';

    protected static bool $isCollapsible = true;

    public function table(Table $table): Table
    {
        return $table
            ->query( // We override getTableRecords, so query is dummy
                Player::query()->where('id', 'impossible') 
            )
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
                Tables\Columns\TextColumn::make('name')->label('Name')->searchable(),
                Tables\Columns\IconColumn::make('is_op')->boolean()->label('OP'),
            ])
            ->actions([
                Action::make('op')
                    ->label(fn ($record) => $record->is_op ? 'Deop' : 'Op')
                    ->color(fn ($record) => $record->is_op ? 'warning' : 'primary')
                    ->button()
                    ->action(function ($record) {
                        $server = Filament::getTenant();
                        if (!$server) return;
                        $command = $record->is_op ? "deop {$record->name}" : "op {$record->name}";
                        app(MinecraftPlayerProvider::class)->sendRconCommand($server->uuid, $command);
                        \Filament\Notifications\Notification::make()->title($record->is_op ? 'De-opped' : 'Opped')->success()->send();
                    }),
                Action::make('kick')
                    ->color('danger')
                    ->button()
                    ->action(function ($record) {
                        $server = Filament::getTenant();
                        app(MinecraftPlayerProvider::class)->sendRconCommand($server->uuid, "kick {$record->name}");
                        \Filament\Notifications\Notification::make()->title('Kicked')->success()->send();
                    })
                    ->requiresConfirmation(),
                Action::make('ban')
                    ->color('danger')
                    ->button()
                    ->action(function ($record) {
                        $server = Filament::getTenant();
                        app(MinecraftPlayerProvider::class)->sendRconCommand($server->uuid, "ban {$record->name}");
                        \Filament\Notifications\Notification::make()->title('Banned')->success()->send();
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
        
        // Filter Online
        $online = array_filter($all, fn($p) => $p['online']);
        
        return collect($online)->map(fn($item) => new Player($item));
    }
}
