<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       {
        $user = User::firstOrCreate(
            ['email' => 'admin@system.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('12345678'),
            ]
        );

        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
    }
}
