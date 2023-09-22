<?php

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       Permission::create([
           'module_name' => 'CMS Management',
           'view' => '1', 'add' => '1', 'edit' => '1', 'delete' => '1'
       ]);

        Permission::create([
            'module_name' => 'State',
            'view' => '1', 'add' => '1', 'edit' => '1', 'delete' => '1'
        ]);

        Permission::create([
            'module_name' => 'City',
            'view' => '1', 'add' => '1', 'edit' => '1', 'delete' => '1'
        ]);
    }
}
