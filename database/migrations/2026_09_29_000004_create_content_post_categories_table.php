<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_post_categories', function (Blueprint $table) {
            $table->uuid('post_id');
            $table->uuid('category_id');
            $table->primary(['post_id', 'category_id']);

            $table->foreign('post_id')->references('id')->on('content_posts')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('content_categories')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_post_categories');
    }
};
