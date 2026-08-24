<?php

declare(strict_types=1);

namespace Database\Factories\Catalog;

use App\Models\Catalog\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Artist> */
final class ArtistFactory extends Factory
{
    protected $model = Artist::class;

    public function definition(): array
    {
        return ['name' => fake()->name(), 'sort_name' => fake()->lastName(), 'slug' => fake()->unique()->slug(), 'artist_type' => 'person', 'country_code' => 'US', 'verification_state' => 'unverified'];
    }
}
