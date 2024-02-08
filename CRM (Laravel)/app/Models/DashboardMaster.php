<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UserDashboardMapping;
use App\Models\UserDashboardWidgetMapping;
class DashboardMaster extends Model
{
    protected   $table      = 'dashboard_master';
    protected   $primaryKey = 'dashboard_id'; // or null
    protected   $guarded    = ['dashboard_id']; // or null
    public      $timestamps = true;

    /*
    Use     : Use for find or create query
    Author  : Axay Shah
    Date    : 29 Mar,2019
    */
    public static function findOrCreate($id)
    {
        $obj = static::find($id);
        return $obj ?: new static;
    }

    /*
    Use     : Save Dashboard
    Author  : Axay Shah
    Date    : 29 Mar,2019
    */
    public static function saveDashboard($request)
    {
        $companyId  = Auth()->user()->company_id;
        $userId     = Auth()->user()->adminuserid;
        if(isset($request->dashboard)  && !empty($request->dashboard)) {
            $dashboardData  = json_decode(json_encode($request->dashboard),true);
            $cityId         = AdminUser::where('adminuserid',Auth()->user()->adminuserid)->value('city');
            $delete         = UserDashboardMapping::where("user_id",$userId)->delete();
            foreach($dashboardData as $dashboard) {
                $record                 = self::findOrCreate($dashboard['dashboard_id']);
                $record->dashboard_name = $dashboard['dashboard_name'];
                $record->created_by     = Auth()->user()->adminuserid;
                $record->city_id        = $cityId;
                $record->company_id     = $companyId;
                if($record->save()) {
                    $dashboardId    = $record->dashboard_id;
                    $mapping        = UserDashboardMapping::saveDashboardUserMapping($dashboardId,$userId);
                    if($mapping) {
                        if(isset($dashboard['widget']) && !empty($dashboard['widget'])) {
                            $delete = UserDashboardWidgetMapping::where("user_id",$userId)->where('dashboard_id',$dashboardId)->delete();
                            $widget = $dashboard['widget'];
                            foreach($widget as $wg) {
                                $widgetData = UserDashboardWidgetMapping::storeDashboardWidget($dashboardId,$userId,$wg['id'],$wg['size']);
                            }
                        }
                    }
                }
            }
            return true;
        } else {
            $delete = self::where("created_by",$userId)->delete();
            $delete = UserDashboardMapping::where("user_id",$userId)->delete();
            return false;
        }
    }
}