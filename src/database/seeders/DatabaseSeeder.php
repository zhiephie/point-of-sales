<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->command->confirm('Apakah anda ingin me-refresh migration sebelum menjalankan seeder, ini akan menghapus data lama ?')) {
            // Call the php artisan migrate:fresh using Artisan
            $this->command->call('migrate:fresh');
            $this->command->line("Sukses refresh Database");
        }

        $this->call(UserSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(TableSeeder::class);
        $this->call(ItemSeeder::class);
        $this->call(TransactionSeeder::class);

        $this->command->info('Database seeder berhasil');
    }
}
