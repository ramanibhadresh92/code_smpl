<?php 
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use yii\helpers\ArrayHelper;

class Grestaurants extends ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    public static function collectionName()
    {
        return 'grestaurants';
    }

     public function attributes()
    {
        return ['_id', 'results', 'status'];
    }
}
