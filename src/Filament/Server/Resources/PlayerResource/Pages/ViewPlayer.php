<?php

namespace R0B0TB0SS\GamePlayerManager\Filament\Server\Resources\PlayerResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use R0B0TB0SS\GamePlayerManager\Filament\Server\Resources\PlayerResource;
use R0B0TB0SS\GamePlayerManager\Services\MinecraftPlayerProvider;
use Filament\Facades\Filament;

class ViewPlayer extends ViewRecord
{
    protected static string $resource = PlayerResource::class;

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('rbs-minecraft-player-manager::messages.pages.view');
    }

    protected function resolveRecord(string | int $key): \Illuminate\Database\Eloquent\Model
    {
        $server = Filament::getTenant();
        $serverId = $server->uuid ?? 'server-1';
        
        $provider = new MinecraftPlayerProvider();
        $details = $provider->getPlayerDetails($serverId, $key);
        
        $data = array_merge(['id' => $key], $details);
        
        $player = new \R0B0TB0SS\GamePlayerManager\Models\Player($data);
        $player->exists = true; // IMPORTANT: Required for Filament Actions to recognize the record context
        return $player;
    }

    public function refreshPlayer(): void
    {
        // Re-fetch data from RCON/File
        $freshRecord = $this->resolveRecord($this->record->id);
        $this->record = $freshRecord;
        $this->fillForm();
    }

    // Expose actions to the custom view
    public function op_toggle(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('op_toggle')
            ->label(fn () => $this->record->is_op ? __('rbs-minecraft-player-manager::messages.actions.op.label_deop') : __('rbs-minecraft-player-manager::messages.actions.op.label_op'))
            ->icon(fn () => $this->record->is_op ? 'heroicon-m-shield-exclamation' : 'heroicon-m-shield-check')
            ->color(fn () => $this->record->is_op ? 'danger' : 'success')
            ->requiresConfirmation()
            ->modalHeading(fn () => $this->record->is_op ? __('rbs-minecraft-player-manager::messages.actions.op.heading_deop') : __('rbs-minecraft-player-manager::messages.actions.op.heading_op'))
            ->modalDescription(fn () => $this->record->is_op ? 
                __('rbs-minecraft-player-manager::messages.actions.op.desc_deop') : 
                __('rbs-minecraft-player-manager::messages.actions.op.desc_op'))
            ->action(function ($record) {
                $server = Filament::getTenant();
                $serverId = $server->uuid ?? 'server-1';
                $provider = new MinecraftPlayerProvider();
                
                if ($record->is_op) {
                        $provider->deop($serverId, $record->name);
                        \Filament\Notifications\Notification::make()->title(__('rbs-minecraft-player-manager::messages.actions.op.notify_deop'))->success()->send();
                } else {
                        $provider->op($serverId, $record->name);
                        \Filament\Notifications\Notification::make()->title(__('rbs-minecraft-player-manager::messages.actions.op.notify_op'))->success()->send();
                }
                
                $this->refreshPlayer();
            });
    }

    public function clear_inventory(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('clear_inventory')
            ->label(__('rbs-minecraft-player-manager::messages.actions.clear_inventory.label'))
            ->icon('heroicon-m-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription(__('rbs-minecraft-player-manager::messages.actions.clear_inventory.desc'))
            ->action(function ($record) {
                $server = Filament::getTenant();
                $serverId = $server->uuid ?? 'server-1';
                $provider = new MinecraftPlayerProvider();
                $provider->clearInventory($serverId, $record->name);
                \Filament\Notifications\Notification::make()->title(__('rbs-minecraft-player-manager::messages.actions.clear_inventory.notify'))->success()->send();
                
                $this->refreshPlayer();
            });
    }

    public function kick(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('kick')
            ->label(__('rbs-minecraft-player-manager::messages.actions.kick.label'))
            ->icon('heroicon-m-arrow-right-start-on-rectangle')
            ->color('danger')
            ->form([
                \Filament\Forms\Components\TextInput::make('reason')
                    ->label(__('rbs-minecraft-player-manager::messages.actions.kick.reason'))
                    ->default(__('rbs-minecraft-player-manager::messages.actions.kick.default_reason')),
            ])
            ->action(function (array $data, $record) {
                $server = Filament::getTenant();
                $serverId = $server->uuid ?? 'server-1';
                $provider = new MinecraftPlayerProvider();
                $provider->kick($serverId, $record->name, $data['reason']);
                \Filament\Notifications\Notification::make()->title(__('rbs-minecraft-player-manager::messages.actions.kick.notify'))->success()->send();
                
                sleep(1);
                $this->refreshPlayer();
            });
    }

    public function ban(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('ban')
            ->label(fn () => $this->record->is_banned ? __('rbs-minecraft-player-manager::messages.actions.ban.label_unban') : __('rbs-minecraft-player-manager::messages.actions.ban.label_ban'))
            ->icon(fn () => $this->record->is_banned ? 'heroicon-m-check-circle' : 'heroicon-m-no-symbol')
            ->color(fn () => $this->record->is_banned ? 'success' : 'danger')
            ->form(fn () => $this->record->is_banned ? [] : [
                \Filament\Forms\Components\TextInput::make('reason')
                    ->label(__('rbs-minecraft-player-manager::messages.actions.ban.reason'))
                    ->default(__('rbs-minecraft-player-manager::messages.actions.ban.default_reason')),
            ])
            ->requiresConfirmation()
            ->action(function (array $data, $record) {
                $server = Filament::getTenant();
                $serverId = $server->uuid ?? 'server-1';
                $provider = new MinecraftPlayerProvider();
                
                if ($record->is_banned) {
                    $provider->pardon($serverId, $record->name);
                    \Filament\Notifications\Notification::make()->title(__('rbs-minecraft-player-manager::messages.actions.ban.notify_unban'))->success()->send();
                } else {
                    $provider->ban($serverId, $record->name, $data['reason'] ?? null);
                     \Filament\Notifications\Notification::make()->title(__('rbs-minecraft-player-manager::messages.actions.ban.notify_ban'))->success()->send();
                }
                
                sleep(1);
                $this->refreshPlayer();
            });
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
