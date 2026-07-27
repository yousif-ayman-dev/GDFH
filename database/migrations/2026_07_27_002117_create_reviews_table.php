<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // Project where the collaboration happened.
            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            // User who writes the review.
            $table->foreignId('reviewer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // User who receives the review.
            $table->foreignId('reviewee_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Overall rating: 1–5.
            $table->unsignedTinyInteger('rating');

            // Optional detailed ratings.
            $table->unsignedTinyInteger('communication_rating')->nullable();
            $table->unsignedTinyInteger('quality_rating')->nullable();
            $table->unsignedTinyInteger('professionalism_rating')->nullable();
            $table->unsignedTinyInteger('deadline_rating')->nullable();

            $table->text('comment')->nullable();

            // Allows moderation without deleting review history.
            $table->enum('status', [
                'pending',
                'published',
                'hidden',
            ])->default('published');

            $table->timestamps();

            // Same user cannot review the same person twice
            // for the same project.
            $table->unique(
                ['project_id', 'reviewer_id', 'reviewee_id'],
                'reviews_project_reviewer_reviewee_unique'
            );

            $table->index(['reviewee_id', 'status']);
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
