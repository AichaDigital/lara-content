<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Tests;

use AichaDigital\LaraContent\ContentServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use Stevebauman\Purify\PurifyServiceProvider;

class TestCase extends Orchestra
{
    // Migrations are registered with the migrator by directory (not a hardcoded
    // include/->up() list) and run by RefreshDatabase, so a new package migration
    // is picked up automatically. Two directories: the package schema and the
    // consumer-side `users` fixture (FK columns are constraint-less UUID char(36)
    // via MigrationHelper). See the umbrella CLAUDE.md lesson (2026-06-27).
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'AichaDigital\\LaraContent\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            PurifyServiceProvider::class,
            ContentServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Register both migration directories with the migrator (the same
        // mechanism Laravel's loadMigrationsFrom uses): the package schema and
        // the consumer-side users fixture. RefreshDatabase runs them up-only, so
        // a non-reversible fixture down() is never invoked.
        $app->afterResolving('migrator', function ($migrator): void {
            $migrator->path(__DIR__.'/../database/migrations');
            $migrator->path(__DIR__.'/database/migrations');
        });
    }
}
