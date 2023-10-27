<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class Gifts extends ActiveRecord
{
    public static function collectionName()
    {
        return 'gifts';
    }

    public function attributes() {
        return ['_id', 'code', 'image', 'created_at', 'updated_at'];
    }

    public function addGift($post) {
        if(isset($post) && !empty($post)) {
            foreach ($post as $singlePost) {
                $code = $singlePost['code'];
                $image = $singlePost['image'];

                if($code != '' && $image != '') {
                    $Gifts = new Gifts();
                    $Gifts->code = $code;
                    $Gifts->image = $image;
                    $Gifts->created_at = time();
                    $Gifts->save();
                }
            }
        }
    }

    public function getallgifts() {
        return Gifts::find()->asarray()->all();
    }
}