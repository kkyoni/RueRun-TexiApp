<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class OutTwonrideBooking extends Model
{
    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'out_of_town_booking';
    protected $fillable = [
    'driver_id','user_id','booking_type','pick_up_location','drop_location','start_time','end_time',
    'total_distance','admin_commision','trip_status','extra_notes','promo_id','booking_date','booking_start_time',
    'latitude','longitude','otp','comment','total_amount','payment_type','drop_latitude',
    'drop_longitude','start_latitude','start_longitude','booking_end_time','tip_amount','toll_amount',
    'airport_charge','taxi_hailing','total_luggage','ride_setting_id','seat_available','seat_booked','payment_status','mailes','start_date','end_date'
    ];

    protected $casts = [
    'driver_id' => 'string',
    'user_id' => 'string',
    'booking_type' => 'string',
    'pick_up_location' => 'string',
    'drop_location' => 'string',
    'start_time' => 'string',
    'end_time' => 'string',
    'total_distance' => 'string',
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
    'booking_date' => 'string',
    'tip_amount' => 'string',
    'booking_start_time' => 'string',
    'booking_end_time' => 'string',
    'toll_amount' => 'string',
    'airport_charge' => 'string',
    'taxi_hailing' => 'string',
    'total_luggage' => 'string',
    'ride_setting_id' => 'string',
    'seat_available' => 'string',
    'seat_booked' => 'string',
    'mailes' => 'string',
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
    public function ride_setting(){
        return $this->hasOne(\App\Models\RideSetting::class,'id','ride_setting_id')->select('id', 'code','name');
    }
}
