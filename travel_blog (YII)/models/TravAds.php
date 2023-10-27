<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\mongodb\ActiveRecord;
use frontend\models\TravAdsVisitors;

class TravAds extends ActiveRecord
{
    public static function collectionName()
    {
        return 'ads';
    }
	
    public function attributes()
    {
        return ['_id', 'user_id', 'post_id'];
    }
	

	public function displayAd($adtype, $isCompulsory=false, $loadedAds=array())
    {
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		
		$returnData = PostForm::find()->where(['post_privacy'=>'Public','is_ad'=>'1','adobj'=>"$adtype"])->orderBy(['post_created_date'=>SORT_DESC])->limit(1)->one();

		if(empty($returnData)) {
			if($isCompulsory) {
				$types = array("pagelikes", "brandawareness", "websiteleads", "websiteconversion", "pageendorse", "eventpromo");

				$idsFilter = ArrayHelper::map(PostForm::find()->select(['_id'])->where(['post_privacy'=>'Public','is_ad'=>'1'])->andWhere(['in', 'adobj', $types])->asarray()->all(), function($result) { 
					return (string)$result['_id']; }, 'ok');
				$idsFilter = array_keys($idsFilter);
				if(!empty($idsFilter)) {
					$Uniqids = array_merge(array_diff($idsFilter, $loadedAds), array_diff($loadedAds, $idsFilter));
					if(!empty($Uniqids)) {
						$randId = $Uniqids[array_rand($Uniqids)];
						$returnDataI = PostForm::find()->where([(string)'_id'=> $randId])->one();
						return $returnDataI;
					} else {
						return false;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			$returnDataId = (string)$returnData['_id'];
			if(in_array($returnDataId, $loadedAds)) {
				return false;
			} else {	
				return $returnData;
			}
		}
	} 
	
	protected function totalAd($adtype)
    {
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		if ($user_id)
		{
			return PostForm::find()->where(['adobj' => $adtype,'is_ad' => '1','adruntype' => 'daily'])->count();
		}
	}
	
	public function getTotalAds()
    {
		return PostForm::find()->where(['is_ad' => '1'])->count();
	}
	
	public function getAds()
    {
		return PostForm::find()->where(['is_ad' => '1'])->orWhere(['is_ad' => '2'])->all();
	}
	
	public function getActiveAds()
    {
		return PostForm::find()->where(['is_ad' => '1'])->all();
	}
	
	protected function getanotherrandad($adtype,$uid)
	{
		$tcount = TravAds::totalAd($adtype);
		if($tcount > 0)
		{
			return TravAds::getrandAd($adtype,$uid);
		}
		else
		{
			return '';
		}
	}

	public function getrandAd($adtype, $uid, $isCompulsory=false, $loadedAds=array())
	{
		$date = time();
		$trav_store_ads = TravAds::displayAd($adtype, $isCompulsory, $loadedAds);

		$post_created_date = $trav_store_ads['post_created_date'];
		$adstartdate = $trav_store_ads['adstartdate'];
		$adenddate = $trav_store_ads['adenddate'];
		if(isset($trav_store_ads) && !empty($trav_store_ads))
		{
			if($trav_store_ads['adruntype'] == 'daily' && $post_created_date <= $date)
			{
				TravAdsVisitors::adInsertion($trav_store_ads['_id'],$uid,'impression');
				return $trav_store_ads;
			}
			else if($trav_store_ads['adruntype'] == 'manual' && $adstartdate <= $date && $adenddate >= $date)
			{
				TravAdsVisitors::adInsertion($trav_store_ads['_id'],$uid,'impression');
				return $trav_store_ads;
			}
			else
			{
				//return TravAds::getanotherrandad($adtype,$uid);
			}
		}
	}
}