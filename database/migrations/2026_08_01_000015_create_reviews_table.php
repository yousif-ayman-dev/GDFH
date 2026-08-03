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
        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();

                $table->foreignId('project_id')
                    ->constrained('projects')
                    ->cascadeOnDelete();

                $table->foreignId('reviewer_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->foreignId('reviewee_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->unsignedTinyInteger('rating')->default(5);
                $table->unsignedTinyInteger('communication_rating')->nullable();
                $table->unsignedTinyInteger('quality_rating')->nullable();
                $table->unsignedTinyInteger('professionalism_rating')->nullable();
                $table->unsignedTinyInteger('deadline_rating')->nullable();

                $table->text('comment')->nullable();
                $table->string('status', 20)->default('published');

                $table->timestamps();

                $table->unique(
                    ['project_id', 'reviewer_id', 'reviewee_id'],
                    'reviews_project_reviewer_reviewee_unique'
                );

                $table->index(['reviewee_id', 'status']);
                $table->index(['project_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Table created by primary migration 2026_07_27_002117_create_reviews_table.php
    }
};
