<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrichment_attempts', function (Blueprint $table): void {
            $table->jsonb('result_payload')->nullable()->after('last_error');
            $table->text('review_reason')->nullable()->after('result_payload');
            $table->timestampTz('completed_at')->nullable()->after('review_reason');
        });
    }

    public function down(): void
    {
        Schema::table('enrichment_attempts', function (Blueprint $table): void {
            $table->dropColumn(['result_payload', 'review_reason', 'completed_at']);
        });
    }
};
