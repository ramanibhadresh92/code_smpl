<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\HtmlPurifier;
use yii\mongodb\ActiveRecord;
use frontend\models\LoginForm;
use frontend\models\PostForm;
use frontend\models\Personalinfo;
use frontend\models\UserForm;
use frontend\models\Vip;
use frontend\models\TravAdsVisitors;
use frontend\models\Order;
use frontend\models\UserMoney;
use frontend\models\Destination;
use frontend\models\PlaceVisitor;
use frontend\models\PlaceAsk;
use frontend\models\Notification;
use frontend\models\SecuritySetting;
use frontend\models\Page;

class QuestionsController extends Controller
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
        
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id); 
        } else {
            $checkuserauthclass = 'checkuserauthclassg';
        }

        $place = Yii::$app->params['place'];
        $placetitle = Yii::$app->params['placetitle'];
        $placefirst = Yii::$app->params['placefirst'];
        $lat = Yii::$app->params['lat'];
        $lng = Yii::$app->params['lng'];
        
        $count = 'all';
        $getpplacereviews = PlaceAsk::getPlaceReviews($place,'ask',$count);
        $getpplacereviewscount = PlaceAsk::getPlaceReviewsCount($place,'ask');
        return $this->render('index',array('checkuserauthclass' => $checkuserauthclass, 'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'getpplacereviews'=> $getpplacereviews,'getpplacereviewscount'=> $getpplacereviewscount,'lat' => $lat,'lng' => $lng));
    }

    public function actionComposenewask() 
    {
        return $this->render('/layouts/placeask');
    }

    public function actionAddAsk()
    {
        $this->layout = 'ajax_layout';
        $session = Yii::$app->session;
        $email = $session->get('email');
        $userid = $user_id = (string)$session->get('user_id');
        
        if(isset($userid) && $userid != '') {
            $authstatus = UserForm::isUserExistByUid($userid);
            if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
                $data['auth'] = $authstatus;
                return $authstatus;
            } else {
                $result = LoginForm::find()->where(['_id' => $userid])->one();
                $date = time();
                $post_status = '0';
                if (empty($_POST['test'])) {
                    $_POST['test'] = ''; 
                }
                if (empty($_POST['title'])) {
                    $_POST['title'] = ''; 
                }
                
                $purifier = new HtmlPurifier();
                $text = HtmlPurifier::process($_POST['test']);
                $post = new PlaceAsk();

                $post->post_status = '1';       
                $post->post_text = ucfirst($text);
                $post->post_type = 'text';
                $post->post_created_date = "$date";
                $post->post_user_id = "$user_id";

                if(isset($_POST['current_location']) && !empty($_POST['current_location']) && $_POST['current_location']!='undefined') {
                    $post->currentlocation = $_POST['current_location'];
                }

                $post->custom_share = (isset($_POST['sharewith']) && !empty($_POST['sharewith'])) ? $_POST['sharewith'] : '';
                $post->custom_notshare = (isset($_POST['sharenot']) && !empty($_POST['sharenot'])) ? $_POST['sharenot'] : '';
                $post->anyone_tag = (isset($_POST['customchk']) && !empty($_POST['customchk'])) ? $_POST['customchk'] : '';
                $post->post_tags = (isset($_POST['posttags']) && !empty($_POST['posttags'])) ? $_POST['posttags'] : '';
            
                $post->post_title = isset($_POST['title']) ? ucfirst($_POST['title']) : '';
                $post->is_deleted = "$post_status";
                $post->post_ip = $_SERVER['REMOTE_ADDR'];
                $post->share_setting = 'Disable';
                $post->comment_setting = 'Enable';
                $post->post_privacy = 'Public';
                $post->placetype = 'ask';
                $post->insert();

                $last_insert_id =  $post->_id;
                
                // Insert record in notification table also
                $notification =  new Notification();
                $notification->post_id =   "$last_insert_id";
                $notification->user_id = "$user_id";
                $notification->notification_type = 'post';
                $notification->is_deleted = '0';
                $notification->created_date = "$date";
                $notification->updated_date = "$date";
                $notification->insert();
                
                if(isset($_POST['posttags']) && $_POST['posttags'] != 'null') {
                    $tag_connections = explode(',',$_POST['posttags']);
                    $tag_count = count($tag_connections);
                    for ($i = 0; $i < $tag_count; $i++) {
                        $result_security = SecuritySetting::find()->where(['user_id' => "$tag_connections[$i]"])->one();
                        if ($result_security) {
                            $tag_review_setting = $result_security['review_posts'];
                        } else {
                            $tag_review_setting = 'Disabled';
                        }
                        $notification =  new Notification();
                        $notification->post_id =   "$last_insert_id";
                        $notification->user_id = $tag_connections[$i];
                        $notification->notification_type = 'tag_connect';
                        $notification->review_setting = $tag_review_setting;
                        $notification->is_deleted = '0';
                        $notification->status = '1';
                        $notification->created_date = "$date";
                        $notification->updated_date = "$date";
                        if($_POST['pagename'] != "collection" && $_POST['pagename'] != "page" && $_POST['pagename'] != "travstore" && $_POST['pagename'] != "tripexperience")
                        {
                            $notification->insert();
                        }
                    }
                }
            
                $this->display_last_ask($last_insert_id);
            }
        } else {
            return 'checkuserauthclassg';
        }
    }

    public function actionEditAsk() 
    {
        $pid = isset($_POST['pid']) ? $_POST['pid'] : '';
        if ($pid != '') {
            $date = time();
            $update = new PlaceAsk();
            $update = PlaceAsk::find()->where(['_id' => $pid])->andWhere(['not','flagger', "yes"])->one();
            $text = $update['post_text'];
            $image = $update['image'];
            $update->post_type = 'text';
            $update->post_title = isset($_POST['title']) ? ucfirst($_POST['title']) : '';
            $update->post_text = isset($_POST['desc']) ? ucfirst($_POST['desc']) : '';
            $update->post_tags = isset($_POST['posttags']) ? $_POST['posttags'] : '';
            $update->post_privacy = 'Public';   
            $update->currentlocation = isset($_POST['edit_current_location']) ? $_POST['edit_current_location'] : '';
            $update->share_setting = 'Disable';
            $update->comment_setting = 'Enable';
            $update->post_created_date = "$date";
            $update->update();
            if($update['pagepost'] == '1')
            {
                $page_details = Page::Pagedetails($update['post_user_id']);
                if($page_details['not_post_edited'] == 'on')
                {
                    Notification::updateAll(['post_created_date' => "$date"], ['post_id' => $pid]);
                }
            }
            $last_insert_id = $pid;
            $this->display_last_ask($last_insert_id);
            
            if($update['is_deleted'] == '2')
            {
                $post_flager_id = $update['post_flager_id'];
                $notification =  new Notification();
                $notification->post_id = "$pid";
                $notification->user_id = "$post_flager_id";
                $notification->notification_type = 'editpostuser';
                $notification->is_deleted = '0';
                $notification->status = '1';
                $notification->created_date = "$date";
                $notification->updated_date = "$date";
                $notification->insert();
            }    
            
        } else {
            return "0";
        }
    }

    public function actionDeleteAsk() 
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');   
        if(isset($user_id) && $user_id != '') {
            return PlaceAsk::DeleteAskCleanUp($_POST['pid'],$_POST['post_user_id']);
        }
    }
}
?>