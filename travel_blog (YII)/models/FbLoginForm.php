<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\mongodb\ActiveRecord;
use yii\mongodb\ActiveQueryInterface;

class FbLoginForm extends ActiveRecord
{
    public static function CollectionName()
    {
        return ['travel','user'];
    }

    public function attributes()
    {
        return [
            '_id' ,
            'fb_id',
            'username',
            'password',
            'email',
            'photo',
            'created_date',
            'updated_date',
        ];
    }
    
     public function behaviors()
     {
        return [
            TimestampBehavior::className(),
        ];
     }
     
     public static function find()
    {
    }
}
