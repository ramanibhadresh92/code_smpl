<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class Education extends ActiveRecord
{
    public static function collectionName()
    {
        return 'education';
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
        return Education::find()->orderBy(['name'=>SORT_DESC])->all();
    }   
}