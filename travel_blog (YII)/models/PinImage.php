<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class PinImage extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_gallery';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'imagename', 'post_id', 'is_saved', 'pinned_at'];
    }

    public function getPinnedImages($user_id, $start)
    {
        if($start == 0 || $start == '') {
            $start = 0;
        }

        return PinImage::find()->where(['user_id'=>"$user_id",'is_saved'=>'1'])->orderBy(['pinned_at' => SORT_DESC])->limit(10)->offset($start)->asarray()->all(); 
    }
    
}