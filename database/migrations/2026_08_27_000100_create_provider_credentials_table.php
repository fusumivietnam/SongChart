<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_credentials', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_id')->constrained()->cascadeOnDelete();
            $table->string('kind');
            $table->string('label');
            $table->text('encrypted_secret');
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedInteger('priority')->default(100);
            $table->timestampTz('cooldown_until')->nullable()->index();
            $table->timestampTz('last_used_at')->nullable()->index();
            $table->unsignedInteger('failure_count')->default(0);
            $table->timestamps();

            $table->index(['provider_id', 'kind', 'is_enabled'], 'provider_credentials_resolver_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_credentials');
    }
};
