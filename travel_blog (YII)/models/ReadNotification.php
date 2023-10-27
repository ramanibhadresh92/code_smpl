<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class ReadNotification extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_readnotification';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'notification_ids'];
    }
}