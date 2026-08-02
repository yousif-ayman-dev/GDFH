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
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'team_id')) {
                $table->foreignId('team_id')
                    ->nullable()
                    ->after('owner_id')
                    ->constrained('teams')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('projects', 'budget')) {
                $table->decimal('budget', 12, 2)->nullable()->after('visibility');
            }

            if (! Schema::hasColumn('projects', 'due_date')) {
                $table->date('due_date')->nullable()->after('start_date');
            }

            if (! Schema::hasColumn('projects', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('completed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'team_id')) {
                $table->dropForeign(['team_id']);
                $table->dropColumn('team_id');
            }

            if (Schema::hasColumn('projects', 'budget')) {
                $table->dropColumn('budget');
            }

            if (Schema::hasColumn('projects', 'due_date')) {
                $table->dropColumn('due_date');
            }

            if (Schema::hasColumn('projects', 'archived_at')) {
                $table->dropColumn('archived_at');
            }
        });
    }
};
