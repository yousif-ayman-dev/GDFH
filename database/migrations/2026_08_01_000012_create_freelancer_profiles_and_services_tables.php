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
        Schema::create('freelancer_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('title')->nullable();
            $table->text('bio')->nullable();
            $table->decimal('hourly_rate', 10, 2)->default(0.00);
            $table->json('skills')->nullable();
            $table->string('location')->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->integer('reviews_count')->default(0);
            $table->integer('completed_projects_count')->default(0);
            $table->string('availability', 20)->default('available');

            $table->timestamps();

            $table->index(['hourly_rate']);
            $table->index(['rating']);
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->integer('delivery_days')->default(3);
            $table->string('category')->default('تطوير البرمجيات');
            $table->json('skills')->nullable();
            $table->string('status', 20)->default('active');
            $table->string('cover_image')->nullable();
            $table->integer('sales_count')->default(0);
            $table->decimal('rating', 3, 2)->default(5.00);

            $table->timestamps();

            $table->index(['category', 'status']);
            $table->index(['price']);
            $table->index(['rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
        Schema::dropIfExists('freelancer_profiles');
    }
};
