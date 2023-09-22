<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DriverDetails extends Model
{
    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'driver_details';
    protected $fillable = [
        'vehicle_model_id','vehicle_model','driver_id','vehicle_plate','vehicle_image','color','ride_type', 'mileage','year','seat'
    ];

    protected $casts = [
        'vehicle_model_id' => 'string',
        'vehicle_model' => 'string',
        'driver_id' => 'string',
        'vehicle_plate' => 'string',
        'vehicle_image' => 'string',
        'ride_type' => 'string',
        'mileage' => 'string',
        'year' => 'string',
        'deleted_at' => 'timestamp',
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
    public function DriverRideSetting()
    {
        return $this->hasOne('App\Models\RideSetting','id','ride_type');
    }
}
