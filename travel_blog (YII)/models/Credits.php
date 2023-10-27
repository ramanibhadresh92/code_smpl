<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use yii\helpers\ArrayHelper;

class Credits extends ActiveRecord
{
    public static function collectionName()
    {
        return 'users_credits';
    }

    public function attributes()
    {
        return ['_id', 'user_id','joined_date','ended_date','credits','credits_desc','status','detail'];
    }
		
	function addcredits($uid,$cre_amt,$cre_desc,$status,$details)
	{
		$date = time();
		$curdate = date('d-m-Y h:i:s');
		$credit_join = new \frontend\models\Credits();
		$credit_join->user_id=$uid;
		$credit_join->joined_date="$date";
		$credit_join->ended_date="$curdate";
		$credit_join->credits=$cre_amt;
		$credit_join->credits_desc=$cre_desc;
		$credit_join->status=$status;
		$credit_join->detail=$details;

		$alredy_exists = Credits::find()->where(['user_id' => $uid,'detail' => $details,'status' => $status])->one();
		if($alredy_exists && $cre_desc!='purchasecredits' && $cre_desc!='sharepost' && $cre_desc!='transfercredits')
		{
			return false;
		}
		else
		{
			$credit_join->insert();
			return true;
		}
	}
	
	public function usertotalcredits()
	{
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        $creditsRecords = ArrayHelper::map(Credits::find()->select(['credits'])->where(['user_id' => $user_id,'status' => '1'])->asarray()->all(), function($data){ return (string)$data['_id'];}, 'credits');
        $credits = array_sum(array_values($creditsRecords));
        $record = array();
        $record[] = array('_id' => $user_id, 'totalcredits' => $credits);
        return $record;
	}
	
	public function travusertotalcredits($user_id)
	{
		$creditsRecords = ArrayHelper::map(Credits::find()->select(['credits'])->where(['user_id' => $user_id,'status' => '1'])->asarray()->all(), function($data){ return (string)$data['_id'];}, 'credits');
        $credits = array_sum(array_values($creditsRecords));
        $record = array();
        $record[] = array('_id' => $user_id, 'totalcredits' => $credits);
        return $record;
	}
	
	public function usercreditshistory()
	{
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		$record = Credits::find()->where(['user_id' => $user_id])->orderBy(['joined_date'=>SORT_DESC])->all();
		return $record;
	}
	
	public function usercreditshistory2($user_id)
	{
		$record = Credits::find()->where(['user_id' => $user_id])->orderBy(['joined_date'=>SORT_DESC])->all();
		return $record;
	}
}