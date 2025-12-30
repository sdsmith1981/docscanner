<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processing_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->unsignedInteger('attempt_number')->default(1);
            $table->text('error_message')->nullable();
            $table->unsignedInteger('processing_time_ms')->nullable();
            $table->json('result_data')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'attempt_number']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processing_attempts');
    }
};
