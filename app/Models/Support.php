<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'supports';
    protected $fillable = [
        'user_id','email','support_categories_id','description','status','admin_comment'
    ];

     protected $casts = [
        'user_id' => 'string',
        'email' => 'string',
        'support_categories_id' => 'string',
        'description' => 'string',
        'deleted_at' => 'timestamp',
        'admin_comment' => 'string',
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

    public function supportCategoryDetail()
    {
        return $this->hasOne('App\Models\SupportCategory','id','support_categories_id');
    }
    public function userDetail()
    {
        return $this->hasOne('App\Models\User','id','user_id');
    }

    public function supportComment()
    {
        return $this->hasOne('App\Models\SupportComment','user_id','user_id');
    }

    public function driverDetail()
    {
        return $this->hasOne('App\Models\User','id','driver_id');
    }
}
