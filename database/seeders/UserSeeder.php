<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin@123'),
                'role_id' => 1,
            ],
            [
                'name' => 'Regular User',
                'email' => 'user@gmail.com',
                'password' => Hash::make('user@123'),
                'role_id' => 2,
            ]
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
