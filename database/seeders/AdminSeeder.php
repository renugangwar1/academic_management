<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Master Admin',
            'email' => 'admin@nchmct.com',
            'role' => 'admin',
            'password' => Hash::make('admin123'), // default password
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
