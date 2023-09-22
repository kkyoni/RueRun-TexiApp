<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class VehicleBody extends Model
{
    use Notifiable;
    use SoftDeletes;
    protected $fillable = [ 'name','status' ];
}
