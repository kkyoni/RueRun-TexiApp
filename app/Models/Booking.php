<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'bookings';
    protected $fillable = [
    'driver_id','user_id','booking_type','pick_up_location','drop_location','start_time','end_time','hold_time','vehicle_id',
    'base_fare','total_km','admin_commision','trip_status','extra_notes','promo_id',
    'latitude','longitude','otp','comment','total_amount','payment_type','drop_latitude',
    'drop_longitude','start_latitude','start_longitude','hold_time_amount','booking_date','booking_start_time',
    'booking_end_time','tip_amount','toll_amount','airport_charge','taxi_hailing','service_id',
    'total_luggage','ride_setting_id','seats','card_id','payment_status','start_date', 'admin_comm_status','tip_amount_status','notification_for','out_town_id','driver_arrived_time','drop_lat','drop_long','bstart_date','bend_date'];

    protected $casts = [
    'driver_id' => 'string',
    'user_id' => 'string',
    'booking_type' => 'string',
    'pick_up_location' => 'string',
    'drop_location' => 'string',
    'start_time' => 'string',
    'end_time' => 'string',
    'hold_time' => 'string',
    'vehicle_id' => 'string',
    'base_fare' => 'string',
    'total_km' => 'string',
    'admin_commision' => 'string',
    'trip_status' => 'string',
    'extra_notes' => 'string',
    'promo_id' => 'string',
    'latitude' => 'string',
    'longitude' => 'string',
    'otp' => 'string',
    'total_amount' => 'string',
    'payment_type' => 'string',
    'start_latitude' => 'string',
    'start_longitude' => 'string',
    'drop_latitude' => 'string',
    'drop_longitude' => 'string',
    'deleted_at' => 'timestamp',
    'hold_time_amount' => 'string',
    'booking_date' => 'string',
    'tip_amount' => 'string',
    'booking_start_time' => 'string',
    'booking_end_time' => 'string',
    'toll_amount' => 'string',
    'airport_charge' => 'string',
    'taxi_hailing' => 'string',
    'service_id' => 'string',
    'total_luggage' => 'string',
    'ride_setting_id' => 'string',
    'seats' => 'string',
    'flag' => 'string',
    'payment_status' => 'string',
    'start_date' => 'string',
    'notification_for' => 'string',
    'out_town_id' => 'string',
    'driver_arrived_time' => 'string',
    'drop_lat'=>'string',
    'drop_long'=>'string'
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

    public function user(){
        return $this->hasOne(\App\Models\User::class,'id','user_id');
    }

    public function driver(){
        return $this->hasOne(\App\Models\User::class,'id','driver_id')->with(['driver_vehicle','driver_model']);
    }

    public function driver_details(){
        return $this->hasOne(\App\Models\DriverDetails::class,'driver_id','driver_id');
    }

    public function ride_type_id(){
        return $this->hasOne(\App\Models\RideSetting::class,'id','ride_setting_id');
    }


    public function promocode_get(){
        return $this->hasOne(\App\Models\Promocodes::class,'id','promo_id');
    }
    public function booking_details(){
        return $this->hasOne(\App\Models\Booking::class,'id','id')->with(['ride_setting']);
    }
    public function shuttle_details(){
        return $this->hasOne(\App\Models\LinerideUserBooking::class,'id','id')->with(['ride_setting']);
    }

    public function car_details()
    {
        return $this->hasOne(\App\Models\Vehicle::class,'id','vehicle_id');
    }

    public function ride_setting(){
        return $this->hasOne(\App\Models\RideSetting::class,'id','ride_setting_id')->select('id', 'code','name');
    }
    public function driver_fields(){
        return $this->hasOne(\App\Models\User::class,'id','driver_id')->select('avatar');
    }

    public function outTownDetails(){
        return $this->hasOne(\App\Models\OutTwonrideBooking::class,'id','out_town_id');
    }

    public function card_details()
    {
        return $this->hasMany(\App\Models\CardDetails::class,'id','card_id');
    }
}
