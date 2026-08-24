<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_import_runs', function (Blueprint $table): void {
            $table->timestampTz('heartbeat_at')->nullable()->index();
            $table->timestampTz('resume_after')->nullable()->index();
            $table->timestampTz('cancellation_requested_at')->nullable()->index();
            $table->unsignedInteger('attempts')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('provider_import_runs', function (Blueprint $table): void {
            $table->dropColumn(['heartbeat_at', 'resume_after', 'cancellation_requested_at', 'attempts']);
        });
    }
};
