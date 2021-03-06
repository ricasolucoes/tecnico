<?php

namespace Tecnico\Tests;

use Dotenv\Dotenv;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadEnvironmentVariables();
        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => realpath(__DIR__.'/../database/migrations'),
        ]);
    }

    protected function loadEnvironmentVariables()
    {
        if (! file_exists(__DIR__.'/../.env')) {
            return;
        }

        $dotEnv = Dotenv::createImmutable(__DIR__.'/..');

        $dotEnv->load();
    }

    protected function getPackageProviders($app)
    {
        return [\Tecnico\TecnicoServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Tecnico' => \Tecnico\Facades\Tecnico::class,
        ];
    }
}
