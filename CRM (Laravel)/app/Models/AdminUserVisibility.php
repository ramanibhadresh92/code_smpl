<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
class AdminUserVisibility extends Model
{
    protected 	$table 		    = 'adminuser_visibility';
    public      $timestamps     = false;
    // public      $incrementing   = false;
    // protected   $primaryKey     = null;
   
    /*
    Use     : Add admin user visiblity 
    Author  : Axay Shah
    Date    : 07 Jan,2019
    */
    public static function add($request){
        try{
            
            DB::beginTransaction();
            $contact = new self();
            $contact->adminuserid   = (isset($request->adminuserid) && !empty($request->adminuserid)) ? $request->adminuserid : 0;
            $contact[`visible`]     = $request->visible;
            $contact->created       = (isset($request->created) && !empty($request->created)) ? $request->created : date("Y-m-d H:i:s");
            $contact->save();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }
}
