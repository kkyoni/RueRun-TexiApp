<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use Notifiable;
    use SoftDeletes;
    protected $fillable = [
       'name','model_id','status',
    ];

    protected $appends = ['fullname'];

    public function model()
    {
        return $this->belongsTo('App\Models\VehicleBody','model_id','id');
    }

	public function getFullNAmeAttribute() {
    	// return $this->name.' - '.$this->model->name;
        return $this->model->name.' - '.$this->name;
    }
}
