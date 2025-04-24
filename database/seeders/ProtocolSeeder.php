<?php

namespace Database\Seeders;

use App\Modules\ContentManagement\Models\Protocol;
use Illuminate\Database\Seeder;

class ProtocolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Protocol::factory()->count(5)->create();

        Protocol::factory()->count(2)->create([
            'is_free' => false,
        ]);
    }
}
