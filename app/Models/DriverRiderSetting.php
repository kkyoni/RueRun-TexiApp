<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverRiderSetting extends Model
{

	protected $table = 'driver_rider_settings';

	protected $fillable = [
	'driver_id','type','weight_limit','min_rate','distance_travel','dimension_data'
	];

}
