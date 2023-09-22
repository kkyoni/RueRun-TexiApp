<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CardDetails extends Model
{
    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'card_details';
    protected $fillable = [
    'user_id','card_number','card_holder_name','card_expiry_month','card_expiry_year','billing_address','bank_name','card_name','cvv','card_type'

    ];

    protected $casts = [
        'card_number' => 'string',
        'card_holder_name' => 'string',
        'card_expiry_month' => 'string',
        'card_expiry_year' => 'string',
        'billing_address' => 'string',
        'bank_name' => 'string',
        'deleted_at' => 'timestamp',
        'card_type' => 'string',
        'cvv' => 'string',
        'card_name' => 'string',
        'card_type' => 'string',
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
