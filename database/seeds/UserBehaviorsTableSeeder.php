<?php

use Illuminate\Database\Seeder;
use App\Models\UserBehavior;

class UserBehaviorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserBehavior::create([
            'feedback'  =>  'Issue with receipt',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Issue with Payment',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Issue with promo code',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'issue with car situation',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'issue with cleaning fee',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Issue with fare /amount/ description/ file or pic upload',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Lost item',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Sexual Abuse',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Driver asked for Extra cash fee',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Driver asked for cash tip',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Driver acting inappropriate',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Angry driver',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Driving too fast',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Bad driver',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Smoking habit',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Smelly car',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Dirty inside',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Driver was drunk',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Why driver rate me too low',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Accident report',
            'flag'		=>  '0',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Driver Station was not appropriate.  (Shuttle drive)',
            'flag'		=>  '0',
        ]);

        UserBehavior::create([
            'feedback'  =>  'Car cleaning fee/ amount/ file or picture upload',
            'flag'		=>  '1',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Tool fee/ airport toll or fee/ add amount ( to the trip cost)',
            'flag'		=>  '1',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Rider had improper behavior',
            'flag'		=>  '1',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Sexual abuse',
            'flag'		=>  '1',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Issue with Payment',
            'flag'		=>  '1',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Issue with my fare',
            'flag'		=>  '1',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Issue with my rider',
            'flag'		=>  '1',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Accident report',
            'flag'		=>  '1',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Rider was drunk',
            'flag'		=>  '1',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Rider left an item in my care',
            'flag'		=>  '1',
        ]);

        UserBehavior::create([
            'feedback'  =>  'Forgot to start the trip/ enter: the start and end address',
            'flag'		=>  '1',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Why passenger rate to low',
            'flag'		=>  '1',
        ]);

        UserBehavior::create([
            'feedback'  =>  'Not find the parcel',
            'flag'		=>  '2',
        ]);
        UserBehavior::create([
            'feedback'  =>  'The parcel not ready',
            'flag'		=>  '2',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Parcel is not transportable',
            'flag'		=>  '2',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Parcel have damage',
            'flag'		=>  '2',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Parcel is not as described',
            'flag'		=>  '2',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Other',
            'flag'		=>  '2',
        ]);
        
        UserBehavior::create([
            'feedback'  =>  'The parcel damaged',
            'flag'		=>  '3',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Driver was not on time for delivery',
            'flag'		=>  '3',
        ]);
        UserBehavior::create([
            'feedback'  =>  'Other',
            'flag'		=>  '3',
        ]);
    }
}
