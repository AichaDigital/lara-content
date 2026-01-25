<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_page_blocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('page_id');
            $table->string('block_type_slug');
            $table->string('zone')->default('main');
            $table->unsignedInteger('position')->default(0);
            $table->json('config')->nullable();
            $table->unsignedInteger('cache_ttl')->nullable(); // null = use default
            $table->timestamps();

            $table->foreign('page_id')
                ->references('id')
                ->on('content_pages')
                ->cascadeOnDelete();

            $table->index(['page_id', 'zone', 'position']);
            $table->index('block_type_slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_page_blocks');
    }
};
