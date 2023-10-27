<?php
namespace frontend\controllers;

use Yii;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\filters\VerbFilter;
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
use frontend\models\Camping;
use frontend\models\CampingReview;
use frontend\models\Notification;

class CampingController extends Controller
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
        return $this->render('index', array('checkuserauthclass' => $checkuserauthclass));
    }

    public function actionDetail()  
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($_GET['id'])) {
            $id = $_GET['id'];
            if(isset($user_id) && $user_id != '') {
                $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            } else {
                $checkuserauthclass = 'checkuserauthclassg';
            }
            return $this->render('detail', array('checkuserauthclass' => $checkuserauthclass, 'id' => $id));
        }
    }

    public function actionCreatecamping() {
        
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
        } else {
            $checkuserauthclass = 'checkuserauthclassg';
        }  
        
        return $this->render('createcamping', array('checkuserauthclass' => $checkuserauthclass));
    }

    public function actionEditcamping() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                if(isset($_POST['id']) && $_POST['id'] != '') { 
                    $id = $_POST['id'];
                    return $this->render('editcamping', array('checkuserauthclass' => $checkuserauthclass, 'id' => $id));
                }
            }
        }
    }

    public function actionCreatecampingsave() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
            $post = $_POST;
            $files = $_FILES;
            return Camping::createcamping($post, $files, $user_id);
        }

        $result = array('status' => false);
        return json_encode($result, true);
        exit;
    }

    public function actionEditcampingsave() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
            $post = $_POST;
            $files = $_FILES;
            return Camping::editcamping($post, $files, $user_id);
        }

        $result = array('status' => false);
        return json_encode($result, true);
        exit;
    }

    public function actionRemovepic()  
    { 
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($_POST['$id']) && $_POST['$id'] != '') {
            if(isset($_POST['$src']) && $_POST['$src'] != '') {
                $id = $_POST['$id'];
                $src = $_POST['$src'];
                $data = Camping::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();

                if(!empty($data)) {
                    $images = $data->images;
                    $images = explode(',', $images);
                    $pos = array_search($src, $images);
                    unset($images[$pos]);

                    $images = implode(',', $images);
                    $data->images = $images;
                    $data->update();
                    $result = array('success' => true);
                    return json_encode($result, true);
                }
            }
        }

        $result = array('success' => false);
        return json_encode($result, true);
    }

    public function actionReview() 
    {
        return $this->render('reviewblock');
    }    
 
    public function actionAddreview() {
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
                
                $url = $_SERVER['HTTP_REFERER'];
                $urls = explode('&',$url);
                $url = explode('=',$urls[1]);
                $post_id = $url[1];

                $purifier = new HtmlPurifier(); 
                $text = HtmlPurifier::process($text);
                $post = new CampingReview();
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
                $post->post_id = $post_id;
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
                
                $lasting = CampingReview::find()->where([(string)'_id' => $last_insert_id])->one();
                if(!empty($lasting)) {
                    $reviews_s_userid = $lasting['post_user_id'];
                    $reviews_s_profile = $this->getuserdata($reviews_s_userid,'thumbnail');
                    $reviews_s_username = $this->getuserdata($reviews_s_userid,'fullname');
                    $reviews_s_review = $lasting['placereview'];
                    $reviews_s_desc = $lasting['post_text'];
                    $reviews_s_date = $lasting['post_created_date'];
                    $reviews_s_date = date('MM d, YYYY', $reviews_s_date);
                    ?>
                    <li class="collection-item avatar">
                       <img src="profile/<?=$reviews_s_profile?>" alt="" class="circle">
                       <span class="title"><?=$reviews_s_username?></span>
                       <span class="ratings">
                            <?php if($reviews_s_review == 1) { ?>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star unfill"></i>
                                <i class="mdi mdi-star unfill"></i>
                                <i class="mdi mdi-star unfill"></i>
                                <i class="mdi mdi-star unfill"></i>
                            <?php } else if($reviews_s_review == 2) { ?>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star unfill"></i>
                                <i class="mdi mdi-star unfill"></i>
                                <i class="mdi mdi-star unfill"></i>
                            <?php } else if($reviews_s_review == 3) { ?>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star unfill"></i>
                                <i class="mdi mdi-star unfill"></i>
                            <?php } else if($reviews_s_review == 4) { ?>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star unfill"></i>
                            <?php } else if($reviews_s_review == 5) { ?>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                                <i class="mdi mdi-star"></i>
                            <?php } ?>
                       </span>
                       <p class="date"><?=$reviews_s_date?></p>
                       <p><?=$reviews_s_desc?></p>
                    </li>
                    <?php
                }
            }
        } else {
            return 'checkuserauthclassg';
        }
    }

    public function actionUploadphotoscampingsave() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST) && !empty($_POST)) {
                    $post = $_POST;
                    $files = $_FILES;
                    $result = Camping::uploadphotoscampingsave($post, $files, $user_id);
                    return $result;
                    exit;
                }
            }
        }
    }

    public function actionUploadphotoscamping() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                if(isset($_POST['id']) && $_POST['id'] != '') { 
                    $id = $_POST['id'];
                    return $this->render('uploadphotoscamping', array('checkuserauthclass' => $checkuserauthclass, 'id' => $id));
                }
            }
        }
    }

    public function actionDelete()  
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
        } else {
            $checkuserauthclass = 'checkuserauthclassg';
        }

        if(isset($_POST['nid']) && $_POST['nid']) {
            $id = $_POST['nid'];
            $post = Camping::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();
            if(!empty($post)) {
                $post->delete();
                $result = array('status' => true);
                return json_encode($result, true);
            }
        }

        $result = array('status' => false);
        return json_encode($result, true);
    }
}
?>