<?php

namespace R0B0TB0SS\GamePlayerManager;

use Filament\Contracts\Plugin;
use Filament\Panel;

use App\Contracts\Plugins\HasPluginSettings;
use App\Traits\EnvironmentWriterTrait;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;

class GamePlayerManagerPlugin implements Plugin, HasPluginSettings
{
    use EnvironmentWriterTrait;

    public function getId(): string
    {
        return 'rbs-minecraft-player-manager';
    }

    public function register(Panel $panel): void
    {
        $id = str($panel->getId())->title();
        
        // Discover Resources, Pages, and Widgets dynamically based on panel ID (Admin, Server, etc.)
        $panel->discoverResources(plugin_path($this->getId(), "src/Filament/$id/Resources"), "R0B0TB0SS\\GamePlayerManager\\Filament\\$id\\Resources");
        $panel->discoverWidgets(plugin_path($this->getId(), "src/Filament/$id/Widgets"), "R0B0TB0SS\\GamePlayerManager\\Filament\\$id\\Widgets");
    }

    public function boot(Panel $panel): void
    {
        // Register Views
        \Illuminate\Support\Facades\View::addNamespace('rbs-minecraft-player-manager', plugin_path('minecraft-player-manager', 'resources/views'));
        \Illuminate\Support\Facades\Lang::addNamespace('rbs-minecraft-player-manager', __DIR__ . '/../resources/lang');

        // Only register widgets for the Server panel
        if ($panel->getId() === 'server') {
            \App\Filament\Server\Pages\Console::registerCustomWidgets(
                \App\Enums\ConsoleWidgetPosition::AboveConsole, 
                [\R0B0TB0SS\GamePlayerManager\Filament\Server\Widgets\PlayerCountWidget::class]
            );
        }
    }

    public function getSettingsForm(): array
    {
        return [
            Toggle::make('rcon_enabled')
                ->label(__('rbs-minecraft-player-manager::messages.settings.rcon_enabled'))
                ->helperText(__('rbs-minecraft-player-manager::messages.settings.rcon_enabled_helper'))
                ->default(fn () => config('rbs-minecraft-player-manager.rcon_enabled')),
            \Filament\Forms\Components\TextInput::make('nav_sort')
                ->label(__('rbs-minecraft-player-manager::messages.settings.nav_sort'))
                ->helperText(__('rbs-minecraft-player-manager::messages.settings.nav_sort_helper'))
                ->numeric()
                ->default(fn () => config('rbs-minecraft-player-manager.nav_sort')),
        ];
    }

    public function getSettingsFormData(): array
    {
        return config('rbs-minecraft-player-manager');;
    }

    public function saveSettings(array $data): void
    {
        $this->writeToEnvironment([
            'MC_PLAYER_MANAGER_RCON_ENABLED' => $data['rcon_enabled'],
            'MC_PLAYER_MANAGER_NAV_SORT' => $data['nav_sort'],
        ]);

        Notification::make()
            ->title(__('rbs-minecraft-player-manager::messages.settings.saved'))
            ->success()
            ->send();
    }
}