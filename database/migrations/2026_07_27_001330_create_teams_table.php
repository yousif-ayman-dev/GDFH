<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->string('slug')->unique();

            $table->enum('type', [
                'permanent',
                'project_based',
            ])->default('permanent');

            $table->enum('visibility', [
                'private',
                'public',
            ])->default('private');

            $table->timestamps();

            $table->index(['type', 'visibility']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
