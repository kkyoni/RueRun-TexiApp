<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ShuttleDriver extends Model
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [	'shuttle_id','driver_id','user_id','driver_amount','status','user_confirm','shuttle_driver_id',
            'arrival_time','departure_time'
        ];

    protected $casts = [
        'shuttle_id' => 'string',
        'driver_id' => 'string',
        'deleted_at' => 'timestamp',
        'user_id' => 'string',
        'driver_amount' => 'string',
        'status' => 'string',
        'user_confirm' => 'string',
        'shuttle_driver_id' => 'string',
        'arrival_time' => 'string',
        'departure_time' => 'string',
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

    public function user_shuttle_details(){
        return $this->hasOne(\App\Models\LinerideUserBooking::class,'id','shuttle_id');
    }

    public function driver_shuttle_details(){
        return $this->hasOne(\App\Models\LinerideBooking::class,'id','shuttle_driver_id')->select('id','seat_available');
    }
}
