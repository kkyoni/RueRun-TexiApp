<?php
/*
|--------------------------------------------------------------------------
| USERS API Routes
|--------------------------------------------------------------------------
|
*/
Route::namespace('Api\User')->group(function () {
    Route::group(['middleware' => ['cors']], function() {
        Route::post('login','UserController@login');
        Route::post('register','UserController@register');
        Route::post('sendOtp','UserController@sendOtp');
        Route::post('verifyOtp','UserController@verifyOtp');
    });

    /*------------- JWT TOKEN AUTHORIZED ROUTE-------------------*/
    Route::group(['middleware' => ['cors','jwt.verify']], function() {
        Route::post('logout','AuthController@logout');
        Route::get('getProfile','UserController@getProfile')->middleware('activeUserCheck');
        Route::post('updateProfile','UserController@updateProfile')->middleware('activeUserCheck');
        Route::post('changePassword','UserController@changePassword')->middleware('activeUserCheck');
        Route::post('add_CardDetails','AuthController@add_CardDetails')->middleware('activeUserCheck');
        Route::post('edit_CardDetails','AuthController@edit_CardDetails')->middleware('activeUserCheck');
        Route::post('delete_CardDetails','AuthController@delete_CardDetails')->middleware('activeUserCheck');
        Route::get('view_CardDetails','AuthController@view_CardDetails')->middleware('activeUserCheck');
        Route::post('default_CardDetails','AuthController@default_CardDetails')->middleware('activeUserCheck');
        Route::post('CreateUserRatingReviews','AuthController@CreateUserRatingReviews')->middleware('activeUserCheck');
        Route::post('UserRatingReviewsStatus','AuthController@UserRatingReviewsStatus')->middleware('activeUserCheck');
        Route::post('createUserTrip','UserController@createUserTrip')->middleware('activeUserCheck');
        Route::post('completeUserTripBooking','UserController@completeUserTripBooking')->middleware('activeUserCheck');
        Route::post('User_payment_type','UserController@User_payment_type')->middleware('activeUserCheck');
        Route::post('completePayment','AuthController@completePayment')->middleware('activeUserCheck');
        Route::post('getDriverVehicleList','UserController@getDriverVehicleList')->middleware('activeUserCheck');
        Route::post('driverdetails','AuthController@driverdetails')->middleware('activeUserCheck');
        Route::post('driverlisting','AuthController@driverlisting')->middleware('activeUserCheck');
        Route::post('get_nearest_driver','AuthController@get_nearest_driver')->middleware('activeUserCheck');
        Route::post('All_get_nearest_driver','AuthController@All_get_nearest_driver')->middleware('activeUserCheck');
        Route::get('getAllPromo','UserController@getAllPromo')->middleware('activeUserCheck');
        Route::post('getFarePrice','UserController@getFarePrice')->middleware('activeUserCheck');
        Route::post('CreateUserSupports','AuthController@CreateUserSupports')->middleware('activeUserCheck');
        Route::post('setReferUser','UserController@setReferUser')->middleware('activeUserCheck');
        Route::post('parcelBooking','AuthController@parcelBooking')->middleware('activeUserCheck');
        Route::post('parcelPayment','AuthController@parcelPayment')->middleware('activeUserCheck');
        Route::post('getParcelEstimate','AuthController@getParcelEstimate')->middleware('activeUserCheck');
        Route::post('getParcelDriverList','AuthController@getParcelDriverList')->middleware('activeUserCheck');
        Route::post('getparceldriverdetails','AuthController@getparceldriverdetails')->middleware('activeUserCheck');
        Route::post('confirmParcelDriver','AuthController@confirmParcelDriver')->middleware('activeUserCheck');
        //Route::post('shuttleRideBooking','UserController@shuttleRideBooking')->middleware('activeUserCheck');
        Route::post('shuttleRideBooking','AuthController@shuttleRideBooking')->middleware('activeUserCheck');
        Route::post('linerideBooking','AuthController@linerideBooking')->middleware('activeUserCheck');
        Route::post('outoftownBooking','AuthController@outoftownBooking')->middleware('activeUserCheck');
        Route::post('getShuttleDriverList','AuthController@getShuttleDriverList')->middleware('activeUserCheck');
        Route::post('bookShuttleDriver','AuthController@bookShuttleDriver')->middleware('activeUserCheck');
        Route::post('bookingDetails','AuthController@bookingDetails')->middleware('activeUserCheck');
    });
    /*-------------Without JWT TOKEN AUTHORIZED ROUTE-------------------*/
});

/*
|--------------------------------------------------------------------------
| DRIVER API Routes
|--------------------------------------------------------------------------
|
*/
Route::namespace('Api\Driver')->group(function () {
    Route::group(['middleware' => ['cors']], function() {
        Route::post('driver_register','DriverController@driver_register');
        Route::post('driver_ride_type','DriverController@driver_ride_type');
        Route::post('driver_details','DriverController@driver_details');
    });
    /*------------- JWT TOKEN AUTHORIZED ROUTE-------------------*/
    Route::group(['middleware' => ['cors','jwt.verify']], function() {
        Route::post('driver_updateProfile','DriverAuthController@updateProfile')->middleware('activeUserCheck');
        Route::post('updateDriverAvailability','DriverAuthController@updateDriverAvailability')->middleware('activeUserCheck');
        Route::post('getDriverAvailability','DriverAuthController@getDriverAvailability')->middleware('activeUserCheck');
        Route::post('CreateuploadDriverDocument','DriverAuthController@CreateuploadDriverDocument')->middleware('activeUserCheck');
        Route::post('uploadCarDetails','DriverAuthController@uploadCarDetails')->middleware('activeUserCheck');
        Route::post('CreateuploadVehicleDocument','DriverAuthController@CreateuploadVehicleDocument')->middleware('activeUserCheck');
        Route::post('UpdateuploadDriverDocument','DriverAuthController@UpdateuploadDriverDocument')->middleware('activeUserCheck');
        Route::post('getDriverProfile','DriverAuthController@getDriverProfile')->middleware('activeUserCheck');
        Route::post('driverStartTrip','DriverAuthController@driverStartTrip')->middleware('activeUserCheck');
        Route::post('driverTripUpdateStatus','DriverAuthController@driverTripUpdateStatus')->middleware('activeUserCheck');
        Route::post('driverBookingStatus','DriverAuthController@driverBookingStatus')->middleware('activeUserCheck');
        Route::post('driverParcelBookingStatus','DriverAuthController@driverParcelBookingStatus')->middleware('activeUserCheck');
        Route::post('driverTripCancelled','DriverAuthController@driverTripCancelled')->middleware('activeUserCheck');
        Route::post('driverTripAccepted','DriverAuthController@driverTripAccepted')->middleware('activeUserCheck');
        Route::post('driverTripArrived','DriverAuthController@driverTripArrived')->middleware('activeUserCheck');
        Route::post('stopHoldTime','DriverAuthController@stopHoldTime')->middleware('activeUserCheck');
        Route::post('CreateDriverSupports','DriverAuthController@CreateDriverSupports')->middleware('activeUserCheck');
        Route::post('calculateHoldTime','DriverAuthController@calculateHoldTime')->middleware('activeUserCheck');
        Route::post('createDriverTrip','DriverAuthController@createDriverTrip')->middleware('activeUserCheck');
        Route::post('driverTripPayment','DriverAuthController@driverTripPayment')->middleware('activeUserCheck');
        Route::post('driverEmergencyRequest','DriverAuthController@driverEmergencyRequest')->middleware('activeUserCheck');
        Route::post('CreateDriverRatingReviews','DriverAuthController@CreateDriverRatingReviews')->middleware('activeUserCheck');
        Route::post('DriverRatingReviewsStatus','DriverAuthController@DriverRatingReviewsStatus')->middleware('activeUserCheck');
        Route::post('completeDriverTripBooking','DriverAuthController@completeDriverTripBooking')->middleware('activeUserCheck');
        Route::get('getDriverDocument','DriverAuthController@getDriverDocument')->middleware('activeUserCheck');
        Route::get('getDriverRideSetting','DriverAuthController@getDriverRideSetting')->middleware('activeUserCheck');
        Route::post('driverShuttleRideBooking','DriverAuthController@driverShuttleRideBooking')->middleware('activeUserCheck');
        Route::post('driverShuttleRideBookingDelete','DriverAuthController@driverShuttleRideBookingDelete')->middleware('activeUserCheck');
        Route::post('getdetailshuttle','DriverAuthController@getdetailshuttle')->middleware('activeUserCheck');
        Route::post('Editgetshuttle','DriverAuthController@Editgetshuttle')->middleware('activeUserCheck');
        Route::post('driverAllShuttleListing','DriverAuthController@driverAllShuttleListing')->middleware('activeUserCheck');
        Route::post('driverShuttleBookingStatus','DriverAuthController@driverShuttleBookingStatus')->middleware('activeUserCheck');
        Route::post('driverShuttleBookingComplete','DriverAuthController@driverShuttleBookingComplete')->middleware('activeUserCheck');
        Route::post('driverShuttleUserDetail','DriverAuthController@driverShuttleUserDetail')->middleware('activeUserCheck');
        Route::get('driverOutOfTown','DriverAuthController@driverOutOfTown')->middleware('activeUserCheck');
        Route::post('driverOutOfTownDetail','DriverAuthController@driverOutOfTownDetail')->middleware('activeUserCheck');
        Route::get('myScheduleList','DriverAuthController@myScheduleList')->middleware('activeUserCheck');
        Route::post('OutTwonrideBookingDelete','DriverAuthController@OutTwonrideBookingDelete')->middleware('activeUserCheck');
    });
Route::post('rideSetting','DriverAuthController@rideSetting');
});

/*
|--------------------------------------------------------------------------
| COMMON API Routes
|--------------------------------------------------------------------------
|
*/
Route::namespace('Api')->group(function () {
    Route::group(['middleware' => ['cors','jwt.verify']], function() {
        Route::post('UpdateLocation','CommonController@UpdateLocation')->middleware('activeUserCheck');
        Route::post('getLocation','CommonController@getLocation')->middleware('activeUserCheck');
        Route::post('setUserWallet','CommonController@setUserWallet')->middleware('activeUserCheck');
        Route::post('getWalletHistory','CommonController@getWalletHistory')->middleware('activeUserCheck');
        // Route::get('makecarType','CommonController@makecarType')->middleware('activeUserCheck');
        Route::get('getcarType','CommonController@getcarType')->middleware('activeUserCheck');
        // Route::post('getcarType','CommonController@getcarType')->middleware('activeUserCheck');
        Route::get('getOngoingTrip','CommonController@getOngoingTrip')->middleware('activeUserCheck');
        Route::post('createemailinvitecontact','CommonController@createemailinvitecontact')->middleware('activeUserCheck');
        Route::post('createinvitecontact','CommonController@createinvitecontact')->middleware('activeUserCheck');
        Route::post('invitecontact','CommonController@invitecontact')->middleware('activeUserCheck');
        Route::post('invitecontactlist','CommonController@invitecontactlist')->middleware('activeUserCheck');
        Route::post('invitecontactlistAndroid','CommonController@invitecontactlistAndroid')->middleware('activeUserCheck');
        Route::post('invitecontactlistIOS','CommonController@invitecontactlistIOS')->middleware('activeUserCheck');
        Route::post('invitecontactlistemail','CommonController@invitecontactlistemail')->middleware('activeUserCheck');
        Route::post('invitecontactlistemailAndroid','CommonController@invitecontactlistemailAndroid')->middleware('activeUserCheck');
        Route::post('getTripHistory','CommonController@getTripHistory')->middleware('activeUserCheck');
        Route::post('getVehicleList','CommonController@getVehicleList')->middleware('activeUserCheck');
        Route::get('checkUserStatus','CommonController@checkUserStatus')->middleware('activeUserCheck');
        Route::post('setUserReport','CommonController@setUserReport')->middleware('activeUserCheck');
        Route::get('getUserParcelList','CommonController@getUserParcelList')->middleware('activeUserCheck');
        Route::get('getDriverParcelList','CommonController@getDriverParcelList')->middleware('activeUserCheck');
        Route::post('getParcelDetails','CommonController@getParcelDetails')->middleware('activeUserCheck');
        Route::post('parcelbookingCancelled','CommonController@parcelbookingCancelled')->middleware('activeUserCheck');
        Route::post('setAdminPercent','CommonController@setAdminPercent')->middleware('activeUserCheck');
        Route::post('getTripHistoryDetail','CommonController@getTripHistoryDetail')->middleware('activeUserCheck');
        Route::post('shuttleBookingDetail','CommonController@shuttleBookingDetail')->middleware('activeUserCheck');
        Route::get('getDriverAllRatingReviews','CommonController@getDriverAllRatingReviews')->middleware('activeUserCheck');
        Route::post('joincontactemail','CommonController@joincontactemail')->middleware('activeUserCheck');
        Route::get('checkUserPayment','CommonController@checkUserPayment')->middleware('activeUserCheck');
        Route::get('getNetwork','CommonController@getNetwork')->middleware('activeUserCheck');
        Route::get('getState','CommonController@getState')->middleware('activeUserCheck');
        Route::post('StatewiseCity','CommonController@StatewiseCity')->middleware('activeUserCheck');
        Route::get('getServiceType','CommonController@getServiceType')->middleware('activeUserCheck');
        Route::get('getParcelType','CommonController@getParcelType')->middleware('activeUserCheck');
        Route::post('updateToken','CommonController@updateToken')->middleware('activeUserCheck');
        Route::get('getAllPreferences','CommonController@getAllPreferences')->middleware('activeUserCheck');
        Route::post('tripCancelled','CommonController@tripCancelled')->middleware('activeUserCheck');
        Route::post('AdminChargetripCancelled','CommonController@AdminChargetripCancelled')->middleware('activeUserCheck');
        Route::get('getAllEmergencyContacts','CommonController@getAllEmergencyContacts')->middleware('activeUserCheck');
        Route::post('CreateConversions','CommonController@CreateConversions')->middleware('activeUserCheck');
        Route::post('getconversions','CommonController@getconversions')->middleware('activeUserCheck');
        Route::post('getRatingReviews','CommonController@getRatingReviews')->middleware('activeUserCheck');
        Route::post('getDriverRatingReviews','CommonController@getDriverRatingReviews')->middleware('activeUserCheck');
        Route::post('Notification','CommonController@Notification')->middleware('activeUserCheck');
        Route::get('GetCountNotification','CommonController@GetCountNotification')->middleware('activeUserCheck');
        Route::get('NotificationCheck','CommonController@NotificationCheck')->middleware('activeUserCheck');
        Route::post('getUserUpcomingRides','CommonController@getUserUpcomingRides')->middleware('activeUserCheck');
        Route::post('jkgetUserUpcomingRides','CommonController@jkgetUserUpcomingRides')->middleware('activeUserCheck');
        Route::get('getDriverUpcomingRides','CommonController@getDriverUpcomingRides')->middleware('activeUserCheck');
        Route::post('getDriverOutTwonRides','CommonController@getDriverOutTwonRides')->middleware('activeUserCheck');
        Route::get('getDriverDailyReport','CommonController@getDriverDailyReport')->middleware('activeUserCheck');
        Route::post('getDriverAllDailyReport','CommonController@getDriverAllDailyReport')->middleware('activeUserCheck');
        Route::post('checkValidation','CommonController@checkValidation')->middleware('activeUserCheck');
        Route::get('InviteCmsPage','CommonController@InviteCmsPage')->middleware('activeUserCheck');
        Route::get('getAllNotification','CommonController@getAllNotification')->middleware('activeUserCheck');
        Route::get('setReadNotification','CommonController@setReadNotification')->middleware('activeUserCheck');
        Route::get('getAllCompany','CommonController@getAllCompany')->middleware('activeUserCheck');
        Route::post('filtering','CommonController@filtering')->middleware('activeUserCheck');
        Route::post('SaveBankDetail','CommonController@SaveBankDetail')->middleware('activeUserCheck');
        Route::get('GetBankDetail','CommonController@GetBankDetail')->middleware('activeUserCheck');
        Route::get('jk','CommonController@jk')->middleware('activeUserCheck');
        Route::get('check','CommonController@check')->middleware('activeUserCheck');
    });
Route::post('forgotPassword','CommonController@forgotPassword');
Route::post('resetPassword','CommonController@resetPassword');
Route::post('get_user_behavior_list','CommonController@get_user_behavior_list');
Route::post('get_driver_behavior_list','CommonController@get_driver_behavior_list');
Route::get('CmsPage','CommonController@CmsPage');
Route::get('faqPage','CommonController@faqPage');
Route::get('getRideSetting','CommonController@getRideSetting');
Route::post('DeActivate','CommonController@DeActivate');
Route::get('SupportCategory','CommonController@SupportCategory');
Route::get('getAllEmergencyType','CommonController@getAllEmergencyType');
Route::get('getAllParcelType','CommonController@getAllParcelType');
Route::get('getAllParcelPackage','CommonController@getAllParcelPackage');
Route::get('getInviteContactText','CommonController@getInviteContactText');
Route::get('paymentMethod','CommonController@paymentMethod');
Route::get('searchdriverofcron','CommonController@searchdriverofcron');
});

/*
|--------------------------------------------------------------------------
| Company API Routes
|--------------------------------------------------------------------------
|
*/
Route::namespace('Api\Company')->group(function () {
    Route::group(['middleware' => ['cors']], function() {
        Route::post('company_register','CompanyAuthController@company_register');
    });
    /*------------- JWT TOKEN AUTHORIZED ROUTE-------------------*/
    Route::group(['middleware' => ['cors','jwt.verify']], function() {
        Route::post('CompanyProfile','CompanyAuthController@CompanyProfile')->middleware('activeUserCheck');
        Route::post('PasswordChange','CompanyAuthController@PasswordChange')->middleware('activeUserCheck');
        Route::post('addDriver','CompanyApiController@addDriver')->middleware('activeUserCheck');
        Route::post('editDriver','CompanyApiController@editDriver')->middleware('activeUserCheck');
        Route::post('deleteDriver','CompanyApiController@deleteDriver')->middleware('activeUserCheck');
        Route::post('companyDriverStore','CompanyAuthController@companyDriverStore')->middleware('activeUserCheck');
        Route::get('companyDriverList','CompanyAuthController@companyDriverList')->middleware('activeUserCheck');
        Route::post('checkUserWallet','CompanyAuthController@checkUserWallet')->middleware('activeUserCheck');
        Route::post('withdrawFromWallet','CompanyAuthController@withdrawFromWallet')->middleware('activeUserCheck');
        Route::post('createRatingReviews','CompanyAuthController@createRatingReviews')->middleware('activeUserCheck');
        Route::get('getAllEmergencyRequest','CompanyAuthController@getAllEmergencyRequest')->middleware('activeUserCheck');
    });
    /*-------------WITHOUT JWT TOKEN AUTHORIZED ROUTE-------------------*/
});