<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_operation_audits', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('provider_id')->nullable()->constrained('providers')->nullOnDelete();
            $table->foreignUlid('provider_import_run_id')->nullable()->constrained('provider_import_runs')->nullOnDelete();
            $table->foreignUlid('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->index();
            $table->string('idempotency_key', 96)->unique();
            $table->jsonb('before_state')->nullable();
            $table->jsonb('after_state')->nullable();
            $table->text('rationale');
            $table->timestampTz('occurred_at')->index();
            $table->timestamps();
            $table->index(['provider_id', 'occurred_at']);
            $table->index(['provider_import_run_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_operation_audits');
    }
};
