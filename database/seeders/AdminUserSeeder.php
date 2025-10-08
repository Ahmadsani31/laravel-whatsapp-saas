<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin user
        User::firstOrCreate(
            ['email' => 'admin@whatsapp-saas.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@whatsapp-saas.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Default users created successfully!');
        $this->command->info('Admin: admin@whatsapp-saas.com / password123');
    }
}