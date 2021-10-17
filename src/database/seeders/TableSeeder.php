<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = (int)$this->command->ask('Mau generate berapa Meja?', 5);

        $this->command->info("{$count} Meja.");

        $tables = \App\Models\Table::factory()
            ->count($count)
            ->create();

        $this->command->info('Table seeder berhasil');
    }
}
