<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class AbuseStatement extends ActiveRecord
{
   public static function collectionName()
    {
        return 'abusestatement';
    }

    public function attributes()
    {
        return ['_id', 'code', 'statement','created_at', 'updated_at'];
    }
}