<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user_notifications';
    protected $fillable = [ 'sent_from_user','sent_to_user','booking_id','out_of_town','taxi_hailing','title','notification_for','description','is_read','admin_flag','parcel_id','shuttle_id','flag_city'];
    protected $casts = [
    'sent_from_user' => 'string',
    'sent_to_user' => 'string',
    'booking_id' => 'string',
    'title' => 'string',
    'description' => 'string',
    'is_read' => 'string',
    'admin_flag' => 'string',
    'notification_for' => 'string',
    'deleted_at' => 'timestamp',
    'parcel_id' => 'string',
    'shuttle_id' => 'string',
    'taxi_hailing' => 'string',
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

    public function driver_details(){
        return $this->hasOne(\App\Models\User::class,'id','sent_from_user');
    }

    public function user_details(){
        return $this->hasOne(\App\Models\User::class,'id','sent_to_user');
    }

    public function booking_details(){
        return $this->hasOne(\App\Models\Booking::class,'id','booking_id')->with(['ride_setting']);
    }

    public function shuttle_details(){
        return $this->hasOne(\App\Models\LinerideUserBooking::class,'id','shuttle_id')->with(['ride_setting']);
    }
    
    public function user(){
        return $this->hasOne(\App\Models\User::class,'id','sent_from_user');
    }

    public function driver(){
        return $this->hasOne(\App\Models\User::class,'id','sent_to_user')->with(['driver_vehicle','driver_model']);
    }

    public function ride_setting(){
        return $this->hasOne(\App\Models\RideSetting::class,'id','ride_setting_id')->select('id', 'code','name');
    }
    public function out_town_data(){
        return $this->hasOne(\App\Models\OutTwonrideBooking::class,'id','out_of_town')->with(['ride_setting']);
    }

}
