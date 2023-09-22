<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class EmergencyRequest extends Model
{
    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'emergency_requests';
    protected $fillable = [
    'type_id','driver_id','user_id','booking_id','parcel_id','shuttle_id','extra_notes','status','view_status'
    ];
    protected $casts = [
        'type_id' => 'string',
        'driver_id' => 'string',
        'status' => 'string',
        'extra_notes' => 'string',
        'view_status' => 'string',
        'deleted_at' => 'timestamp',
        'user_id' => 'string',
        'booking_id' => 'string',
        'parcel_id' => 'string',
        'shuttle_id' => 'string',
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

    public function driver(){
        return $this->hasOne(\App\Models\User::class,'id','driver_id');
    }

    public function user(){
        return $this->hasOne(\App\Models\User::class,'id','user_id');
    }


    public function emergency_type(){
        return $this->hasOne(\App\Models\EmergencyType::class,'id','type_id');
    }
}
