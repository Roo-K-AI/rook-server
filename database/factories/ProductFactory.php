<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'seo_title' => $this->faker->sentence(4),
            'meta_description' => $this->faker->text(150),
            'long_description' => $this->faker->paragraph(3),
            'benefits' => [$this->faker->sentence(), $this->faker->sentence()],
            'specifications' => [
                'color' => $this->faker->safeColorName(),
                'weight' => '500g',
            ],
            'usage_tips' => [$this->faker->sentence()],
            'seo_tags' => [$this->faker->word(), $this->faker->word()],
            'rook_job_id' => 'job-' . $this->faker->uuid(),
            'rook_status' => 'completed',
        ];
    }
}

