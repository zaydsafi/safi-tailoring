<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@safitailoring.com'],
            [
                'name' => 'Safi Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '93700000000',
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Demo Customer',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '93700111111',
                'address' => 'Street 5, District 3',
                'city' => 'Kabul',
            ]
        );
    }
}
