<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class Notification extends ActiveRecord
{ 
    public static function collectionName()
    {
        return 'notification';
    }

    public function attributes()
    {
         return ['_id', 'user_id', 'post_id','post_owner_id','shared_by','share_id','from_friend_id', 'comment_content','comment_id','reply_comment_id','like_id','page_id','notification_type','status','created_date','updated_date','ip','is_deleted','review_setting','entity','page_id','flag_reason','page_role_type','collection_owner_id','tag_id','credits','collection_id', 'travelbuddy_trip_id', 'travelbuddy_trip_id', 'travelbuddy_id', 'travelbuddy_invited_id', 'hireaguide_id', 'hireaguide_invited_to_id', 'hireaguide_invited_from_id','hangout_invited_from_id','hangout_invited_to_id','hangout_id', 'weekend_escape_invited_to_id', 'weekend_escape_invited_from_id', 'weekend_escape_id','localguide_id', 'localguide_invited_to_id', 'localguide_invited_from_id','localdriver_id', 'localdriver_invited_to_id', 'localdriver_invited_from_id','homestay_id','camping_id','localdine_id'];
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
        return $this->hasMany(Like::className(), ['_id' => 'like_id']);
    }
    
    public function getComment()
    {
        return $this->hasMany(Comment::className(), ['_id' => 'comment_id']);
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
        $mute_ids = MuteConnect::getMuteconnectionsIds($uid);
        $mute_connect_ids =  (explode(",",$mute_ids['mute_ids']));
        $login_user = LoginForm::find()->where(['_id' => "$uid"])->one();
        $view_noti_time =  $login_user->last_logout_time;
        $notification_settings = NotificationSetting::find()->where(['user_id' => (string)$uid])->one();
       
           $notificationpost = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['in','user_id',$ids])->andwhere(['not in','user_id',$mute_connect_ids])->andwhere(['not','notification_type','tag_connect'])->andwhere(['not','notification_type','connectrequestaccepted'])->andwhere(['not','notification_type','followcollection'])->andwhere(['not','notification_type','sharecollection'])->andwhere(['not','notification_type','connectrequestdenied'])->andwhere(['not','notification_type','comment'])->andwhere(['not','notification_type','likepost'])->andwhere(['not','notification_type','onpagewall'])->andwhere(['not','notification_type','page_role_type'])->andwhere(['not','notification_type','likecomment'])->andwhere(['not','notification_type','commentreply'])->andwhere(['not','notification_type','sharepost'])->andwhere(['not','notification_type','pageinvite'])->andwhere(['not','notification_type','pageinvitereview'])->andwhere(['not','notification_type','likepage'])->andwhere(['not','notification_type','pagereview'])->andwhere(['not','notification_type','deletepostadmin'])->andwhere(['not','notification_type','editpostuser'])->andwhere(['not','notification_type','editcollectionuser'])->andwhere(['not','notification_type','publishpost'])->andwhere(['not','notification_type','publishcollection'])->andwhere(['not','notification_type','publishpage'])->andwhere(['not','notification_type','deletecollectionadmin'])->andwhere(['not','notification_type','deletepageadmin'])->andwhere(['not','notification_type','invitereferal'])->andwhere(['not','notification_type','addreferal'])->andwhere(['not','notification_type','replyreferal'])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();

			$notificationtag = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'tag_connect','user_id'=>(string)$uid])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();

		   
			$notificationconnectaccepted = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'connectrequestaccepted','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();

			$notificationconnectdenied = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'connectrequestdenied','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
		   
		   
			$notificationlikepost = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'likepost','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
				
			$notificationcomment = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'comment','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
				
			$notificationsharepost = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'sharepost','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
				
				
			$notificationlikecomment = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'likecomment','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();

			$notificationcommentreply = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'commentreply','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
				
			$notificationlowcredits = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'low_credits','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
				
				
			$notificationfollowcollection = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'followcollection','collection_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
				
			$notificationsharecollection = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'sharecollection','collection_owner_id'=>(string)$uid])->andwhere(['not','user_id',(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
			$notificationainvitedfortrip = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['created_date'=> ['$gte'=>"$view_noti_time"]])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
				
			$notificationsharepostowner = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'sharepost','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
		   
			$notificationpageinvitation = Notification::find()->with('user')->with('like')->where(['notification_type'=>'pageinvite','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
			$notificationpageinvitationreview = Notification::find()->with('user')->with('like')->where(['notification_type'=>'pageinvitereview','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
		
			$notificationpagelikes = Notification::find()->with('user')->with('like')->where(['notification_type'=>'likepage','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();

			$notificationpagereviews = Notification::find()->with('user')->with('like')->where(['notification_type'=>'pagereview','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
			$notificationdeletebyadmin = Notification::find()->with('user')->with('like')->where(['notification_type'=>'deletepostadmin','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
			$n_flag_collection = Notification::find()->with('user')->with('like')->where(['notification_type'=>'deletecollectionadmin','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
			$n_flag_page = Notification::find()->with('user')->with('like')->where(['notification_type'=>'deletepageadmin','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
			$notificationeditbyadmin = Notification::find()->with('user')->with('like')->where(['notification_type'=>'editpostuser','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
			$n_edit = Notification::find()->with('user')->with('like')->where(['notification_type'=>'editcollectionuser','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();

			$notificationpublishpost = Notification::find()->with('user')->with('like')->where(['notification_type'=>'publishpost','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
			$n_publish_collection = Notification::find()->with('user')->with('like')->where(['notification_type'=>'publishcollection','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
			$n_publish_page = Notification::find()->with('user')->with('like')->where(['notification_type'=>'publishpage','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
			$notificationpageroles = Notification::find()->with('user')->with('like')->where(['notification_type'=>'page_role_type','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
			$notificationpagewall = Notification::find()->with('user')->with('like')->where(['notification_type'=>'onpagewall','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
			$notificationrefinvite = Notification::find()->with('user')->where(['notification_type'=>'invitereferal','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
			$notificationrefadd = Notification::find()->with('user')->where(['notification_type'=>'addreferal','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
			$notificationrefreply = Notification::find()->with('user')->where(['notification_type'=>'replyreferal','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->andwhere(['created_date'=> ['$gte'=>"$view_noti_time"]])->orderBy(['created_date'=>SORT_DESC])->all();
			
			if(isset($notification_settings) && !empty($notification_settings))
			{
				if($notification_settings['connect_activity'] == 'No')
				{
					$notificationconnectaccepted = array();
					$notificationconnectdenied = array();
					$notificationlikepost = array();
					$notificationcomment = array();
					$notificationsharepost = array();
					$notificationfollowcollection = array();
					$notificationsharecollection = array();
					$notificationainvitedfortrip = array();
				}
				else if($notification_settings['connect_request'] == 'No')
				{
					$notificationconnectaccepted = array();
					$notificationconnectdenied = array();
				}
				else if($notification_settings['is_like'] == 'No')
				{
					$notificationlikepost = array();
				}
				else if($notification_settings['is_comment'] == 'No')
				{
					$notificationcomment = array();
				}
				else if($notification_settings['is_share'] == 'No')
				{
					$notificationsharepost = array();
				}
				else if($notification_settings['follow_collection'] == 'No')
				{
					$notificationfollowcollection = array();
				}
				else if($notification_settings['share_collection'] == 'No')
				{
					$notificationsharecollection = array();
				}
				else if($notification_settings['invited_for_trip'] == 'No')
				{
					$notificationainvitedfortrip = array();
				}
			}

			$notification = array_merge_recursive($notificationpost,$notificationtag,$notificationconnectaccepted,$notificationconnectdenied,$notificationcomment,$notificationlikepost,$notificationlikecomment,$notificationcommentreply,$notificationsharepost,$notificationsharepostowner,$notificationpageinvitation,$notificationpageinvitationreview,$notificationpagelikes,$notificationpagereviews, $notificationdeletebyadmin,$notificationeditbyadmin,$notificationpublishpost,$notificationpageroles,$notificationpagewall,$n_flag_collection,$n_edit,$n_publish_collection,$n_flag_page,$n_publish_page,$notificationrefinvite,$notificationrefadd,$notificationrefreply,$notificationlowcredits,$notificationfollowcollection,$notificationsharecollection,$notificationainvitedfortrip);
			
			return count($notification);
     }
    
    public function getAllNotification()
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $connections =  Connect::getuserConnections($uid);
        $ids = array();
        foreach($connections as $connect)
        {
            $ids[]= $connect['from_id'];
        }
        $mute_ids = MuteConnect::getMuteconnectionsIds($uid);
        $mute_connect_ids =  (explode(",",$mute_ids['mute_ids']));
        
        $notification_settings = NotificationSetting::find()->where(['user_id' => (string)$uid])->one();

		$notificationpost = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['in','user_id',$ids])->andwhere(['not in','user_id',$mute_connect_ids])->andwhere(['not','notification_type','connectrequestaccepted'])->andwhere(['not','notification_type','followcollection'])->andwhere(['not','notification_type','sharecollection'])->andwhere(['not','notification_type','tag_connect'])->andwhere(['not','notification_type','page_role_type'])->andwhere(['not','notification_type','comment'])->andwhere(['not','notification_type','likepost'])->andwhere(['not','notification_type','pagereview'])->andwhere(['not','notification_type','onpagewall'])->andwhere(['not','notification_type','likecomment'])->andwhere(['not','notification_type','commentreply'])->andwhere(['not','notification_type','sharepost'])->andwhere(['not','notification_type','pageinvite'])->andwhere(['not','notification_type','pageinvitereview'])->andwhere(['not','notification_type','likepage'])->andwhere(['not','notification_type','deletepostadmin'])->andwhere(['not','notification_type','deletecollectionadmin'])->andwhere(['not','notification_type','deletepageadmin'])->andwhere(['not','notification_type','editpostuser'])->andwhere(['not','notification_type','editcollectionuser'])->andwhere(['not','notification_type','publishpost'])->andwhere(['not','notification_type','publishcollection'])->andwhere(['not','notification_type','publishpage'])->andwhere(['not','notification_type','invitereferal'])->andwhere(['not','notification_type','addreferal'])->andwhere(['not','notification_type','replyreferal'])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		

		$notificationtag = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'tag_connect','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();

   		$notificationconnectaccepted = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'connectrequestaccepted','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();

		$notificationconnectdenied = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'connectrequestdenied','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
              
		$notificationcomment = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'comment','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		
		$notificationlikepost = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'likepost','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		
		$notificationsharepost = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'sharepost','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
					
		$notificationlikepost = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'likepost','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
					
		$notificationcomment = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'comment','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
					
		$notificationsharepost = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'sharepost','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
					
		$notificationlikecomment = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'likecomment','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();

		$notificationcommentreply = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'commentreply','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
	
		$notificationlowcredits = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'low_credits','user_id'=> (string)$uid])->orderBy(['created_date'=>SORT_DESC])->all();
		
		
		$notificationfollowcollection = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'followcollection','collection_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		
        $notificationsharecollection = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'sharecollection','collection_owner_id'=>(string)$uid])->andwhere(['not','user_id',(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
					
		$notificationainvitedfortrip = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		$notificationsharepostowner = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'sharepost','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
              
		$notificationpageinvitation = Notification::find()->with('user')->with('like')->where(['notification_type'=>'pageinvite','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
                
		$notificationpageinvitationreview = Notification::find()->with('user')->with('like')->where(['notification_type'=>'pageinvitereview','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
            
		$notificationpagelikes = Notification::find()->with('user')->with('like')->where(['notification_type'=>'likepage','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
                
		$notificationpagereviews = Notification::find()->with('user')->with('like')->where(['notification_type'=>'pagereviews','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
                
		$notificationadmindelete = Notification::find()->with('user')->with('like')->where(['notification_type'=>'deletepostadmin','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		
		$n_flag_collection_adminn = Notification::find()->with('user')->with('like')->where(['notification_type'=>'deletecollectionadmin','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		
		$n_flag_page_adminn = Notification::find()->with('user')->with('like')->where(['notification_type'=>'deletepageadmin','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		
		$notificationeditpost = Notification::find()->with('user')->with('like')->where(['notification_type'=>'editpostuser','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		
		$n_editcollection = Notification::find()->with('user')->with('like')->where(['notification_type'=>'editcollectionuser','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();

		$notificationpublishpost = Notification::find()->with('user')->with('like')->where(['notification_type'=>'publishpost','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		
		$n_publish_collection3 = Notification::find()->with('user')->with('like')->where(['notification_type'=>'publishcollection','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		
		$n_publish_page3 = Notification::find()->with('user')->with('like')->where(['notification_type'=>'publishpage','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		
		$notificationpageroles = Notification::find()->with('user')->with('like')->where(['notification_type'=>'page_role_type','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		
		$notificationpagewall = Notification::find()->with('user')->with('like')->where(['notification_type'=>'onpagewall','post_owner_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		
		$notificationrefinvite = Notification::find()->with('user')->where(['notification_type'=>'invitereferal','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		
		$notificationrefadd = Notification::find()->with('user')->where(['notification_type'=>'addreferal','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();

		$notificationrefreply = Notification::find()->with('user')->where(['notification_type'=>'replyreferal','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
		
		if(isset($notification_settings) && !empty($notification_settings))
		{
			if($notification_settings['connect_activity'] == 'No')
			{
				$notificationconnectaccepted = array();
				$notificationconnectdenied = array();
				$notificationlikepost = array();
				$notificationcomment = array();
				$notificationsharepost = array();
				$notificationfollowcollection = array();
				$notificationsharecollection = array();
				$notificationainvitedfortrip = array();
			}
			else if($notification_settings['connect_request'] == 'No')
			{
				$notificationconnectaccepted = array();
				$notificationconnectdenied = array();
			}
			else if($notification_settings['is_like'] == 'No')
			{
				$notificationlikepost = array();
			}
			else if($notification_settings['is_comment'] == 'No')
			{
				$notificationcomment = array();
			}
			else if($notification_settings['is_share'] == 'No')
			{
				$notificationsharepost = array();
			}
			else if($notification_settings['follow_collection'] == 'No')
			{
				$notificationfollowcollection = array();
			}
			else if($notification_settings['share_collection'] == 'No')
			{
				$notificationsharecollection = array();
			}
			else if($notification_settings['invited_for_trip'] == 'No')
			{
				$notificationainvitedfortrip = array();
			}
		}
				
		$notification = array_merge_recursive($notificationpost,$notificationtag,$notificationconnectaccepted,$notificationconnectdenied,$notificationcomment,$notificationlikepost,$notificationlikecomment,$notificationcommentreply,$notificationsharepost,$notificationsharepostowner,$notificationpageinvitation,$notificationpageinvitationreview,$notificationpagelikes,$notificationpagereviews, $notificationadmindelete,$notificationeditpost,$notificationpublishpost,$notificationpageroles,$notificationpagewall,$n_flag_collection_adminn,$n_editcollection,$n_publish_collection3,$n_flag_page_adminn,$n_publish_page3,$notificationrefinvite,$notificationrefadd,$notificationrefreply,$notificationlowcredits,$notificationfollowcollection,$notificationsharecollection,$notificationainvitedfortrip);
		foreach ($notification as $key)
		{
			$sortkeys[] = $key["created_date"];
		}

		if(count($notification))
		{
			array_multisort($sortkeys, SORT_DESC, SORT_STRING, $notification);
		}

		return $notification;
    }
    
}