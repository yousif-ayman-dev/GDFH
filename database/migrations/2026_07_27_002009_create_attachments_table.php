<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->nullableMorphs('attachable');

            $table->string('original_name');
            $table->string('stored_name')->nullable();

            $table->string('disk')->default('local');
            $table->string('path');

            $table->string('mime_type')->nullable();
            $table->string('extension', 20)->nullable();

            $table->unsignedBigInteger('size')->nullable();

            $table->string('checksum', 64)->nullable();

            $table->enum('visibility', [
                'private',
                'project',
                'public',
            ])->default('private');

            $table->timestamps();

            $table->index(['uploaded_by', 'created_at']);
            $table->index('checksum');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
