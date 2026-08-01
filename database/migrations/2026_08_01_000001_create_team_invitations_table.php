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
        Schema::create('team_invitations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('team_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('inviter_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('invitee_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('role', [
                'owner',
                'admin',
                'member',
                'viewer',
            ])->default('member');

            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'cancelled',
                'expired',
            ])->default('pending');

            $table->uuid('token')->unique();
            $table->text('message')->nullable();

            $table->timestamp('expires_at');
            $table->timestamp('responded_at')->nullable();

            $table->timestamps();

            $table->unique(['team_id', 'invitee_id', 'status']);
            $table->index(['team_id', 'status']);
            $table->index(['invitee_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_invitations');
    }
};
