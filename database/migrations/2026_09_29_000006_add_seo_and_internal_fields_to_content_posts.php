<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds SEO and internal editorial fields to content_posts (AID-1490).
 *
 * Public (renderable) fields: meta_title, meta_description, featured_image_alt.
 * Internal-only fields (focus_keyword, secondary_keywords, internal_notes) are
 * stored for the editorial workflow but must never be rendered or exposed by
 * any public API surface.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_posts', function (Blueprint $table) {
            $table->json('meta_title')->nullable()->after('excerpt'); // Translatable
            $table->json('meta_description')->nullable()->after('meta_title'); // Translatable
            $table->json('featured_image_alt')->nullable()->after('featured_image'); // Translatable
            $table->string('focus_keyword')->nullable()->after('featured_image_alt');
            $table->json('secondary_keywords')->nullable()->after('focus_keyword');
            $table->json('internal_notes')->nullable()->after('secondary_keywords');
        });
    }

    public function down(): void
    {
        Schema::table('content_posts', function (Blueprint $table) {
            $table->dropColumn([
                'meta_title',
                'meta_description',
                'featured_image_alt',
                'focus_keyword',
                'secondary_keywords',
                'internal_notes',
            ]);
        });
    }
};
