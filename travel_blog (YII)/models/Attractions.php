<?php 
namespace frontend\models;

use Yii;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\db\ActiveQuery;
use yii\db\Expression;

class Attractions extends ActiveRecord 
{
	public static function collectionName()
    {
        return 'attractions';
    }
	
    public function attributes()
    {
        return ['_id', 'name', 'city', 'peak_season', 'description', 'profile', 'visitors', 'rank', 'created_at', 'modified_at', 'status'];
    }
	
	public function getAttractions($start, $limit)
    {
        return Attractions::find()->where(['status' => 1])->orderBy(['rank'=>SORT_ASC])->limit($limit)->offset($start)->asarray()->all();
    }

    public function getAttractionsAll()
    {
        return Attractions::find()->where(['status' => 1])->orderBy(['rank'=>SORT_ASC])->asarray()->all();
    }
}