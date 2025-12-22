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
        // User::factory(10)->create();

        User::query()->create([
            'nama' => 'Administrator',
            'email' => 'admin@anterin.test',
            'password' => bcrypt('1q2w3e4r5t'),
            'role' => 'admin',
            'is_active' => true
        ]);
    }
}
