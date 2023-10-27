<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class SuggestConnect extends ActiveRecord
{
    public static function collectionName()
    {
        return 'suggestedconnections';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'connect_id', 'suggest_to', 'created_at', 'status'];
    }
}