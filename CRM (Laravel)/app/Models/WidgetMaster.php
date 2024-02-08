<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetMaster extends Model
{
    protected 	$table 		=	'widget_master';
    protected 	$primaryKey =	'widget_id'; // or null
    protected 	$guarded    =	['widget_id']; // or null
    public      $timestamps =   true;

    /*
    Use     : List widget
    Author  : Axay Shah
    Date    : 29 Mar,2019   
    */
    public static function list($request){
        $name       = (isset($request->name)    && !empty($request->name))      ? $request->name : " ";
        $id         = (isset($request->id)      && !empty($request->id))          ? $request->id : " ";
        $apiUrl     = (isset($request->api_url) && !empty($request->api_url))   ? $request->api_url : " ";

        $list       = self::orderBy('id');
        if(!empty($name)){
            $list->where('name','like',"%$name%");
        }
        if(!empty($apiUrl)){
            $list->where('api_url','like',"%$apiUrl%");
        }
        if(!empty($id)){
            $list->where('id',$id);
        }
        $data = $list->get();
        return $data;
    }

    
}
