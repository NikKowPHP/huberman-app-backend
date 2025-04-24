<?php

namespace Database\Seeders;

use App\Modules\ContentManagement\Models\Episode;
use App\Modules\ContentManagement\Models\Protocol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EpisodeProtocolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $episodes = Episode::all();
        $protocols = Protocol::all();

        foreach ($episodes as $episode) {
            foreach ($protocols as $protocol) {
                if (rand(0, 1)) {
                    DB::table('episode_protocol')->insert([
                        'episode_id' => $episode->id,
                        'protocol_id' => $protocol->id,
                    ]);
                }
            }
        }
    }
}
