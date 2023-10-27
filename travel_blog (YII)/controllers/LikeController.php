<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\mongodb\ActiveRecord;
use frontend\models\UserForm;
use frontend\models\LoginForm;
use frontend\models\PostForm;
use frontend\models\Like;
use frontend\models\Page;
use frontend\models\HidePost;
use frontend\models\SavePost;
use frontend\models\Notification;
use frontend\models\Comment;

class LikeController extends Controller
{
   public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }
    
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
	
      public function actions()
      {
            return [
            'auth' => [
              'class' => 'yii\authclient\AuthAction',
              'successCallback' => [$this, 'oAuthSuccess'],
            ],
                'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
            ],                
         ];           
      }
    
    public function actionLikePost()
    { 
		$session = Yii::$app->session;
		$post_id = $_POST['post_id'];
		$uid = (string)$session->get('user_id');
		$like = new Like();
		$date = time();
		$data = array();
		 
		if(isset($uid) && $uid != '') {
		$authstatus = UserForm::isUserExistByUid($uid);
		if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
			$data['auth'] = $authstatus;
		} else {
			$already_like = Like::find()->where(['post_id' => $post_id , 'user_id' => (string)$uid])->one();
				   
			$status = $already_like['status'];
			if($status == '1'){$status = '0';}else{$status = '1';}
			
			if(!empty($already_like))
			{
				$date = time();
				$already_like->status = $status;
				$already_like->updated_date = $date;
				$already_like->update();
					   
				$data['status'] = $status;
				$data['msg'] = 'updated';
			   
			}
			else
			{
				$like->post_id = $_POST['post_id'];
				$like->user_id = (string)$uid;
				$like->like_type = 'post';
				$like->status = '1';
				$like->created_date = $date;
				$like->updated_date = $date;
				$like->insert();
				$data['status'] = '1';
				$data['msg'] = 'inserted';
				$last_insert_id = $like->_id;
			}
			if(isset($last_insert_id))
			{
				if($status == '1')
				{
					// Insert record in notification table also
					$notification =  new Notification();
					if($last_insert_id != '')
						$notification->like_id =   "$last_insert_id";
					else
						$notification->like_id = "$already_like->_id";
					$notification->user_id = "$uid";
					$notification->post_id = $_POST['post_id'];
					$notification->notification_type = 'likepost';
					$notification->is_deleted = '0';
					$notification->status = '1'; 
					$notification->created_date = "$date";
					$notification->updated_date = "$date";
					$post_details = PostForm::find()->where(['_id' => $_POST['post_id']])->one();
					$post_owner_id = (string) $post_details['post_user_id'];
					$notification->post_owner_id = $post_owner_id;
					$notification->tag_id = $post_details['post_tags'];
					if(isset($post_details['pagepost']) && !empty($post_details['pagepost']) && $post_details['pagepost'] == '1')
					{
						$page_id = Page::Pagedetails($post_details['post_user_id']);
						$usrid = $page_id['created_by'];
						$pageid = $post_details['post_user_id'];
						$notification->page_id = "$pageid";
						$notification->post_owner_id = "$usrid";
						//$notification->entity = 'page';
						if($usrid != $uid && $post_details['post_privacy'] != "Private" && $page_id['not_like_post'] == 'on')
						{
							$notification->insert();
						}
					}
					else
					{
						if($post_details['post_user_id'] != "$uid" && $post_details['post_privacy'] != "Private")
						{
							$notification->insert();
						}
					}
				}
			}
			
			
			$likes = Like::find()->where(['post_id' => $post_id ,'status' => '1'])->all();
			$like_names = Like::getLikeUserNames((string)$post_id);
			$like_buddies = Like::getLikeUser((string)$post_id);
			$data['display_ctr'] = $like_names['count'];
			
			$data['names'] =  $like_names['names'];
			$data['fname'] = ucfirst($like_names['login_user_details']['user']['fname']).' '.ucfirst($like_names['login_user_details']['user']['lname']);
			$buddies = '';
			foreach($like_buddies AS $like_buddy)
			{
			   $buddies .= ucfirst($like_buddy['user']['fname']). ' '.ucfirst($like_buddy['user']['lname']).'<br>' ;
			}
			$data['buddies'] = $buddies;
			$data['like_count'] = count($likes);
		}
        } else {
        	$data['auth'] = 'checkuserauthclassg';
        }
        return json_encode($data);
    } 
    
    public function actionHidePost()
    {
       
        $loginmodel = new \frontend\models\LoginForm();
        if(isset($_POST['post_id']) && !empty($_POST['post_id'])) {
            $data = array();

            $session = Yii::$app->session;
            $email = $session->get('email_id');
            $user_id = (string) $session->get('user_id');

            $userexist = HidePost::find()->where(['user_id' => $user_id])->one();
            $unfollow = new HidePost();
            if($userexist){
                if(strstr($userexist['post_ids'], $_POST['post_id']))
                {
                    print true;
                }
                else{
                    $unfollow = HidePost::find()->where(['user_id' => $user_id])->one();
                    $unfollow->post_ids = $userexist['post_ids'].','.$_POST['post_id'];
                    if($unfollow->update())
                    {
                        print true;
                    }
                    else{
                        print false;
                    }
                }
            }
            else{
                $unfollow->user_id = $user_id;
                $unfollow->post_ids = $_POST['post_id'];
                if($unfollow->insert())
                {
                    print true;
                }
                else{
                    print false;
                }
            }
        }
    }
    
    public function actionSavePost()
    {
        if(isset($_POST['postid']) && !empty($_POST['postid']))
        {
            $postid = $_POST['postid'];
            $posttype = $_POST['posttype'];
            $type = $_POST['type'];
            $date = time();
            $data = array();

            $session = Yii::$app->session;
            $email = $session->get('email_id');
            $user_id = (string) $session->get('user_id');

            $userexist = SavePost::find()->where(['user_id' => $user_id,'post_id' => $postid])->one();
            $savestatus = new SavePost();
            if($userexist){
                $savestatus = SavePost::find()->where(['user_id' => $user_id,'post_id' => $postid])->one();
                $spvalue = $savestatus['is_saved'];
                if($spvalue == '1') {
                    $savestatus->is_saved = '0';
                      $data['saved'] = '1';
                }
                else {
                    $savestatus->is_saved = '1';
                     $data['saved'] = '0';
                }
                $savestatus->saved_date = $date;
                if($savestatus->update()) {
                    $data['status'] = 'true';
                    
                }
                else {
                     $data['status'] = 'false';
                }
            }
            else {
                $savestatus->user_id = $user_id;
                $savestatus->post_id = $postid;
                $savestatus->post_type = $posttype;
                $savestatus->type = $type;
                $savestatus->is_saved = '1';
                $savestatus->saved_date = $date;
                if($savestatus->insert())
                {
                    $data['status'] = 'true';
                     $data['saved'] = '0';
                }
                else{
					$data['status'] = 'false';
					$data['saved'] = '1';
				}
            }
        }
       
        return json_encode($data);
    }  
  
    public function actionCommentLike()
    { 
        $session = Yii::$app->session;
        $comment_id = $_POST['comment_id'];
        $uid = (string)$session->get('user_id');
        $like = new Like();
        $date = time();  
        $data = array();
        $already_like = Like::find()->where(['comment_id' => $comment_id , 'user_id' => (string)$uid])->one();
               
        $status = $already_like['status'];
        if($status == '1'){ $status = '0'; }else{ $status = '1'; }
        
        if(!empty($already_like))
        {
            $date = time();
            $already_like->status = $status;
            $already_like->updated_date = $date;
            $already_like->update();
                   
            $data['status'] = $status;
            $data['msg'] = 'updated';  
        }
        else
        {
			$like->comment_id = $_POST['comment_id'];
            $like->user_id = (string)$uid;
            $like->like_type = 'comment';
            $like->status = '1';
            $like->created_date = $date;
            $like->updated_date = $date;
            $like->insert();
            $last_insert_id = $like->_id;
            $data['status'] = '1';
            $data['msg'] = 'inserted';
        
        }
        if(isset($last_insert_id))
        { 
            if($status == '1') 
            {
                // Insert record in notification table also
                $notification =  new Notification(); 
                if($last_insert_id != '')
                    $notification->like_id =   "$last_insert_id";
                else
                    $notification->like_id = "$already_like->_id";
                $comment_details = Comment::find()->where(['_id' => $_POST['comment_id']])->one();
                $notification->user_id = "$uid";
                $notification->post_id = $comment_details['post_id'];
                $notification->notification_type = 'likecomment';
                $notification->is_deleted = '0';
                $notification->status = '1';
                $notification->created_date = "$date";
                $notification->updated_date = "$date";
                $notification->post_owner_id = $comment_details['user_id'];
                if($comment_details['user_id'] != "$uid")
                {
                    $notification->insert();
                }
            }
        }
        
        $likes = Like::find()->where(['comment_id' => $comment_id ,'status' => '1'])->all();

        $user_ids = ArrayHelper::map(Like::find()->select(['user_id'])->where(['comment_id' => "$comment_id", 'like_type' => 'comment', 'status' => '1'])->orderBy(['updated_date'=>SORT_DESC])->all(), 'user_id', 'user_id');
        if(!empty($user_ids)) {
            $array = "'".implode("','",$user_ids)."'";
            $array = explode(",", $array);
        }

        $comlikeuseinfo = UserForm::find()
        ->select(['_id','fname', 'lname'])
        ->asArray()
        ->where(['in','_id', $user_ids])
        ->all();

        $usrbox = array();
        foreach ($comlikeuseinfo as $key => $single) {
            $fullnm = $single['fname'] . ' ' . $single['lname'];
            $usrbox[(string)$single['_id']] = $fullnm; 
        }

        $data['like_buddies'] = implode("<br/>", $usrbox);
        $data['like_count'] = count($likes);
        return json_encode($data);
    }       
}
