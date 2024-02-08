<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Log extends Authenticatable
{
    
    protected 	$table              = 'log';
    protected 	$guarded            = ['log_id'];
    protected 	$primaryKey         = 'log_id'; // or null
    protected static $arr_action_perform = array('Category_Added'    =>'23',
                                            'Category_Updated'  => '24',
                                            'Company_Added'     => '26',
                                            'Company_Updated'   => '27');

    /**
     * addLog
     *
     * Behaviour : Public
     *
     * @param : 
     *
     * @defination : In order to add log.
     **/
    public static function addLog($action_id,$action_value='',$action_value_table='',$system=false,$remark="",$log_id=0,$request="")
    {
        if(!empty($request)){
            $log_ip = $request->ip();
        }else{
            $log_ip = getipaddress();
        }
        
        
        if (empty($log_ip)) $log_ip = getIP("X");
        if ($log_id == 0)
        {
            $logObj             = self::create([
            'log_ip'            => $log_ip,
            'log_dt'            => date('Y-m-d H:i:s'),
            'loguser_id'        => isset(auth()->user()->adminuserid) ? auth()->user()->adminuserid : '1',
            'action_id'         => self::$arr_action_perform[$action_id],
            'action_value'      => $action_value,
            'action_value_table'=> $action_value_table,
            'remark'            => $remark,
            'user_type'         => (isset(auth()->user()->user_type) && !empty(auth()->user()->user_type))?auth()->user()->user_type:'',
            ]);
            
        }
        else
        {
            
        }
        
    }
}

