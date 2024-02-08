<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WmBatchCollectionMap extends Model
{
    protected 	$table 		=	'wm_batch_collection_map';
    protected 	$primaryKey =	'id'; // or null
    protected 	$guarded 	=	['id'];
    public 		$timestamps = 	false;

    public static function insertBatchCollectionMapData($insert_batch,$res){
		if(!empty($insert_batch) && !empty($res)) {
			$data = array();	
			if(is_array($res)) {
				foreach ($res as $result) { 
					$data['batch_id'] 		= $insert_batch;
					$data['collection_id'] 	= (isset($result['collection_id'])?$result['collection_id']:0);
					self::insert($data);
				}	
			} else {
				$data['batch_id'] 		= $insert_batch;
				$data['collection_id'] 	= (isset($res)?$res:0);
                self::insert($data);
            }
		}
		return true;
    }
}
