<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->count(5)->create(['gender' => 'female']);
        User::factory()->count(5)->create(['gender' => 'male']);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'gender' => 'female',
        ]);
    }
}
