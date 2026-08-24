<?php

declare(strict_types=1);

namespace Database\Factories\Catalog;

use App\Models\Catalog\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Collection> */
final class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        return ['title' => fake()->sentence(3), 'slug' => fake()->unique()->slug(), 'description' => fake()->sentence(), 'visibility' => 'private', 'verification_state' => 'unverified'];
    }
}
