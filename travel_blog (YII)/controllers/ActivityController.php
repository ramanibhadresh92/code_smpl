<?php
namespace frontend\controllers;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\mongodb\ActiveRecord;
use frontend\models\Notification;
use frontend\models\Page;
use frontend\models\PostForm;
use frontend\models\Referal;
use frontend\models\Comment;
use frontend\models\Like;
use frontend\models\UserPhotos;

$mark = Yii::$app->getUrlManager()->getBaseUrl();

class ActivityController extends Controller
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
     
  
    public function actionIndex()
    { 
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		
		$uid = $_POST['wall_user_id'];
		$baseUrl = $_POST['baseUrl'];
		
		$notificationconnectaccepted = Notification::find()->where(['notification_type'=>'connectrequestaccepted','from_connect_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->all();
		
		$notificationconnectacceptedfrd = Notification::find()->where(['notification_type'=>'connectrequestaccepted','user_id'=>(string)$uid])->andwhere(['is_deleted'=>"0"])->all();
		
		$createpost = PostForm::find()->with('user')->where(['post_user_id'=>"$uid",'is_deleted'=>'0','rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'is_coverpic'=>null,'is_profilepic'=>null])->all();
		
		$likes = Like::find()->where(['user_id' => "$uid",'status' => '1'])->all();
		
		$createpage = Page::getMyPages($uid);
		$tripexp = PostForm::find()->where(['is_trip'=>"1",'is_deleted'=>"0",'post_user_id'=>"$uid"])->all();
		
		$referal = Referal::find()->where(['sender_id'=>"$uid",'is_deleted'=>"1"])->all();
		
		$album = array();
		
		$comment = array();
		
		$profilepic = PostForm::find()->where(['is_profilepic'=>"1",'is_deleted'=>"0",'post_user_id'=>"$uid"])->all();
		
		$coverpic = PostForm::find()->where(['is_coverpic'=>"1",'is_deleted'=>"0",'post_user_id'=>"$uid"])->all();
		
		$travstore = array();
		
		
		$activities = array_merge_recursive($notificationconnectaccepted, $notificationconnectacceptedfrd, $createpost, $likes, $createpage, $tripexp, $referal, $album ,$comment, $profilepic, $coverpic, $travstore);
		foreach ($activities as $key)
		{
			if(isset($key["post_created_date"]))
			{
				$created_time = $key['post_created_date'];
			}
			else if(isset($key["created_at"]))
			{
				$created_time = $key['created_at'];
			}
			else
			{
				$created_time = $key["created_date"];
			}
			 
			$sortkeys[] = $created_time; 
		}
		
		if(count($activities))
		{
			array_multisort($sortkeys, SORT_DESC, SORT_STRING, $activities);
		}
		return $this->render('index',array('baseUrl' => $baseUrl, 'user_id'=>$uid, 'activities'=> $activities));
    }
	
	public function actionPostdisplay()
	{
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		$existing_posts = '1';
		$cls = '';
		
		$post_id = isset($_POST['post_id']) ? $_POST['post_id'] : '';
		if($post_id != '') {
			$post = PostForm::find()->where([(string)'_id' => $post_id])->one();
			if(!empty($post)) {
				$postid = (string)$post['_id'];
				$postownerid = (string)$post['post_user_id'];
				$postprivacy = $post['post_privacy'];
				$isOk = $this->filterDisplayLastPost($postid, $postownerid, $postprivacy);
				if($isOk == 'ok2389Ko') {
					$this->display_last_post($postid,$existing_posts, '', $cls);
				}
			}
		}
	}
	
	public function actionReferaldisplay()
	{
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		
		$post_id = $_POST['post_id'];
		$existing_posts = '1';
		$cls = '';
		$this->display_last_referal($post_id,$existing_posts, '', $cls);
	}
}	
  