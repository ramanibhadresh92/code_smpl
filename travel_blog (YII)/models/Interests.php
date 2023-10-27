<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class Interests extends ActiveRecord
{
    public static function collectionName()
    {
        return 'interests';
    }

    public function attributes()
    {
        return ['_id', 'name'];
    }

    public function getUser()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'user_id']);
    }
	
	public function getBusCat()
    {
        return Interests::find()->orderBy(['name'=>SORT_DESC])->all();
    }    
}