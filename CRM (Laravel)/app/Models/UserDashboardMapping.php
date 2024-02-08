<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UserDashboardWidgetMapping;
class UserDashboardMapping extends Model
{
    protected 	$table 		=	'user_dashboard_mapping';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded    =	['id']; // or null
    public      $timestamps =   true;

    public function dashboard()
    {
        return $this->belongsTo(DashboardMaster::class,'dashboard_id','dashboard_id');
    }
    public function widget()
    {
        return $this->hasMany(UserDashboardWidgetMapping::class,'user_id','user_id');
    }
    /*
    Use     : Added user dashboard mapping
    Author  : Axay Shah
    Date    : 29 Mar,2019
    */
    public static function saveDashboardUserMapping($dashboardId,$userId){
        $mapping = new self();
        $mapping->dashboard_id  =  $dashboardId;
        $mapping->user_id       =  $userId;
        $mapping->created_by    =  Auth()->user()->adminuserid;
        if($mapping->save()){
            return true;
        }
        return false;
    }

    /*
    Use     : List Dashboard with widget
    Author  : Axay Shah
    Date    : 29 Mar,2019
    */
    public static function listDashboard($userId){
        $DashboardWidgetMpg     = new UserDashboardWidgetMapping();
        $DashboardMaster        = new DashboardMaster();
        $Widget                 = new WidgetMaster();
        $DashboardMasterTbl     = $DashboardMaster->getTable();
        $DashboardWidgetMpgTbl  = $DashboardWidgetMpg->getTable();
        $static                 = (new static)->getTable();
        $WidgetTbl              = $Widget->getTable();

        $dashboardList  = self::join($DashboardMasterTbl,"$DashboardMasterTbl.dashboard_id","=","$static.dashboard_id")
                        ->where("$static.user_id",$userId)
                        ->where("$DashboardMasterTbl.status",DASHBOARD_STATUS)
                        ->get();

        if($dashboardList){
            foreach($dashboardList as $dashboard){
               $data    = UserDashboardWidgetMapping::join($WidgetTbl,"$DashboardWidgetMpgTbl.widget_id","=","$WidgetTbl.widget_id")
                ->where("$DashboardWidgetMpgTbl.user_id",$userId)
                ->where("$DashboardWidgetMpgTbl.dashboard_id",$dashboard->dashboard_id)
                ->get();
                $dashboard['widget'] = $data;
            }
        }
        return $dashboardList;
    }
}
