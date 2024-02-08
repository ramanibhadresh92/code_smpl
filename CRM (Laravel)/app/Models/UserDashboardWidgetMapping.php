<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDashboardWidgetMapping extends Model
{
    protected 	$table 		=	'user_dashboard_widget_mapping';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded    =	['id']; // or null
    public      $timestamps =   true;

    /*
    Use     : Store user dashboard widget mapping Data
    Author  : Axay Shah
    Date    : 29 Mar,2019
    */
    public static function storeDashboardWidget($dashboardId,$userId,$widgetId,$size = 0){
        $mapping = new self();
        $mapping->dashboard_id  =  $dashboardId;
        $mapping->user_id       =  $userId;
        $mapping->widget_id     =  $widgetId;
        $mapping->size          =  $size;
        $mapping->created_by    =  Auth()->user()->adminuserid;
        $mapping->save();
    }
}
