<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::create([
            'first_name'     => 'Admin',
            'last_name'      => 'User',
            'address'        => 'address',
            'country'        => 'country',
            'country_code'   => '+91',
            'contact_number' => '1234567890',
            'uuid'           => Str::random(10),
            'email'          => 'admin@admin.com',
            'ref_id'         => '',
            'avatar'         => 'default.png',
            'password'       => Hash::make('12345678'),
            'status'         => 'active',
            'user_type'      => 'superadmin',
            'doc_status'     => 'pending',
            'sign_up_as'     => 'web',
            'login_status'   => 'online',
            'gender'         => 'male'
        ]);
    }
}


