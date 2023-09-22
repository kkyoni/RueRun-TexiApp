<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/clear-cache', static function () {
	Artisan::call('cache:clear');
	Artisan::call('route:clear');
	Artisan::call('config:cache');
	Artisan::call('config:clear');
	Artisan::call('view:clear');
	Artisan::call('view:cache');
	Artisan::call('clear-compiled');
	Artisan::call('optimize:clear');
	return response()->json([
		'message' => 'All cache removed successfully.'
		]);
});
Route::get('/cancle_booking','Cron\CronController@cancle_booking')->name('cancle_booking');
// Route::get('email-test', function(){
// 	$details['email'] = 'mjain9997@mailinator.com';
// 	$emailcontent = array (
// 		'text' =>'sdsdfvsdgfds',
// 		'userName' => 'dzxczx'
// 		);


// 	dispatch(new App\Jobs\SendEmailTest($details,$emailcontent));
// });
Route::get('/check','Admin\MainController@remember')->name('get.check');
Route::get('/', 'HomeController@index')->name('home');
Route::group(['middleware' => 'preventBackHistory'],function(){
	Route::get('admin','Admin\Auth\LoginController@showLoginForm')->name('admin.showLoginForm');
	Route::get('admin/login','Admin\Auth\LoginController@showLoginForm')->name('admin.login');

	Route::post('admin/login', 'Admin\Auth\LoginController@login');

	Route::get('admin/resetPassword','Admin\Auth\PasswordResetController@showPasswordRest')->name('admin.resetPassword');
	Route::post('admin/sendResetLinkEmail', 'Admin\Auth\ForgotPasswordController@sendResetLinkEmail')->name('admin.sendResetLinkEmail');
	Route::get('admin/find/{token}', 'Admin\Auth\PasswordResetController@find')->name('admin.find');
	Route::post('admin/create', 'Admin\Auth\PasswordResetController@create')->name('admin.sendLinkToUser');
	Route::post('admin/reset', 'Admin\Auth\PasswordResetController@reset')->name('admin.resetPassword_set');
	Route::any('admin/resend_link', 'Admin\Auth\PasswordResetController@resend_link')->name('admin.resend_link');
	Route::group(['prefix' => 'admin','middleware'=>'Admin','namespace' => 'Admin','as' => 'admin.'],function(){

		Route::get('/dashboard','MainController@dashboard')->name('dashboard');
		Route::get('/live_map','MainController@live_map')->name('live_map');
		Route::get('/get_city','MainController@get_city')->name('get_city');
		Route::get('/get_taxi','MainController@get_taxi')->name('get_taxi');
		Route::get('/get_taxi_lat_long','MainController@get_taxi_lat_long')->name('get_taxi_lat_long');
		Route::get('/logout','Auth\LoginController@logout')->name('logout');

		Route::get('/location/live_map','BehaviorsController@live_map')->name('location.live_map');
			//====================> User Management =========================
		Route::get('/user','UsersController@index')->name('index');
		Route::get('/user/create','UsersController@create')->name('create');
		Route::get('/user/edit/{id}','UsersController@edit')->name('edit');
		Route::post('/user/delete/{id}','UsersController@delete')->name('delete');

		Route::post('/sendAdminCommission','UsersController@sendAdminCommission')->name('sendAdminCommission');

			//POST
		Route::get('/contacts','UsersController@contacts')->name('contacts');
		Route::post('/user/change_status','UsersController@change_status')->name('change_status');
		Route::post('/user/store','UsersController@store')->name('store');
		Route::post('/user/update/{id}','UsersController@update')->name('update');
		Route::get('/user/user_trip/{id}','UsersController@user_trip')->name('user_trip');
		Route::post('/user/u_trip_info/{id}','UsersController@u_trip_info')->name('u_trip_info');
		Route::post('/user/getcities','UsersController@getcities')->name('user.getcities');

		Route::get('/user/user_parcel_booking/{id}','UsersController@user_parcel_booking')->name('user_parcel_booking');
		Route::post('/user/u_booking_info/{id}','UsersController@u_booking_info')->name('u_booking_info');
		
    	//====================> User Management =========================
		Route::resource('/role','RoleController');
		Route::get('/role/create','RoleController@create')->name('role.create');
		Route::post('/role/store','RoleController@store')->name('role.store');
		Route::get('/role/{id}/view','RoleController@view')->name('role.view');
		Route::post('/role/add/update','RoleController@addRole')->name('role.add.update');
		Route::post('/role/add/getPermissionDetail','RoleController@getPermissionDetail')->name('role.add.getPermissionDetail');
		//====================> Driver Management =========================
		Route::get('/driver','DriverController@index')->name('driver.index');
		Route::get('/driver/create','DriverController@create')->name('driver.create');
		Route::get('/driver/edit/{id}','DriverController@edit')->name('driver.edit');
		Route::post('/driver/delete/{id}','DriverController@delete')->name('driver.delete');
			//POST
		Route::post('/driver/change_status','DriverController@change_status')->name('driver.change_status');
		Route::post('/driver/store','DriverController@store')->name('driver.store');
		Route::post('/driver/update/{id}','DriverController@update')->name('driver.update');
		Route::get('/driver/driver_trip/{id}','DriverController@driver_trip')->name('driver_trip');

		Route::post('/driver/setadminrating','DriverController@setadminrating')->name('driver.setadminrating');

		Route::get('/driver/driver_trip/{id}','DriverController@driver_trip')->name('driver_trip');
		Route::post('/driver/d_trip_info/{id}','DriverController@d_trip_info')->name('d_trip_info');

		Route::post('/sendDriverTipAmount','DriverController@sendDriverTipAmount')->name('sendDriverTipAmount');
		//====================> Company Management =========================
		Route::get('/company','ComapnyController@index')->name('company.index');
		Route::get('/company/create','ComapnyController@create')->name('company.create');
		Route::get('/company/edit/{id}','ComapnyController@edit')->name('company.edit');
		Route::post('/company/delete/{id}','ComapnyController@delete')->name('company.delete');
			//POST
		Route::post('/company/change_status','ComapnyController@change_status')->name('company.change_status');
		Route::post('/company/store','ComapnyController@store')->name('company.store');
		Route::post('/company/update/{id}','ComapnyController@update')->name('company.update');
		Route::get('/company/company_trip/{id}','ComapnyController@driver_trip')->name('company_trip');

		Route::get('/company/companyDriver/{id}','ComapnyController@companyDriver')->name('company.companyDriver');
		Route::get('/company/companyDriver/create/{id}','ComapnyController@companyDriverCreate')->name('company.companyDriverCreate');
		Route::post('/company/companyDriver/store/{id}','ComapnyController@companyDriverStore')->name('company.companyDriverStore');
		Route::get('/company/companyDriver/edit/{id}/{did}','ComapnyController@companyDriverEdit')->name('company.companyDriverEdit');
		Route::post('/company/companyDriver/update/{id}/{did}','ComapnyController@companyDriverUpdate')->name('company.companyDriverUpdate');


		//====================> State/City Management =========================
		Route::get('/states','StateController@index')->name('states.index');
		Route::get('/states/create','StateController@create')->name('states.create');
		Route::get('/states/edit/{id}','StateController@edit')->name('states.edit');
		Route::post('/states/delete/{id}','StateController@delete')->name('states.delete');
			//POST
		Route::post('/states/change_status','StateController@change_status')->name('company.change_status');
		Route::post('/states/store','StateController@store')->name('states.store');
		Route::post('/states/update','StateController@update')->name('states.update');


		Route::get('/cities','StateController@city_index')->name('states.city_index');
		Route::get('/cities/create','StateController@city_create')->name('states.city_create');
		Route::get('/cities/edit/{id}','StateController@city_edit')->name('states.city_edit');
		Route::post('/cities/delete/{id}','StateController@city_delete')->name('states.city_delete');
			//POST
		Route::post('/cities/change_status','StateController@city_change_status')->name('cities.change_status');
		Route::post('/cities/store','StateController@city_store')->name('states.city_store');
		Route::post('/cities/update/{id}','StateController@city_update')->name('states.city_update');

		//====================> Parcel Management =========================
		Route::get('/parcels','ParcelController@index')->name('parcels.index');
		Route::get('/parcels/create','ParcelController@create')->name('parcels.create');
		Route::get('/parcels/edit/{id}','ParcelController@edit')->name('parcels.edit');
		Route::post('/parcels/delete/{id}','ParcelController@delete')->name('parcels.delete');
		Route::get('/parcels/parcelslistuser/{id}','ParcelController@parcelslistuser')->name('parcelslistuser');
		Route::get('/parcels/parcelslistdriver/{id}','ParcelController@parcelslistdriver')->name('parcelslistdriver');
		Route::post('/parcels/change_status','ParcelController@change_status')->name('parcels.change_status');
		Route::post('/parcels/store','ParcelController@store')->name('parcels.store');
		Route::post('/parcels/update/{id}','ParcelController@update')->name('parcels.update');
		Route::post('/parcels/show_info/{id}','ParcelController@show_info')->name('parcels.show_info');
		Route::post('/parcels/parcel_images/{id}','ParcelController@parcel_images')->name('parcels.parcel_images');
		Route::post('/parcels/parcel_user_info/{id}','ParcelController@parcel_user_info')->name('parcels.parcel_user_info');
		Route::post('/parcels/parcel_driver_info/{id}','ParcelController@parcel_driver_info')->name('parcels.parcel_driver_info');
		
		Route::get('/lineride','LineRideController@index')->name('lineride.index');
		Route::get('/lineride/listshuttleridedriver/{id}','LineRideController@listshuttleridedriver')->name('listshuttleridedriver');

		Route::get('/subadmin','SubAdminController@index')->name('subadmin.index');
		Route::get('/subadmin/create','SubAdminController@create')->name('subadmin.create');
		Route::get('/subadmin/edit/{id}','SubAdminController@edit')->name('subadmin.edit');
		Route::post('/subadmin/delete/{id}','SubAdminController@delete')->name('subadmin.delete');
		Route::post('/subadmin/change_status','SubAdminController@change_status')->name('subadmin.change_status');
		Route::post('/subadmin/store','SubAdminController@store')->name('subadmin.store');
		Route::post('/subadmin/update/{id}','SubAdminController@update')->name('subadmin.update');
		Route::post('/subadmin/getcities','SubAdminController@getcities')->name('subadmin.getcities');
		//====================> Driver Document Management =========================
		Route::get('/driver_document','DriverDocController@index')->name('driverdoc.index');
		Route::post('/driver_document/change_status','DriverDocController@change_status')->name('driverdoc.change_status');
		Route::post('/driver_document/user_doc/{id}','DriverDocController@user_doc')->name('user_doc');
	   	//====================> Vehicle Document Management =========================
		Route::get('/vehciledoc','DriverDocController@vehcile_index')->name('vehciledoc.index');
		Route::post('/vehciledoc/vehicle_change_status','DriverDocController@vehicle_change_status')->name('vehciledoc.vehicle_change_status');
		Route::post('/vehicledoc/vehicle_doc/{id}','DriverDocController@vehicle_doc')->name('vehicle_doc');
		//====================> Update Admin Profile =========================
		Route::get('/profile','UsersController@updateProfile')->name('profile');
		Route::post('/updatePassword','UsersController@updatePassword')->name('updatePassword');
		Route::post('/updateProfileDetail','UsersController@updateProfileDetail')->name('updateProfileDetail');
		//====================> Promocode Management =========================
		Route::resource('/promocode','PromocodeManagementController');
		Route::resource('/join_network','JoinNetworkController');
		Route::post('/promocodedelete/delete/{id}','PromocodeManagementController@delete')->name('promocodedelete');
		Route::post('/statusupdate','PromocodeManagementController@statusupdate')->name('statusupdate');
		Route::get('/driver_referral','ReferralController@index')->name('driver_ref.index');
		Route::get('/user_referral','ReferralController@user_index')->name('user_ref.index');
		Route::post('/referral_detail/{id}','ReferralController@referral_info')->name('referral_info');
		Route::post('/joinreferral_detail/{id}','JoinNetworkController@joinreferral_info')->name('joinreferral_info');
		Route::post('/earn_info/{id}','ReferralController@earn_info')->name('earn_info');
		Route::post('/get_user_info_data/{id}','ReferralController@get_user_info_data')->name('get_user_info_data');
		Route::post('/get_driver_info_data/{id}','ReferralController@get_driver_info_data')->name('get_driver_info_data');
		Route::get('/driver_star_rating','StarRatingController@index')->name('driver_star.index');
		Route::get('/user_star_rating','StarRatingController@user_index')->name('user_star.index');
		Route::post('/get_user_info_data/{id}','ReferralController@get_user_info_data')->name('get_user_info_data');
		Route::post('/get_driver_info_data/{id}','ReferralController@get_driver_info_data')->name('get_driver_info_data');
		//====================> Behaviors Management =========================
		Route::resource('/behaviors','BehaviorsController');
		Route::get('/behaviors/create','BehaviorsController@create')->name('behaviorcreate');
		Route::post('/behaviors/delete/{id}','BehaviorsController@delete')->name('deletebehavior');
		//====================> Emergency Contact  =========================
		Route::get('/emergency','EmergencyManagementController@index')->name('emergencyindex');
		Route::get('/emergency/create','EmergencyManagementController@create')->name('emergencycreate');
		Route::get('/emergency/edit/{id}','EmergencyManagementController@edit')->name('emergencyedit');
		Route::post('/emergency/delete/{id}','EmergencyManagementController@delete')->name('emergencydelete');
		Route::post('/emergency/change_status','EmergencyManagementController@change_status')->name('emergencychange_status');
		Route::post('/emergency/store','EmergencyManagementController@store')->name('emergencystore');
		Route::post('/emergency/update/{id}','EmergencyManagementController@update')->name('emergencyupdate');
		Route::get('/ridesetting','EmergencyManagementController@ridesetting_index')->name('ridesetting');
		Route::post('/ridesetting/change_status','EmergencyManagementController@ride_change_status')->name('ride_change_status');
		Route::get('/ridesetting/edit/{id}','EmergencyManagementController@ridesetting_edit')->name('ridesetting.edit');
		Route::post('/ridesetting/update/{id}','EmergencyManagementController@ridesetting_update')->name('ridesetting.update');
		Route::get('/emergencytypes','EmergencyManagementController@emergencytypes')->name('emergencytypes');
		Route::post('/emergencytypes/change_status','EmergencyManagementController@type_change_status')->name('type_change_status');
		Route::get('/emergency_type/emergencytypeedit/{id}','EmergencyManagementController@emergencytypeedit')->name('emergencytypeedit');
		Route::post('/emergency/emergencytypeupdate/{id}','EmergencyManagementController@emergencytypeupdate')->name('emergencytypeupdate');
		Route::get('/emergencyrequest','EmergencyManagementController@request_index')->name('emergencyrequest');
		Route::post('/emergencyrequest/change_status','EmergencyManagementController@emergecyrequest_changestatus')->name('emergecyrequest_changestatus');
		Route::get('/userreports','EmergencyManagementController@userreports')->name('userreports');
		Route::get('/get_reports/{id}','EmergencyManagementController@get_reports')->name('get_reports');
		//====================> Vehicle Category  =========================
		Route::get('/vehiclecategories','VehiclecategoriesController@index')->name('vehicleindex');
		Route::get('/vehiclecategories/create','VehiclecategoriesController@create')->name('vehiclecreate');
		Route::get('/vehiclecategories/edit/{id}','VehiclecategoriesController@edit')->name('vehiclecategoriesedit');
		Route::post('/vehiclecategories/delete/{id}','VehiclecategoriesController@delete')->name('vehicledelete');
		Route::post('/vehiclecategories/change_status','VehiclecategoriesController@change_status')->name('vehiclechange_status');
		Route::post('/vehiclecategories/store','VehiclecategoriesController@store')->name('vehiclestore');
		Route::post('/vehiclecategories/update/{id}','VehiclecategoriesController@update')->name('vehicleupdate');
        //====================> Vehicle Model =========================
		Route::resource('vehiclebody', 'VehicleBodyController');
		Route::post('vehiclebody/changestatus','VehicleBodyController@changestatus')->name('vehiclebody.changestatus');
        //====================> Vehicle Type =========================
		Route::resource('vehicletype', 'VehicleTypeController');
		Route::post('vehicletype/changestatus','VehicleTypeController@changestatus')->name('vehicletype.changestatus');
        //====================> Trip Details  =========================
		Route::get('/trip','BookingController@index')->name('tripindex');
		Route::get('/trip/create','BookingController@create')->name('tripcreate');
		Route::post('/trip/delete/{id}','BookingController@delete')->name('tripdelete');
		Route::post('/trip/get_driver_info/{id}','BookingController@get_driver_info')->name('get_driver_info');
		Route::post('/trip/get_user_info/{id}','BookingController@get_user_info')->name('get_user_info');
		Route::post('/trip/show_info/{id}','BookingController@show_info')->name('show_info');
		Route::post('/trip/store','BookingController@store')->name('tripstore');
		Route::post('/trip/ride_info/{id}','BookingController@ride_info')->name('ride_info');
		Route::get('/trip/listtrip/{id}','BookingController@listtrip')->name('listtrip');
		Route::get('/trip/listtripdriver/{id}','BookingController@listtripdriver')->name('listtripdriver');
		//====================> Review Rating Category  =========================
		Route::get('/reviewrating','ReviewratingController@index')->name('reviewindex');
		Route::get('/reviewrating/create','ReviewratingController@create')->name('reviewcreate');
		Route::get('/reviewrating/edit/{id}','ReviewratingController@edit')->name('reviewedit');
		Route::post('/reviewrating/delete/{id}','ReviewratingController@delete')->name('reviewdelete');
		Route::post('/reviewrating/trip_info/{id}','ReviewratingController@trip_info')->name('trip_info');
		Route::post('/reviewrating/driver_info/{id}','ReviewratingController@driver_info')->name('driver_info');
		Route::post('/reviewrating/user_info/{id}','ReviewratingController@user_info')->name('user_info');
		Route::post('/reviewrating/revie_status','ReviewratingController@revie_status')->name('revie_status');
		Route::post('/reviewrating/store','ReviewratingController@store')->name('reviewstore');
		Route::post('/reviewrating/update/{id}','ReviewratingController@update')->name('reviewupdate');
		Route::post('/reviewrating/get_parcel_details/{id}','ReviewratingController@get_parcel_details')->name('review.get_parcel_details');
		//====================> Notification =========================
		Route::get('/notification','NotificationController@index')->name('notification.index');
		Route::get('/mailbox','NotificationController@mailbox')->name('mailbox');
		Route::post('/deletemail/{id}','NotificationController@deletemail')->name('deletemail');
		Route::get('/mail_detail/{id}','NotificationController@mail_detail')->name('mail_detail');
		Route::post('/notificationSend','NotificationController@mailSend')->name('notification.mailSend');
		Route::get('/setreadall','NotificationController@setreadall')->name('setreadall');
		Route::post('/notification/store','NotificationController@store')->name('notification.store');
		Route::get('/transaction_detail','TransactionController@index')->name('transaction_detail.index');
		Route::post('/transaction_detail','TransactionController@index')->name('transaction_detail.filter_by');
		Route::post('/transaction_detail/transaction_info/{id}','TransactionController@transaction_info')->name('transaction_info');
		Route::get('/reports_detail','TransactionController@reports_index')->name('transaction_detail.reports_detail');
		Route::get('/export', 'TransactionController@export')->name('transaction_detail.export');
		Route::get('/importExportView', 'TransactionController@importExportView');
		Route::post('/import', 'TransactionController@import')->name('import');
		Route::post('/transaction_detail/get_parcel_details/{id}','TransactionController@get_parcel_details')->name('get_parcel_details');
		//====================> CMS Management =========================
		Route::get('/cms','CmsController@index')->name('cms.index');
		Route::get('/cms/create','CmsController@create')->name('cms.create');
		Route::get('/cms/edit/{id}','CmsController@edit')->name('cms.edit');
		Route::post('/cms/delete/{id}','CmsController@delete')->name('cms.delete');
		Route::post('/cms/change_status','CmsController@change_status')->name('cms.change_status');
		Route::post('/cms/store','CmsController@store')->name('cms.store');
		Route::post('/cms/update/{id}','CmsController@update')->name('cms.update');
		//====================> Map Management =========================
		//Route::get('/map_show','MapController@index')->name('map_show.index');
		//====================> Support Management =========================
		Route::get('/support','SupportController@index')->name('support.index');
		Route::post('/support/add_comment_info/{id}','SupportController@add_comment_info')->name('add_comment_info');
		Route::post('/support/add_comment_admin','SupportController@add_comment_admin')->name('add_comment_admin');
		Route::post('/support/change_status','SupportController@change_status')->name('support.changestatus');
		Route::get('/locations','BehaviorsController@location_index')->name('location_index');
	});
});

Event::listen('send-mail-user', function($data) {
	$path = storage_path().'/logs/'.date("d-m-Y").'_customeLog.log';
	file_put_contents($path, "\n\n".date("d-m-Y H:i") . "_getMyProjects : ".json_encode($data)."\n", FILE_APPEND);

	\Mail::send($data['template'], $data['data'], function ($message) use ($data) {
		$message->from('admin@aistechnolabs.us','Admin');
		$message->to($data['to_email'], $data['to_name']);
		$message->subject($data['title']);
		if(!empty($data['mailUpload'])){
			$message->attach($data['mailUpload']->getRealPath(), ['as' => $data['mailUpload']->getClientOriginalName(), 'mime' => $data['mailUpload']->getMimeType()]);
		}
		$message->priority(3);
	});
});

Event::listen('send-notification-assigned-driver', function($value,$data) {
	try {
		$path = public_path().'/webservice_logs/'.date("d-m-Y").'_notification.log';
		file_put_contents($path, "\n\n".date("d-m-Y H:i:s").":".json_encode(['user'=>$value->id,'data'=>$data])."\n", FILE_APPEND);
		$response = [];
		if($value->device_type == 'ios'){
			try {
				$device_token = $value->device_token;          
				$passphrase = '';
				$cert =config_path('iosCertificates/Rurun_taxi.pem');
				$message = json_encode($data);
				$ctx = stream_context_create();
				stream_context_set_option($ctx, 'ssl', 'local_cert', $cert);
				stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
				$fp = stream_socket_client(
					'ssl://gateway.sandbox.push.apple.com:2195', $err,
					$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
				if (!$fp)
					exit("Failed to connect: $err $errstr" . PHP_EOL);
				$body['aps'] = array('alert' => $data['message'],'sound' => 'default');
				$body['params'] = $data;
				$payload = json_encode($body);
				$msg = chr(0) . pack('n', 32) . pack('H*', $device_token) . pack('n', strlen($payload)) . $payload;
				$result = fwrite($fp, $msg, strlen($msg));
				fclose($fp);
				$response[] = $result;
				file_put_contents($path, "\n\n".date("d-m-Y H:i:s")."_Response_IOS payload : ".json_encode($payload)."\n", FILE_APPEND);
				file_put_contents($path, "\n\n".date("d-m-Y H:i:s")."_Response_IOS : ".json_encode($response)."\n", FILE_APPEND);
			} catch (Exception $e) {
				file_put_contents($path, "\n\n".date("d-m-Y H:i:s")."_Responses_IOS : ".$e."\n", FILE_APPEND);
			} 
		}else{
			file_put_contents($path, "\n\n".date("d-m-Y H:i:s")."_Notification_data : ".json_encode($data)."\n", FILE_APPEND);
			$response[] = PushNotification::setService('fcm')->setMessage([
				'data' => $data
				])->setApiKey('AIzaSyCh1wuN2xJvXKI7PrY5ANrcud1kuHvvd9E')->setConfig(['dry_run' => false])->sendByTopic($data['type'])->setDevicesToken([$value->device_token])->send()->getFeedback();
		}
		file_put_contents($path, "\n\n".date("d-m-Y H:i:s")."_Response_Driver_android : ".json_encode($response)."\n", FILE_APPEND);
		return $response;
	} catch (Exception $e) {
		file_put_contents($path, "\n\n".date("d-m-Y H:i:s")."_Response : ".json_encode($e)."\n", FILE_APPEND);
	}
});
Event::listen('send-notification-assigned-user', function($value,$data) {
	try {
		$path = public_path().'/webservice_logs/'.date("d-m-Y").'_notification.log';
		file_put_contents($path, "\n\n".date("d-m-Y H:i:s").":".json_encode(['user'=>$value->id,'data'=>$data])."\n", FILE_APPEND);
		$response = [];
		$device_token = $value->device_token;
		if($value->device_type == 'ios'){
			try {
				$passphrase = '';
				$cert =config_path('iosCertificates/Rurun_user.pem');
				$message = json_encode($data);
				$ctx = stream_context_create();
				stream_context_set_option($ctx, 'ssl', 'local_cert', $cert);
				stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
				$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err,
					$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
				if (!$fp)
					exit("Failed to connect: $err $errstr" . PHP_EOL);
				$body['aps'] = array('alert' => $data['message'],'sound' => 'default');
				$body['params'] = $data;
				$payload = json_encode($body);
				$msg = chr(0) . pack('n', 32) . pack('H*', $device_token) . pack('n', strlen($payload)) . $payload;
				$result = fwrite($fp, $msg, strlen($msg));
				fclose($fp);
				$response[] = $result;
				file_put_contents($path, "\n\n".date("d-m-Y H:i:s")."_Response_IOS payload : ".json_encode($payload)."\n", FILE_APPEND);
				file_put_contents($path, "\n\n".date("d-m-Y H:i:s")."_Response_IOS : ".json_encode($response)."\n", FILE_APPEND);
			} catch (Exception $e) {
				file_put_contents($path, "\n\n".date("d-m-Y H:i:s")."_Response_IOS : ".$e."\n", FILE_APPEND);
			}
		}else{
			file_put_contents($path, "\n\n".date("d-m-Y H:i:s")."_Notification_data : ".json_encode($data)."\n", FILE_APPEND);

			$response[] = PushNotification::setService('fcm')->setMessage([
				'data' => $data
				])->setApiKey('AIzaSyCh1wuN2xJvXKI7PrY5ANrcud1kuHvvd9E')->setConfig(['dry_run' => false])->sendByTopic($data['type'])->setDevicesToken([$device_token])->send()->getFeedback();
		}
		file_put_contents($path, "\n\n".date("d-m-Y H:i:s")."_Response_User_android : ".json_encode($response)."\n", FILE_APPEND);
		return $response;
	} catch (Exception $e) {
		file_put_contents($path, "\n\n".date("d-m-Y H:i:s")."_Response : ".json_encode($e)."\n", FILE_APPEND);
	}
});