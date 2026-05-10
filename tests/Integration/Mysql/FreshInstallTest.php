<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

// MysqlIntegrationTestCase is wired in tests/Pest.php for Integration/Mysql/.

describe('lara-content fresh install on MySQL — UUID-first contract (ADR-001)', function () {

    it('emits content_posts.author_id as UUID char(36) and accepts a UUID v7 insert', function () {
        $this->bootstrap();

        // 1. Schema contract: author_id is char(36).
        expect($this->getMysqlColumnType('content_posts', 'author_id'))->toBe('char');
        expect($this->getMysqlColumnLength('content_posts', 'author_id'))->toBe(36);

        // 2. Smoke: insert a user (UUID v7) and a post that references it.
        $userId = Uuid::uuid7()->toString();

        DB::table('users')->insert([
            'id' => $userId,
            'name' => 'Test Author',
            'email' => 'author@example.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $postId = Uuid::uuid7()->toString();

        DB::table('content_posts')->insert([
            'id' => $postId,
            'slug' => 'fresh-install-smoke',
            'title' => json_encode(['en' => 'Fresh install smoke']),
            'author_id' => $userId,
            'content_type' => 1,
            'is_published' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('content_posts')->where('id', $postId)->first();

        expect($row)->not->toBeNull();
        expect($row->author_id)->toBe($userId);
    });

});
