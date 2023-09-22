<?php

use Illuminate\Database\Seeder;
use App\Models\EmergencyDetails;
use App\Models\RideSetting;
use App\Models\EmergencyType;
use App\Models\SupportCategory;

class EmergencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmergencyDetails::create([
            'contact_person'    =>  'fire brigade',
            'image'				=>  'default.png',
            'contact_details'	=>  'fire brigade',
            'contact_number'	=>  '101',
        ]);

        EmergencyDetails::create([
            'contact_person'    =>  'Police hotline',
            'image'				=>  'default.png',
            'contact_details'	=>  'Police Hotline',
            'contact_number'	=>  '18002550000',
        ]);
        EmergencyDetails::create([
            'contact_person'    =>  'Traffic police',
            'image'				=>  'default.png',
            'contact_details'	=>  'Traffic police',
            'contact_number'	=>  '65470000',
        ]);
        EmergencyDetails::create([
            'contact_person'    =>  'Ambulance',
            'image'				=>  'default.png',
            'contact_details'	=>  'Ambulance',
            'contact_number'	=>  '103',
        ]);
        EmergencyDetails::create([
            'contact_person'    =>  'Police',
            'image'				=>  'default.png',
            'contact_details'	=>  'Police',
            'contact_number'	=>  '100',
        ]);


        RideSetting::create([
            'code'    =>  'in_town_ride',
            'name'    =>  'In Town Ride',
            'status'  =>  'active',
        ]);

        RideSetting::create([
            'code'    =>  'out_of_town',
            'name'    =>  'Out Of Town',
            'status'  =>  'active',
        ]);
        RideSetting::create([
            'code'    =>  'shuttle_ride',
            'name'    =>  'Shuttle Ride',
            'status'  =>  'active',
        ]);
        RideSetting::create([
            'code'    =>  'parcel_delivery',
            'name'    =>  'Parcel Delivery',
            'status'  =>  'active',
        ]);


        EmergencyType::create([
            'code'      =>  'accident',
            'type_name' =>  'Accident',
            'contact_number' =>  '9789675675',
            'status'    =>  'active'
        ]);
        EmergencyType::create([
            'code'      =>  'car_broken',
            'type_name' =>  'Car Broken',
            'contact_number' =>  '9789675675',
            'status'    =>  'active'
        ]);
        EmergencyType::create([
            'code'      =>  'flat_tyre',
            'type_name' =>  'Flat Tyre',
            'contact_number' =>  '9789675675',
            'status'    =>  'active'
        ]);
        EmergencyType::create([
            'code'      =>  'sick',
            'type_name' =>  'Sick',
            'contact_number' =>  '9789675675',
            'status'    =>  'active'
        ]);

        EmergencyType::create([
            'code'      =>  'call_police',
            'type_name' =>  'Call Police',
            'contact_number' =>  '9789675675',
            'status'    =>  'active'
        ]);
        EmergencyType::create([
            'code'      =>  'other',
            'type_name' =>  'Other',
            'contact_number' =>  '9789675675',
            'status'    =>  'active'
        ]);

        SupportCategory::create([ 'name'=>'Work Request']);
        SupportCategory::create([ 'name'=>'Help']);
        SupportCategory::create([ 'name'=>'Enquiry']);
        SupportCategory::create([ 'name'=>'Feedback	']);
    }
}
