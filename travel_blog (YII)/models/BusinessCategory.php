<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;


class BusinessCategory extends ActiveRecord
{
    public static function collectionName()
    {
        return 'business_category';
    }
	
    public function attributes()
    {
        return ['_id', 'name'];
    }
}