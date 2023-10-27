<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\Like;
use frontend\models\Preferences;
use frontend\models\SavePost;
use frontend\models\ReportPost;
use frontend\models\TurnoffNotification;
use frontend\models\PinImage;
use frontend\models\Order;
use frontend\models\PlaceDiscussion;
use frontend\models\PlaceReview;

class PostForm extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_post';
    }

    public function attributes()
    {
        return ['_id','post_type', 'post_ip', 'post_title', 'post_text', 'shared_by','post_status', 'post_created_date', 'post_user_id','image','link_title','link_description','share_by','shared_from','post_privacy','is_timeline','is_deleted','currentlocation','parent_post_id','is_coverpic','share_setting','comment_setting', 'post_tags','custom_share', 'custom_notshare', 'anyone_tag', 'is_profilepic', 'pagepost', 'is_page_review', 'rating', 'page_id', 'collection_id', 'trav_cat', 'trav_link', 'trav_price', 'trav_item', 'is_ad', 'ad_type', 'post_flager_id' , 'is_trip', 'country', 'continent', 'adobj', 'adname', 'adid', 'adcatch', 'adheadeline', 'adtitle', 'adtext', 'adlogo', 'adimage', 'adsubcatch', 'adurl', 'adbtn', 'adlocations', 'adminage', 'admaxage', 'adlanguages', 'admale', 'adfemale', 'adpro', 'adint', 'adbudget', 'adruntype', 'adstartdate', 'adenddate', 'rate_impression', 'rate_action', 'rate_click', 'placetype', 'placetitlepost', 'placereview','page_owner','adtotbudget','ad_duration','is_album','share_trip','direct_publish','customids','pinned','flagger', 'flagger_date', 'flagger_by'];
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
            return PostForm::find()->with('user')->where(['like','post_user_id',"$userid",'is_deleted'=>'0','is_ad'=>null])->orWhere(['shared_by' => $userid, 'shared_from' => 'shareentity'])->orwhere(['like','post_tags',"$userid"])->orderBy(['post_created_date'=>SORT_DESC])->limit(7)->offset($start)->asarray()->all();
        }
		else
		{
			$wall = PostForm::find()->with('user')->where(['like','post_user_id',"$userid",'is_deleted'=>'0','is_ad'=>null])->andWhere(['not in', 'post_privacy', 'Private'])->orWhere(['shared_by' => $userid, 'shared_from' => 'shareentity'])->orwhere(['like','post_tags',"$userid"])->orderBy(['post_created_date'=>SORT_DESC])->limit(7)->offset($start)->asarray()->all(); 

			$wall_posts = array();
			$result_security = SecuritySetting::find()->where(['user_id' => "$userid"])->one();
			$is_connect = Connect::find()->where(['from_id' => "$userid",'to_id' => "$uid",'status' => '1'])->one();

			$add_post_on_your_wall_view = (isset($result_security['add_post_on_your_wall_view']) && !empty($result_security['add_post_on_your_wall_view'])) ? $result_security['add_post_on_your_wall_view'] : 'Public';

			foreach($wall as $wall2)
			{
				if(isset($wall2['shared_by']) && !empty($wall2['shared_by'])) {
					if($wall2['shared_by'] != (string)$userid && $add_post_on_your_wall_view == 'Private')
					{
						continue;
					}
					else if($wall2['shared_by'] != (string)$userid && $add_post_on_your_wall_view == 'Connections')
					{
						if($is_connect)
						{
							$wall_posts[] = $wall2;
						}
						else
						{
							continue;
						}
					}
					else
					{
						$wall_posts[] = $wall2;
					}
				}
				else
				{
					$wall_posts[] = $wall2;
				}
			}
			return $wall_posts;
		
		}
	}
	
	public function getUserPostAPI($userid, $start='',$limit)
    {
        if($start=='') {
            $start = 0;
        }

        $session = Yii::$app->session;
    	$uid = (string)$session->get('user_id');
    	if($uid == (string)$userid)
    	{
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'placetype'=>null])->orwhere(['like','post_tags',"$userid"])->orderBy(['post_created_date'=>SORT_DESC])->limit($limit)->offset($start)->all();
    	}
    	else
    	{
             return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'placetype'=>null])->andWhere(['not in', 'post_privacy', 'Private'])->orwhere(['like','post_tags',"$userid"])->orderBy(['post_created_date'=>SORT_DESC])->limit($limit)->offset($start)->all(); 
        }
        
    }
	
	public function getUserPostCount($userid)
    {
        $session = Yii::$app->session;
    	$uid = (string)$session->get('user_id');
    	if($uid == (string)$userid)
    	{
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'placetype'=>null])->orwhere(['like','post_tags',"$userid"])->orderBy(['post_created_date'=>SORT_DESC])->all();
    	}
    	else
    	{
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'placetype'=>null])->andWhere(['not in', 'post_privacy', 'Private'])->orwhere(['like','post_tags',"$userid"])->orderBy(['post_created_date'=>SORT_DESC])->all(); 
        }
    }
    
	public function getTravPostCount()
    {
        return PostForm::find()->with('user')->where(['is_deleted'=>'0','rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'placetype'=>null])->andWhere(['not in', 'post_privacy', 'Private'])->count();
    }
	
	public function getUserPhotos($userid)
    {
        $session = Yii::$app->session;
    	$uid = (string)$session->get('user_id');
    	if($uid == (string)$userid)
    	{
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','is_album'=>'1','is_timeline'=>null])->orderBy(['post_created_date'=>SORT_DESC])->all();
    	}
    	else
    	{
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','is_album'=>'1','is_timeline'=>null])->andWhere(['not in', 'post_privacy', 'Private'])->orderBy(['post_created_date'=>SORT_DESC])->all(); 
        }
    }

	public function getUserPostPhotos($userid)
    {
        $session = Yii::$app->session;
		$uid = (string)$session->get('user_id');
		if($uid == (string)$userid)
		{
			return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','is_timeline' => null,'is_album' => '1'])->orderBy(['post_created_date'=>SORT_DESC])->all();
		}
		else
		{
			return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','is_timeline' => null,'is_album' => '1'])->andWhere(['not in', 'post_privacy', 'Private'])->orderBy(['post_created_date'=>SORT_DESC])->all();
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
        $user_last_login_date = UserForm::find()->where(['_id' => "$uid"])->one();//last_login_time
        $ids = array();
        foreach($connections as $connect)
        {
            $ids[]= $connect['from_id'];
        }
        $connect_posts =  PostForm::find()->with('user')->where(['in','post_user_id',$ids])->orderBy(['post_created_date'=>SORT_DESC])->all();
      
        return $connect_posts;
    }

    public function getUserConnectionsPosts($flag= '', $start='')
    {
        if($start=='')
            $start = 0;

        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $connections =  Connect::getuserConnections($uid);
        
        $ids = array();
        foreach($connections as $connect)
        {
            $ids[]= $connect['from_id'];
        }
     
        $ids[] = array_push($ids,(string)$uid);
        $unfollow_ids = UnfollowConnect::getUnfollowconnectionsIds($uid);
        $unfollow_connect_ids =  (explode(",",$unfollow_ids['unfollow_ids']));
       
        $hidepost = HidePost::find()->where(['user_id' => (string)$uid])->one();
        $hide_ids = explode(',',$hidepost['post_ids']); 
        if($flag == 'updates') 
        {
            $user_connect_posts =  PostForm::find()->with('user')->where(['is_deleted'=>"0",'is_album'=>null,'is_profilepic'=>null,'is_coverpic'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'placetype'=>null])->andwhere(['not in','post_user_id',$unfollow_connect_ids])->andwhere(['not in','_id',$hide_ids])->andwhere(['not','post_privacy','Private'])->orderBy(['post_created_date'=>SORT_DESC])->limit(7)->offset($start)->all();
        }
        else if(isset($flag) && !empty($flag) && is_string($flag))
        {
            $user_connect_posts =  PostForm::find()->where(['is_deleted'=>"0",'post_user_id'=>"$flag",'rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'placetype'=>null])->orderBy(['post_created_date'=>SORT_DESC])->limit(7)->offset($start)->all();
        }
        else 
        {
			$user_connect_posts = array();
			$user_connect_posts1 =  PostForm::find()->with('user')->where(['is_deleted'=>"0",'is_album'=>null,'is_profilepic'=>null,'is_coverpic'=>null, 'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'placetype'=>null])->andwhere(['not','shared_from','shareentity'])->andwhere(['not in','post_user_id',$unfollow_connect_ids])->andwhere(['not in','_id',$hide_ids])->orderBy(['post_created_date'=>SORT_DESC])->limit(7)->offset($start)->asarray()->all();
			foreach($user_connect_posts1 as $user_connect_posts2)
			{
				if(isset($user_connect_posts2['page_id']) && !empty($user_connect_posts2['page_id']))
				{
					$pageid = $user_connect_posts2['page_id'];
					$result = Like::find()->where(['post_id' => "$pageid",'user_id' => "$uid",'status' => '1'])->one();
					if(!$result)
					{
						continue;
					}
					else if(isset($user_connect_posts2['is_page_review']) && !empty($user_connect_posts2['is_page_review']))
					{
						continue;
					}
					if($result)
					{
						 $like_time  = $result['updated_date'];
						 $post_time =  $user_connect_posts2['post_created_date'];
						if($post_time < $like_time)
						{
							continue;
						}
					}
				}
				$user_connect_posts[] = $user_connect_posts2;
			}
        }
        return $user_connect_posts;
    }
	
	
	public function getUserConnectionsPostsAPI($user_id,$start_from,$limit,$flag= '')
    {
        $uid = $user_id;
        $connections =  Connect::getuserConnections($uid);
        
        $ids = array();
        foreach($connections as $connect)
        {
            $ids[]= $connect['from_id'];
        }
     
        $ids[] = array_push($ids,(string)$uid);
        $unfollow_ids = UnfollowConnect::getUnfollowconnectionsIds($uid);
        $unfollow_connect_ids =  (explode(",",$unfollow_ids['unfollow_ids']));
       
        $hidepost = HidePost::find()->where(['user_id' => (string)$uid])->one();
        $hide_ids = explode(',',$hidepost['post_ids']); 

        if($flag == 'updates')
        {
            $user_connect_posts =  PostForm::find()->with('user')->where(['is_deleted'=>"0",'is_album'=>null,'is_profilepic'=>null,'is_coverpic'=>null])->andwhere(['not in','post_user_id',$unfollow_connect_ids])->andwhere(['not in','_id',$hide_ids])->andwhere(['not','post_privacy','Private'])->orderBy(['post_created_date'=>SORT_DESC])->limit($limit)->offset($start_from)->all();
        }
        else if(isset($flag) && !empty($flag) && is_string($flag))
        {
            $user_connect_posts =  PostForm::find()->where(['is_deleted'=>"0",'post_user_id'=>"$flag"])->orderBy(['post_created_date'=>SORT_DESC])->limit($limit)->offset($start_from)->all();
        }
        else 
        {
            $user_connect_posts =  PostForm::find()->with('user')->where(['is_deleted'=>"0",'is_album'=>null,'is_profilepic'=>null,'is_coverpic'=>null])->andwhere(['not in','post_user_id',$unfollow_connect_ids])->andwhere(['not in','_id',$hide_ids])->orderBy(['post_created_date'=>SORT_DESC])->limit($limit)->offset($start_from)->all();
        }
        return $user_connect_posts;
    }
	
    public function getUserConnectionsPostsAPICount($user_id,$flag= '', $start='')
    {
        if($start=='')
            $start = 0;
        $uid = $user_id;
        $connections =  Connect::getuserConnections($uid);
        
        $ids = array();
        foreach($connections as $connect)
        {
            $ids[]= $connect['from_id'];
        }
     
        $ids[] = array_push($ids,(string)$uid);
        $unfollow_ids = UnfollowConnect::getUnfollowconnectionsIds($uid);
        $unfollow_connect_ids =  (explode(",",$unfollow_ids['unfollow_ids']));
       
        $hidepost = HidePost::find()->where(['user_id' => (string)$uid])->one();
        $hide_ids = explode(',',$hidepost['post_ids']); 

        if($flag == 'updates')
        {
            $user_connect_posts =  PostForm::find()->with('user')->where(['is_deleted'=>"0",'is_album'=>null,'is_profilepic'=>null,'is_coverpic'=>null])->andwhere(['not in','post_user_id',$unfollow_connect_ids])->andwhere(['not in','_id',$hide_ids])->andwhere(['not','post_privacy','Private'])->orderBy(['post_created_date'=>SORT_DESC])->count();
        }
        else if(isset($flag) && !empty($flag) && is_string($flag))
        {
            $user_connect_posts =  PostForm::find()->where(['is_deleted'=>"0",'post_user_id'=>"$flag"])->orderBy(['post_created_date'=>SORT_DESC])->count();
        }
        else 
        {
            $user_connect_posts =  PostForm::find()->with('user')->where(['is_deleted'=>"0",'is_album'=>null,'is_profilepic'=>null,'is_coverpic'=>null])->andwhere(['not in','post_user_id',$unfollow_connect_ids])->andwhere(['not in','_id',$hide_ids])->orderBy(['post_created_date'=>SORT_DESC])->count();
        }
        return $user_connect_posts;
    }
	
    public function getCollectionPosts($col_id, $start='')
    { 
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if($start=='') {
            $start = 0;
        }

        // hide post..
        $hide_ids = array();
        $hidepost = HidePost::find()->where(['user_id' => (string)$user_id])->one();
        if(!empty($hidepost)) {
            $hide_ids = explode(',',$hidepost['post_ids']); 
            $hide_ids = array_unique($hide_ids); 
        }

        $posts = PostForm::find()->with('user')->where(['collection_id'=>"$col_id",'is_deleted'=>"0",'is_album'=>null,'is_profilepic'=>null,'is_coverpic'=>null,'is_ad'=>null])->andwhere(['not in', '_id', $hide_ids])->orderBy(['post_created_date'=>SORT_DESC])->asarray()->limit(7)->offset($start)->all();
        if($user_id == '') {
            $data = Preferences::find()->where(['user_id' => $user_id, 'object_id' => $col_id])->asarray()->one();
            if(!empty($data)) {
               $sort = $data['sort'];
               if($sort == 'Popular') {

                    $newpostsarray = array();
                    foreach ($posts as $key => $post) {
                        $postId = (string)$post['_id'];
                        $count = Like::getLikePostCount($postId);
                        if($count == '') {
                            $count = 0;
                        }
                        $post['likecount'] = $count;
                        $newpostsarray[] = $post;
                    }

                    usort($newpostsarray, function($a, $b) {
                        if ($a['likecount'] == $b['likecount']) {
                            return 0;
                        }

                        return $b['likecount'] < $a['likecount'] ? -1 : 1;
                    });

                    return $newpostsarray;
               }
            }
        }
        
        return $posts;
    }

    public function getLikesCount() {

    }
	
	// Trav store your Post Display
	public function getTravstoreyourPosts($uid ,$cat_name ,$searchval, $start='')
    {
        if($start=='')
            $start = 0;
		if($cat_name == "undefined")
		{
			if($searchval=="undefined")
			{
				$posts =  PostForm::find()->with('user')->where(['trav_item'=>"1",'is_deleted'=>"0",'post_user_id'=>"$uid"])->orderBy(['post_created_date'=>SORT_DESC])->all();
			}else{
				$posts =  PostForm::find()->with('user')->where(['trav_item'=>"1",'is_deleted'=>"0",'post_user_id'=>"$uid"])->andwhere(['like','post_title', $searchval])->orderBy(['post_created_date'=>SORT_DESC])->all();
			}			
		}else{
			$posts =  PostForm::find()->with('user')->where(['trav_item'=>"1",'is_deleted'=>"0",'post_user_id'=>"$uid",'trav_cat'=>"$cat_name"])->orderBy(['post_created_date'=>SORT_DESC])->all();
		}	
		return $posts;
    }
	
	// Trav store featured Post Display
	public function getTravstorefeaturedPosts($cat_name ,$searchval, $start='')
    {
		if($start=='')
            $start = 0;
		
        if($cat_name == "undefined")
		{
			if($searchval=="undefined")
			{
				$posts =  PostForm::find()->with('user')->where(['trav_item'=>"1",'is_deleted'=>"0"])->orderBy(['post_created_date'=>SORT_DESC])->all();
			}else{
				$posts =  PostForm::find()->with('user')->where(['trav_item'=>"1",'is_deleted'=>"0"])->andwhere(['like','post_title', $searchval])->orderBy(['post_created_date'=>SORT_DESC])->all();
			}			
		}else{
			$posts =  PostForm::find()->with('user')->where(['trav_item'=>"1",'is_deleted'=>"0",'trav_cat'=>"$cat_name"])->orderBy(['post_created_date'=>SORT_DESC])->all();
		}	
		
		return $posts;
    }
	
	public function getTravstorestaffPosts($cat_name ,$searchval, $start='')
    {
		if($start=='')
            $start = 0;
		
        if($cat_name == "undefined")
		{
			if($searchval=="undefined")
			{
				$posts =  PostForm::find()->with('user')->where(['trav_item'=>"1", 'is_ad'=>"1", 'is_deleted'=>"0"])->orderBy(['post_created_date'=>SORT_DESC])->all();
			}else{
				$posts =  PostForm::find()->with('user')->where(['trav_item'=>"1", 'is_ad'=>"1",'is_deleted'=>"0"])->andwhere(['like','post_title', $searchval])->orderBy(['post_created_date'=>SORT_DESC])->all();
			}			
		}else{
			$posts =  PostForm::find()->with('user')->where(['trav_item'=>"1", 'is_ad'=>"1",'is_deleted'=>"0",'trav_cat'=>"$cat_name"])->orderBy(['post_created_date'=>SORT_DESC])->all();
		}	
		
		return $posts;
    }
	
	// Trip Expreince your Post Display
	public function getTripexpyourPosts($uid,$country,$searchval,$start='')
    {
        if($start=='')
            $start = 0;
		if($country == "undefined")
		{
			if($searchval=="undefined")
			{
				$posts =  PostForm::find()->with('user')->where(['is_trip'=>"1",'is_deleted'=>"0",'post_user_id'=>"$uid"])->orwhere(['share_trip'=>"1",'is_deleted'=>"0",'post_user_id'=>"$uid"])->orderBy(['post_created_date'=>SORT_DESC])->all();
			} else {
				$posts =  PostForm::find()->with('user')->where(['is_trip'=>"1",'is_deleted'=>"0",'post_user_id'=>"$uid"])->orwhere(['share_trip'=>"1",'is_deleted'=>"0",'post_user_id'=>"$uid"])->andwhere(['like','currentlocation', $searchval])->orderBy(['post_created_date'=>SORT_DESC])->all();
			}	
		} else {
			$posts =  PostForm::find()->with('user')->where(['is_trip'=>"1",'is_deleted'=>"0",'post_user_id'=>"$uid",'country'=>"$country"])->orwhere(['share_trip'=>"1",'is_deleted'=>"0",'post_user_id'=>"$uid",'country'=>"$country"])->orderBy(['post_created_date'=>SORT_DESC])->all();
		}
		
		return $posts;
    }
	
	// Trip Expreince All Post Display
	public function getTripexpallPosts()
    {
		$posts =  PostForm::find()->with('user')->where(['is_trip'=>"1",'is_deleted'=>"0"])->orwhere(['share_trip'=>"1",'is_deleted'=>"0"])->orderBy(['post_created_date'=>SORT_DESC])->all();
        
        return $posts;
    }
	
	public function getAlbums($userid)
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        if($uid == (string)$userid)
        {
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_album'=>'1','is_deleted'=>'0','is_timeline' => null])->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        else
        {
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_album'=>'1','is_deleted'=>'0','is_timeline' => null])->andWhere(['not in', 'post_privacy', 'Private'])->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        
    }

    public function getAlbumsAPI($userid,$uid)
    {
        if($uid == (string)$userid)
        {
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_album'=>'1','is_deleted'=>'0','is_timeline' => null])->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        else
        {
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'is_album'=>'1','is_deleted'=>'0','is_timeline' => null])->andWhere(['not in', 'post_privacy', 'Private'])->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        
    }
	
	public function getProfilePics($userid)
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        if($uid == (string)$userid)
        {
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'post_type'=>'profilepic','is_deleted'=>'0','is_timeline' => null])->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        else
        {
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'post_type'=>'profilepic','is_deleted'=>'0','is_timeline' => null])->andWhere(['not in', 'post_privacy', 'Private'])->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
    }
	
     public function getCoverPics($userid)
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        if($uid == (string)$userid)
        {
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'post_type'=>'image','is_coverpic'=>'1','is_deleted'=>'0','is_timeline' => null])->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        else
        {
            return PostForm::find()->with('user')->where(['post_user_id'=>"$userid",'post_type'=>'image','is_coverpic'=>'1','is_deleted'=>'0','is_timeline' => null])->andWhere(['not in', 'post_privacy', 'Private'])->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
    }

	public function getPics($userid)
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        if($uid == (string)$userid)
        {
            $results = PostForm::find()->where(['post_type'=>'text and image'])
                ->orwhere(['post_type'=>'image'])
                ->andwhere(['post_user_id'=>"$userid",'is_deleted'=>'0','is_timeline' => null,'is_coverpic' => null,'is_album'=>'1'])
                ->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        else
        {
            $results = PostForm::find()->where(['post_type'=>'text and image'])
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
	
	public function getTravPhotosCount()
    {
        $results = PostForm::find()->where(['post_type'=>'text and image'])
                ->orwhere(['post_type'=>'image'])
                ->andwhere(['is_deleted'=>'0','is_timeline' => null])
                ->orderBy(['post_created_date'=>SORT_DESC])->all();
        $total = 0;
        foreach($results as $result)
        {
            $eximgs = explode(',',$result['image'],-1);
			if(count($eximgs) > 0){$tpics = count($eximgs);}else{$tpics = 0;}
			$total = $tpics + $total;
        }
        return $total;
    }

    public function getAlbumCounts($userid)
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        if($uid == (string)$userid)
        {
            $results = PostForm::find()->where(['post_type'=>'text and image'])
                ->orwhere(['post_type'=>'image'])
                ->andwhere(['post_user_id'=>"$userid",'is_deleted'=>'0','is_timeline' => null,'is_coverpic' => null,'is_album'=>'1'])
                ->orderBy(['post_created_date'=>SORT_DESC])->all();
        }
        else
        {
            $results = PostForm::find()->where(['post_type'=>'text and image'])
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
        $results = PostForm::find()->where(['post_type'=>'text and image'])
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
		return PostForm::find()->where(['post_user_id'=>"$userid",'is_deleted'=>'0'])->orderBy(['post_created_date'=>SORT_DESC])->all();
    }

    public function getAllCollectionPost($collection_id)
    {
        return PostForm::find()->with('user')->where(['is_deleted'=>"0"])->andwhere(['post_collection_id'=>$collection_id])->orderBy(['post_created_date'=>SORT_DESC])->all();
    }

    public function getContinents($user_id='')
	{
		if(empty($user_id))	{
			$data = PostForm::find()->select(['continent'])->where(['is_trip'=>'1','is_deleted'=>"0"])->distinct('continent');
		} else {
			$data = PostForm::find()->select(['continent'])->where(['is_trip'=>'1','is_deleted'=>"0", 'post_user_id'=> "$user_id"])->distinct('continent');
		}
		return $data;
	}
	
    public function getPlaceReviews($place,$type,$count)
    {
		if($count == 'all')
		{
			return PostForm::find()->with('user')->where(['is_deleted'=>"0",'currentlocation'=>"$place",'placetype'=>"$type"])->orderBy(['post_created_date'=>SORT_DESC])->all();
		} else {
			return PostForm::find()->with('user')->where(['is_deleted'=>"0",'currentlocation'=>"$place",'placetype'=>"$type"])->orderBy(['post_created_date'=>SORT_DESC])->limit($count)->offset(0)->all();
		}
    }
	
    public function getPlaceReviewsCount($place,$type)
    {
        return PostForm::find()->with('user')->where(['is_deleted'=>"0",'currentlocation'=>"$place",'placetype'=>"$type"])->orderBy(['post_created_date'=>SORT_DESC])->count();
    }
	
    public function getPlacePost($type)
    {
        return PostForm::find()->with('user')->where(['is_deleted'=>"0",'placetype'=>"$type"])->orderBy(['post_created_date'=>SORT_DESC])->all();
    }
	
    public function getUserAds($user_id)
    {
        return PostForm::find()->with('user')->where(['is_deleted'=>"0",'post_user_id'=>"$user_id",'is_ad'=>"1"])->orWhere(['is_deleted'=>"0",'post_user_id'=>"$user_id",'is_ad'=>"0"])->orderBy(['post_created_date'=>SORT_DESC])->all();
    }
	
	public function DeletePostCleanUp($post_id,$post_user_id)
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
		$status = $session->get('status');

        $delete_post = new PostForm();
		$date = time();
        $data = array();
        $delete_post = PostForm::find()->where(['_id' => $post_id])->one();
		 
		$post_owner_id = $delete_post['post_user_id'];
		if(isset($delete_post['parent_post_id']) && !empty($delete_post['parent_post_id']))
		{
			$parent_post_id = $delete_post['parent_post_id'];
			
			/* update share by in main post */
						
			$main_post = PostForm::find()->where(['_id' => $parent_post_id])->one();
			if (strstr($main_post['share_by'], $post_user_id))
			{
				$main_post->share_by = str_replace($post_user_id.',',"",$main_post['share_by']);
				$main_post->update();
			}	
		}
		else
		{
			$parent_post_id = '1';
		}
		
		if($delete_post['pagepost'] == '1')
		{
			$post_owner_id = $delete_post['shared_by'];
		}
		
		if($delete_post['is_ad'] == '1')
		{
			if($delete_post['adobj']=='travstorefocus')
			{
				$delete_post->is_ad = null;
				$delete_post->post_type = 'text and image';
				$delete_post->update();
			}
			else
			{
				$adid = (string)$_POST['pid'];
			
				$order = Order::find()->where(['detail' => $adid])->one();
				$paid_amount =  $delete_post['adtotbudget'];
				
				/* Spent Amount */
					$impression = TravAdsVisitors::getCount($adid,'impression');
					$action = TravAdsVisitors::getCount($adid,'action');
					$click = TravAdsVisitors::getCount($adid,'click');
					$total = $impression + $action + $click;
					$isvip = Vip::isVIP((string)$uid);
					$rate_impression = Yii::$app->GenCls->getadrate($isvip,'impression');
					$rate_action = Yii::$app->GenCls->getadrate($isvip,'action');
					$rate_click = Yii::$app->GenCls->getadrate($isvip,'click');
					$spent_impression = (($impression/1000)*$rate_impression);
					$spent_action = $action*$rate_action;
					$spent_click = $click*$rate_click;
					$spent = $spent_impression + $spent_action + $spent_click;
				
					$diffAmount	= $paid_amount - $spent;
				/* Spent Amount */
				
				if(isset($_POST['money']) && !empty($_POST['money']))
				{
					$money = UserMoney::find()->where(['user_id' => "$uid"])->one();
					
					if($money)
					{
						$final = $diffAmount + $money['amount'];
						$money->amount = (int)$final;
						$money->update();
					}
					else {
						$money = new UserMoney();
						$money->user_id = $post_user_id;
						$money->amount = (int)$diffAmount;
						$money->insert();	
					}
					
				}
				if(isset($_POST['refund']) && !empty($_POST['refund']))
				{
					$refund = UserRefund::find()->where(['user_id' => "$uid"])->one();
					
					if($refund) {
						$final = $diffAmount + $refund['amount'];
						$refund->amount = (int)$final;
						$refund->update();
					}
					else {
						$refund = new UserRefund();
						$refund->user_id = $post_user_id;
						$refund->amount = (int)$diffAmount;
						$refund->insert();
					}
				}
				$delete_post->delete();
			}
		}
		else
		{
            if(!empty($delete_post)) {
			 $delete_post->delete();	
            }
		}
		/* Delete All Notification Releted To This (Delete Post) */
			
			Notification::deleteAll(['post_id' => $post_id]);
			
		/* Delete All Likes Releted To This (Delete Post) */
			
			Like::deleteAll(['post_id' => $post_id]);
			
		/* Delete All Comments Releted To This (Delete Post) */
			
			Comment::deleteAll(['post_id' => $post_id]);
			
		/* Delete All Saved Post Releted To This (Delete Post) */
			
			SavePost::deleteAll(['post_id' => $post_id]);	
			
		/* Delete All Shared Post Releted To This (Delete Post) */
			
			PostForm::deleteAll(['parent_post_id' => $post_id]);	
		
		/* Delete All Reported Post Releted To This (Delete Post) */
			
			ReportPost::deleteAll(['post_id' => $post_id]);		
			
		/* Delete All PinImage Releted To This (Delete Post) */
			
			PinImage::deleteAll(['post_id' => $post_id]);

		/* Delete All Images Releted To This (Delete Post) */
			
			$url = '../web'; 
			
			$img = explode(",",$delete_post['image']);
			foreach($img as $img2)
			{
				$img2 = $url . $img2;
				error_reporting(E_ERROR | E_PARSE);
				unlink($img2);
			} 
			
		/* Update All Turned Off Notification Releted To This (Delete Post) */
			
			$userexist1 = TurnoffNotification::find()->all();
			
			if($userexist1)
			{		
				foreach($userexist1 as $userexist)
				{
					if (strstr($userexist['post_ids'], $post_id))
					{
						$ton = $userexist;
						$ton->post_ids = str_replace($post_id.',',"",$userexist['post_ids']);
						$tonids = $ton->post_ids;
						$ton->update();
						if(strlen($tonids) == 0)
						{
							$ton = TurnoffNotification::find()->one();
							$ton->delete();
						}
					}
				}
			}
		echo $parent_post_id;
    }

    public function getUserTagIds($user_id, $post_id) {
        $data = PostForm::find()->select(['post_tags'])->where(['_id' => $post_id, 'post_user_id' => $user_id])->asarray()->one();
        $ids = array('status' => false);
        $issearch = 'yes';
        if(!empty($data)) {
            $issearch = 'no';
            $ids = isset($data['post_tags']) ? $data['post_tags'] : '';
            $ids = explode(",", $ids);
            $ids = array('status' => true, 'ids' => $ids);
        }   

        if($issearch == 'yes') {
            $data = PlaceDiscussion::find()->select(['post_tags'])->where(['_id' => $post_id, 'post_user_id' => $user_id])->andWhere(['not','flagger', "yes"])->asarray()->one();
            if(!empty($data)) {
                $issearch = 'no';
                $ids = isset($data['post_tags']) ? $data['post_tags'] : '';
                $ids = explode(",", $ids);
                $ids = array('status' => true, 'ids' => $ids);
            }             
        }

        if($issearch == 'yes') {
            $data = PlaceReview::find()->select(['post_tags'])->where(['_id' => $post_id, 'post_user_id' => $user_id])->andWhere(['not','flagger', "yes"])->asarray()->one();
            if(!empty($data)) {
                $issearch = 'no';
                $ids = isset($data['post_tags']) ? $data['post_tags'] : '';
                $ids = explode(",", $ids);
                $ids = array('status' => true, 'ids' => $ids);
            }             
        }
        return json_encode($ids, true);
    }
}