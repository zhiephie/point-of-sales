<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $datas = [
            [
                'name' => 'Admin Oke',
                'email' => 'admin@santrikoding.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Kasir Oke',
                'email' => 'kasir@santrikoding.com',
                'password' => Hash::make('password'),
                'role' => 'kasir',
            ]
        ];

        DB::table('users')->insert($datas);
    }
}
