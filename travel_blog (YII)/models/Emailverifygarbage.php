<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\Like;

class Emailverifygarbage extends ActiveRecord
{
    public static function collectionName()
    {
        return 'emailverifygarbage'; 
    }

    public function attributes()
    {
        return ['_id','user_id','email','token','pwd','date_on'];
    }   
}