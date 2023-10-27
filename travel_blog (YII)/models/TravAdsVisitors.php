<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class TravAdsVisitors extends ActiveRecord
{
    public static function collectionName()
    {
        return 'ads_visitors';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'ad_id', 'visited_date', 'action', 'v_date'];
    }

	public function adInsertion($adid,$uid,$type)
	{
		$date = time();
		$ad = new TravAdsVisitors();
		$ad->user_id = "$uid";
		$ad->ad_id = "$adid";
		$ad->visited_date = "$date";
		$ad->v_date = date("Y-m-d h:i:s");
		$ad->action = "$type";
		$ad->insert();
	}

	public function getCount($adid,$type)
	{
		return TravAdsVisitors::find()->where(['ad_id' => "$adid",'action' => "$type"])->count();
	}

	public function getSpecificCount($adid,$currenttime,$pasttime)
	{
		return TravAdsVisitors::find()->where(['ad_id'=>"$adid"])->andwhere(['visited_date'=> ['$lte'=>"$currenttime"]])->andwhere(['visited_date'=> ['$gte'=>"$pasttime"]])->count();
	}

	public function getSpecificTypeCount($adid,$type,$currenttime,$pasttime)
	{
		return TravAdsVisitors::find()->where(['ad_id'=>"$adid",'action' => "$type"])->andwhere(['visited_date'=> ['$lte'=>"$currenttime"]])->andwhere(['visited_date'=> ['$gte'=>"$pasttime"]])->count();
	}
}