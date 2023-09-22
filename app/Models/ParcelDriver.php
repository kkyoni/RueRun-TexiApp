<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ParcelDriver extends Model
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [	'parcel_id','driver_id','user_id','driver_amount','status','user_confirm' ];

    protected $casts = [
        'parcel_id' => 'string',
        'driver_id' => 'string',
        'deleted_at' => 'timestamp',
        'user_id' => 'string',
        'driver_amount' => 'string',
        'status' => 'string',
        'user_confirm' => 'string',
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

    public function parcel_details(){
        return $this->hasOne(\App\Models\ParcelDetail::class,'id','parcel_id');
    }
}
