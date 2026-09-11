<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_metric_observations', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('observation_id', 64)->unique();
            $table->ulid('canonical_recording_id');
            $table->string('provider', 64);
            $table->string('provider_item_id', 255);
            $table->string('metric', 128);
            $table->string('metric_unit', 64);
            $table->string('metric_semantics_version', 128);
            $table->decimal('value', 30, 8);
            $table->string('value_kind', 16);
            $table->timestampTz('observed_at');
            $table->timestampTz('fetched_at')->nullable();
            $table->text('source_reference')->nullable();
            $table->timestampsTz();

            $table->index(['canonical_recording_id', 'metric', 'observed_at'], 'chart_metric_observations_recording_metric_observed_idx');
            $table->index(['provider', 'metric', 'observed_at'], 'chart_metric_observations_provider_metric_observed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_metric_observations');
    }
};
