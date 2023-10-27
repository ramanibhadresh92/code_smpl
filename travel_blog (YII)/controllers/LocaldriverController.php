<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\HtmlPurifier;
use yii\filters\AccessControl;
use yii\mongodb\ActiveRecord;
use frontend\models\UserForm;
use frontend\models\HangoutEvent; 
use frontend\models\LocaldriverPost;
use frontend\models\LocaldriverPostInvite;
use frontend\models\LocaldriverPostInviteMsgs;  
use frontend\models\Language;  
use frontend\models\TravelSavePost;
use backend\models\LocaldriverActivity;
use frontend\models\Personalinfo;
use frontend\models\Education;
use frontend\models\Interests;
use frontend\models\Occupation;
use frontend\models\LocaldriverReview;
use frontend\models\LoginForm;
use frontend\models\Notification;
use frontend\models\SecuritySetting;

class LocaldriverController extends Controller
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

    public function actionIndex() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id'); 

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
        } else {
            $checkuserauthclass = 'checkuserauthclassg';
        }  

        $posts = LocaldriverPost::recentLocaldriverPosts($user_id);
        $information = LocaldriverActivity::getallactivity();
        
        return $this->render('index', array('information' => $information, 'checkuserauthclass' => $checkuserauthclass, 'posts' => $posts));
    }

    public function actionDetail() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id'); 

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
        } else {
            $checkuserauthclass = 'checkuserauthclassg';
        }  

        if(isset($_GET['id']) && $_GET['id'] != '') {
            $id = $_GET['id'];
            return $this->render('detail', array('id' => $id, 'checkuserauthclass' => $checkuserauthclass));
        } else {

        }

    }

    public function actionCreatepost() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST) && !empty($_POST)) {
                    $post = $_POST;
                    $files = $_FILES;
                    $result = LocaldriverPost::createpost($post, $files, $user_id);
                    return $result;
                    exit;
                } 
            }
        }
    }

    public function actionEditpost() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST) && !empty($_POST)) {
                    $post = $_POST;
                    $files = $_FILES;
                    $result = LocaldriverPost::editpost($post, $files, $user_id);
                    return $result;
                    exit;
                }
            }
        }
    }

    public function actionUploadphotoslocaldriversave() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST) && !empty($_POST)) {
                    $post = $_POST;
                    $files = $_FILES;
                    $result = LocaldriverPost::uploadphotoslocaldriversave($post, $files, $user_id);
                    return $result;
                    exit;
                }
            }
        }
    }
 
 
    // Recent Section Area4
    public function actionRecentLocaldriverPosts() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        $lazyhelpcount = isset($_POST['$lazyhelpcount']) ? $_POST['$lazyhelpcount'] : 0;
        $start = $lazyhelpcount * 12;
        
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            $posts = LocaldriverPost::recentLocaldriverPosts($user_id,$start);
        } else {
            $checkuserauthclass = 'checkuserauthclassg';
            $posts = LocaldriverPost::recentLocaldriverPosts($start);
        }
        
        $posts = json_decode($posts, true);
        
        return $this->render('recent', array('posts' => $posts, 'checkuserauthclass' => $checkuserauthclass, 'lazyhelpcount' => $lazyhelpcount));
    }

    public function actionSelectedRecord() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($_POST['$id']) && $_POST['$id'] != '') {
            if(isset($_POST['$address']) && $_POST['$address'] != '') {
                $id = trim($_POST['$id']);
                $address = $_POST['$address'];

                if(isset($user_id) && $user_id != '') {
                    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
                    $posts = LocaldriverPost::selectedrecord($id, $address, $user_id);
                } else {
                    $checkuserauthclass = 'checkuserauthclassg';
                    $posts = LocaldriverPost::selectedrecord($id, $address);
                }
                
                $posts = json_decode($posts, true);
                if(!empty($posts)) {
                    return $this->render('recent', array('posts' => $posts, 'checkuserauthclass' => $checkuserauthclass));
                }
            }
        }
    }

    public function actionMyposts() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $posts = array();
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                $posts = LocaldriverPost::myposts($user_id);
                $posts = json_decode($posts, true); 
            }
        } else {
            $checkuserauthclass = 'checkuserauthclassg';
        }
        
        return $this->render('myposts', array('posts' => $posts, 'checkuserauthclass' => $checkuserauthclass));
    }
 
    public function actionEditpostpopupopen() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
         
        $tripinfo = array();
        $information = array();

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                if(isset($_POST['id']) && $_POST['id'] != '') { 


                    $id = (string)$_POST['id']; 
                    $post = LocaldriverPost::gettrip($id, $user_id);
                    $post = json_decode($post, true);
                    
                    $information = LocaldriverActivity::getallactivity();
                    $information = json_decode($information, true);

                    $languages = Language::languages();
                }
            }
        }

        return $this->render('edit_post_popup', array('post'=>$post, 'information' => $information, 'languages' => $languages)); 
    }

    public function actionUploadphotoslocaldriver() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
         
        $tripinfo = array();
        $information = array();

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                if(isset($_POST['id']) && $_POST['id'] != '') { 


                    $id = (string)$_POST['id']; 
                    $post = LocaldriverPost::gettrip($id, $user_id);
                    $post = json_decode($post, true);
                    
                    $information = LocaldriverActivity::getallactivity();
                    $information = json_decode($information, true);

                    $languages = Language::languages();
                }
            }
        }

        return $this->render('uploadphotoslocaldriver', array('post'=>$post, 'information' => $information, 'languages' => $languages)); 
    }

    public function actionSearch() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($_POST) && !empty($_POST)) {
            $post = $_POST;
            if(isset($user_id) && $user_id != '') {
                $checkuserauthclass = UserForm::isUserExistByUid($user_id);
                $posts = LocaldriverPost::search($post, $user_id);
            } else {
                $checkuserauthclass = 'checkuserauthclassg';
                $posts = LocaldriverPost::search($post);
            }
        }
        
        $posts = json_decode($posts, true);
        
        return $this->render('searchdata', array('posts' => $posts, 'checkuserauthclass' => $checkuserauthclass));
    }    

    public function actionSendInvitePost() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST) && !empty($_POST)) {
                    $post = $_POST;
                    $sendpost = LocaldriverPostInvite::sendinvitepost($post, $user_id);
                    return $sendpost;
                    exit;
                }
            }
        }
    }

    public function actionSavePost() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
    
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST['$id']) && $_POST['$id'] != '') {
                    $id = $_POST['$id'];
                    $result = LocaldriverPost::savepost($id, $user_id);
                    return $result;
                    exit;
                }
            }
        }
    }

    public function actionSaveEvent() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                if(isset($_POST['$id']) && $_POST['$id'] != '') { 
                    $id = $_POST['$id'];
                    $result = TravelSavePost::localdriversaveevent($id, $user_id);
                    return $result;
                    exit;
                }
            }
        }
    }



    public function actionUnsaveEvent() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
      
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST['$id']) && $_POST['$id'] != '') {
                    $id = $_POST['$id'];
                    $result = HangoutEvent::unsaveevent($id, $user_id);
                    return $result;
                    exit;
                }
            }
        }
    }

    public function actionDeletePost() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST['$id']) && $_POST['$id'] != '') {
                    $id = $_POST['$id'];
                    $result = LocaldriverPost::deletepost($id, $user_id);
                    return $result;
                    exit;
                }
            }
        }
    } 

    public function actionBlockUserPost() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST['$id']) && $_POST['$id'] != '') {
                    $id = $_POST['$id'];
                    $result = LocaldriverPost::blockuserpost($id, $user_id);
                    return $result;
                    exit;
                }
            }
        }
    }   

    public function actionUnsavePost() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST['$id']) && $_POST['$id'] != '') {
                    $id = $_POST['$id'];
                    $result = LocaldriverPost::unsavepost($id, $user_id);
                    return $result;
                    exit;
                }
            }
        }
    }

     // Offer Tab Functions......................
    public function actionRequestsList() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array();
        $checkuserauthclass = 'checkuserauthclassg';
        
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
       
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                $model = new \frontend\models\LocaldriverPostInvite(); 
                $invitors = $model->requestslist($user_id);
                $invitors = json_decode($invitors, true);
                $daysArray = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
         
                if(!empty($invitors)) {
                    foreach($invitors as $key => $value) {   
                        $idArray = array_values($value['_id']);
                        $invitationId = $idArray[0];
                        //$invitationId = (string)$value['_id']['$id'];
                        $uniqid = rand(9999, 99999).$invitationId;
                        $inviteUid = $value['user_id'];
                        $post_id = $value['post_id'];
                        $today = time();
                        $created_at = isset($value['is_last']['created_at']) ? $value['is_last']['created_at'] : $value['created_at'];
                        $time = date('H:i A', $created_at);
                        $tempDate = date('Y-m-d', $created_at);
                        $tempDate = strtotime($tempDate);
                        $datediff = $today - $tempDate;
                        $days = floor($datediff / (60 * 60 * 24));
                        if($days <7) {
                            if($days ==0) {
                                $printdate = 'Today';
                            } else if($days == 1){
                                $printdate = 'Yesterday';
                            } else {
                                $weekday = date('w', $created_at);
                                $printdate = $daysArray[$weekday];
                            } 
                        } else {
                            if($days == 30) {
                                $printdate = 'about a month ago';
                            } elseif($days == 365) {
                                $printdate = 'about a year ago';
                            } else {
                                $printdate = date('d M Y', $created_at);
                            }
                        }

                        $fullname = $value['userinfo']['fullname'];           
                        $country = $value['userinfo']['country'];           
                        $photo = $value['userinfo']['profile'];
                        

                        $is_sent = '';
                        $is_read = '';
                        $is_last = json_decode($value['is_last'], true);
                        $onclick = 'onclick="invitationisread(\''.$invitationId.'\', this)"';
                        if(!empty($is_last)) {
                            $inviteUid = $is_last['from_id'];
                            if($inviteUid == $user_id) {
                                $is_sent = 'sent';
                            }
                            if(isset($is_last['is_read']) && $is_last['is_read'] == true) {
                                $is_read = 'read';
                            }
                            $msg = $is_last['message'];
                        } else if($inviteUid == $user_id) {
                            $onclick = '';
                            $is_sent = 'sent';
                            $is_read = 'read';
                            $msg = $value['message'];
                        } else {
                            $is_read = 'read';
                            $msg = $value['message'];
                        }

                        $arrival_date = '';
                        $departure_date = '';
                        $datelabel = '';
                        if(isset($value['post_info'])) {
                            $arrival_date = $value['post_info']['arrival_date'];
                            $departure_date = $value['post_info']['departure_date'];
                            $datelabel = $arrival_date . ' to ' . $departure_date;
                        }
                        $label = ($inviteUid == $user_id) ? 'You send request for <b>guide</b>' : 'You receive request for <b>guide</b>';
                        $currentHtml = '<li class="postrequest_'.$invitationId.' '.$is_sent.' '. $is_read.'" '.$onclick.'>
                            <div class="person-holder">
                                <img src="'.$photo.'">
                                <div class="person-info">
                                    <h6>'.$fullname.'</h6>
                                    <span>'.$country.'</span>
                                </div>
                            </div>
                            <div class="msg-holder">
                                <span class="trip-duration">
                                    <span>'.$label.'</span>
                                    <span class="tripdates">'.$datelabel.'</span>
                                    <div class="settings-icon">
                                        <a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="'.$uniqid.'">
                                            <i class="zmdi zmdi-more"></i>
                                        </a>
                                        <ul id="'.$uniqid.'" class="dropdown-content custom_dropdown">
                                            <li><a href="javascript:void(0)" onclick="deleteofferlocaldriver(\''.$invitationId.'\', this)">Delete this request</a></li>
                                            <li><a href="javascript:void(0)" onclick="actionBlockUserforPost(\''.$post_id.'\')">Block this person</a></li>
                                            <li onclick="localdriverreporttoabuse(\''.$invitationId.'\')"><a href="javascript:void(0)">Report this request</a></li>
                                        </ul>
                                    </div>
                                </span>
                                <a class="offer-holder" href="javascript:void(0)" onclick="openOffer(\''.$invitationId.'\', this)">
                                    <div class="msg-bbl">
                                        <p>'.$msg.'</p>
                                    </div>
                                </a>
                                <span class="timestamp">'.$printdate .'</span>
                            </div>
                        </li>';
                        $result[] = $currentHtml;
                    }
                }
            }
        }

        return json_encode($result);
        exit;
    }

    public function actionInvitationDetail() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                $resultSet = array('status' => false);

                if(isset($_POST['$id']) && !empty($_POST['$id'])) {
                    $invitationId = $_POST['$id'];
                    $model = new \frontend\models\LocaldriverPostInvite();
                    $invitor = $model->invitationdetail($invitationId, $user_id);
                    $invitor = json_decode($invitor, true);
                    $result = array();
                    $daysArray = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
                    if(!empty($invitor)) {
                        $idArray = array_values($invitor['_id']);
                        $invitationId = $idArray[0];
                        //$invitationId = (string)$invitor['_id']['$id'];
                        $profilepic = $invitor['userinfo']['photo'];
                        $country = isset($invitor['userinfo']['country']) ? $invitor['userinfo']['country'] : '';
                        $birth_date = isset($invitor['userinfo']['birth_date']) ? $invitor['userinfo']['birth_date'] : '';
                        $age = 0;
                        if($birth_date != '') {
                            $birth_split = explode('-', $birth_date);
                            if(!empty($birth_split)) {
                                $byear = isset($birth_split[2]) ? $birth_split[2] : '';
                                if($byear != '') {
                                    $currentyear = date("Y");
                                    $age = $currentyear - $byear;
                                } 
                            }
                        }   
                        $gender = isset($invitor['userinfo']['gender']) ? $invitor['userinfo']['gender'] : '';
                         $languages = isset($invitor['userinfo']['language']) ? $invitor['userinfo']['language'] : '';
                        $languages = explode(',', $languages);
                        $languages2 = $languages;
                        unset($languages2[0]);
                        $languagesString = implode('<br/>', $languages2);
                        $languagesLabel = '';
                        if(!empty($languages)) {
                            if(count($languages) == 1) {
                                $languagesLabel = $languages[0]; 
                            } else if(count($languages) == 2) {
                                $languagesLabel = $languages[0] . ' and <a class="livetooltip" title="'.$languagesString.'">' . (count($languages) - 1) . ' other</a>';
                            } else if(count($languages) > 2) {
                                $languagesLabel = $languages[0] . ' and <a class="livetooltip" title="'.$languagesString.'">' . (count($languages) - 1) . ' others</a>';
                            }
                        }
                        $occupation = isset($invitor['userinfo']['occupation']) ? $invitor['userinfo']['occupation'] : '';
                        $occupation = explode(',', $occupation);
                        $occupation2 = $occupation;
                        unset($occupation2[0]);
                        $occupationString = implode('<br/>', $occupation2);
                        $occupationLabel = '';
                        if(!empty($occupation)) {
                            if(count($occupation) == 1) {
                                $occupationLabel = $occupation[0]; 
                            } else if(count($occupation) == 2) {
                                $occupationLabel = $occupation[0] . ' and <a class="livetooltip" title="'.$occupationString.'">' . (count($occupation) - 1) . ' other</a>';
                            } else if(count($occupation) > 2) {
                                $occupationLabel = $occupation[0] . ' and <a class="livetooltip" title="'.$occupationString.'">' . (count($occupation) - 1) . ' others</a>';
                            }
                        }       
                        $totalfriends = isset($invitor['userinfo']['totalfriends']) ? $invitor['userinfo']['totalfriends'] : 0;
                        $fullname = isset($invitor['userinfo']['fullname']) ? $invitor['userinfo']['fullname'] : '';
                        $arrival_date = $invitor['post_info']['arrival_date'];
                        $departure_date = $invitor['post_info']['departure_date'];

                        $from_id = $invitor['user_id'];
                        $to_id = $invitor['post_info']['user_id'];

                        if($from_id == $user_id) {
                            $setId = $to_id;
                            $message = 'You send request for guide.';
                        } else {
                            $setId = $from_id;
                            $message = 'You receive request for guide.';
                        }
                        
                        $sendDateTime = date('m-d-Y H:i', $invitor['created_at']);
                        $detail = '<div class="topmenu">
                                    <a href="javascript:void(0)" class="backlink" onclick="getLstMsg(\''.$invitationId.'\'),backToOffer(this)"><i class="mdi mdi-arrow-left-thick"></i>Offer Inbox</a>
                                </div>
                                <div class="offer-conversation">
                                    <div class="add-offer">

                                        <div class=" fullwidth tt-holder">
                                            <textarea maxlength="70" class="materialize-textarea mb0 md_textarea descinput" id="localdriverdetailmessage" placeholder="Write a message..."></textarea>
                                            <a href="javascript:void(0)" onclick="sendmessagelocaldriver(\''.$invitationId.'\')" class="btn-custom manage-btn">Send</a>
                                        </div>        
                                    </div>
                                    <div class="offer-subject">
                                        Going for  guide <span>'.$arrival_date. ' ' .$departure_date.'</span>
                                    </div>
                                    <ul class="messagelisting">
                                    </ul>
                            </div>';

                        $searchSide = '<a href="javascript:void(0)" class="expand-link" onclick="mng_drop_searcharea(this)"><i class="mdi mdi-menu-right"></i>User\'s Profile</a>
                                            <div class="expandable-area">                                       
                                                <a href="javascript:void(0)" class="closearea" onclick="mng_drop_searcharea(this)">
                                                    <i class="mdi mdi-close "></i>
                                                </a>
                                                <div class="user-profile">
                                                    <div class="desc-holder">
                                                        <div class="img-holder"><img src="'.$profilepic.'"/></div>                                      
                                                        <div class="content-area">
                                                            <h4>'.$fullname.'</h4>
                                                            <div class="row-sec">
                                                                <div class="inforow">
                                                                    <div class="icon-holder"><i class="zmdi zmdi-pin"></i></div> Lives in <span>'.$country.'</span>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row-sec">
                                                                <div class="inforow">
                                                                    <div class="icon-holder"><i class="mdi mdi-gender-male-female"></i></div> <span>'.$age.' / '.$gender.'</span>
                                                                </div>
                                                                <div class="inforow">
                                                                    <div class="icon-holder"><i class="mdi mdi-comment"></i></div> Speaks <span>'.$languagesLabel.'</span>
                                                                </div>
                                                                <div class="inforow">
                                                                    <div class="icon-holder"><i class="mdi mdi-briefcase"></i></div> Works as <span>'.$occupationLabel.'</span>
                                                                </div>
                                                                <div class="inforow">
                                                                    <div class="icon-holder"><i class="mdi mdi-format-quote-open"></i></div> Lastly logged in <span>Yesterday</span>
                                                                </div>
                                                            </div>
                                                            
                                                            
                                                            <div class="inforow">
                                                                <div class="icon-holder"><i class="mdi mdi-account-group"></i></div> Friends <span>'.$totalfriends.'</span>
                                                            </div>
                                                            <div class="inforow">
                                                                <div class="icon-holder"><i class="mdi mdi-bookmark"></i></div> References <span>20</span>
                                                            </div>
                                                        
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>';

                        $resultSet = array('status' => true, 'detail' => $detail, 'search' => $searchSide);
                    }
                }

                return json_encode($resultSet, true);
                exit;
            }
        }
    } 

     public function actionFetchMsgHistory() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                $result = array('status' => false);
                $msgli = '';
                if(isset($_POST['$id']) && $_POST['$id'] != '') {
                    $invitationId = $_POST['$id'];
                    $history = LocaldriverPostInviteMsgs::fetchmsghistory($invitationId, $user_id);
                    $history = json_decode($history, true);
                    if(!empty($history)) {
                        foreach ($history as $key => $his) {
                            $idArray = array_values($his['_id']);
                            $messageId = $idArray[0];
                            //$messageId = (string)$his['_id']['$id'];
                            $created_at = $his['created_at'];
                            if ($created_at != '') {
                                $created_at = date("Y/m/d H:i A", $created_at);       
                            }
                            $uid = $his['from_id'];
                            $message = $his['message'];
                            if(isset($his['is_invitation_message']) && $his['is_invitation_message'] == true) {
                                $is_not_invitation_message = false;
                            } else {
                                $is_not_invitation_message = true;
                            }
                            if($uid != '') {
                                $userinfo = UserForm::find()->select(['gender', 'thumbnail', 'photo', 'fullname'])->where([(string)'_id' => $uid])->asarray()->one();
                                if(!empty($userinfo)) {
                                    $thumbnail = '';    
                                      
                                    $assetsPath = '../../vendor/bower/travel/images/';
                                    if(isset($userinfo['photo']) && substr($userinfo['photo'],0,4) == 'http') {
                                            $thumbnail = $userinfo['photo'];
                                    } else if(isset($userinfo['thumbnail']) && $userinfo['thumbnail'] != '' && file_exists('profile/'.$userinfo['thumbnail'])) {
                                            $thumbnail = "profile/".$userinfo['thumbnail'];
                                    } else if(isset($userinfo['gender'])) {
                                            $thumbnail = $assetsPath.$userinfo['gender'].".jpg";
                                    } else {
                                             $thumbnail = $assetsPath."Male.jpg";
                                    }
                                    
                                    $fullname = $userinfo['fullname'];

                                     $msgli .='<li id="message_'.$messageId.'">
                                        <div class="img-holder"><img src="'.$thumbnail.'"/></div>
                                        <div class="desc-holder">

                                            <h5>'.$fullname.'</h5>';

                                            if($is_not_invitation_message == true) {
                                            
                                            $msgli .='<div class="settings-icon custom-ul-drop">
                                                <a class="dropdown-button more_btn " href="javascript:void(0)" data-activates="messageulid_'.$messageId.'">
                                                  <i class="zmdi zmdi-more"></i>
                                                </a>
                                                <ul id="messageulid_'.$messageId.'" class="dropdown-content custom_dropdown " >
                                                    <li><a href="javascript:void(0)" onclick="deletemessage(\''.$messageId.'\')">Delete this message</a></li>
                                                </ul>
                                                <!-- Dropdown Structure -->
                                            </div>';
                                            }
                                            $msgli .='<span class="timestamp">'.$created_at.'</span>
                                            <div class="offer-msg">
                                                <p>'.$message.'</p>
                                            </div>
                                        </div>
                                    </li>';
                                }                    
                            }
                        }
                        $result = array('status' => true, 'data' => $msgli);
                         return json_encode($result, true);
                        exit;
                    }
                }

                return json_encode($result, true);
                exit;
            }
        }
    }

    public function actionSendMessage() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST['invitationId']) && $_POST['invitationId'] != '') {
                    $post = array();
                    $post['message'] = $_POST['message'];
                    $post['invitationId'] = $_POST['invitationId'];
                    $result = LocaldriverPostInviteMsgs::sendmessage($post, $user_id);
                    return $result;
                }
            }
        }
    }

    public function actionInvitationIsread() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
       
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST['$id']) && $_POST['$id'] != '') {
                    $id = $_POST['$id'];
                    $result = LocaldriverPostInviteMsgs::invitationisread($id, $user_id);
                    return $result;
                    exit;
                }
            }
        }
    }

    public function actionDeleteOffer() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
      
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST['$id']) && $_POST['$id'] != '') {
                    $id = $_POST['$id'];
                    $result = LocaldriverPostInviteMsgs::deleteoffer($id, $user_id);
                    return $result;
                    exit;
                }
            }
        }
    }

    public function actionBlockEventRequestUser() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
       
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST['$id']) && $_POST['$id'] != '') {
                    $id = $_POST['$id'];
                    $result = LocaldriverPostInviteMsgs::blockeventrequestuser($id, $user_id);
                    return $result;
                    exit;
                }
            }
        }
    } 

    public function actionGetLstMsg() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
 
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST['$id']) && $_POST['$id'] != '') {
                    $id = $_POST['$id'];
                    $result = LocaldriverPostInviteMsgs::getLstMsg($id, $user_id);
                    return $result;
                    exit;
                }
            }
        }
    }

    public function actionDeleteMyPost() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                if(isset($_POST['$id']) && $_POST['$id'] != '') {
                    $id = $_POST['$id'];
                    $result = LocaldriverPost::deletemyevent($id, $user_id);
                    return $result;
                    exit;
                }
            }
        }
    }

    public function actionCreateEventPrepare() {
        
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
        } else {
            $checkuserauthclass = 'checkuserauthclassg';
        }  
        
        return $this->render('createevent', array('checkuserauthclass' => $checkuserauthclass));
    }

    // Recent Section Area4
    public function actionSavedEventList() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') { 
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                $posts = LocaldriverPost::savedpostlist($user_id); 
                $posts = json_decode($posts, true);
                return $this->render('save',array('posts'=>$posts, 'checkuserauthclass' => $checkuserauthclass));
            }
        }
    }

    public function actionDeleteMessage() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') { 
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                if(isset($_POST['$id']) && $_POST['$id'] != '') {
                    $id = $_POST['$id'];
                    $record = LocaldriverPostInviteMsgs::find()->where([(string)'_id' => $id])->one();
                    if(!empty($record)) {
                        $record->delete();
                        return true;
                    }
                }
            }
        }
        return false;
    }


    public function actionFetchlanguages() {
        $data = ArrayHelper::map(Language::languages(), 'name', 'name');    
        return json_encode($data, true);
    }

    public function actionFetchlanguagesforaccountsetting() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $data = array();
        $language = array();

        if(isset($user_id) && $user_id != '') { 
            $result_personal = Personalinfo::find()->where(['user_id' => $user_id])->one();
            $language = $result_personal['language'];
            $language = explode(',', $language);
        }

        $languagearray = ArrayHelper::map(Language::languages(), 'name', 'name');    
        $languagearray = array_filter($languagearray);

        $data['$language'] = $language;
        $data['$languagearray'] = $languagearray;

        return json_encode($data, true);
    }    

    public function actionFetcheducationforaccountsetting() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $data = array();
        $education = array();

        if(isset($user_id) && $user_id != '') { 
            $result_personal = Personalinfo::find()->where(['user_id' => $user_id])->one();
            $education = $result_personal['education'];
            $education = explode(',', $education);
        }

        $educationarray = ArrayHelper::map(Education::find()->all(), 'name', 'name');
        $educationarray = array_filter($educationarray);

        $data['$education'] = $education;
        $data['$educationarray'] = $educationarray;

        return json_encode($data, true);
    }

    public function actionFetchinterestsforaccountsetting() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $data = array();
        $interests = array();

        if(isset($user_id) && $user_id != '') { 
            $result_personal = Personalinfo::find()->where(['user_id' => $user_id])->one();
            $interests = $result_personal['interests'];
            $interests = explode(',', $interests);
        }

        $interestsarray = ArrayHelper::map(Interests::find()->all(), 'name', 'name');
        $interestsarray = array_filter($interestsarray);

        $data['$interests'] = $interests;
        $data['$interestsarray'] = $interestsarray;

        return json_encode($data, true);
    }

    public function actionFetchoccupationforaccountsetting() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $data = array();
        $occupation = array();

        if(isset($user_id) && $user_id != '') { 
            $result_personal = Personalinfo::find()->where(['user_id' => $user_id])->one();
            $occupation = $result_personal['occupation'];
            $occupation = explode(',', $occupation);
        }

        $occupationarray = ArrayHelper::map(Occupation::find()->all(), 'name', 'name');
        $occupationarray = array_filter($occupationarray);

        $data['$occupation'] = $occupation;
        $data['$occupationarray'] = $occupationarray;

        return json_encode($data, true);
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
                $post = new LocaldriverReview();
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

                $lasting = LocaldriverReview::find()->where([(string)'_id' => $last_insert_id])->one();
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

    public function actionRemovepic()  
    { 
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($_POST['$id']) && $_POST['$id'] != '') {
            if(isset($_POST['$src']) && $_POST['$src'] != '') {
                $id = $_POST['$id'];
                $src = $_POST['$src'];
                $data = LocaldriverPost::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();

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
}
?>