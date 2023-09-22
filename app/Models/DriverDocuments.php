<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DriverDocuments extends Model
{
    use Notifiable;
    use SoftDeletes;

    protected $table = 'driver_documents';
    protected $fillable = [
        'driver_id','doc_image','doc_name','vehicle_id','doc_type','ssn_no'
    ];

    protected $casts = [
        'driver_id' => 'string',
        'doc_image' => 'string',
        'doc_name' => 'string',
        'vehicle_id' => 'string',
        'deleted_at' => 'timestamp',
        'doc_type' => 'string',
        'ssn_no' => 'string',
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

    public function userDetail()
    {
        return $this->hasOne('App\Models\User','id','driver_id');
    }
    
}
