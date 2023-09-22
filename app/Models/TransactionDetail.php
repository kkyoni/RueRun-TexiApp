<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'transaction_details';
    protected $fillable = [
        'booking_id','parcel_id','shuttle_id','amount','user_id','promo_id','transaction_company','transaction_id','status'
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
        return $this->hasOne('App\Models\User','id','user_id');
    }
    public function tripDetail()
    {
        return $this->hasOne('App\Models\Booking','id','booking_id');
    }

    public function parcelDetail()
    {
        return $this->hasOne('App\Models\ParcelDetail','id','parcel_id');
    }
    public function promoDetail(){
        return $this->hasOne('App\Models\Promocodes','id','promo_id');
    }

}
