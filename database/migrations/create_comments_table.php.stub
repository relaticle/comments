<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('comments.table_names.comments', 'comments'), function (Blueprint $table) {
            $table->id();
            if (config('comments.multi_tenancy.enabled', false)) {
                $tenantColumn = config('comments.multi_tenancy.tenant_column', 'tenant_id');
                (match (config('comments.multi_tenancy.tenant_column_type', 'unsignedBigInteger')) {
                    'uuid'   => $table->uuid($tenantColumn),
                    'string' => $table->string($tenantColumn),
                    default  => $table->unsignedBigInteger($tenantColumn),
                })->nullable()->index();
            }
            $table->morphs('commentable');
            $table->morphs('commenter');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained(config('comments.table_names.comments', 'comments'))
                ->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('edited_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['commentable_type', 'commentable_id', 'parent_id']);
        });
    }
};
