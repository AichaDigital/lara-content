<?php

declare(strict_types=1);

use AichaDigital\LaraContent\Support\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->json('title'); // Translatable
            $table->json('excerpt')->nullable(); // Translatable
            $table->json('content')->nullable(); // Translatable
            $table->string('featured_image')->nullable();
            MigrationHelper::userIdColumn($table, 'author_id', nullable: true);
            $table->unsignedTinyInteger('content_type')->default(1); // ContentType enum
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_published');
            $table->index('published_at');
            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_posts');
    }
};
