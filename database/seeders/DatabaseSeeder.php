<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create Filament admin user
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@sankalpa.test',
            'password' => Hash::make('Qaxje1-tosfog-tyvguk'),
        ]);
    }
}
