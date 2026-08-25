<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canonical_admission_decisions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('metadata_assertion_id')->unique()->constrained('metadata_assertions')->cascadeOnDelete();
            $table->string('entity_type', 32);
            $table->ulid('entity_id');
            $table->string('field_name', 128);
            $table->string('status', 32)->default('pending');
            $table->json('canonical_value')->nullable();
            $table->text('decision_reason')->nullable();
            $table->foreignUlid('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampTz('applied_at')->nullable();
            $table->timestampsTz();

            $table->index(['status', 'created_at']);
            $table->index(['entity_type', 'entity_id', 'field_name'], 'canonical_admission_entity_field_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canonical_admission_decisions');
    }
};
