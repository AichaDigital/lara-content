<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Tests;

use AichaDigital\LaraContent\ContentServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Stevebauman\Purify\PurifyServiceProvider;

class TestCase extends Orchestra
{
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

        config()->set('content.user_id_type', 'uuid');

        // Run migrations
        $this->runMigrations();
    }

    protected function runMigrations(): void
    {
        // Create users table first (dependency for posts)
        $migration = include __DIR__.'/database/migrations/create_users_table.php';
        $migration->up();

        // Run package migrations
        $migrations = [
            '2025_01_01_000001_create_content_pages_table',
            '2025_01_01_000002_create_content_page_blocks_table',
            '2025_01_01_000003_create_content_posts_table',
            '2025_01_01_000004_create_content_menus_table',
            '2025_01_01_000005_create_content_menu_items_table',
        ];

        foreach ($migrations as $migrationFile) {
            $migration = include __DIR__.'/../database/migrations/'.$migrationFile.'.php';
            $migration->up();
        }
    }
}
