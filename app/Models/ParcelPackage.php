<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ParcelPackage extends Model
{
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [	'parcelbooking_id','parcel_length','parcel_deep','parcel_height','parcel_weight','total_amount','package_type' ];

    protected $casts = [
        'parcelbooking_id' => 'string',
        'parcel_length' => 'string',
        'parcel_deep' => 'string',
        'parcel_height' => 'string',
        'parcel_weight' => 'string',
        'total_amount' => 'string',
        'deleted_at' => 'timestamp',
        'package_type' => 'string',
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

    public function parcel_detail()
    {
        return $this->belongsTo('App\ParcelDetail','parcelbooking_id','id');
    }
}
