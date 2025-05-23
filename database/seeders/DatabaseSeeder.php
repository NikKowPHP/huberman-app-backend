<?php

namespace Database\Seeders;

use App\Modules\UserManagement\Models\User; // Updated namespace
use Database\Seeders\PlanSeeder;
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

        $this->call([
            PlanSeeder::class,
        ]);

        $this->call([
            EpisodeSeeder::class,
            ProtocolSeeder::class,
            SummarySeeder::class,
            EpisodeProtocolSeeder::class,
        ]);
    }
}
