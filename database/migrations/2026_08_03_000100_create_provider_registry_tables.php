<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('category')->index();
            $table->string('status')->default('research')->index();
            $table->boolean('is_enabled')->default(false)->index();
            $table->string('feature_flag')->nullable();
            $table->text('official_docs_url')->nullable();
            $table->date('policy_reviewed_at')->nullable();
            $table->jsonb('configuration')->nullable();
            $table->timestamps();
        });

        Schema::create('provider_capabilities', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_id')->constrained()->cascadeOnDelete();
            $table->string('capability');
            $table->string('status')->default('unsupported');
            $table->boolean('requires_user_consent')->default(false);
            $table->boolean('market_dependent')->default(false);
            $table->unsignedInteger('cache_ttl_seconds')->nullable();
            $table->jsonb('configuration')->nullable();
            $table->timestamps();
            $table->unique(['provider_id', 'capability']);
        });

        Schema::create('provider_entities', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type')->index();
            $table->string('external_id');
            $table->text('canonical_url')->nullable();
            $table->string('market', 16)->nullable();
            $table->string('raw_fingerprint')->nullable();
            $table->timestampTz('fetched_at')->nullable();
            $table->timestampTz('expires_at')->nullable()->index();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->unique(['provider_id', 'entity_type', 'external_id', 'market'], 'provider_entity_identity_unique');
        });

        Schema::create('provider_sync_runs', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_id')->constrained()->cascadeOnDelete();
            $table->string('operation');
            $table->string('status')->index();
            $table->timestampTz('started_at')->index();
            $table->timestampTz('finished_at')->nullable();
            $table->unsignedInteger('processed_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->text('error_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_sync_runs');
        Schema::dropIfExists('provider_entities');
        Schema::dropIfExists('provider_capabilities');
        Schema::dropIfExists('providers');
    }
};
