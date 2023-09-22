<?php

use Illuminate\Database\Seeder;
use App\Models\Cms;

class CmsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Cms::create([
            'page_code'    		=>  'about_us_page',
            'page_title'		=>  'About Us Page',
            'page_description'	=>  '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
            'status'			=>  'active',
        ]);

        Cms::create([
            'page_code'         =>  'contact_us_page',
            'page_title'        =>  'Contact Us Page',
            'page_description'  =>  '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
            'status'            =>  'active',
        ]);

        Cms::create([
            'page_code'    		=>  'terms_and_condition_page',
            'page_title'		=>  'Terms and Condition Page',
            'page_description'	=>  '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
            'status'			=>  'active',
        ]);

        Cms::create([
            'page_code'    		=>  'privacy_policy_page',
            'page_title'		=>  'Privacy Policy Page',
            'page_description'	=>  '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
            'status'			=>  'active',
        ]);

        Cms::create([
            'page_code'    		=>  'cancellation_policy',
            'page_title'		=>  'Cancellation Policy',
            'page_description'	=>  '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
            'status'			=>  'active',
        ]);

        Cms::create([
            'page_code'         =>  'contact_us',
            'page_title'        =>  'Contact Us',
            'page_description'  =>  '<p>Phone No - 7872384728</p>',
            'status'            =>  'active',
        ]);

        Cms::create([
            'page_code'    		=>  'invite_contact',
            'page_title'		=>  'Invite Contact',
            'page_description'	=>  '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
            'status'			=>  'active',
        ]);
    }
}
