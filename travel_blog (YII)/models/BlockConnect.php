<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class BlockConnect extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_blocks';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'block_ids'];
    }
	
	public function blocklist($uid)
    {
		$result = BlockConnect::find()->where(['user_id' => "$uid"])->one();
		if($result)
		{
			return $result;
		}
		else
		{
			return false;
		}
	}
}