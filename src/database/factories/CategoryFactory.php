<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->jobTitle;
        $nameArr = explode(' ', $name);

        $name = trim($nameArr[0]);

        return [
            'name' => $name,
            'slug' => $name,
        ];
    }
}
