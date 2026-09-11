<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_snapshots', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('chart_key', 96)->index();
            $table->string('metric', 64);
            $table->string('calculation_version', 64);
            $table->string('input_fingerprint', 64);
            $table->timestampTz('snapshot_at')->index();
            $table->jsonb('payload');
            $table->timestamps();

            $table->unique(
                ['chart_key', 'calculation_version', 'input_fingerprint'],
                'chart_snapshot_input_unique',
            );
            $table->unique(
                ['chart_key', 'snapshot_at', 'calculation_version'],
                'chart_snapshot_revision_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_snapshots');
    }
};
