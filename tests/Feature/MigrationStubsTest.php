<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

it('ships migration stubs that are valid PHP', function () {
    $stubs = glob(dirname(__DIR__, 2).'/database/migrations/*.php.stub');

    expect($stubs)->not->toBeEmpty();

    foreach ($stubs as $stub) {
        try {
            token_get_all((string) file_get_contents($stub), TOKEN_PARSE);
        } catch (ParseError $e) {
            $this->fail(basename($stub).' contains a syntax error: '.$e->getMessage());
        }
    }
});

it('runs the published migration stubs and names long indexes explicitly', function () {
    $connection = 'stub_migration_test';

    config()->set("database.connections.{$connection}", [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);

    $stubDir = dirname(__DIR__, 2).'/database/migrations';
    $tmpDir = sys_get_temp_dir().'/comments_migrations_'.Str::random(8);
    mkdir($tmpDir);

    $ordered = [
        'create_comments_table',
        'create_comment_mentions_table',
        'create_comment_reactions_table',
        'create_comment_subscriptions_table',
        'create_comment_attachments_table',
        'add_pinned_at_to_comments_table',
    ];

    foreach ($ordered as $index => $name) {
        copy(
            "{$stubDir}/{$name}.php.stub",
            sprintf('%s/2024_01_01_0000%02d_%s.php', $tmpDir, $index, $name),
        );
    }

    try {
        $this->artisan('migrate', [
            '--database' => $connection,
            '--path' => $tmpDir,
            '--realpath' => true,
        ])->assertSuccessful();

        expect(Schema::connection($connection)->hasTable('comments'))->toBeTrue()
            ->and(Schema::connection($connection)->hasTable('comment_reactions'))->toBeTrue();

        $indexNames = collect(DB::connection($connection)->select("PRAGMA index_list('comment_reactions')"))
            ->pluck('name');

        expect($indexNames)->toContain('comment_reactions_unique');

        foreach ($indexNames as $indexName) {
            expect(strlen((string) $indexName))->toBeLessThanOrEqual(63);
        }
    } finally {
        array_map('unlink', glob("{$tmpDir}/*") ?: []);
        @rmdir($tmpDir);
    }
});
