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
            ['email' => 'admin@fadilah.com'],
            [
                'name' => 'Admin Fadilah',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'owner@fadilah.com'],
            [
                'name' => 'Owner Fadilah',
                'password' => Hash::make('password123'),
                'role' => 'owner',
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@gmail.com'],
            [
                'name' => 'Pelanggan Setia',
                'password' => Hash::make('password123'),
                'role' => 'customer',
            ]
        );
    }
}