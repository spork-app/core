<?php

namespace Spork\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Spork\Core\Models\FeatureList;

class FeatureDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // This seeder should have bits for everything.
        FeatureList::factory()->create([
            'feature' => 'core',
            'settings' => [],
            'name' => 'Core Feature',
        ]);
    }
}
