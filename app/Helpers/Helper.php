<?php

namespace App\Helpers;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\RoleHasPermissions;
use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\Auth;
class Helper {


    // % calculation
	public static function ValueInPer($baseAmount,$totalAmount) {
		$count = 0;
		if($baseAmount != 0){
			$data= ($baseAmount * 100 );
			$count= ($data / $totalAmount);         
			$count=number_format((float)$count, 2, '.', '');
		}
		return $count.'%';    

	}
    // % calculation


	public static function TimeToMin($min = 0) {
		return 0;  

	}

	public static function auth(){
        $users = User::with('role')
        ->whereHas('role',function ($q){
            return $q->where('id','=',\Auth::user()->role_id);
        })
        ->orderBy('id','desc')->first();
        if ($users){
           return $users->role->name;
       }
       return null;
   }


   public static function checkPermission($permissionName=0){

    if(Auth::user()->user_type == "superadmin"){
        return true;
    }else{

        $user = Auth::user();

        $role = RoleHasPermissions::with('permission')
        ->where('role_id','=',$user->role_id)
        ->get();
        // dd($role);

        $user_permission=[];
        foreach ($role as $key=>$value){
            foreach ($value->permission as $perm){
                if(in_array($perm->name, $permissionName)){
                    $user_permission[]=$perm;    
                }                            
            }
        }

        if(empty($user_permission)){
            return false;
        }else{
            return true;
        }

    }
    return false;


}
}