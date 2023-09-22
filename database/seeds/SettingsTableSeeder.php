<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Setting::create([
            'code'   => 'application_logo',
            'type'   => 'FILE',
            'label'  => 'Application logo',
            'value'  => 'application_logo.png',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'application_title',
            'type'   => 'TEXT',
            'label'  => 'Application Title',
            'value'  => 'Ruerun',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'comission_rate',
            'type'   => 'TEXT',
            'label'  => 'Admin Comission Rate',
            'value'  => '5',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'referral_rate',
            'type'   => 'TEXT',
            'label'  => 'Referral Rate',
            'value'  => '5',
            'hidden' => '0'
        ]);
        \App\Models\Setting::create([
            'code'   => 'driver_confirmation',
            'type'   => 'TEXT',
            'label'  => 'Driver Confirmation (in second)',
            'value'  => '300',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'     => 'faq',
            'type'     => 'SELECT',
            'label'    => 'FAQs Page',
            'value'    => '{"What if the car does not show up?":"In case the vehicle you booked does not show up, we will offer you a full refund immediately.","Do I need to register on your site to book tickets?":"No. You can use our service fully without the need to register. You just need to provide your details at the time of booking.","What if the car shows up late?":"We try our best to ensure our partners reach our customers on time. But in case of delays, do call us and we will help you out by either providing an alternate vehicle or giving you a full refund."}',
            'hidden'   => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'fb_link',
            'type'   => 'TEXT',
            'label'  => 'Facebook Link',
            'value'  => 'Taxi',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'search_driver_area',
            'type'   => 'TEXT',
            'label'  => 'Search Driver Area',
            'value'  => '10',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'distance_in',
            'type'   => 'TEXT',
            'label'  => 'Distance In',
            'value'  => 'km',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'parcel_height',
            'type'   => 'TEXT',
            'label'  => 'Parcel Height',
            'value'  => '10',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'parcel_deep',
            'type'   => 'TEXT',
            'label'  => 'Parcel Deep',
            'value'  => '10',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'parcel_length',
            'type'   => 'TEXT',
            'label'  => 'Parcel Length',
            'value'  => '10',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'package_type',
            'type'   => 'SELECT',
            'label'  => 'Parcel Type',
            'value'  => '{"0":"Heavy Package","1":"Light Package"}',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'service_type',
            'type'   => 'SELECT',
            'label'  => 'Parcel Type',
            'value'  => '{"0":"Car","1":"Delivery","2":"Line By Ride"}',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'parcel_weight',
            'type'   => 'TEXT',
            'label'  => 'Parcel Weight',
            'value'  => '10',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'midnight_charges',
            'type'   => 'TEXT',
            'label'  => 'Midnight Charges',
            'value'  => '5',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'penalty_charges',
            'type'   => 'TEXT',
            'label'  => 'Penalty Charges',
            'value'  => '5',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'city_travel_rate',
            'type'   => 'TEXT',
            'label'  => 'City Travel Rate',
            'value'  => '5',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'parcel_max_insurance_amount',
            'type'   => 'NUMBER',
            'label'  => 'Parcel Max Insurance Amount',
            'value'  => '5',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'light_weight_charge',
            'type'   => 'TEXT',
            'label'  => 'Light Weight Charge',
            'value'  => '10',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'heavy_weight_charge',
            'type'   => 'TEXT',
            'label'  => 'Heavy Weight Charge',
            'value'  => '15',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'parcel_length_charge',
            'type'   => 'TEXT',
            'label'  => 'Parcel Length Charge',
            'value'  => '5',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'parcel_deep_charge',
            'type'   => 'TEXT',
            'label'  => 'Parcel Deep Charge',
            'value'  => '5',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'parcel_height_charge',
            'type'   => 'TEXT',
            'label'  => 'Parcel Height Charge',
            'value'  => '5',
            'hidden' => '0'
        ]);

        \App\Models\Setting::create([
            'code'   => 'ref_percentage',
            'type'   => 'TEXT',
            'label'  => 'Referral Percentage',
            'value'  => '10',
            'hidden' => '0'
        ]);
        

    }
}

