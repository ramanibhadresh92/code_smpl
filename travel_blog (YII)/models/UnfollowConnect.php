<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class UnfollowConnect extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_mute_post';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'unfollow_ids'];
    }

     public function  getUnfollowconnectionsIds($uid)
     {
       return UnfollowConnect::find()->where(['user_id'=>"$uid"])->one();  
     }
}