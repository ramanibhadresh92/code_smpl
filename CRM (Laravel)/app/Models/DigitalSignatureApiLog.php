<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Model;

class DigitalSignatureApiLog extends Model
{

	protected $table = 'digisign_api_log';
	/**
	 * addLog
	 *
	 * Behaviour : Public
	 *
	 * @param :
	 *
	 * @defination : In order to add log.
	 **/
	public static function addLog($fullpath="",$request_xml='',$response='')
	{
		$log_ip = getipaddress();
		if (empty($log_ip)) $log_ip = getIP("X");

		$LogObj = new self;
		$LogObj->fullpath = $fullpath;
		$LogObj->request_xml = $request_xml;
		$LogObj->response = $response;
		$LogObj->log_ip = $log_ip;
		$LogObj->created_at = date('Y-m-d H:i:s');
		$LogObj->updated_at = date('Y-m-d H:i:s');
		$LogObj->save();
	}
}

