<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class SupportTeam extends ActiveRecord
{
    public static function collectionName()
    {
        return 'support_team';
    }

    public function attributes()
    {
        return ['_id', 'name', 'email', 'password', 'image', 'created_at'];
    }

    public function getinfo() {
        return SupportTeam::find()->asarray()->one();
    }
}