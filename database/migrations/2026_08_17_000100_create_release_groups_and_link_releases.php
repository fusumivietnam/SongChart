<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('release_groups', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('primary_type')->nullable()->index();
            $table->jsonb('secondary_types')->nullable();
            $table->date('first_release_date')->nullable()->index();
            $table->string('disambiguation')->nullable();
            $table->string('verification_state')->default('unverified')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('releases', function (Blueprint $table): void {
            $table->foreignUlid('release_group_id')->nullable()->after('id')->constrained('release_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('releases', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('release_group_id');
        });
        Schema::dropIfExists('release_groups');
    }
};
