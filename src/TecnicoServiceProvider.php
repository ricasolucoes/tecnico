<?php

namespace Tecnico;

use App;
use Config;
use Tecnico\Facades\Tecnico as TecnicoFacade;
use Tecnico\Services\TecnicoService;
use Illuminate\Foundation\AliasLoader;

use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

use Log;

use Muleta\Traits\Providers\ConsoleTools;
use Route;

class TecnicoServiceProvider extends ServiceProvider
{
    use ConsoleTools;

    public $packageName = 'tecnico';
    const pathVendor = 'ricasolucoes/tecnico';

    public static $aliasProviders = [
        'Tecnico' => \Tecnico\Facades\Tecnico::class,
    ];

    public static $providers = [
        
    ];

    /**
     * Rotas do Menu
     */
    public static $menuItens = [
        // 'Tecnico' => [
        [
            'text'        => 'My Settings',
            'route'       => 'profile.tecnico.home',
            'icon'        => 'fas fa-fw fa-gamepad',
            'icon_color'  => 'blue',
            'label_color' => 'success',
            'section' => "profile",
            // 'access' => \Porteiro\Models\Role::$ADMIN
        ],
        // [
        //     'text'        => 'Root',
        //     'route'       => 'rica.tecnico.home',
        //     'icon'        => 'fas fa-fw fa-flag',
        //     'icon_color'  => 'blue',
        //     'label_color' => 'success',
        //     'section' => "master",
        //     // 'access' => \Porteiro\Models\Role::$ADMIN
        // ],
        // ],
        [
            'text'        => 'Settings',
            'route'       => 'profile.tecnico.home',
            'icon'        => 'fas fa-fw fa-gamepad',
            'icon_color'  => 'blue',
            'label_color' => 'success',
            'section' => "admin",
            // 'access' => \Porteiro\Models\Role::$ADMIN
        ]
    ];
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
        $this->publishMigration();
    }

    /**
     * Publish Tecnico configuration.
     *
     * @return void
     */
    protected function publishConfig(): void
    {
        // Publish config files
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('tecnico.php'),
            __DIR__.'/../publishes/config/settings-mapper.php' => config_path('settings-mapper.php'),
        ], ['config', 'tecnico', 'tecnico-config', 'rica', 'rica-config']);
    }

    /**
     * Publish Tecnico migration.
     *
     * @return void
     */
    protected function publishMigration(): void
    {
        if (! class_exists('TecnicoSetupTables')) {
            // Publish the migration
            $timestamp = date('Y_m_d_His', time());
            $this->publishes([
                __DIR__.'/../database/migrations/2016_05_18_000000_tecnico_setup_tables.php' => database_path('migrations/'.$timestamp.'_tecnico_setup_tables.php'),
              ], ['migrations', 'tecnico', 'tecnico-migrations', 'rica', 'rica-migrations']);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
        $this->registerTecnico();
        $this->registerCommands();
        $this->routes();

        $loader = AliasLoader::getInstance();
        $loader->alias('Tecnico', TecnicoFacade::class);

        $this->app->singleton(
            'tecnico', function () {
                return new Tecnico();
            }
        );
        
        /*
        |--------------------------------------------------------------------------
        | Register the Utilities
        |--------------------------------------------------------------------------
        */
        /**
         * Singleton Tecnico
         */
        $this->app->singleton(
            TecnicoService::class, function ($app) {
                Log::info('Singleton Tecnico');
                return new TecnicoService(\Illuminate\Support\Facades\Config::get('sitec.tecnico'));
            }
        );

        // Register commands
        $this->registerCommandFolders(
            [
            base_path('vendor/ricasolucoes/tecnico/src/Console/Commands') => '\Tecnico\Console\Commands',
            ]
        );
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        /**
         * Transmissor; Routes
         */
        $this->loadRoutesForRiCa(__DIR__.'/../routes');
    }
    /**
     * Register the application bindings.
     *
     * @return void
     */
    protected function registerTecnico()
    {
        $this->app->alias(Tecnico::class, 'tecnico');
    }

    /**
     * Merges user's and tecnico's configs.
     *
     * @return void
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', 'tecnico'
        );
        $this->mergeConfigFrom(__DIR__.'/../publishes/config/settings-mapper.php', 'settings-mapper');
    }

    private function loadViews(): void
    {
        // View namespace
        $viewsPath = $this->getResourcesPath('views');
        $this->loadViewsFrom($viewsPath, 'tecnico');
        $this->publishes(
            [
            $viewsPath => base_path('resources/views/vendor/tecnico'),
            ], ['views', 'tecnico', 'tecnico-views', 'rica', 'rica-views']
        );
    }
    
    private function loadTranslations(): void
    {
        // Publish lanaguage files
        $this->publishes(
            [
            $this->getResourcesPath('lang') => resource_path('lang/vendor/tecnico')
            ], ['lang', 'tecnico', 'tecnico-lang', 'rica', 'rica-lang', 'translations']
        );

        // Load translations
        $this->loadTranslationsFrom($this->getResourcesPath('lang'), 'tecnico');
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'tecnico',
        ];
    }

}
