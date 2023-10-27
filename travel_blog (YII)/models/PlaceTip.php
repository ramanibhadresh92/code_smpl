<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\Like;
use frontend\models\SavePost;
use frontend\models\ReportPost;
use frontend\models\TurnoffNotification;
use frontend\models\PinImage;
use frontend\models\Order;
use frontend\models\HidePost;

class PlaceTip extends ActiveRecord
{
    public static function collectionName()
    {
        return 'place_tip';
    }

    public function attributes()
    {
		return ['_id','post_type', 'post_ip', 'post_title', 'post_text', 'shared_by','post_status', 'post_created_date', 'post_user_id','image','link_title','link_description','share_by','shared_from','post_privacy','is_timeline','is_deleted','currentlocation','parent_post_id','is_coverpic','share_setting','comment_setting', 'post_tags','custom_share', 'custom_notshare', 'anyone_tag', 'is_profilepic', 'pagepost', 'is_page_review', 'rating', 'page_id', 'collection_id', 'trav_cat', 'trav_link', 'trav_price', 'trav_item', 'is_ad', 'ad_type', 'post_flager_id' , 'is_trip', 'country', 'continent', 'adobj', 'adname', 'adid', 'adcatch', 'adheadeline', 'adtitle', 'adtext', 'adlogo', 'adimage', 'adsubcatch', 'adurl', 'adbtn', 'adlocations', 'adminage', 'admaxage', 'adlanguages', 'admale', 'adfemale', 'adpro', 'adint', 'adbudget', 'adruntype', 'adstartdate', 'adenddate', 'rate_impression', 'rate_action', 'rate_click', 'placetype', 'placetitlepost', 'placereview','page_owner','adtotbudget','pinned','flagger', 'flagger_date', 'flagger_by'];
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
        return PlaceTip::find()->with('user')->where(['is_deleted'=>"0"])->andWhere(['not','flagger', "yes"])->orderBy(['post_created_date'=>SORT_DESC])->all();
    }
	
	public function getAllPosts()
    {
        return PlaceTip::find()->where(['is_deleted'=>"0"])->andWhere(['not','flagger', "yes"])->all();
    }
	
	public function getAllPhotoPosts()
    {
        return PlaceTip::find()->where(['is_deleted'=>"0",'post_type'=>'image'])->orwhere(['post_type'=>'text and image'])->andWhere(['not','flagger', "yes"])->all();
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
            return PlaceTip::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'placetype'=>null])->orwhere(['like','post_tags',"$userid"])->andWhere(['not','flagger', "yes"])->orderBy(['post_created_date'=>SORT_DESC])->limit(7)->offset($start)->asarray()->all();
    	}
    	else
    	{
            return PlaceTip::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'placetype'=>null])->andWhere(['not in', 'post_privacy', 'Private'])->orwhere(['like','post_tags',"$userid"])->andWhere(['not','flagger', "yes"])->orderBy(['post_created_date'=>SORT_DESC])->limit(7)->offset($start)->asarray()->all(); 
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
            return PlaceTip::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0'])->orWhere(['shared_by' => $userid, 'shared_from' => 'shareentity'])->orwhere(['like','post_tags',"$userid"])->andWhere(['not','flagger', "yes"])->orderBy(['post_created_date'=>SORT_DESC])->limit(7)->offset($start)->asarray()->all();
            
        }
        else
        {
            return PlaceTip::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0'])->andWhere(['not in', 'post_privacy', 'Private'])->orWhere(['shared_by' => $userid, 'shared_from' => 'shareentity'])->orwhere(['like','post_tags',"$userid"])->andWhere(['not','flagger', "yes"])->orderBy(['post_created_date'=>SORT_DESC])->limit(7)->offset($start)->asarray()->all(); 
        }
    }
	
	public function getUserPostCount($userid)
    {
        $session = Yii::$app->session;
    	$uid = (string)$session->get('user_id');
    	if($uid == (string)$userid)
    	{
            return PlaceTip::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'placetype'=>null])->orwhere(['like','post_tags',"$userid"])->andWhere(['not','flagger', "yes"])->orderBy(['post_created_date'=>SORT_DESC])->all();
    	}
    	else
    	{
            return PlaceTip::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'placetype'=>null])->andWhere(['not in', 'post_privacy', 'Private'])->orwhere(['like','post_tags',"$userid"])->andWhere(['not','flagger', "yes"])->orderBy(['post_created_date'=>SORT_DESC])->all(); 
        }
    }
    
	public function getTravPostCount()
    {
        return PlaceTip::find()->with('user')->where(['is_deleted'=>'0','rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'placetype'=>null])->andWhere(['not in', 'post_privacy', 'Private'])->andWhere(['not','flagger', "yes"])->count();
    }

     public function getUserPhotos($userid)
    {
        $session = Yii::$app->session;
    	$uid = (string)$session->get('user_id');
    	if($uid == (string)$userid)
    	{
            return PlaceTip::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','is_album'=>'1','is_timeline'=>null])->andWhere(['not','flagger', "yes"])->orderBy(['post_created_date'=>SORT_DESC])->all();
    	}
    	else
    	{
            return PlaceTip::find()->with('user')->where(['post_user_id'=>"$userid",'is_deleted'=>'0','is_album'=>'1','is_timeline'=>null])->andWhere(['not in', 'post_privacy', 'Private'])->andWhere(['not','flagger', "yes"])->orderBy(['post_created_date'=>SORT_DESC])->all(); 
        }
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
        $connect_posts =  PlaceTip::find()->with('user')->where(['in','post_user_id',$ids])->andWhere(['not','flagger', "yes"])->orderBy(['post_created_date'=>SORT_DESC])->all();
      
        return $connect_posts;
    }
	
	public function getPlaceReviews($place,$type,$count)
    {
		$session = Yii::$app->session;
		$uid = (string)$session->get('user_id');
		$hidepost = HidePost::find()->where(['user_id' => (string)$uid])->one();
		$hide_ids = explode(',',$hidepost['post_ids']); 
		
		if($count == 'all')
		{
			return PlaceTip::find()->with('user')->where(['is_deleted'=>"0",'currentlocation'=>"$place",'placetype'=>"$type"])->andwhere(['not in','_id',$hide_ids])->andWhere(['not','flagger', "yes"])->asarray()->orderBy(['post_created_date'=>SORT_DESC])->all();
		} else {
			return PlaceTip::find()->with('user')->where(['is_deleted'=>"0",'currentlocation'=>"$place",'placetype'=>"$type"])->andwhere(['not in','_id',$hide_ids])->andWhere(['not','flagger', "yes"])->asarray()->orderBy(['post_created_date'=>SORT_DESC])->limit($count)->offset(0)->all();
		}
    }
	
	public function getPlaceReviewsCount($place,$type)
    {
		$session = Yii::$app->session;
		$uid = (string)$session->get('user_id');
		$hidepost = HidePost::find()->where(['user_id' => (string)$uid])->one();
		$hide_ids = explode(',',$hidepost['post_ids']); 
		return PlaceTip::find()->with('user')->where(['is_deleted'=>"0",'currentlocation'=>"$place",'placetype'=>"$type"])->andwhere(['not in','_id',$hide_ids])->andWhere(['not','flagger', "yes"])->orderBy(['post_created_date'=>SORT_DESC])->count();
    }
	
	public function getPlacePost($type)
    {
        return PlaceTip::find()->with('user')->where(['is_deleted'=>"0",'placetype'=>"$type"])->andWhere(['not','flagger', "yes"])->orderBy(['post_created_date'=>SORT_DESC])->all();
    }
	
	
	public function DeleteTipCleanUp($post_id,$post_user_id)
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
		$status = $session->get('status');

        $delete_post = new PlaceTip();
		$date = time();
        $data = array();
        $delete_post = PlaceTip::find()->where(['_id' => $post_id])->andWhere(['not','flagger', "yes"])->one();
		 
		$post_owner_id = $delete_post['post_user_id'];
		if(isset($delete_post['parent_post_id']) && !empty($delete_post['parent_post_id']))
		{
			$parent_post_id = $delete_post['parent_post_id'];
			$main_post = PlaceTip::find()->where(['_id' => $parent_post_id])->andWhere(['not','flagger', "yes"])->one();
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
				$paid_amount = $order['amount'];
				
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
					else
					{
						$money = new UserMoney();
						$money->user_id = $post_user_id;
						$money->amount = (int)$diffAmount;
						$money->insert();	
					}
					
				}
				if(isset($_POST['refund']) && !empty($_POST['refund']))
				{
					$refund = UserRefund::find()->where(['user_id' => "$uid"])->one();
					
					if($refund)
					{
						$final = $diffAmount + $refund['amount'];
						$refund->amount = (int)$final;
						$refund->update();
					}
					else
					{
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
			$delete_post->delete();	
		}
		/* Delete All Notification Releted To This (Delete Post) */
			
			Notification::deleteAll(['post_id' => $post_id]);
			
		/* Delete All Likes Releted To This (Delete Post) */
			
			Like::deleteAll(['post_id' => $post_id]);
			
		/* Delete All Comments Releted To This (Delete Post) */
			
			Comment::deleteAll(['post_id' => $post_id]);
			
		/* Delete All Saved Post Releted To This (Delete Post) */
			
			SavePost::deleteAll(['post_id' => $post_id]);	
				
		
		/* Delete All Reported Post Releted To This (Delete Post) */
			
			ReportPost::deleteAll(['post_id' => $post_id]);		
			
		/* Delete All PinImage Releted To This (Delete Post) */
			
			PinImage::deleteAll(['post_id' => $post_id]);

		/* Delete All Images Releted To This (Delete Post) */
			
			$url = '../web'; 
			
			//chown($url, 0777);
			
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
}