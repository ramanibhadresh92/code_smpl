<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\Like;

class UserPhotos extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_photos';
    }

    public function attributes()
    {
        return ['_id','post_type', 'post_ip', 'post_text','post_status', 'post_created_date', 'post_user_id','image','album_title','is_album','album_place','album_img_date','post_privacy','is_deleted', 'page_id','shared_by','pagepost', 'customids'];
    }

    public function getUser()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'post_user_id']);
    }

    public function getSavedPosts()
    {
        return $this->hasMany(SavePost::className(), ['post_id' => '_id']);
    }

    public function getPostlike()
    {
        return $this->hasMany(Like::className(), ['_id' => 'user_id']);
    }

    public function getPostcomment()
    {
        return $this->hasMany(Comment::className(), ['_id' => 'user_id']);
    }

    public function getAllPost()
    {
        return PostForm::find()->with('user')->where(['is_deleted'=>"0"])->orderBy(['post_created_date'=>SORT_DESC])->all();
    }
	
	public function getAllPosts()
    {
        return PostForm::find()->where(['is_deleted'=>"0"])->all();
    }
	
	public function getAllPhotoPosts()
    {
        return PostForm::find()->where(['is_deleted'=>"0",'post_type'=>'image'])->orwhere(['post_type'=>'text and image'])->all();
    }

    public function getUserPost($userid, $start='')
    {
        if($start=='') {
            $start = 0;
        }

        $session = Yii::$app->session;
    	$uid = (string)$session->get('user_id');
    	if($uid == (string)$userid)
    	{
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'placetype'=>null])->orwhere(['like','post_tags',"$userid"])->orderBy(['post_created_date'=>SORT_DESC])->limit(7)->offset($start)->asarray()->all();
    	}
    	else
    	{
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'placetype'=>null])->andWhere(['not in', 'post_privacy', 'Private'])->orwhere(['like','post_tags',"$userid"])->orderBy(['post_created_date'=>SORT_DESC])->limit(7)->offset($start)->asarray()->all(); 
        }
    }

    public function getUserPostUserwall($userid, $start='')
    {
        if($start=='') {
            $start = 0;
        }

        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        if($uid == (string)$userid)
        {
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0'])->orWhere(['shared_by' => $userid, 'shared_from' => 'shareentity'])->orwhere(['like','post_tags',"$userid"])->orderBy(['post_created_date'=>SORT_DESC])->limit(7)->offset($start)->asarray()->all();
        }
        else
        {
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0'])->andWhere(['not in', 'post_privacy', 'Private'])->orWhere(['shared_by' => $userid, 'shared_from' => 'shareentity'])->orwhere(['like','post_tags',"$userid"])->orderBy(['post_created_date'=>SORT_DESC])->limit(7)->offset($start)->asarray()->all();  
        }
    }	

	public function getUserPhotos($userid)
    {
        $session = Yii::$app->session;
    	$uid = (string)$session->get('user_id');
    	if($uid == (string)$userid)
    	{
            return UserPhotos::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','is_album'=>'1','is_timeline'=>null])->orderBy(['post_created_date'=>SORT_DESC])->all();
    	}
    	else
    	{
            return UserPhotos::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','is_album'=>'1','is_timeline'=>null])->andWhere(['not in', 'post_privacy', 'Private'])->orderBy(['post_created_date'=>SORT_DESC])->all(); 
        }
    }

     public function getUserPostPhotos($userid)
    {
        $session = Yii::$app->session;
		$uid = (string)$session->get('user_id');
		if($uid == (string)$userid)
		{
			return UserPhotos::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','is_timeline' => null,'is_album' => '1'])->orderBy(['post_created_date'=>SORT_DESC])->all();
		}
		else
		{
			return UserPhotos::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','is_timeline' => null,'is_album' => '1'])->andWhere(['not in', 'post_privacy', 'Private'])->orderBy(['post_created_date'=>SORT_DESC])->all();
		}
    }

	public function getUserPostBudge()
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $connections =  Connect::getuserConnections($uid);

        $ids = array();
        foreach($connections as $connect)
        {
            $ids[]= $connect['from_id'];
        }
        $user_last_login_date = UserForm::find()->where(['_id' => "$uid"])->one();//last_login_time

        $mute_ids = MuteConnect::getMuteconnectionsIds($uid);
        $mute_connect_ids =  (explode(",",$mute_ids['mute_ids']));
        $connect_posts =  PostForm::find()->with('user')->where(['in','post_user_id',$ids])->andwhere(['not in','post_user_id',$mute_connect_ids])->orderBy(['post_created_date'=>SORT_DESC])->all();
        return $post_count = count($connect_posts);
    }
	
	public function getUserNotifications()
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $connections =  Connect::getuserConnections($uid);
        $user_last_login_date = UserForm::find()->where(['_id' => "$uid"])->one();
        $ids = array();
        foreach($connections as $connect)
        {
            $ids[]= $connect['from_id'];
        }
		
        $connect_posts =  PostForm::find()->with('user')->where(['in','post_user_id',$ids])->orderBy(['post_created_date'=>SORT_DESC])->all();
      
        return $connect_posts;
    }
	
	
	public function getAlbums($userid)
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        if($uid == (string)$userid)
        {
            return UserPhotos::find()->with('user')->where(['post_user_id'=>"$userid",'is_album'=>'1','is_deleted'=>'0','is_timeline' => null])->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        else
        {
            return UserPhotos::find()->with('user')->where(['post_user_id'=>"$userid",'is_album'=>'1','is_deleted'=>'0','is_timeline' => null])->andWhere(['not in', 'post_privacy', 'Private'])->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        
    }

    public function getAlbumsName($userid)
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        if($uid == (string)$userid)
        {
            return UserPhotos::find()->select(['album_title'])->where(['post_user_id'=>"$userid",'is_album'=>'1','is_deleted'=>'0','is_timeline' => null])->orderBy(['post_created_date'=>SORT_DESC])->asarray()->all();
        }
        else
        {
            return UserPhotos::find()->select(['album_title'])->where(['post_user_id'=>"$userid",'is_album'=>'1','is_deleted'=>'0','is_timeline' => null])->andWhere(['not in', 'post_privacy', 'Private'])->orderBy(['post_created_date'=>SORT_DESC])->asarray()->all();
        }
        
    }

	public function getProfilePics($userid)
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        if($uid == (string)$userid)
        {
            return UserPhotos::find()->with('user')->where(['post_user_id'=>"$userid",'post_type'=>'profilepic','is_deleted'=>'0','is_timeline' => null])->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        else
        {
            return UserPhotos::find()->with('user')->where(['post_user_id'=>"$userid",'post_type'=>'profilepic','is_deleted'=>'0','is_timeline' => null])->andWhere(['not in', 'post_privacy', 'Private'])->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
    }

     public function getCoverPics($userid)
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        if($uid == (string)$userid)
        {
            return UserPhotos::find()->with('user')->where(['post_user_id'=>"$userid",'post_type'=>'image','is_coverpic'=>'1','is_deleted'=>'0','is_timeline' => null])->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        else
        {
            return UserPhotos::find()->with('user')->where(['post_user_id'=>"$userid",'post_type'=>'image','is_coverpic'=>'1','is_deleted'=>'0','is_timeline' => null])->andWhere(['not in', 'post_privacy', 'Private'])->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
    }

	public function getPics($userid)
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        if($uid == (string)$userid)
        {
            $results = UserPhotos::find()->where(['post_type'=>'text and image'])
                ->orwhere(['post_type'=>'image'])
                ->andwhere(['post_user_id'=>"$userid",'is_deleted'=>'0','is_timeline' => null,'is_coverpic' => null,'is_album'=>'1'])
                ->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        else
        {
            $results = UserPhotos::find()->where(['post_type'=>'text and image'])
                ->orwhere(['post_type'=>'image'])
                ->andwhere(['post_user_id'=>"$userid",'is_deleted'=>'0','is_timeline' => null,'is_coverpic' => null,'is_album'=>'1'])
                ->andWhere(['not in', 'post_privacy', 'Private'])
                ->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        $total = 0;
        foreach($results as $result)
        {
            $is_connect = Connect::find()->where(['from_id' => "$userid",'to_id' => "$uid",'status' => '1'])->one();
            $my_post_view_status = $result['post_privacy'];
            if(isset($result['image']) && !empty($result['image']) && ($my_post_view_status == 'Public') || ($my_post_view_status == 'Connections' && ($is_connect || $userid == $uid)) || ($my_post_view_status == 'Private' && $userid == $uid))
            {
                $eximgs = explode(',',$result['image'],-1);
                if(count($eximgs) > 0){$tpics = count($eximgs);}else{$tpics = 0;}
                $total = $tpics + $total;
            }
        }
        return $total;
    }
	
    public function getAlbumCounts($userid)
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        if($uid == (string)$userid)
        {
            $results = UserPhotos::find()->where(['post_type'=>'text and image'])
                ->orwhere(['post_type'=>'image'])
                ->andwhere(['post_user_id'=>"$userid",'is_deleted'=>'0','is_timeline' => null,'is_coverpic' => null,'is_album'=>'1'])
                ->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        else
        {
            $results = UserPhotos::find()->where(['post_type'=>'text and image'])
                ->orwhere(['post_type'=>'image'])
                ->andwhere(['post_user_id'=>"$userid",'is_deleted'=>'0','is_timeline' => null,'is_coverpic' => null,'is_album'=>'1'])
                ->andWhere(['not in', 'post_privacy', 'Private'])
                ->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        $total = 0;
        foreach($results as $result)
        {
            $is_connect = Connect::find()->where(['from_id' => "$userid",'to_id' => "$uid",'status' => '1'])->one();
            $my_post_view_status = $result['post_privacy'];
            if(($my_post_view_status == 'Public') || ($my_post_view_status == 'Connections' && ($is_connect || $userid == $uid)) || ($my_post_view_status == 'Private' && $userid == $uid))
            {
                $total = $total + 1;
                $total = $total;
            }
        }
        return $total;
    }

    public function getPagepics($pageid)
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $results = UserPhotos::find()->where(['post_type'=>'text and image'])
                ->orwhere(['post_type'=>'image'])
                ->andwhere(['post_user_id'=>"$pageid",'is_deleted'=>'0'])
                ->orderBy(['post_created_date'=>SORT_DESC])->all();
        $total = '';
        foreach($results as $result)
        {
            if(isset($result['image']) && !empty($result['image']))
            {
                $eximgs = explode(',',$result['image'],-1);
                if(count($eximgs) > 0){$tpics = count($eximgs);}else{$tpics = 0;}
                $total = $tpics + $total;
            }
        }
        return $total;
    }

    public function getPagePostPhotos($userid)
    {
		$session = Yii::$app->session;
		$uid = (string)$session->get('user_id');
		return UserPhotos::find()->where(['post_user_id'=>"$userid",'is_deleted'=>'0'])->orderBy(['post_created_date'=>SORT_DESC])->all();
	}


	public function DeletePhotoCleanUp()
    {
		$album_id = isset($_POST['album_id']) ? $_POST['album_id'] : '';
        $data = array();
        if($album_id != '')
        {
            $delimage = new UserPhotos();
            $delimage = UserPhotos::find()->where(['_id' => $album_id])->one();
            if($delimage)
            {
                $delimage->is_album = '0';
                $delimage->is_deleted = '1';
                if($delimage->delete())
                {
                    $data['value'] = '1';
                    echo json_encode($data);
                }
            }
            else
            {
                $data['value'] = '0';
                echo json_encode($data);
            }
        }
        else
        {
            $data['value'] = '0';
            echo json_encode($data);
        }
	}	
	
}