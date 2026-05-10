<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Support;

use Illuminate\Database\Schema\Blueprint;

/**
 * Migration Helper for User foreign key columns.
 *
 * lara-content is UUID-first (see docs/ADR-001-uuid-first.md). FK columns
 * referencing the consumer app's `users.id` are emitted as UUID char(36).
 * bigint and ULID are out of scope.
 */
class MigrationHelper
{
    /**
     * Add a UUID FK column referencing users.id.
     */
    public static function userIdColumn(
        Blueprint $table,
        string $columnName = 'user_id',
        bool $nullable = false
    ): void {
        $column = $table->uuid($columnName);

        if ($nullable) {
            $column->nullable();
        }

        $table->index($columnName);
    }
}
