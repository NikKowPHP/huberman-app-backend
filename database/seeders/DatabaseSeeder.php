<?php

namespace Database\Seeders;

use App\Modules\UserManagement\Models\User; // Updated namespace
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

        // Add calls to other seeders here as they are created
        // $this->call([
        //     PlanSeeder::class,
        //     // ... other seeders
        // ]);
    }
}