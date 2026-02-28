<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusStopSeeder extends Seeder
{
    public function run()
    {
        $stops = [
            ['name' => 'Stacijas laukums', 'latitude' => 57.3125, 'longitude' => 25.2680],
            ['name' => 'Raunas iela', 'latitude' => 57.3105, 'longitude' => 25.2715],
            ['name' => 'Valmieras iela', 'latitude' => 57.3140, 'longitude' => 25.2650],
            ['name' => 'Piebalgas iela', 'latitude' => 57.3098, 'longitude' => 25.2698],
            ['name' => 'Lenču iela', 'latitude' => 57.3152, 'longitude' => 25.2725],
            ['name' => 'Maija parks', 'latitude' => 57.3118, 'longitude' => 25.2640],
            ['name' => 'Priekuļi', 'latitude' => 57.3072, 'longitude' => 25.3556],
        ];

        foreach ($stops as $stop) {
            DB::table('bus_stops')->insert($stop);
        }
    }
}
