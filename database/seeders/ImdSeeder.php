<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Imd;

class ImdSeeder extends Seeder
{
    public function run(): void
    {
        Imd::factory()->count(10)->create();
    }
}
