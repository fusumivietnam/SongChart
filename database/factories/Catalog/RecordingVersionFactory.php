<?php

declare(strict_types=1);

namespace Database\Factories\Catalog;

use App\Models\Catalog\Recording;
use App\Models\Catalog\RecordingVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RecordingVersion> */
final class RecordingVersionFactory extends Factory
{
    protected $model = RecordingVersion::class;

    public function definition(): array
    {
        return ['recording_id' => Recording::factory(), 'name' => 'Live', 'slug' => fake()->unique()->slug(), 'version_type' => 'live', 'duration_ms' => 250000, 'verification_state' => 'unverified'];
    }
}
