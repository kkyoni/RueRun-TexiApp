<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use Notifiable;
    use SoftDeletes;
    protected $fillable = ['module_name','view','add','edit','delete','status'];
}
