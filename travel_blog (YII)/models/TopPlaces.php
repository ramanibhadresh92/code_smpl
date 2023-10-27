<?php 
namespace frontend\models;
use Yii;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\db\ActiveQuery;
use yii\db\Expression;

class TopPlaces extends ActiveRecord 
{
	public static function collectionName()
    {
        return 'discover_top_places';
    }
	
    public function attributes()
    {
        return ['_id', 'name', 'country', 'category', 'rank', 'profile', 'created_at', 'modified_at', 'status'];
    }
	
	public function getTopPlacesAll()
    {
        return TopPlaces::find()->where(['status' => 1])->orderBy(['rank'=>SORT_ASC])->asarray()->all();
    } 

    public function getTopPlaces($start, $limit)
    {
        return TopPlaces::find()->where(['status' => 1])->orderBy(['rank'=>SORT_ASC])->limit($limit)->offset($start)->asarray()->all();
    }
}