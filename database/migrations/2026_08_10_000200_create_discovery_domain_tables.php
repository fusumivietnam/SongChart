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
        Schema::create('discovery_channels', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('key', 96)->unique();
            $table->string('slug', 160)->unique();
            $table->string('name', 160);
            $table->text('description')->nullable();
            $table->string('entity_type', 32)->index();
            $table->string('mode', 24)->index();
            $table->string('status', 24)->default('draft')->index();
            $table->unsignedSmallInteger('rule_schema_version')->default(1);
            $table->jsonb('rules')->nullable();
            $table->jsonb('sorts')->nullable();
            $table->jsonb('presentation');
            $table->unsignedSmallInteger('default_limit')->default(20);
            $table->timestampTz('publish_from')->nullable()->index();
            $table->timestampTz('publish_until')->nullable()->index();
            $table->string('publication_timezone', 64)->default('UTC');
            $table->unsignedInteger('revision')->default(1);
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUlid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('archived_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'publish_from', 'publish_until']);
            $table->index(['entity_type', 'status']);
        });

        Schema::create('discovery_channel_items', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('channel_id')->constrained('discovery_channels')->cascadeOnDelete();
            $table->string('entity_type', 32);
            $table->ulid('entity_id');
            $table->unsignedSmallInteger('position')->nullable();
            $table->boolean('pinned')->default(false);
            $table->timestampTz('starts_at')->nullable();
            $table->timestampTz('ends_at')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['channel_id', 'entity_type', 'entity_id'], 'discovery_channel_item_entity_unique');
            $table->index(['channel_id', 'pinned', 'position']);
        });

        Schema::create('discovery_channel_exclusions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('channel_id')->constrained('discovery_channels')->cascadeOnDelete();
            $table->string('entity_type', 32);
            $table->ulid('entity_id');
            $table->text('reason')->nullable();
            $table->timestampTz('expires_at')->nullable()->index();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['channel_id', 'entity_type', 'entity_id'], 'discovery_channel_exclusion_entity_unique');
        });

        Schema::create('discovery_placements', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('channel_id')->constrained('discovery_channels')->cascadeOnDelete();
            $table->string('surface', 32)->index();
            $table->string('slot', 64);
            $table->string('scope_type', 32)->default('global');
            $table->string('scope_key', 160)->default('global');
            $table->unsignedSmallInteger('position');
            $table->boolean('visible')->default(true)->index();
            $table->timestampTz('starts_at')->nullable();
            $table->timestampTz('ends_at')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUlid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(
                ['surface', 'scope_type', 'scope_key', 'slot', 'position'],
                'discovery_placement_slot_position_unique',
            );
            $table->index(['surface', 'scope_type', 'scope_key', 'visible'], 'discovery_placement_surface_scope_index');
        });

        Schema::create('discovery_projections', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('channel_id')->constrained('discovery_channels')->cascadeOnDelete();
            $table->unsignedInteger('channel_revision');
            $table->unsignedInteger('projection_revision');
            $table->unsignedSmallInteger('rule_schema_version')->default(1);
            $table->string('source_version', 96);
            $table->unsignedSmallInteger('item_count')->default(0);
            $table->jsonb('payload');
            $table->timestampTz('generated_at')->index();
            $table->timestampTz('expires_at')->nullable()->index();
            $table->timestamps();
            $table->unique(['channel_id', 'projection_revision'], 'discovery_projection_revision_unique');
            $table->index(['channel_id', 'generated_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE discovery_channels ADD CONSTRAINT discovery_channels_default_limit_check CHECK (default_limit BETWEEN 1 AND 100)');
            DB::statement('ALTER TABLE discovery_channels ADD CONSTRAINT discovery_channels_revision_check CHECK (revision > 0)');
            DB::statement('ALTER TABLE discovery_channels ADD CONSTRAINT discovery_channels_publish_window_check CHECK (publish_until IS NULL OR publish_from IS NULL OR publish_until > publish_from)');
            DB::statement('ALTER TABLE discovery_channel_items ADD CONSTRAINT discovery_channel_items_position_check CHECK (position IS NULL OR position BETWEEN 1 AND 500)');
            DB::statement('ALTER TABLE discovery_placements ADD CONSTRAINT discovery_placements_position_check CHECK (position BETWEEN 1 AND 500)');
            DB::statement('ALTER TABLE discovery_projections ADD CONSTRAINT discovery_projections_revision_check CHECK (channel_revision > 0 AND projection_revision > 0)');
            DB::statement('ALTER TABLE discovery_projections ADD CONSTRAINT discovery_projections_item_count_check CHECK (item_count BETWEEN 0 AND 100)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('discovery_projections');
        Schema::dropIfExists('discovery_placements');
        Schema::dropIfExists('discovery_channel_exclusions');
        Schema::dropIfExists('discovery_channel_items');
        Schema::dropIfExists('discovery_channels');
    }
};
