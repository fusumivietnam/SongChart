<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extensions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('type')->index();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('state')->default('installed')->index();
            $table->string('active_version')->nullable();
            $table->text('active_path')->nullable();
            $table->boolean('enabled')->default(false)->index();
            $table->jsonb('manifest');
            $table->timestampTz('installed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('extension_releases', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('extension_id')->constrained()->cascadeOnDelete();
            $table->string('version');
            $table->text('path');
            $table->char('checksum_sha256', 64);
            $table->string('signature_status')->default('unsigned');
            $table->string('status')->default('staged')->index();
            $table->jsonb('manifest');
            $table->timestampTz('installed_at')->nullable();
            $table->timestamps();
            $table->unique(['extension_id', 'version']);
            $table->unique(['extension_id', 'checksum_sha256']);
        });

        Schema::create('extension_operations', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('extension_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->index();
            $table->string('status')->index();
            $table->foreignUlid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('source')->nullable();
            $table->char('checksum_sha256', 64)->nullable();
            $table->jsonb('details')->nullable();
            $table->timestampTz('started_at');
            $table->timestampTz('finished_at')->nullable();
            $table->text('error_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extension_operations');
        Schema::dropIfExists('extension_releases');
        Schema::dropIfExists('extensions');
    }
};
