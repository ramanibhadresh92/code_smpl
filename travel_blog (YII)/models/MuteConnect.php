<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class MuteConnect extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_mute';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'mute_ids'];
    }
    
    public function getMuteconnectionsIds($user_id)
    {
        return MuteConnect::find()->where(['user_id'=>"$user_id"])->one();
    }    
}