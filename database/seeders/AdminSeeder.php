<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email' => 'admin@cooperative.local',
            'password' => Hash::make('password'),
            'role' => 'system_admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }
}
