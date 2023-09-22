<?php

use Illuminate\Database\Seeder;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class ServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Service::create([
            'name'   => 'Car',
            'status' => 'active',
        ]);

        Service::create([
            'name'   => 'Delivery',
            'status' => 'active',
        ]);

        Service::create([
            'name'   => 'Line By Ride',
            'status' => 'active',
        ]);
    }
}
