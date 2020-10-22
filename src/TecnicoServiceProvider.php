<?php

namespace Tecnico;

use Illuminate\Support\ServiceProvider;

class TecnicoServiceProvider extends ServiceProvider
{
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
     */
    protected function publishConfig()
    {
        // Publish config files
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('tecnico.php'),
        ]);
    }

    /**
     * Publish Tecnico migration.
     */
    protected function publishMigration()
    {
        if (! class_exists('TecnicoSetupTables')) {
            // Publish the migration
            $timestamp = date('Y_m_d_His', time());
            $this->publishes([
                __DIR__.'/../database/migrations/2016_05_18_000000_tecnico_setup_tables.php' => database_path('migrations/'.$timestamp.'_tecnico_setup_tables.php'),
              ], 'migrations');
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
    }

    /**
     * Register scaffolding command.
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\MakeTecnico::class,
            ]);
        }
    }
}
