<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class CountryCode extends ActiveRecord
{
    public static function collectionName()
    {
        return 'country_codes';
    }

    public function attributes()
    {
        return ['_id', 'country_name', 'isd_code', 'code'];
    }
   
    public function getUser()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'user_id']);
    }    
}