<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entity_relationships', function (Blueprint $table): void {
            $table->json('metadata')->nullable();
        });

        Schema::table('artist_recording', function (Blueprint $table): void {
            $table->string('credited_name')->nullable();
            $table->string('join_phrase', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('artist_recording', function (Blueprint $table): void {
            $table->dropColumn(['credited_name', 'join_phrase']);
        });

        Schema::table('entity_relationships', function (Blueprint $table): void {
            $table->dropColumn('metadata');
        });
    }
};
