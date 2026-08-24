<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrichment_attempts', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('entity_type', 32);
            $table->string('entity_id', 64);
            $table->string('need_kind', 64);
            $table->string('need_key', 128);
            $table->string('provider', 64);
            $table->string('priority', 16);
            $table->string('cost_class', 16);
            $table->text('reason');
            $table->char('idempotency_key', 64)->unique();
            $table->string('status', 24)->default('planned')->index();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->text('last_error')->nullable();
            $table->timestampsTz();

            $table->index(['entity_type', 'entity_id', 'status'], 'enrichment_attempt_entity_status');
            $table->index(['provider', 'status', 'priority'], 'enrichment_attempt_provider_queue');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrichment_attempts');
    }
};
