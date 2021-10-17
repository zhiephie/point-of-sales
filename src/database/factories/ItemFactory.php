<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        $itemPrefixes = ['Sweater', 'Pants', 'Shirt', 'Hat', 'Glasses', 'Socks'];
        $name = $this->faker->company . ' ' . Arr::random($itemPrefixes);

        return [
            'category_id' => rand(1, 5),
            'name' => $name,
            'barcode' => $this->faker->ean13,
            'slug' => $name,
            'description' => $this->faker->realText(320),
            'price' => $this->faker->numberBetween(10000, 100000),
            'quantity' => rand(1, 15)
        ];
    }
}
