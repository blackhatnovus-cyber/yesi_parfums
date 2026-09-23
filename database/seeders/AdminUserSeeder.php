<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Issey Parfums Administrator',
                'email' => 'admin@isseyparfums.test',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'active',
            ],
        );
    }
}
