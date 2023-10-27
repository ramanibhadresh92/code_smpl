<?php
namespace frontend\models;
use yii\base\Model;
use Yii;
use yii\mongodb\ActiveRecord;
use yii\helpers\ArrayHelper;
use frontend\models\LoginForm;
use frontend\models\Comment;

class Comment extends ActiveRecord
{  
    public static function collectionName()
    {
        return 'post_comment';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'post_id', 'comment','comment_type','image','status','created_date','updated_date','ip','parent_comment_id'];
    }
   
    public function getUser()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'user_id']);
    }
   
    public function getPost()
    {
        return $this->hasOne(PostForm::className(), ['_id' => 'post_id']);
    } 
    
    public function getLike()
    {
        return $this->hasMany(Like::className(), ['comment_id' => '_id']);
    }
    
	public function getAllComment()
    {
        return Comment::find()->with('user')->orderBy(['created_date'=>SORT_ASC])->all();       
    }

	public function getAllPostLike($post_id)
    {
        $comments = Comment::find()->with('user')->with('post')->where(['post_id' => "$post_id",'status' => '1','parent_comment_id'=>'0'])->orderBy(['created_date'=>SORT_ASC])->all();
        return $comments;
    }

    public function getAllPostLikeCount($post_id)
    {
        return Comment::find()->with('user')->with('post')->where(['post_id' => (string)$post_id,'status' => '1','parent_comment_id'=>'0'])->orderBy(['created_date'=>SORT_ASC])->count();
    }

    public function totalcomments($post_id)
    {
        return Comment::find()->where(['post_id' => $post_id,'status' => '1','parent_comment_id'=>'0'])->count();
    }

    public function isICommented($post_id, $user_id)
    {
        return Comment::find()->where(['post_id' => (string)$post_id, 'user_id' => $user_id, 'status' => '1','parent_comment_id'=>'0'])->one();
    }
  
	public function getFirstThreePostComments($post_id)
    {
       return $init_comments = Comment::find()->with('user')->with('post')->where(['post_id' => "$post_id",'status' => '1','parent_comment_id' => '0'])->orderBy(['created_date' => SORT_ASC])->limit(3)->all();
    }

    public function getFirstPostComments($post_id)
    {
       return $init_comments = Comment::find()->with('user')->with('post')->where(['post_id' => "$post_id",'status' => '1','parent_comment_id' => '0'])->orderBy(['created_date' => SORT_ASC])->limit(1)->all();
    }
	
	public function getUserPostComment($user_id)
    {
        return Comment::find()->with('user')->with('post')->where(['user_id' => $user_id,'status' => '1','parent_comment_id'=>'0'])->orderBy(['created_date'=>SORT_ASC])->all();
    }
    
	public function getCommentCount($post_id)
    {
        $comments = Comment::find()->where(['post_id' => $post_id,'status' => '1'])->orderBy(['created_date'=>SORT_ASC])->all();       
        return count($comments);
    }
     
    public function getCommentReply($comment_id)
    {
        return Comment::find()->with('user')->with('post')->where(['parent_comment_id' => "$comment_id",'status' => '1'])->orderBy(['created_date'=>SORT_ASC])->all();
    }    

    public function getSliderComments($id) {
        return Comment::find()->with('user')->with('post')->where(['post_id' => $id,'status' => '1','parent_comment_id'=>'0'])->orderBy(['created_date'=>SORT_DESC])->all();
    }

    public function getSliderCommentsUserPhotos($id) {
        $comments = Comment::find()->where(['post_id' => $id,'status' => '1','parent_comment_id'=>'0'])->orderBy(['created_date'=>SORT_DESC])->asarray()->all();
        $commentsUserIdsBulk = ArrayHelper::map($comments, function($data) { return $data['user_id'];}, 1);

        $user_ids = array_keys($commentsUserIdsBulk);

        $usersList = ArrayHelper::map(LoginForm::find()->select(['fullname'])->where(['in', '_id', $user_ids])->asarray()->all(), function($data) { return (string)$data['_id'];}, 'fullname'); 

        $commentsResults = array();

        foreach ($comments as $singlecomment) {
            $tempuid = $singlecomment['user_id'];

            if(array_key_exists($tempuid, $usersList)) {
                $temp = array();
                $temp['user_id'] = $singlecomment['user_id'];
                $temp['comment'] = $singlecomment['comment'];
                $temp['created_date'] = $singlecomment['created_date'];
                $temp['user']['fullname'] = $usersList[$tempuid];
                $commentsResults[] = $temp;
            }
        }

        return $commentsResults;
    }
}