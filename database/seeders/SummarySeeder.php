<?php

namespace Database\Seeders;

use App\Modules\ContentManagement\Models\Summary;
use App\Modules\ContentManagement\Models\Episode;
use Illuminate\Database\Seeder;

class SummarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $episodes = Episode::all();

        foreach ($episodes as $episode) {
            Summary::factory()->count(2)->create(['episode_id' => $episode->id]);
        }
    }
}
