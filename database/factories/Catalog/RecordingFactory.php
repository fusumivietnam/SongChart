<?php

declare(strict_types=1);

namespace Database\Factories\Catalog;

use App\Models\Catalog\Recording;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Recording> */
final class RecordingFactory extends Factory
{
    protected $model = Recording::class;

    public function definition(): array
    {
        return ['title' => fake()->sentence(3), 'slug' => fake()->unique()->slug(), 'duration_ms' => 240000, 'is_explicit' => false, 'verification_state' => 'unverified'];
    }
}
