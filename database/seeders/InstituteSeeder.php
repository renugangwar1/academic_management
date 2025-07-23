<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Institute; 
use Illuminate\Support\Facades\Hash;

class InstituteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutes = [
    [
        'name' => 'Institute of Hotel Management Delhi',
        'code' => 'IHMDEL',
        'email' => 'contact@ihmdelhi.edu.in',
        'contact_phone' => '011-12345678',
        'password' => Hash::make('password123'), // ✅ default password
    ],
    [
        'name' => 'Institute of Hotel Management Mumbai',
        'code' => 'IHMMUM',
        'email' => 'info@ihmmumbai.in',
        'contact_phone' => '022-23456789',
        'password' => Hash::make('password123'),
    ],
    [
        'name' => 'Institute of Hotel Management Bangalore',
        'code' => 'IHMBLR',
        'email' => 'admin@ihmbangalore.ac.in',
        'contact_phone' => '080-98765432',
        'password' => Hash::make('password123'),
    ],
];

        foreach ($institutes as $institute) {
            Institute::create($institute);
        }
    }

}
