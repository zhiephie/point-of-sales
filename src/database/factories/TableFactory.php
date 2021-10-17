<?php

namespace Database\Factories;

use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TableFactory extends Factory
{
    private static $increment = 1;

    protected $model = Table::class;

    public function definition(): array
    {
        $name = 'Meja ' . self::$increment++;

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
