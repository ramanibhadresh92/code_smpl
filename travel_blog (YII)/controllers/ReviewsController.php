<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\HtmlPurifier;
use yii\filters\AccessControl;
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
use frontend\models\PlaceReview; 
use frontend\models\Notification; 
use frontend\models\SecuritySetting; 
class ReviewsController extends Controller
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
        $uid = (string)$session->get('user_id');
        /*if(isset($uid) && !empty($uid))
        {
            $result = LoginForm::find()->select(['city'])->where(['_id' => $uid])->asarray()->one();
            
            if(!(isset($result['city']) && !empty($result['city'])))
            {
                $url = Yii::$app->urlManager->createUrl(['site/complete-profile']);
                Yii::$app->getResponse()->redirect($url);
            }
        }*/

        if(isset($uid) && $uid != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($uid);
        } else {
            $checkuserauthclass = 'checkuserauthclassg';
        } 
        
        $place = Yii::$app->params['place'];
        $placetitle = Yii::$app->params['placetitle'];
        $placefirst = Yii::$app->params['placefirst'];
        $lat = Yii::$app->params['lat'];
        $lng = Yii::$app->params['lng'];

        $getpplacereviews = PlaceReview::getPlaceReviews($place,'reviews','all');
        $getpplacereviewscount = PlaceReview::getPlaceReviewsCount($place,'reviews');
        if($getpplacereviewscount == 0) {
            $getpplacereviewscountt = 1;
        } else {
            $getpplacereviewscountt = $getpplacereviewscount;
        }

        $totalcnt = PlaceReview::find()->where(['is_deleted'=>"0",'currentlocation'=>"$place",'placetype'=>'reviews'])->andWhere(['not','flagger', "yes"])->all();
        $sum = 0;
        foreach($totalcnt as $totalcnts)
        {
            $sum += $totalcnts['placereview'];
        }
        $avgcnt = ceil($sum / $getpplacereviewscountt);
        return $this->render('index',array('place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'getpplacereviews' => $getpplacereviews,'getpplacereviewscount' => $getpplacereviewscount,'avgcnt' => $avgcnt, 'checkuserauthclass' => $checkuserauthclass,'lat' => $lat,'lng' => $lng));
    }

    public function actionAddPlaceReview() {
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
                $text = isset($_POST['test']) ? $_POST['test'] : '';
                 
                $purifier = new HtmlPurifier(); 
                $text = HtmlPurifier::process($text);
                $post = new PlaceReview();
                $post->post_status = '1';
                $post->post_text = ucfirst($text);
                $post->post_type = 'text';
                $post->post_created_date = "$date";
                $post->post_user_id = "$user_id";

                if(isset($_POST['current_location']) && !empty($_POST['current_location']) && $_POST['current_location']!='undefined')
                {
                    $post->currentlocation = $_POST['current_location'];
                }

                $post->custom_share = (isset($_POST['sharewith']) && !empty($_POST['sharewith'])) ? $_POST['sharewith'] : '';
                $post->custom_notshare = (isset($_POST['sharenot']) && !empty($_POST['sharenot'])) ? $_POST['sharenot'] : '';
                $post->anyone_tag = (isset($_POST['customchk']) && !empty($_POST['customchk'])) ? $_POST['customchk'] : '';
                $post->post_tags = (isset($_POST['posttags']) && !empty($_POST['posttags'])) ? $_POST['posttags'] : '';    
                $post->post_title = isset($_POST['title']) ? ucfirst($_POST['title']) : '';
                $post->post_title = ucfirst($_POST['title']);
                $post->share_setting = $_POST['share_setting'];
                $post->comment_setting = $_POST['comment_setting'];
                $post->post_privacy = $_POST['post_privacy'];
                $post->customids = $_POST['custom'];
                $post->is_deleted = "$post_status";
                $post->post_ip = $_SERVER['REMOTE_ADDR'];
                $post->placetype = 'reviews';
                $post->placetitlepost = isset($_POST['placetitlepost']) ? $_POST['placetitlepost'] : '';
                if($_POST['placereview'] > 5){$_POST['placereview'] = '5';}
                else if($_POST['placereview'] < 1){$_POST['placereview'] = '1';}
                else{$_POST['placereview'] = $_POST['placereview'];}
                $post->placereview = (int)$_POST['placereview'];
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
                
                if(isset($_POST['posttags']) && $_POST['posttags'] != 'null')
                {
                    // Insert record in notification table also
                    $tag_connections = explode(',',$_POST['posttags']);
                    $tag_count = count($tag_connections);
                    for ($i = 0; $i < $tag_count; $i++)
                    {
                        $result_security = SecuritySetting::find()->where(['user_id' => "$tag_connections[$i]"])->one();
                        if ($result_security)
                        {
                            $tag_review_setting = $result_security['review_posts'];
                        }
                        else
                        {
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
                        $notification->insert();
                    }
                }
                if($post_status == '0')
                {
                    $this->display_last_place_review($last_insert_id,'from_save','','reviewpost-holder bborder ');
                } 
                else
                {
                    if($page_details['gen_post_review'] == 'on' && $page_details['created_by'] != $user_id)
                    {
                        $this->display_review_message();
                    }
                    else
                    {
                        $this->display_last_place_review($last_insert_id,'from_save','','reviewpost-holder bborder ');
                    }
                }
            }
        } else {
            return 'checkuserauthclassg';
        }
    }

    public function actionDeletePlaceReview() 
    { 
        return PlaceReview::DeletePlaceReviewCleanUp($_POST['pid'],$_POST['post_user_id']);
    }

    public function actionEditPlaceReview() 
    {
        $pid = isset($_POST['pid']) ? $_POST['pid'] : '';
        if ($pid != '') 
        {
            $date = time(); 
            $update = PlaceReview::find()->where(['_id' => $pid])->andWhere(['not','flagger', "yes"])->one();
            $text = $update['post_text'];
            
            if(isset($_POST['current_location']) && !empty($_POST['current_location']) && $_POST['current_location']!='undefined')
            {
                $update->currentlocation = $_POST['current_location'];
            }
            $update->post_created_date = "$date";

            $update->custom_share = (isset($_POST['sharewith']) && !empty($_POST['sharewith'])) ? $_POST['sharewith'] : '';
            $update->custom_notshare = (isset($_POST['sharenot']) && !empty($_POST['sharenot'])) ? $_POST['sharenot'] : '';
            $update->anyone_tag = (isset($_POST['customchk']) && !empty($_POST['customchk'])) ? $_POST['customchk'] : '';
            $update->post_tags = (isset($_POST['posttags']) && !empty($_POST['posttags'])) ? $_POST['posttags'] : '';

            $update->post_title = ucfirst($_POST['title']);
            $update->share_setting = $_POST['share_setting'];
            $update->comment_setting = $_POST['comment_setting'];
            $update->post_privacy = $_POST['post_privacy'];
            $update->customids = $_POST['custom'];

            if (trim($_POST['link_description']) != '' && trim($_POST['link_description']) != 'undefined')
            {
                $title = $_POST['link_title'];
                $description = $_POST['link_description'];
                $image = $_POST['link_image'];
                $url = $_POST['link_url'];

                $update->post_type = 'link';
                $update->link_title = ucfirst($title);
                $update->image = $image;
                $update->post_text = ucfirst($url);
                $update->link_description = $description;
            } else {
                if (isset($_POST['test']) && !empty($_POST['test'])) {
                    $update->post_text = ucfirst($_POST['test']);
                    $update->post_type = 'text';
                }
            }
            
            if(isset($_POST['placereview']) && !empty($_POST['placereview']))
            {
                if($_POST['placereview'] > 5)
                {
                    $_POST['placereview'] = '5';
                }
                else if($_POST['placereview'] < 1)
                {
                    $_POST['placereview'] = '1';
                }
                else
                {
                    $_POST['placereview'] = $_POST['placereview'];
                }
                $update->placereview = $_POST['placereview'];
            }

            $update->update();

           
            $last_insert_id = $pid;
            $this->display_last_place_review($last_insert_id,'from_save','','reviewpost-holder bborder ');
            
            if($update['is_deleted'] == '2')
            {
                $post_flager_id = $update['post_flager_id'];
                /* Insert Notification For The Owner of Post For Flagging*/
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
            
        } 
        else 
        {
            return "0";
        }
    }
    
    public function actionComposenewreviewpopup() 
    {
        return $this->render('/layouts/placereviewpost');
    }    
}
?>