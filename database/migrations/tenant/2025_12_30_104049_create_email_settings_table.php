<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('process_email_attachments')->default(false);
            $table->string('allowed_senders')->nullable(); // JSON array of allowed email addresses
            $table->string('blocked_senders')->nullable(); // JSON array of blocked email addresses
            $table->enum('default_document_type', ['invoice', 'receipt', 'purchase_order', 'other'])->default('invoice');
            $table->boolean('auto_process_attachments')->default(true);
            $table->boolean('delete_processed_emails')->default(false);
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['user_id', 'process_email_attachments']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};
