<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class WalletHistory extends Model
{
    protected $table = 'wallet_histories';
    protected $fillable = [
       'user_id','amount','description','refer_user_id'
    ];

    protected $casts = [
		'user_id' => 'string',
		'amount' => 'string',
		'description' => 'string',
		'deleted_at' => 'timestamp',
        'refer_user_id' => 'string',
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

    public function user_details()
    {
        return $this->hasOne(\App\Models\User::class,'id','user_id');
    }

    public function refer_user_details()
    {
        return $this->hasMany(\App\Models\User::class,'id','refer_user_id');
    }
}
