<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');

            $table->string('category')->nullable();

            $table->enum('visibility', [
                'private',
                'marketplace',
            ])->default('private');

            $table->enum('status', [
                'draft',
                'open',
                'in_progress',
                'on_hold',
                'completed',
                'cancelled',
            ])->default('draft');

            $table->enum('budget_type', [
                'fixed',
                'hourly',
            ])->nullable();

            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();

            $table->char('currency', 3)->default('USD');

            $table->date('start_date')->nullable();
            $table->date('deadline')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['owner_id', 'status']);
            $table->index(['visibility', 'status']);
            $table->index(['category', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
