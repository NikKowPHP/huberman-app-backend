<?php

namespace Database\Seeders;

use App\Modules\ContentManagement\Models\Episode;
use Illuminate\Database\Seeder;

class EpisodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Episode::factory()->count(5)->create();
    }
}
