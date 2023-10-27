<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class TurnoffNotification extends ActiveRecord
{
    public static function collectionName()
    {
        return 'turn_off_notification';
    }
	
    public function attributes()
    {
        return ['_id', 'user_id', 'post_ids'];
    }
}