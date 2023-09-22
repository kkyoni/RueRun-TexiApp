<?php

use Illuminate\Database\Seeder;
use App\Models\ParcelType;
use Illuminate\Support\Facades\DB;

class ParcelTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        ParcelType::create(['name'   => 'Heavy', 'status' => 'active',]);
//        ParcelType::create(['name'   => 'Light', 'status' => 'active', ]);

        ParcelType::create(['name'   => 'Home goods (furniture, appliances, others)', 'status' => 'active' ]);
        ParcelType::create(['name'   => 'Moving', 'status' => 'active' ]);
        ParcelType::create(['name'   => 'Construction material', 'status' => 'active' ]);
        ParcelType::create(['name'   => 'Industrial material', 'status' => 'active' ]);
        ParcelType::create(['name'   => 'Industrial Vehicle', 'status' => 'active' ]);
        ParcelType::create(['name'   => 'Vehicle', 'status' => 'active' ]);
        ParcelType::create(['name'   => 'Boat', 'status' => 'active' ]);
        ParcelType::create(['name'   => 'Box', 'status' => 'active' ]);
        ParcelType::create(['name'   => 'Bags', 'status' => 'active' ]);
        ParcelType::create(['name'   => 'Mail', 'status' => 'active' ]);
    }
}
