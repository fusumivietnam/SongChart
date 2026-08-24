<?php

declare(strict_types=1);

namespace Database\Factories\Catalog;

use App\Models\Catalog\MetadataSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MetadataSource> */
final class MetadataSourceFactory extends Factory
{
    protected $model = MetadataSource::class;

    public function definition(): array
    {
        return ['key' => fake()->unique()->slug(), 'name' => fake()->company(), 'source_type' => 'editorial', 'retention_policy' => 'project_owned'];
    }
}
