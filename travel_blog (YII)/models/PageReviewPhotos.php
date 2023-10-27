<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\Like;

class PageReviewPhotos extends ActiveRecord
{
    public static function collectionName()
    {
        return 'page_review_photos';
    }

    public function attributes()
    {
        return ['_id','post_type', 'post_ip', 'post_text','post_status', 'post_created_date', 'post_user_id','image','album_title','is_album','album_place','album_img_date','post_privacy','is_deleted', 'page_id','shared_by','pagepost','isnewalbum','album_id','page_id','movedId'];
    }

    public function getUser()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'post_user_id']);
    }
}