<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class UserReport extends Model
{
    use Notifiable;
    use SoftDeletes;
    protected $table = 'user_reports';
    protected $fillable = ['from_user_id','to_user_id','booking_id','behaviors_id','title','description','date','time','admin_comments'];

    protected $casts = [
        'from_user_id' => 'string',
        'to_user_id' => 'string',
        'booking_id' => 'string',
        'title' => 'string',
        'description' => 'string',
        'date' => 'string',
        'time' => 'string',
        'admin_comments' => 'string',
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

    public function from_user()
    {
        return $this->hasOne('App\Models\User','id','from_user_id');
    }

    public function to_user()
    {
        return $this->hasOne('App\Models\User','id','to_user_id');
    }
}
