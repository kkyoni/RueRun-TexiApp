<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use SoftDeletes;
    use HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $table = 'users';
    protected $fillable = [
    'first_name','last_name','address','country','city_id','state_id','contact_number','uuid','email','ref_id','avatar','password','encrypt_password','status','doc_status','user_type','sign_up_as','login_status','gender','social_id','social_media','link_expire','link_code','availability_status','latitude','longitude','device_token','device_type','driver_doc','car_doc','vehicle_doc_status','driver_signup_as','company_id','company_name','vehicle_id','add_latitude','add_longitude','zipcode','driver_company_id','country_code','role_id','make','login_token','country_code_al'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [ 'password', 'remember_token' ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
    'first_name' => 'string',
    'last_name' => 'string',
    'address' => 'string',
    'country' => 'string',
    'country_code' => 'string',
    'city_id' => 'string',
    'state_id' => 'string',
    'contact_number' => 'string',
    'uuid' => 'string',
    'email' => 'string',
    'ref_id' => 'string',
    'avatar' => 'string',
    'status' => 'string',
    'doc_status' => 'string',
    'user_type' => 'string',
    'sign_up_as' => 'string',
    'login_status' => 'string',
    'gender' => 'string',
    'social_id' => 'string',
    'social_media' => 'string',
    'link_expire' => 'string',
    'link_code' => 'string',
    'availability_status' => 'string',
    'latitude' => 'string',
    'longitude' => 'string',
    'vehicle_id' => 'string',
    'device_token' => 'string',
    'device_type' => 'string',
    'email_verified_at' => 'datetime',
    'deleted_at' => 'timestamp',
    'driver_doc' => 'string',
    'car_doc' => 'string',
    'vehicle_doc_status' => 'string',
    'company_id' => 'string',
    'company_name' => 'string',
    'add_latitude' => 'string',
    'add_longitude' => 'string',
    'reason_for_inactive' => 'string',
    'role_id' => 'string',
    'make' => 'string',
    'login_token' => 'string',
    ];
    protected function castAttribute($key, $value)
    {
        if (! is_null($value)) {
            return parent::castAttribute($key, $value);
        }
        switch ($this->getCastType($key)) {
            case 'int':
            case 'integer':
            return (int) 0;
            case 'real':
            case 'float':
            case 'double':
            return (float) 0;
            case 'enum':
            return '';
            case 'string':
            return '';
            case 'bool':
            case 'boolean':
            return false;
            case 'object':
            case 'array':
            case 'json':
            return [];
            case 'collection':
            return new BaseCollection();
            case 'date':
            return $this->asDate('0000-00-00');
            case 'datetime':
            return $this->asDateTime('0000-00-00');
            case 'timestamp':
            return '';
            default:
            return $value;
        }
    }
    public static function boot()
    {
        parent::boot();
        // self::creating(function ($model) {
        //     $model->uuid = strtoupper(uniqid());
        // });
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    

    protected $appends = ['totalKm','totalRat','userTotalKm','avgRate'];

    public function card_details()
    {
        return $this->hasMany(\App\Models\CardDetails::class,'user_id','id');
    }

    public function company_details()
    {
        return $this->hasOne(\App\Models\CompanyDetail::class,'company_id','id');
    }

    public function driver_details()
    {
        return $this->hasOne(\App\Models\DriverDetails::class,'driver_id','id');
    }

    public function driver_details_settings()
    {
        return $this->hasMany(\App\Models\DriverDetails::class,'driver_id','id');
    }

    public function driver_model()
    {
        return $this->hasOne(\App\Models\Vehicle::class,'id','vehicle_id');
    }
    public function driver_vehicle()
    {
        return $this->hasOne(\App\Models\DriverDetails::class,'driver_id','id');
    }
    public function state()
    {
        return $this->hasOne(\App\Models\State::class,'id','state_id');
    }
    public function city()
    {
        return $this->hasOne(\App\Models\City::class,'id','city_id');
    }

    public function referralCount()
    {
        return $this->hasMany(\App\Models\User::class,'ref_id','uuid');
    }

    public function bookingDriverDetail()
    {
        return $this->hasMany(\App\Models\Booking::class,'driver_id');

    }
    public function bookingUserDetail()
    {
        return $this->hasMany(\App\Models\Booking::class,'user_id');

    }
    public function getTotalKmAttribute()
    {
        $totalKm = \App\Models\Booking::where('driver_id',$this->id)->sum('total_km');
        return $totalKm;
    }

    public function getUserTotalKmAttribute()
    {
        $totalKm = \App\Models\Booking::where('user_id',$this->id)->sum('total_km');
        return $totalKm;
    }

    public function getTotalRatAttribute()
    {
        $totalRat = \App\Models\RatingReviews::where('from_user_id',$this->id)->orWhere('to_user_id',$this->id)->sum('rating');
        return $totalRat;
    }

    public function getAvgRateAttribute(){
        $avgRat = \App\Models\RatingReviews::Where('to_user_id',$this->id)->avg('rating');
        // if(empty($avgRat)){
        //     return $avgRat = 0;
        // } else{
        //     return $avgRat;
        // }
        return $avgRat;
    }
    public function role(){
        return $this->hasOne(Role::class,'id','role_id');
    }

    //  public function userDetail()
    // {
    //     return $this->hasOne(\App\Models\User::class,'uuid','=','ref_id');
    // }

}
