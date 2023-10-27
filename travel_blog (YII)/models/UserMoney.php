<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use yii\helpers\ArrayHelper;

class UserMoney extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_money';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'amount'];
    }
	
	public function usertotalmoney()
	{
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        $UserMoneyRecords = ArrayHelper::map(UserMoney::find()->select(['amount'])->where(['user_id' => $user_id])->asarray()->all(), function($data){ return (string)$data['_id'];}, 'amount');
		$UserMoney = array_sum(array_values($UserMoneyRecords));
		$record = array();
		$record[] = array('_id' => $user_id, 'totalmoney' => $UserMoney);
		return $record;
	}
}