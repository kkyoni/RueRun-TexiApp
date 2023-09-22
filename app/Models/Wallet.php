<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallets';
    protected $fillable = [
       'user_id','amount','reffering_amount','company_amount'
    ];

    protected $casts = [
		'user_id' => 'string',
		'amount' => 'string',
		'deleted_at' => 'timestamp',
        'reffering_amount' => 'string',
        'company_amount' => 'string',
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

    public function user()
    {
        return $this->hasOne(\App\Models\User::class,'id','user_id');
    }
}
