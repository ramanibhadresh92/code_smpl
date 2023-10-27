<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class HideComment extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_hidecomment';
    }
	
    public function attributes()
    {
        return ['_id', 'user_id', 'comment_ids'];
    }
}