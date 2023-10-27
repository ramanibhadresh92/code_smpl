<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class BlogComments extends ActiveRecord
{
    public static function collectionName()
    {
        return 'blog_comments';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'blog_id', 'comment', 'created_at', 'modified_at'];
    }
    
    public function docomment($user_id, $comment) {
        $url = $_SERVER['HTTP_REFERER'];
        $urls = explode('&',$url);
        $url = explode('=',$urls[1]); 
        $blog_id = $url[1];
        if($blog_id != '') {
            $Blog = new BlogComments();
            $Blog->user_id = $user_id;
            $Blog->blog_id = $blog_id;
            $Blog->comment = $comment;
            $Blog->created_at = time();
            $Blog->insert();

            $cmtusrthmb = Yii::$app->GenCls->getuserdata($user_id,'thumbnail');
            $cmtusrnm = Yii::$app->GenCls->getuserdata($user_id,'fullname');

            $count = BlogComments::find()->where(['blog_id' => $blog_id])->count();

            $commenthtml = '<li> <div class="ranker-box"> <div class="img-holder"> <img src="profile/'.$cmtusrthmb.'"> </div> <div class="desc-holder"> <a href="javascript:void(0)" class="userlink">'.$cmtusrnm.'</a> <span class="comment-date">May 26, 2018</span> <span class="info">'.$comment.'</span> </div> </div> </li>';

            $result = array('success' => true, 'comment' => $commenthtml, 'count' => $count);
            return json_encode($result, true);
        }
        $result = array('success' => false);
        return json_encode($result, true);
    }

    public function editcomment($id, $user_id, $comment) {
        $Blog = BlogComments::find()->where(['_id' => $id, 'user_id' => $user_id])->one();
        if($Blog) {
            $Blog->comment = $comment;
            $Blog->modified_at = time();
            $Blog->update();
            $result = array('success' => true);
            return json_encode($result, true);
        }
        $result = array('success' => false);
        return json_encode($result, true);
    }
}