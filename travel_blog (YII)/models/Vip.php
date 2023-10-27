<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class Vip extends ActiveRecord
{
    public static function collectionName()
    {
        return 'users_vip';
    }

    public function attributes()
    {
        return ['_id', 'user_id','joined_date','ended_date','status'];
    }

	public function getUser()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'user_id']);
    }

	public function getVipusers()
    {
        return Vip::find()->all();
	}
	
	public function isVIP($id)
    {
		$session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
		$curdate = strtotime('now');
		
		$result = Vip::find()->where(['user_id' => (string)$id,'status' => '1'])->orderBy(['joined_date'=>SORT_DESC])->asarray()->one();
		
		if(!empty($result))
		{
			$ended_date = isset($result['ended_date']) ? $result['ended_date'] : '';
			if($ended_date != '') {
				$ended_date = strtotime($ended_date);
				if($ended_date > $curdate) {
					return true;
					exit;
				}
			}
		}
		return false;
		exit;
	}

}