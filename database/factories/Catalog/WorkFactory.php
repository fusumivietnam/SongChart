<?php

declare(strict_types=1);

namespace Database\Factories\Catalog;

use App\Models\Catalog\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Work> */
final class WorkFactory extends Factory
{
    protected $model = Work::class;

    public function definition(): array
    {
        return ['title' => fake()->sentence(3), 'slug' => fake()->unique()->slug(), 'work_type' => 'song', 'language_code' => 'en', 'verification_state' => 'unverified'];
    }
}
