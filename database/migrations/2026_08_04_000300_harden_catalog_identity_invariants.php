<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('metadata_conflicts', function (Blueprint $table): void {
            $table->string('normalized_pair_key', 53)->nullable()->after('right_assertion_id');
            $table->unique('normalized_pair_key', 'metadata_conflict_pair_unique');
        });

        Schema::table('entity_matches', function (Blueprint $table): void {
            $table->string('active_match_key', 26)->nullable()->after('status');
            $table->unique('active_match_key', 'provider_active_match_unique');
        });
    }

    public function down(): void
    {
        Schema::table('entity_matches', function (Blueprint $table): void {
            $table->dropUnique('provider_active_match_unique');
            $table->dropColumn('active_match_key');
        });

        Schema::table('metadata_conflicts', function (Blueprint $table): void {
            $table->dropUnique('metadata_conflict_pair_unique');
            $table->dropColumn('normalized_pair_key');
        });
    }
};
