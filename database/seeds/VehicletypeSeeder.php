<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class VehicletypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\VehicleType::create([
            'name'          => 'Motorcycle/bike',
            'model_id'      => '9',
            'status'        => 'active'
            ]);


        \App\Models\VehicleType::create([
            'name'          => 'Moto',
            'model_id'      => '1',
            'status'        => 'active'
            ]);

        \App\Models\VehicleType::create([
            'name'          => 'Truck',
            'model_id'      => '7',
            'status'        => 'active'
            ]);

        \App\Models\VehicleType::create([
            'name'          => 'Cadillac',
            'model_id'      => '1',
            'status'        => 'active'
            ]);

        \App\Models\VehicleType::create([
            'name'          => 'passenger car',
            'model_id'      => '1',
            'status'        => 'active'
            ]);

        \App\Models\VehicleType::create([
            'name'          => 'Others',
            'model_id'      => '1',
            'status'        => 'active'
            ]);

        \App\Models\VehicleType::create([
            'name'          => 'dasdas',
            'model_id'      => '6',
            'status'        => 'active'
            ]);

    }
}


