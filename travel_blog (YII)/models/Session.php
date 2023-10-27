<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class Session extends ActiveRecord
{
    public static function tableName()
    {
        return 'session';
    }

    public function attributes()
    {
        return ['_id', 'id', 'data', 'expire'];
    }

    public function getOnlineUsers($data, $loginuid)
    {
		if(!empty($data)) {
            return Session::find()->asArray()->where(['in','id', $data])->andWhere(['not in', 'id', array($loginuid)])->all();
        }
    }
	
	public function getAllOnlineUsers($loginuid)
    {
		return Session::find()->asArray()->where(['not in','data','__flash|a:0:{}'])->andWhere(['in', 'id', $loginuid])->all();
    }
	
	public function getUser()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'id']);
    }
}