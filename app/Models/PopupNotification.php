<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PopupNotification extends Model
{
    use Notifiable;
    use SoftDeletes;
    protected $table = 'popup_notifications';
    protected $fillable = ['from_user_id','to_user_id','booking_id','title','description','date','time','parcel_id','shuttle_id'];

    protected $casts = [
        'from_user_id' => 'string',
        'to_user_id' => 'string',
        'booking_id' => 'string',
        'title' => 'string',
        'description' => 'string',
        'date' => 'string',
        'time' => 'string',
        'deleted_at' => 'timestamp',
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
}
