<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class SavePost extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_savedpost';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'post_id', 'post_type', 'type', 'is_saved', 'saved_date'];
    }
    
     public function getUserDetail()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'user_id']);
    }
    
     public function getPostData()
    {
        return $this->hasOne(PostForm::className(), ['_id' => 'post_id']);
    }
    
}