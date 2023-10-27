<?php
namespace frontend\models;
use Yii;
use yii\base\Model; 
use yii\mongodb\ActiveRecord;

class Language extends ActiveRecord
{
    public static function collectionName()
    {
        return 'language';
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
        return Language::find()->orderBy(['name'=>SORT_DESC])->all();
    }  

    public function languages() {
        $languages = Language::find()->asarray()->all();
        return $languages;
    }  
}