<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            if (! Schema::hasColumn('comments', 'commentable_type')) {
                $table->string('commentable_type')->nullable()->after('user_id');
                $table->unsignedBigInteger('commentable_id')->nullable()->after('commentable_type');
                $table->index(['commentable_type', 'commentable_id']);
            }

            if (! Schema::hasColumn('comments', 'body')) {
                $table->text('body')->nullable()->after('parent_id');
            }

            if (Schema::hasColumn('comments', 'task_id')) {
                $table->foreignId('task_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasColumn('comments', 'commentable_type')) {
                $table->dropIndex(['commentable_type', 'commentable_id']);
                $table->dropColumn(['commentable_type', 'commentable_id']);
            }

            if (Schema::hasColumn('comments', 'body')) {
                $table->dropColumn('body');
            }
        });
    }
};
