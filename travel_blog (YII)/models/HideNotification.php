<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class HideNotification extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_hidenotification';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'notification_ids'];
    }
    
    public function getHideNotifications($user_id)
    {
        return HideNotification::find()->where(['user_id'=>"$user_id"])->one();
    }
}