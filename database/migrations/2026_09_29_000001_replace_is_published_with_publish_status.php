<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the `is_published` boolean with the `publish_status` enum column
 * (AID-1490). Backfills existing rows: published rows keep their state, every
 * other row becomes `draft`.
 *
 * Index inventory differs per table: content_posts has the composite
 * (is_published, published_at) index, content_pages does not.
 */
return new class extends Migration
{
    private const TABLES = [
        'content_posts' => true,
        'content_pages' => false,
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table => $hasCompositeIndex) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('publish_status')->default('draft')->after('content_type');
            });

            DB::statement(sprintf(
                "update %s set publish_status = case when is_published = 1 then 'published' else 'draft' end",
                $table
            ));

            Schema::table($table, function (Blueprint $t) use ($table, $hasCompositeIndex) {
                if ($hasCompositeIndex) {
                    $t->dropIndex($table.'_is_published_published_at_index');
                }
                $t->dropIndex($table.'_is_published_index');
                $t->dropColumn('is_published');
            });

            Schema::table($table, function (Blueprint $t) {
                $t->index('publish_status');
                $t->index(['publish_status', 'published_at']);
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table => $hasCompositeIndex) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropIndex($table.'_publish_status_published_at_index');
                $t->dropIndex($table.'_publish_status_index');
                $t->boolean('is_published')->default(false)->after('content_type');
            });

            DB::statement(sprintf(
                "update %s set is_published = case when publish_status = 'published' then 1 else 0 end",
                $table
            ));

            Schema::table($table, function (Blueprint $t) use ($hasCompositeIndex) {
                $t->dropColumn('publish_status');
                $t->index('is_published');
                if ($hasCompositeIndex) {
                    $t->index(['is_published', 'published_at']);
                }
            });
        }
    }
};
