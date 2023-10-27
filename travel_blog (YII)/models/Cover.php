<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class Cover extends ActiveRecord
{
    public static function collectionName()
    {
        return 'Cover';
    }

    public function attributes()
    {
        return ['_id', 'cover_image','created_at'];
    }
}