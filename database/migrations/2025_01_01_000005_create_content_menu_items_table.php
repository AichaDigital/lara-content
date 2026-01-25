<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_menu_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('menu_id');
            $table->uuid('parent_id')->nullable();
            $table->json('label'); // Translatable
            $table->unsignedTinyInteger('type')->default(1); // MenuItemType enum
            $table->string('reference')->nullable(); // page slug, post slug, URL, or route name
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('menu_id')
                ->references('id')
                ->on('content_menus')
                ->cascadeOnDelete();

            $table->foreign('parent_id')
                ->references('id')
                ->on('content_menu_items')
                ->nullOnDelete();

            $table->index(['menu_id', 'parent_id', 'position']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_menu_items');
    }
};
