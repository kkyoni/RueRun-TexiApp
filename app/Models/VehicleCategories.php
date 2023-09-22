<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class VehicleCategories extends Model
{
    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'vehicle_categories';
    protected $fillable = [
       'vehicle_image','name','base_fare','price_per_km','extra_cost_dropdown','extra_cost_include','status','cancellation_time_in_minutes','cancellation_charge_in_per','vehicle_type'
    ];
    protected $casts = [
        'vehicle_image' => 'string',
        'name' => 'string',
        'base_fare' => 'string',
        'price_per_km' => 'string',
        'extra_cost_dropdown' => 'string',
        'extra_cost_include' => 'string',
        'status' => 'string',
        'cancellation_time_in_minutes' => 'string',
        'cancellation_charge_in_per' => 'string',
        'vehicle_type' => 'string',
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
}
