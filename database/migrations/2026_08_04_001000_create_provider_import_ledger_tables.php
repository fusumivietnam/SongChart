<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_import_runs', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('operation')->index();
            $table->string('status')->index();
            $table->string('requested_by_type')->nullable();
            $table->string('requested_by_id')->nullable();
            $table->string('cursor_start')->nullable();
            $table->string('cursor_end')->nullable();
            $table->string('configuration_hash', 64);
            $table->jsonb('configuration')->nullable();
            $table->jsonb('statistics')->nullable();
            $table->text('error_summary')->nullable();
            $table->timestampTz('started_at')->nullable()->index();
            $table->timestampTz('finished_at')->nullable();
            $table->timestamps();
            $table->index(['provider_id', 'operation', 'status'], 'provider_import_run_lookup');
        });

        Schema::create('provider_import_requests', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_import_run_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('method', 16);
            $table->text('endpoint');
            $table->jsonb('query')->nullable();
            $table->jsonb('request_headers')->nullable();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->jsonb('response_headers')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedInteger('attempt')->default(1);
            $table->timestampTz('requested_at')->index();
            $table->timestampTz('responded_at')->nullable();
            $table->timestamps();
            $table->unique(['provider_import_run_id', 'sequence'], 'provider_import_request_sequence_unique');
        });

        Schema::create('provider_import_payloads', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_import_run_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('provider_import_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider_entity_type')->index();
            $table->string('provider_entity_id');
            $table->string('payload_hash', 64);
            $table->jsonb('payload');
            $table->string('schema_version')->default('1');
            $table->timestampTz('received_at')->index();
            $table->timestamps();
            $table->unique(['provider_import_run_id', 'provider_entity_type', 'provider_entity_id', 'payload_hash'], 'provider_import_payload_identity_unique');
        });

        Schema::create('provider_import_items', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_import_run_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('provider_import_payload_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider_entity_type')->index();
            $table->string('provider_entity_id');
            $table->string('status')->index();
            $table->string('canonical_entity_type')->nullable();
            $table->ulid('canonical_entity_id')->nullable();
            $table->string('normalizer_version')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->jsonb('result')->nullable();
            $table->timestampTz('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['provider_import_run_id', 'provider_entity_type', 'provider_entity_id'], 'provider_import_item_identity_unique');
        });

        Schema::create('provider_import_failures', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_import_run_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('provider_import_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('provider_import_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stage')->index();
            $table->string('kind')->index();
            $table->string('error_code')->nullable();
            $table->text('message');
            $table->jsonb('context')->nullable();
            $table->boolean('retryable')->default(false)->index();
            $table->timestampTz('occurred_at')->index();
            $table->timestamps();
        });

        Schema::create('provider_import_checkpoints', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_import_run_id')->constrained()->cascadeOnDelete();
            $table->string('checkpoint_key');
            $table->text('cursor')->nullable();
            $table->jsonb('state')->nullable();
            $table->timestampTz('committed_at')->index();
            $table->timestamps();
            $table->unique(['provider_import_run_id', 'checkpoint_key'], 'provider_import_checkpoint_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_import_checkpoints');
        Schema::dropIfExists('provider_import_failures');
        Schema::dropIfExists('provider_import_items');
        Schema::dropIfExists('provider_import_payloads');
        Schema::dropIfExists('provider_import_requests');
        Schema::dropIfExists('provider_import_runs');
    }
};
