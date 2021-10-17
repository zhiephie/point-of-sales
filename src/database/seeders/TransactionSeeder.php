<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        \App\Models\Transaction::factory()
            ->times(3)
            ->create();

        $details = [];
        \App\Models\Transaction::all()->each(function ($transaction) use ($faker, $details) {
            $item_id = rand(1, 5);
            $item = \App\Models\Item::find($item_id);
            $quantity = rand(1, 5);
            $price = $item->price;
            $subtotal = ($item->price * $quantity);
            $details[] = [
                'item_id' => $item->id,
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $subtotal
            ];
            $transaction->details()->createMany($details);
        });
    }
}
