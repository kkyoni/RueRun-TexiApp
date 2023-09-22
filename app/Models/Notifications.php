<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'notifications';
    protected $fillable = [
        'driver_id','user_id','title','description','is_read_user','is_read_driver','booking_id','booking_date','time'
    ];
    protected $casts = [
        'driver_id' => 'string',
        'user_id' => 'string',
        'description' => 'string',
        'title' => 'string',
        'is_read_user' => 'string',
        'is_read_driver' => 'string',
        'booking_id' => 'string',
        'booking_date' => 'string',
        'time' => 'string',
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
    public function user(){
        return $this->hasOne(\App\Models\User::class,'id','user_id');
    }
    
}
