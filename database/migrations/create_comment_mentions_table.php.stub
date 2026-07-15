<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('comments.table_names.mentions', 'comment_mentions'), function (Blueprint $table) {
            $table->id();
            if (config('comments.multi_tenancy.enabled', false)) {
                $tenantColumn = config('comments.multi_tenancy.tenant_column', 'tenant_id');
                (match (config('comments.multi_tenancy.tenant_column_type', 'unsignedBigInteger')) {
                    'uuid'   => $table->uuid($tenantColumn),
                    'string' => $table->string($tenantColumn),
                    default  => $table->unsignedBigInteger($tenantColumn),
                })->nullable()->index();
            }
            $table->foreignId('comment_id')
                ->constrained(config('comments.table_names.comments', 'comments'))
                ->cascadeOnDelete();
            $table->morphs('commenter');
            $table->timestamps();

            $table->unique(['comment_id', 'commenter_id', 'commenter_type']);
        });
    }
};
