<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
class HidePost extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_hidepost';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'post_ids'];
    }
}