<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extensions', function (Blueprint $table): void {
            $table->jsonb('configuration')->nullable()->after('manifest');
            $table->timestampTz('last_health_checked_at')->nullable();
            $table->string('health_status')->nullable()->index();
        });

        Schema::create('extension_snapshots', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('extension_id')->constrained()->cascadeOnDelete();
            $table->string('reason');
            $table->string('active_version')->nullable();
            $table->text('active_path')->nullable();
            $table->boolean('enabled')->default(false);
            $table->jsonb('manifest')->nullable();
            $table->jsonb('configuration')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('trusted_publishers', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('public_key');
            $table->string('fingerprint')->unique();
            $table->string('status')->default('active')->index();
            $table->timestampTz('valid_from')->nullable();
            $table->timestampTz('valid_until')->nullable();
            $table->timestampTz('revoked_at')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trusted_publishers');
        Schema::dropIfExists('extension_snapshots');
        Schema::table('extensions', function (Blueprint $table): void {
            $table->dropColumn(['configuration', 'last_health_checked_at', 'health_status']);
        });
    }
};
