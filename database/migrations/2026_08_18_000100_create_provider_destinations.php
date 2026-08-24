<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_destinations', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('entity_type', 32)->index();
            $table->ulid('entity_id')->index();
            $table->string('provider_resource_id', 64);
            $table->text('url');
            $table->string('title')->nullable();
            $table->string('channel_id', 64)->nullable();
            $table->string('channel_title')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->boolean('is_embeddable')->default(false);
            $table->string('privacy_status', 32)->nullable();
            $table->unsignedTinyInteger('match_score')->default(0);
            $table->string('review_state', 32)->default('approved');
            $table->jsonb('evidence')->nullable();
            $table->timestampTz('verified_at');
            $table->timestampTz('last_checked_at')->nullable();
            $table->timestamps();

            $table->unique(['provider_id', 'provider_resource_id', 'entity_type', 'entity_id'], 'provider_destinations_resource_entity_unique');
            $table->index(['entity_type', 'entity_id', 'review_state'], 'provider_destinations_entity_review_idx');
        });

        DB::table('providers')->where('slug', 'youtube')->update([
            'status' => 'approved',
            'official_docs_url' => 'https://developers.google.com/youtube/v3',
            'policy_reviewed_at' => '2026-08-18',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_destinations');
    }
};
