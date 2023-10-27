<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class UserRefund extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_refund';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'amount'];
    }
		
}