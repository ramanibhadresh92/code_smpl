<?php
namespace frontend\models;
use Yii;
use yii\mongodb\ActiveRecord;

class DefaultImage extends ActiveRecord
{
    public static function collectionName()
    {
        return 'default_image';
    }

    public function attributes()
    {
		return ['_id', 'type', 'image','updated_date'];
	}
	
	public function getimage($type)
	{
		$images = DefaultImage::find()->select(['image'])->where(['type'=>"$type"])->one();
		return $images['image'];
	}
}
