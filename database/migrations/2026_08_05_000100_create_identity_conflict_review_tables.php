<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_conflict_reviews', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_entity_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type');
            $table->string('status')->default('open')->index();
            $table->json('candidate_entity_ids');
            $table->json('evidence')->nullable();
            $table->timestampTz('opened_at');
            $table->timestampTz('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['provider_entity_id', 'status']);
        });

        Schema::create('identity_conflict_decisions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('identity_conflict_review_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->index();
            $table->ulid('selected_entity_id')->nullable();
            $table->text('rationale')->nullable();
            $table->json('before_snapshot');
            $table->json('after_snapshot');
            $table->timestampTz('created_at');
            $table->index(['identity_conflict_review_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_conflict_decisions');
        Schema::dropIfExists('identity_conflict_reviews');
    }
};
