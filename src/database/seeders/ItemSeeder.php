<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        \App\Models\Item::factory()
            ->times(30)
            ->create();

        \App\Models\Item::all()->each(function ($item) use ($faker) {
            $item->image()->create([
                'url' => $faker->imageUrl(360, 360, 'animals', true, 'cats')
            ]);
        });
    }
}
