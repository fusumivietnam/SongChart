<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('songchart_environment_guard', function (Blueprint $table): void {
            $table->string('marker', 32)->primary();
            $table->string('environment', 32);
            $table->string('database_role', 32);
            $table->timestampTz('created_at')->useCurrent();
        });

        $environment = app()->environment();
        $role = $environment === 'testing'
            ? 'testing'
            : (in_array($environment, ['production', 'staging'], true) ? 'production' : 'development');

        DB::table('songchart_environment_guard')->insert([
            'marker' => 'songchart',
            'environment' => $environment,
            'database_role' => $role,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('songchart_environment_guard');
    }
};
