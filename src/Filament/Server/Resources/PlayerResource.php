<?php

namespace R0B0TB0SS\GamePlayerManager\Filament\Server\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use R0B0TB0SS\GamePlayerManager\Filament\Server\Resources\PlayerResource\Pages;
use R0B0TB0SS\GamePlayerManager\Services\MinecraftPlayerProvider;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;

class PlayerResource extends Resource
{
    protected static ?string $model = \R0B0TB0SS\GamePlayerManager\Models\Player::class;
    protected static ?string $slug = 'game-players';
    protected static string | \BackedEnum | null $navigationIcon = 'tabler-users-group';
    
    public static function getNavigationSort(): ?int
    {
        return (int) env('MC_PLAYER_MANAGER_NAV_SORT', 2);
    }

    protected static ?string $navigationLabel = null; 

    public static function getNavigationLabel(): string
    {
        return __('rbs-minecraft-player-manager::messages.navigation_label');
    }

    public static function canAccess(): bool
    {
        $server = \Filament\Facades\Filament::getTenant();
        // Check if the server has the 'minecraft' tag
        $tags = $server->egg->tags ?? [];
        return parent::canAccess() && in_array('minecraft', $tags);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('')
                    ->state(function ($record) {
                        $name = $record['name'];
                        // Generic Java Edition Username Regex (approx)
                        // If it contains anything other than a-z, 0-9, or _, it's likely Bedrock or invalid for Minotar
                        if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
                            return "https://mc-heads.net/head/MHF_Steve/32";
                        }
                        return "https://mc-heads.net/head/{$name}/32";
                    }),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label(__('rbs-minecraft-player-manager::messages.columns.name')),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->state(function ($record) {
                        if ($record['is_banned']) return 'Banned';
                        return $record['online'] ? 'Online' : 'Offline';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Online' => 'success',
                        'Banned' => 'danger',
                        'Offline' => 'gray',
                        'ERR' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Online' => __('rbs-minecraft-player-manager::messages.columns.online'),
                        'Banned' => __('rbs-minecraft-player-manager::messages.filters.banned'),
                        'Offline' => __('rbs-minecraft-player-manager::messages.columns.offline'),
                        'ERR' => 'RCON Debug',
                        default => $state,
                    })
                    ->label(__('rbs-minecraft-player-manager::messages.columns.status')),

                Tables\Columns\IconColumn::make('is_op')
                     ->boolean()
                     ->label(__('rbs-minecraft-player-manager::messages.columns.op')),
            ])
            ->actions([
                 Action::make('view_details')
                    ->label(__('rbs-minecraft-player-manager::messages.actions.view'))
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->url(fn ($record) => PlayerResource::getUrl('view', ['record' => $record->id])),
                    
                 Action::make('op')
                    ->label(fn ($record) => $record->is_op ? __('rbs-minecraft-player-manager::messages.actions.op.label_deop') : __('rbs-minecraft-player-manager::messages.actions.op.label_op'))
                    ->color(fn ($record) => $record->is_op ? 'warning' : 'primary')
                    ->icon(fn ($record) => $record->is_op ? 'heroicon-m-shield-exclamation' : 'heroicon-m-shield-check')
                    ->button()
                    ->action(function ($record) {
                        if (!$record) return;
                        
                        $server = \Filament\Facades\Filament::getTenant();
                        if (!$server) return; // Context check

                        $command = $record->is_op ? "deop {$record->name}" : "op {$record->name}";
                        
                        $provider = app(MinecraftPlayerProvider::class);
                        $provider->sendRconCommand($server->uuid, $command);
                        
                        \Filament\Notifications\Notification::make()
                            ->title($record->is_op ? __('rbs-minecraft-player-manager::messages.actions.op.notify_deop') : __('rbs-minecraft-player-manager::messages.actions.op.notify_op'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),

                Action::make('kick')
                    ->color('danger')
                    ->icon('heroicon-m-arrow-right-on-rectangle')
                    ->button()
                    ->action(function ($record) {
                        if (!$record) return;
                        $server = \Filament\Facades\Filament::getTenant();
                        if (!$server) return;

                        $provider = app(MinecraftPlayerProvider::class);
                        $provider->sendRconCommand($server->uuid, "kick {$record->name}");
                        \Filament\Notifications\Notification::make()->title(__('rbs-minecraft-player-manager::messages.actions.kick.notify'))->success()->send();
                    })
                    ->requiresConfirmation(),

                Action::make('ban')
                    ->label(fn ($record) => $record->is_banned ? __('rbs-minecraft-player-manager::messages.actions.ban.label_unban') : __('rbs-minecraft-player-manager::messages.actions.ban.label_ban'))
                    ->color(fn ($record) => $record->is_banned ? 'success' : 'danger')
                    ->icon(fn ($record) => $record->is_banned ? 'heroicon-m-check-circle' : 'heroicon-m-no-symbol')
                    ->button()
                    ->form(fn ($record) => $record->is_banned ? [] : [
                        \Filament\Forms\Components\TextInput::make('reason')
                            ->label(__('rbs-minecraft-player-manager::messages.actions.ban.reason'))
                            ->default(__('rbs-minecraft-player-manager::messages.actions.ban.default_reason')),
                    ])
                    ->action(function (array $data, $record) {
                        if (!$record) return;
                        $server = \Filament\Facades\Filament::getTenant();
                        if (!$server) return;

                        $provider = app(MinecraftPlayerProvider::class);
                        
                        if ($record->is_banned) {
                            $provider->pardon($server->uuid, $record->name);
                            \Filament\Notifications\Notification::make()->title(__('rbs-minecraft-player-manager::messages.actions.ban.notify_unban'))->success()->send();
                        } else {
                            $provider->ban($server->uuid, $record->name, $data['reason'] ?? null);
                            \Filament\Notifications\Notification::make()->title(__('rbs-minecraft-player-manager::messages.actions.ban.notify_ban'))->success()->send();
                        }
                    })
                    ->requiresConfirmation(),
            ]);
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                // 1. General Info
                \Filament\Schemas\Components\Section::make(__('rbs-minecraft-player-manager::messages.sections.identity'))
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')->label(__('rbs-minecraft-player-manager::messages.fields.username'))->disabled(),
                        \Filament\Forms\Components\TextInput::make('status')
                            ->label(__('rbs-minecraft-player-manager::messages.fields.current_status'))
                            ->disabled()
                            ->formatStateUsing(fn ($state) => match (strtolower($state ?? '')) {
                                'online' => __('rbs-minecraft-player-manager::messages.values.online'),
                                'offline' => __('rbs-minecraft-player-manager::messages.values.offline'),
                                default => $state,
                            }),
                        \Filament\Forms\Components\TextInput::make('uuid')->label(__('rbs-minecraft-player-manager::messages.fields.uuid'))->disabled(),
                    ])->columns(3),

                // 2. Historical Stats (Grid)
                \Filament\Schemas\Components\Section::make(__('rbs-minecraft-player-manager::messages.sections.statistics'))
                    ->description(__('rbs-minecraft-player-manager::messages.sections.statistics_desc'))
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('play_time')->label(__('rbs-minecraft-player-manager::messages.fields.play_time'))->disabled(),
                        \Filament\Forms\Components\TextInput::make('walk_distance')->label(__('rbs-minecraft-player-manager::messages.fields.distance_walked'))->disabled(),
                        \Filament\Forms\Components\TextInput::make('mobs_killed')->label(__('rbs-minecraft-player-manager::messages.fields.mobs_killed'))->disabled(),
                        \Filament\Forms\Components\TextInput::make('deaths')->label(__('rbs-minecraft-player-manager::messages.fields.deaths'))->disabled(),
                    ])->columns(4),

                \Filament\Schemas\Components\Section::make(__('rbs-minecraft-player-manager::messages.sections.live_status'))
                    ->description(fn ($record) => match($record?->raw_stats) {
                        'Offline (Data from Save File)' => __('rbs-minecraft-player-manager::messages.sections.offline_status_desc'),
                        'RconDisabled' => __('rbs-minecraft-player-manager::messages.sections.rcon_disabled_status_desc'),
                        default => __('rbs-minecraft-player-manager::messages.sections.live_status_desc'),
                    })
                    ->schema([

                        \Filament\Forms\Components\ViewField::make('visual_stats')
                            ->label(__('rbs-minecraft-player-manager::messages.fields.status'))
                            ->view('rbs-minecraft-player-manager::filament.server.resources.player-resource.widgets.visual-stats-view')
                            ->columnSpanFull(),
                        \Filament\Forms\Components\TextInput::make('level')
                            ->label(__('rbs-minecraft-player-manager::messages.fields.xp_level'))
                            ->disabled()
                            ->columnSpan(2),
                        \Filament\Forms\Components\TextInput::make('gamemode')
                            ->label(__('rbs-minecraft-player-manager::messages.fields.gamemode'))
                            ->disabled()
                            ->formatStateUsing(fn ($state) => match (strtolower($state ?? '')) {
                                'survival' => __('rbs-minecraft-player-manager::messages.values.survival'),
                                'creative' => __('rbs-minecraft-player-manager::messages.values.creative'),
                                'adventure' => __('rbs-minecraft-player-manager::messages.values.adventure'),
                                'spectator' => __('rbs-minecraft-player-manager::messages.values.spectator'),
                                default => $state,
                            })
                            ->columnSpan(2),
                    ])->columns(4)->collapsible(),

                // Inventory Section
                \Filament\Schemas\Components\Section::make(__('rbs-minecraft-player-manager::messages.sections.inventory'))
                    ->schema([
                         \Filament\Forms\Components\ViewField::make('inventory_data')
                            ->label(__('rbs-minecraft-player-manager::messages.fields.visual_inventory'))
                            ->view('rbs-minecraft-player-manager::filament.server.resources.player-resource.widgets.inventory-view')
                            ->columnSpanFull(),
                    ])->collapsible(),
                    
                // Ender Chest Section
                \Filament\Schemas\Components\Section::make(__('rbs-minecraft-player-manager::messages.sections.enderchest'))
                    ->schema([
                         \Filament\Forms\Components\ViewField::make('enderchest_data')
                            ->label(__('rbs-minecraft-player-manager::messages.fields.visual_enderchest'))
                            ->view('rbs-minecraft-player-manager::filament.server.resources.player-resource.widgets.enderchest-view')
                            ->columnSpanFull(),
                    ])->collapsible(),

                // Management Section (Actions)
                 \Filament\Schemas\Components\Section::make(__('rbs-minecraft-player-manager::messages.sections.management'))
                    ->description(__('rbs-minecraft-player-manager::messages.sections.management_desc'))
                    ->schema([
                        \Filament\Forms\Components\ViewField::make('management_actions')
                            ->view('rbs-minecraft-player-manager::filament.server.resources.player-resource.widgets.management-actions')
                            ->columnSpanFull(),
                    ])->collapsible(),
                



            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlayers::route('/'),
            'view' => Pages\ViewPlayer::route('/{record}'),
        ];
    }
}
