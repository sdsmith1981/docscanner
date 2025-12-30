<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['accounting', 'crm', 'other'])->default('accounting');
            $table->enum('provider', ['xero', 'sage', 'quickbooks'])->default('xero');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('scope')->nullable();
            $table->json('provider_data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'provider', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
