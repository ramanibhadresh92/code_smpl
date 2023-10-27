<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use yii\helpers\Url;

class Like extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_like';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'post_id', 'comment_id','like_type','status','created_date','updated_date','ip','liked_on','liked_modified'];
    }
  
    public function getUser()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'user_id']);
    }
    
    public function getPost()
    {
        return $this->hasOne(PostForm::className(), ['_id' => 'post_id']);
    }
    
    public function getComment()
    {
        return $this->hasOne(Comment::className(), ['_id' => 'comment_id']);
    }
    
	public function getAllLike()
    {
        return Like::find()->with('user')->orderBy(['created_date'=>SORT_DESC])->all();
    }
    
	public function getAllPostLike($post_id)
    {
        return Like::find()->with('user')->with('post')->where(['post_id' => $post_id,'status' => '1'])->orderBy(['created_date'=>SORT_DESC])->all();    
    }
    
     public function getUserPostLike($user_id)
    {
        return Like::find()->with('user')->with('post')->where(['user_id' => "$user_id",'status' => '1'])->orderBy(['created_date'=>SORT_DESC])->all();
        
    }
    
	public function getLikeCount($post_id)
    {
        return Like::find()->where(['post_id' => $post_id,'status' => '1'])->orderBy(['created_date'=>SORT_DESC])->count();
    }
    
	public function getLikeUserNames($post_id)
    {
        $session = Yii::$app->session;
        $likes_buddy_counts = 0;
        $uid = (string)$session->get('user_id');
        $likes_buddy_names = Like::find()->with('user')->where(['post_id' => "$post_id",'status' => '1'])->andwhere(['not in','user_id',array("$uid")])->orderBy(['created_date'=>SORT_DESC])->limit(3)->all();
        if(count($likes_buddy_names) == 3)
            $offset = 2;
        else 
            $offset = count($likes_buddy_names)-3;
        if($offset >=1)
        {
            $likes_buddy_counts = Like::find()->with('user')->where(['post_id' => "$post_id",'status' => '1'])->andwhere(['not in','user_id',array("$uid")])->orderBy(['created_date'=>SORT_DESC])->offset($offset)->all();
        } 
        $is_like_login = Like::find()->with('user')->where(['post_id' => "$post_id",'status' => '1','user_id'=> "$uid"])->orderBy(['updated_date'=>SORT_DESC])->one();
       
       if(!empty($is_like_login))
       {
           if(!empty($likes_buddy_names))
                $names = 'You, ';
           else
               $names = ucfirst($is_like_login['user']['fname']).' '.ucfirst($is_like_login['user']['lname']);
            $ctr = count($likes_buddy_counts)-1;
       }
       else
       {
           $names = '';
           $ctr = count($likes_buddy_counts);
       }
       foreach($likes_buddy_names AS $like_buddy_name)
       {
            $names .= ucfirst($like_buddy_name['user']['fname']).' '.ucfirst($like_buddy_name['user']['lname']).', ';
       }
       
       $data['count'] = $ctr; 
       $data['like_ctr'] = count($likes_buddy_names);
       $data['names'] =  trim($names, ", ");
       $data['login_user_details'] = $is_like_login;
       
       return $data;
    }
	
    public function getLikeUser($post_id)
    {
        $likes_buddy = Like::find()->with('user')->where(['post_id' => $post_id,'status' => '1'])->orderBy(['updated_date'=>SORT_DESC])->all();        
		return $likes_buddy;
    }
    
    public function getLikePostCount($post_id)
    {
        return Like::find()->where(['post_id' => $post_id, 'status' => '1'])->count();
    }

    public function getUserCommentLike($user_id, $comment_id)
    {
        $user_comments_like = Like::find()->where(['user_id' => "$user_id",'comment_id' => "$comment_id"])->orderBy(['updated_date'=>SORT_DESC])->one();
		
        return $user_comments_like['status'];
    }
    
    public function getPageLike($pageid)
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
		return Like::find()->where(['user_id' => "$user_id",'post_id' => "$pageid",'status' => "1"])->one();
    }
    
    public function getLikepageUserNames($post_id)
    {
        $session = Yii::$app->session;
        $likes_buddy_counts = 0;
        $uid = (string)$session->get('user_id');
        $likes_buddy_names = Like::find()->with('user')->where(['post_id' => "$post_id",'status' => '1'])->andwhere(['not in','user_id',array("$uid")])->orderBy(['created_date'=>SORT_DESC])->limit(3)->all();
        if(count($likes_buddy_names) == 3)
            $offset = 2;
        else 
            $offset = count($likes_buddy_names)-3;
        if($offset >=1)
        {
            $likes_buddy_counts = Like::find()->with('user')->where(['post_id' => "$post_id",'status' => '1'])->andwhere(['not in','user_id',array("$uid")])->orderBy(['created_date'=>SORT_DESC])->offset($offset)->all();
        } 
        $is_like_login = Like::find()->with('user')->where(['post_id' => "$post_id",'status' => '1','user_id'=> "$uid"])->orderBy(['updated_date'=>SORT_DESC])->one();
       
        if(!empty($is_like_login))
        {
            if(!empty($likes_buddy_names))
            {
                $id = Url::to(['userwall/index', 'id' => "$uid"]);
                $names = "<a href='$id'>You</a>, ";
            }
            else
            {
                $names = ucfirst($is_like_login['user']['fname']).' '.ucfirst($is_like_login['user']['lname']);
            }
            $ctr = count($likes_buddy_counts)-1;
        }
        else
        {
            $names = '';
            $ctr = count($likes_buddy_counts);
        }
        $like_count = Like::getLikeCount($post_id);
       $start = 0;
       foreach($likes_buddy_names AS $like_buddy_name)
       {
            if($start < 2)
            {
                $lid = $like_buddy_name['user']['_id'];
                $id = Url::to(['userwall/index', 'id' => "$lid"]);
                if((string)$uid == (string)$lid)
                {
                    $name = 'You';
                }
                else
                {
                    $name = ucfirst($like_buddy_name['user']['fname']). ' '.ucfirst($like_buddy_name['user']['lname']);
                }
                if($start != 1)
                {
                    $names .= "<a href='$id'>".$name."</a>, ";
                }
                else
                {
                    $names .= "<a href='$id'>".$name."</a> ";
                }
                if($like_count > 3 )
                {
                    $val = $like_count - 3; 
                    $counter = $val.' others'; 
                    $counter = ' and <a href="javascript:void(0)">'.$counter.'</a>';
                }
                else {$counter = '';}
            }
            $start++;
       }
       if($like_count > 3 )
        {
       $names = $names . $counter;
        }
        else
        {
        $names = $names;
        }
       $data['count'] = $ctr; 
       $data['like_ctr'] = count($likes_buddy_names);
       $data['names'] =  trim($names, ", ");
       $data['login_user_details'] = $is_like_login;
       
       return $data;
    }    
}