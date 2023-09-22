<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
        $this->call(EmergencyTableSeeder::class);
        $this->call(CmsTableSeeder::class);
        $this->call(ParcelTypesTableSeeder::class);
        $this->call(ServicesTableSeeder::class);
        // $this->call(PermissionTableSeeder::class);
        $this->call(UserBehaviorsTableSeeder::class);
    }
}
