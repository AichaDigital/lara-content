<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Tests\Integration\Mysql;

use AichaDigital\LaraContent\ContentServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Stevebauman\Purify\PurifyServiceProvider;

/**
 * Base test case for MySQL integration (UUID-first contract).
 *
 * lara-content is UUID-first per docs/ADR-001-uuid-first.md. This case takes
 * full control: configure MySQL, create users.id as UUID v7 char(36), then
 * run the package's migrations.
 *
 * Env resolution: primary keys are LARACONTENT_TEST_MYSQL_*; falls back to
 * LARABILL_TEST_MYSQL_* to keep umbrella convenience local. Skip semantics:
 * if neither set is fully populated, markTestSkipped — keeps the suite green
 * in environments without MySQL.
 *
 * @see docs/ADR-001-uuid-first.md
 */
abstract class MysqlIntegrationTestCase extends Orchestra
{
    /**
     * @var array<int, string>
     */
    private const ENV_KEYS = ['HOST', 'PORT', 'DATABASE', 'USERNAME', 'PASSWORD'];

    /**
     * @var array<int, string>
     */
    private const ENV_PREFIXES = ['LARACONTENT_TEST_MYSQL_', 'LARABILL_TEST_MYSQL_'];

    protected function setUp(): void
    {
        if (! self::mysqlEnvAvailable()) {
            $this->markTestSkipped(
                'MySQL integration env not configured. Set LARACONTENT_TEST_MYSQL_HOST, '
                .'LARACONTENT_TEST_MYSQL_PORT, LARACONTENT_TEST_MYSQL_DATABASE, '
                .'LARACONTENT_TEST_MYSQL_USERNAME, LARACONTENT_TEST_MYSQL_PASSWORD '
                .'(or the LARABILL_TEST_MYSQL_* equivalents) to run.'
            );
        }

        parent::setUp();

        $this->dropAllTables();
    }

    protected function tearDown(): void
    {
        if (self::mysqlEnvAvailable()) {
            $this->dropAllTables();
        }

        parent::tearDown();
    }

    public function getEnvironmentSetUp($app): void
    {
        if (! self::mysqlEnvAvailable()) {
            return;
        }

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'mysql',
            'host' => self::resolveEnv('HOST'),
            'port' => (int) self::resolveEnv('PORT'),
            'database' => self::resolveEnv('DATABASE'),
            'username' => self::resolveEnv('USERNAME'),
            'password' => self::resolveEnv('PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => 'InnoDB',
        ]);

        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }

    protected function getPackageProviders($app): array
    {
        return [
            PurifyServiceProvider::class,
            ContentServiceProvider::class,
        ];
    }

    /**
     * Create the consumer-side `users` table with UUID v7 char(36) id, then
     * run the package's migration set.
     */
    protected function bootstrap(): void
    {
        $this->createUsersTable();
        $this->runPackageMigrations();
    }

    private function createUsersTable(): void
    {
        Schema::create('users', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamps();
        });
    }

    private function runPackageMigrations(): void
    {
        $migrations = [
            '2025_01_01_000001_create_content_pages_table',
            '2025_01_01_000002_create_content_page_blocks_table',
            '2025_01_01_000003_create_content_posts_table',
            '2025_01_01_000004_create_content_menus_table',
            '2025_01_01_000005_create_content_menu_items_table',
        ];

        foreach ($migrations as $file) {
            $migration = include __DIR__.'/../../../database/migrations/'.$file.'.php';
            $migration->up();
        }
    }

    public static function mysqlEnvAvailable(): bool
    {
        foreach (self::ENV_PREFIXES as $prefix) {
            if (self::prefixComplete($prefix)) {
                return true;
            }
        }

        return false;
    }

    private static function prefixComplete(string $prefix): bool
    {
        foreach (self::ENV_KEYS as $key) {
            $value = env($prefix.$key);
            if ($value === null || $value === '') {
                return false;
            }
        }

        return true;
    }

    private static function resolveEnv(string $key): mixed
    {
        foreach (self::ENV_PREFIXES as $prefix) {
            if (self::prefixComplete($prefix)) {
                return env($prefix.$key);
            }
        }

        return null;
    }

    /**
     * Drop every table in the test database. Uses FK-checks bypass so order
     * does not matter.
     */
    protected function dropAllTables(): void
    {
        $database = self::resolveEnv('DATABASE');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            $tables = DB::select(
                'SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?',
                [$database]
            );

            foreach ($tables as $row) {
                DB::statement("DROP TABLE IF EXISTS `{$row->name}`");
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    protected function getMysqlColumnType(string $table, string $column): string
    {
        $row = DB::selectOne(
            'SELECT DATA_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [self::resolveEnv('DATABASE'), $table, $column]
        );

        return $row !== null ? strtolower($row->DATA_TYPE) : '';
    }

    protected function getMysqlColumnLength(string $table, string $column): ?int
    {
        $row = DB::selectOne(
            'SELECT CHARACTER_MAXIMUM_LENGTH AS len FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [self::resolveEnv('DATABASE'), $table, $column]
        );

        return $row !== null && $row->len !== null ? (int) $row->len : null;
    }
}
