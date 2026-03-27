<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'kuweza-admin@bnpl.com'],
            [
                'name' => 'David',
                'role_id' => 1,
                'password' => Hash::make('kuweza-admin123!@'),
                'phone' => '+1234567890',
            ]
        );
    }
}
