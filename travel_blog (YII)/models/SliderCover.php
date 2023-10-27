<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class SliderCover extends ActiveRecord
{
    public static function collectionName()
    {
        return 'slider_cover';
    }

    public function attributes()
    {
        return ['_id', 'user_id','image_name','image_path','image_id','type','pageid'];
    }

}