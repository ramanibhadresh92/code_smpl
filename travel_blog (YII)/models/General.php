<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\Like;

class General extends ActiveRecord
{
    public static function collectionName()
    {
        return 'general'; 
    }

    public function attributes()
    {
        return ['_id','label','count','images'];
    }

    public function storedphotostreamcount($count) {
        // Delete first all photostream records....
        General::deleteAll(['label' => 'photostreamcount']);

        $gn = new General();
        $gn->label = 'photostreamcount';
        $gn->count = $count;
        $gn->save();
        return true;
    }   
}