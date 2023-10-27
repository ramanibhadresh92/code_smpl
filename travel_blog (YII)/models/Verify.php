<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class Verify extends ActiveRecord
{
    public static function collectionName()
    {
        return 'users_verify';
    }

    public function attributes()
    {
        return ['_id', 'user_id','joined_date','ended_date','status'];
    }
	
	public function getVerifyusers()
    {
        return Verify::find()->all();
	}

	public function isVerify($id)
    {
		$session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
		$curdate = strtotime('now');
		
		$result = Verify::find()->where(['user_id' => (string)$id,'status' => '1'])->orderBy(['joined_date'=>SORT_DESC])->asarray()->one();
		
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
	
	public function End_date_Verify($id)
    {
		$session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
		$curdate = strtotime('now');
		
		$result = Verify::find()->where(['user_id' => (string)$id,'status' => '1'])->orderBy(['joined_date'=>SORT_DESC])->asarray()->one();
		
		if(!empty($result))
		{
			$ended_date = isset($result['ended_date']) ? $result['ended_date'] : '';
			if($ended_date != '') {
				$ended_date = strtotime($ended_date);
				if($ended_date > $curdate) {
					return $result['ended_date'];
					exit;
				}
			}
		}
		
		return false;
		exit;
	}

}