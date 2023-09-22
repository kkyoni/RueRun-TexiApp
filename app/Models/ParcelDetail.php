<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ParcelDetail extends Model
{
    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'parcel_details';
    protected $fillable = [
    	'driver_id','user_id','pick_up_location','drop_location','start_latitude','start_longitude','drop_latitude','drop_longitude','start_time','end_time','parcel_length','parcel_deep','parcel_height','hold_time','base_fare','total_distance','admin_commision','parcel_status','extra_notes','promo_id','otp','total_amount','payment_type','booking_date','booking_start_time','booking_end_time','hold_time_amount','tip_amount','airport_charge','toll_amount','booking_type','recepient_name','contact_number','description','ride_setting_id','package_type','parcel_weight','transaction_id','card_id','parcel_package_type'
        ,'admin_comm_status','tip_amount_status'
    ];

    protected $casts = [
        'driver_id' => 'string',
        'user_id' => 'string',
        'booking_type' => 'string',
        'pick_up_location' => 'string',
        'drop_location' => 'string',
        'start_time' => 'string',
        'end_time' => 'string',
        'hold_time' => 'string',
        'base_fare' => 'string',
        'total_distance' => 'string',
        'admin_commision' => 'string',
        'parcel_length' => 'string',
        'parcel_status' => 'string',
        'extra_notes' => 'string',
        'promo_id' => 'string',
        'start_latitude' => 'string',
        'start_longitude' => 'string',
        'otp' => 'string',
        'total_amount' => 'string',
        'payment_type' => 'string',
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
        'parcel_deep' => 'string',
        'parcel_height' => 'string',
        'package_type' => 'string',
        'parcel_weight' => 'string',
        'ride_setting_id' => 'string',
        'transaction_id' => 'string',
        'card_id' => 'string',
        'parcel_package_type' => 'string',
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
    public function service_type(){
        return $this->hasOne(\App\Models\RideSetting::class,'id','ride_setting_id');
    }
    public function parcel_images(){
        return $this->hasMany(\App\Models\ParcelImage::class,'parcel_id','id');
    }

    public function parcel_packages(){
        return $this->hasMany(\App\Models\ParcelPackage::class,'parcelbooking_id','id');
    }
    public function ride_setting(){
        return $this->hasOne(\App\Models\RideSetting::class,'id','ride_setting_id')->select('id', 'code','name');
    }
    public function driver_fields(){
        return $this->hasOne(\App\Models\User::class,'id','driver_id')->select('avatar');
    }

}
