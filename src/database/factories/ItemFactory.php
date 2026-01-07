<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->word(),
            'brand_name' => $this->faker->optional()->company(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(100, 100000),
            'condition' => $this->faker->randomElement(['新品', '未使用に近い', '目立った傷や汚れなし', 'やや傷や汚れあり', '傷や汚れあり', '全体的に状態が悪い']),
            'image' => 'items/placeholder.jpg',
            'is_published' => true,
            'sold_at' => null,
        ];
    }
}

