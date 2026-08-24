<?php

declare(strict_types=1);

namespace Database\Factories\Catalog;

use App\Models\Catalog\Release;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Release> */
final class ReleaseFactory extends Factory
{
    protected $model = Release::class;

    public function definition(): array
    {
        return ['title' => fake()->sentence(3), 'slug' => fake()->unique()->slug(), 'release_type' => 'album', 'released_on' => fake()->date(), 'country_code' => 'US', 'verification_state' => 'unverified'];
    }
}
