<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class RatingReviews extends Model
{
    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'rating_reviews';
    protected $fillable = [
        'from_user_id','to_user_id','status','booking_id','shuttle_id','rating','comment','is_read_user','is_read_driver','behaviors_id','parcel_id'
    ];

    protected $casts = [
        'from_user_id' => 'string',
        'to_user_id' => 'string',
        'status' => 'string',
        'booking_id' => 'string',
        'shuttle_id' => 'string',
        'rating' => 'string',
        'comment' => 'string',
        'is_read_user' => 'string',
        'is_read_driver' => 'string',
        'deleted_at' => 'timestamp',
        'parcel_id' => 'string',
    ];

    protected function castAttribute($key, $value)
    {
        if (!is_null($value)) {
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

    public function from_user(){
        return $this->hasOne(\App\Models\User::class,'id','from_user_id');
    }

    public function to_user(){
        return $this->hasOne(\App\Models\User::class,'id','to_user_id');
    }
}
