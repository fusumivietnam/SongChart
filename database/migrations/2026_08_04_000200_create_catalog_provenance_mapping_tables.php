<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metadata_sources', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('source_type')->index();
            $table->text('reference_url')->nullable();
            $table->string('license_name')->nullable();
            $table->string('retention_policy')->nullable();
            $table->timestamps();
        });

        Schema::create('external_identifiers', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('entity_type');
            $table->ulid('entity_id');
            $table->string('namespace');
            $table->string('value');
            $table->foreignUlid('metadata_source_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->string('verification_state')->default('unverified')->index();
            $table->timestamps();
            $table->unique(['namespace', 'value']);
            $table->unique(['entity_type', 'entity_id', 'namespace', 'value'], 'entity_external_identifier_unique');
            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('metadata_assertions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('entity_type');
            $table->ulid('entity_id');
            $table->string('field_name');
            $table->json('value');
            $table->string('value_fingerprint', 64);
            $table->foreignUlid('metadata_source_id')->constrained()->restrictOnDelete();
            $table->string('verification_state')->default('candidate')->index();
            $table->decimal('confidence', 5, 4)->nullable();
            $table->timestampTz('observed_at');
            $table->timestampTz('expires_at')->nullable()->index();
            $table->timestamps();
            $table->unique(['entity_type', 'entity_id', 'field_name', 'metadata_source_id', 'value_fingerprint'], 'metadata_assertion_identity_unique');
            $table->index(['entity_type', 'entity_id', 'field_name']);
        });

        Schema::create('metadata_conflicts', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('entity_type');
            $table->ulid('entity_id');
            $table->string('field_name');
            $table->foreignUlid('left_assertion_id')->constrained('metadata_assertions')->cascadeOnDelete();
            $table->foreignUlid('right_assertion_id')->constrained('metadata_assertions')->cascadeOnDelete();
            $table->string('status')->default('open')->index();
            $table->text('resolution_note')->nullable();
            $table->timestampTz('resolved_at')->nullable();
            $table->timestamps();
            $table->unique(['left_assertion_id', 'right_assertion_id']);
            $table->index(['entity_type', 'entity_id', 'field_name']);
        });

        Schema::create('entity_matches', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_entity_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type');
            $table->ulid('entity_id');
            $table->string('status')->default('candidate')->index();
            $table->string('match_method')->default('manual')->index();
            $table->decimal('confidence', 5, 4)->nullable();
            $table->text('evidence')->nullable();
            $table->timestamps();
            $table->unique(['provider_entity_id', 'entity_type', 'entity_id'], 'provider_canonical_match_unique');
            $table->index(['entity_type', 'entity_id', 'status']);
        });

        Schema::create('entity_relationships', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('subject_type');
            $table->ulid('subject_id');
            $table->string('relationship_type')->index();
            $table->string('object_type');
            $table->ulid('object_id');
            $table->foreignUlid('metadata_source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('verification_state')->default('candidate')->index();
            $table->timestamps();
            $table->unique(['subject_type', 'subject_id', 'relationship_type', 'object_type', 'object_id'], 'canonical_relationship_unique');
            $table->index(['subject_type', 'subject_id']);
            $table->index(['object_type', 'object_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_relationships');
        Schema::dropIfExists('entity_matches');
        Schema::dropIfExists('metadata_conflicts');
        Schema::dropIfExists('metadata_assertions');
        Schema::dropIfExists('external_identifiers');
        Schema::dropIfExists('metadata_sources');
    }
};
