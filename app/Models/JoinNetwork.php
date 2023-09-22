<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;


class JoinNetwork extends Model
{
    use Notifiable;
    use SoftDeletes;


    protected $table = 'join_network';
    protected $fillable = [
        'user_id','ref_id','total_earning'
    ];


}
