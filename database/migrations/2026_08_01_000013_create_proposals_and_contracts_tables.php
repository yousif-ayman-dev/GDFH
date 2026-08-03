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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->foreignId('freelancer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->text('cover_letter');
            $table->decimal('bid_amount', 10, 2);
            $table->integer('delivery_days')->default(7);
            $table->string('status', 20)->default('pending'); // pending, accepted, rejected, withdrawn

            $table->timestamps();

            $table->unique(['project_id', 'freelancer_id']);
            $table->index(['status']);
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->foreignId('proposal_id')
                ->nullable()
                ->constrained('proposals')
                ->nullOnDelete();

            $table->foreignId('client_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('freelancer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->string('status', 20)->default('active'); // active, completed, cancelled
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['freelancer_id', 'status']);
            $table->index(['project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('proposals');
    }
};
