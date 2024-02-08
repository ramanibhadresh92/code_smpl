<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Facades\LiveServices;

class PortalImpactStats extends Model
{
	protected 	$table 		= 'portal_impact_stats';
	protected 	$primaryKey = 'id'; // or null
	protected 	$guarded 	= ['id'];
	public 		$timestamps = true;

	public function saveImpactData($request)
	{
		$PortalImpactStats 				= new self();
		$PortalImpactStats->stats_json	= json_encode($request['portal_impact_stats']);
		$PortalImpactStats->save();
		return true;
	}
}