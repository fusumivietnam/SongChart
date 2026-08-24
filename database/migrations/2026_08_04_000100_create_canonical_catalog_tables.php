<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artists', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('sort_name')->nullable()->index();
            $table->string('slug')->unique();
            $table->string('artist_type')->default('person')->index();
            $table->string('country_code', 2)->nullable()->index();
            $table->string('verification_state')->default('unverified')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('works', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('work_type')->default('song')->index();
            $table->string('language_code', 12)->nullable()->index();
            $table->string('verification_state')->default('unverified')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('recordings', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('work_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->boolean('is_explicit')->default(false)->index();
            $table->string('verification_state')->default('unverified')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('releases', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('release_type')->default('album')->index();
            $table->date('released_on')->nullable()->index();
            $table->string('country_code', 2)->nullable()->index();
            $table->string('barcode', 32)->nullable()->index();
            $table->string('verification_state')->default('unverified')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('release_tracks', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('release_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('recording_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('disc_number')->default(1);
            $table->unsignedSmallInteger('track_number');
            $table->string('title_override')->nullable();
            $table->timestamps();
            $table->unique(['release_id', 'disc_number', 'track_number']);
            $table->unique(['release_id', 'recording_id', 'disc_number', 'track_number'], 'release_recording_position_unique');
        });

        Schema::create('recording_versions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('recording_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version_type')->index();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('verification_state')->default('unverified')->index();
            $table->timestamps();
            $table->unique(['recording_id', 'name']);
        });

        Schema::create('collections', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('visibility')->default('private')->index();
            $table->string('verification_state')->default('unverified')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('collection_items', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('collection_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type');
            $table->ulid('entity_id');
            $table->unsignedInteger('position');
            $table->timestamps();
            $table->unique(['collection_id', 'position']);
            $table->unique(['collection_id', 'entity_type', 'entity_id']);
            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('artist_recording', function (Blueprint $table): void {
            $table->foreignUlid('artist_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('recording_id')->constrained()->cascadeOnDelete();
            $table->string('credit_role')->default('primary');
            $table->unsignedSmallInteger('position')->default(1);
            $table->timestamps();
            $table->primary(['artist_id', 'recording_id', 'credit_role']);
        });

        Schema::create('artist_release', function (Blueprint $table): void {
            $table->foreignUlid('artist_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('release_id')->constrained()->cascadeOnDelete();
            $table->string('credit_role')->default('primary');
            $table->unsignedSmallInteger('position')->default(1);
            $table->timestamps();
            $table->primary(['artist_id', 'release_id', 'credit_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_release');
        Schema::dropIfExists('artist_recording');
        Schema::dropIfExists('collection_items');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('recording_versions');
        Schema::dropIfExists('release_tracks');
        Schema::dropIfExists('releases');
        Schema::dropIfExists('recordings');
        Schema::dropIfExists('works');
        Schema::dropIfExists('artists');
    }
};
