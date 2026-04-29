<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('comments.table_names.comments', 'comments'), function (Blueprint $table) {
            $table->timestamp('pinned_at')->nullable()->after('edited_at');
        });
    }

    public function down(): void
    {
        Schema::table(config('comments.table_names.comments', 'comments'), function (Blueprint $table) {
            $table->dropColumn('pinned_at');
        });
    }
};
