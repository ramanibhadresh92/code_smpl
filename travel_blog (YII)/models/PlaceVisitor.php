<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class PlaceVisitor extends ActiveRecord
{ 
    public static function collectionName()
    {
        return 'place_visitor';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'place_id', 'visited_date', 'place', 'ip', 'day', 'month', 'year'];
    }
   
    public function getUser()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'user_id']);
    }
    
     public function getPlacesCount()
    {
        return PlaceVisitor::find()->count();
    }
    
     public function getPlacesDetails()
    {
        return PlaceVisitor::find()->orderBy(['visited_date'=>SORT_DESC])->all();
    }
}