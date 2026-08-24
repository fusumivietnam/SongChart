<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_log') || Schema::hasColumn('activity_log', 'attribute_changes')) {
            return;
        }

        Schema::table('activity_log', function (Blueprint $table): void {
            $table->json('attribute_changes')->nullable();
        });
    }

    public function down(): void
    {
        // Forward-only repair: the column may have pre-existed on databases
        // created from a transient historical migration shape, so rollback
        // must not destructively remove it.
    }
};
