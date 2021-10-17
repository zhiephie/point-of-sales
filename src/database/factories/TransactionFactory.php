<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $total = random_int(100000, 999999);;
        $pay = ($total + 10000);
        return [
            'user_id' => rand(1, 2),
            'table_id' => rand(1, 2),
            'total' => $total,
            'pay' => $pay,
            'change' => ($pay - $total),
            'status' => array_rand(['pending', 'success']),
        ];
    }
}
