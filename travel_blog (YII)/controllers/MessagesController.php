<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter; 
use yii\filters\AccessControl;
use yii\mongodb\ActiveRecord;

use frontend\components\ExSession;
use frontend\models\GiftCost;
use frontend\models\Messages;
use frontend\models\SecuritySetting;
use frontend\models\CommunicationSettings;
use frontend\models\StarMessages;
use frontend\models\LoginForm;
use frontend\models\Friend;
use frontend\models\Gifts;
use frontend\models\SupportTeam;

class MessagesController extends Controller {

    public function behaviors() {
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

    public function actions() {
        return [ 
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this,
                    'oAuthSuccess'],
            ],
            'captcha' => [
                'class' => 'mdm\captcha\CaptchaAction',
                'level' => 1,
            ],
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionListAllUserInfo() {
        $session = Yii::$app->session;
        $email = $session->get('email_id'); 
        $user_id = (string)$session->get('user_id');
        
        if(isset($_POST) && !empty($_POST)) {
            if((isset($email) && !empty($email)) && isset($user_id) && !empty($user_id)) {
                $post = $_POST;
                $users = Messages::getAllUsers($post);

            }
        }
    }
 
    public function actionForSelf() {
        $session = Yii::$app->session;
        $email = $session->get('email_id'); 
        $user_id = (string)$session->get('user_id'); 
        if(isset($_POST['userList']) && !empty($_POST['userList'])) {
            $post = $_POST['userList'];
            $newPost = Messages::getInfoForSelf($post);
            return $newPost;
            exit;
        }
    }

    public function actionForAll() {
        $session = Yii::$app->session;
        $email = $session->get('email_id'); 
        $user_id = (string)$session->get('user_id'); 
        if(isset($_POST['userList']) && !empty($_POST['userList'])) {
            $post = $_POST['userList']; 
            $newPost = Messages::getInfoForAll($post, $user_id);
            return $newPost;
            exit;
        }
    }

    public function actionRecentMessageUserInformation() {
        $session = Yii::$app->session; 
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        $post = $_POST['post'];
        $newPost = Messages::getRecentMessageUserInformation($post, $user_id);

        return json_encode($newPost, true);
        exit;
    }
	
    public function actionLoadHistoryMessage() {
        $session = Yii::$app->session; 
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
		if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
		{
			$url = $_SERVER['HTTP_REFERER'];
			$urls = explode('&',$url);
			$url = explode('=',$urls[1]);
			$user_id = $url[1];
		}
        $post = $_POST;
        $newPost = Messages::getLoadHistoryMessage($post, $user_id);        
        return $newPost;
        exit;
    }
 
    public function actionLoadHistoryChat() {
        $session = Yii::$app->session; 
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        $post = $_POST;
        $newPost = Messages::getLoadHistoryChat($post, $user_id);        
        return $newPost;
        exit;
    }
 
    public function actionSearchFromMessages() {
        $session = Yii::$app->session; 
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');

        $friendIdsBox = Messages::getFriendsUserIds($user_id);
        $key = $_POST['key'];
        $newPost = Messages::getSearchResult($key, $user_id);
        return json_encode($newPost, true);
        exit;
    }   

    public function actionSearchFromMessagesForPage() {
        $session = Yii::$app->session; 
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');

        if(isset($_POST['pageID']) && $_POST['pageID'] != '') {
            if(isset($_POST['key']) && $_POST['key'] != '') {
                $post = $_POST;
                $newPost = Messages::getSearchResultFORPAGE($post, $user_id);
                return $newPost;
                exit;
            }
        }
    } 
 
    public function actionIsReadMessage() {
        $session = Yii::$app->session; 
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');

        $from_id = $_POST['id'];
        $newPost = Messages::isreadmessage($from_id, $user_id);
        return json_encode($newPost, true);
        exit;
    }

    public function actionSetIsReadMessage() {
        $session = Yii::$app->session; 
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        if(isset($_POST['msgid']) && $_POST['msgid'] != '') {
            $msgid = $_POST['msgid'];
            $newPost = Messages::setisreadmessage($msgid, $user_id);
            return json_encode($newPost, true);
            exit;
        }
    }

    public function actionGetInfoForSingle() {
        $session = Yii::$app->session; 
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        $id = $_POST['id'];
        $newPost = Messages::getinfoforsingle($id);
        return $newPost; 
        exit;
    }

    public function actionUserBasicInfo() {
        $session = Yii::$app->session; 
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        $id = $_POST['id'];

        // unarchieve user..
        $securitySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
        if(!empty($securitySetting)) {
            if(isset($securitySetting['archive_users']) && $securitySetting['archive_users'] != '') { 
                $archive_users = $securitySetting['archive_users'];
                $archive_users = explode(",", $archive_users);

                if (($key = array_search($id, $archive_users)) !== false) {
                    unset($archive_users[$key]);
                    $archive_users = array_values(array_filter($archive_users));
                    $archive_users = implode(',', $archive_users);
                    $securitySetting->archive_users = $archive_users;
                    $securitySetting->update();
                }
            }
        }
  

        $newPost = Messages::userbasicinfo($id, $user_id);
        return $newPost; 
        exit;
    } 

    public function actionGetInfoForMultiple() {
        $session = Yii::$app->session; 
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        $ids = $_POST['ids'];
        $newPost = Messages::getinfoformultiple($ids);
        return $newPost;
        exit;
    }

    public function actionGetUnreadMsg() {
        $session = Yii::$app->session; 
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        $newPost = Messages::getUnreadMsg($user_id);
        return json_encode($newPost, true);
        exit;
    }

    public function actionSetReadAll() {
        $session = Yii::$app->session; 
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        $newPost = Messages::setreadall($user_id);
        return json_encode($newPost, true);
        exit;
    }

    public function actionDeleteSocketConversation() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        $id = $_POST['id'];
        $responce = Messages::deletesocketconversation($id, $user_id);
        return json_encode($responce, true);
        exit;
    }

    public function actionGetCategory() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        $id = $_POST['id'];
        $isOnHitEnter = 'no';

        $CommunicationSettings = CommunicationSettings::find()->where(['user_id' => $user_id])->one();
        if(!empty($CommunicationSettings)) {
            $JIsd = $CommunicationSettings['is_send_message_on_enter'] ? $CommunicationSettings['is_send_message_on_enter'] : '';
            if($JIsd == 'on') {
                $isOnHitEnter = 'yes';                
            }
        }

        if($id) {
            //check for if user is blocked or not...
            $is_blocked = SecuritySetting::isBlocked($id, $user_id);
            if($is_blocked['status'] == true) {
                $label = '';
                $userData = LoginForm::find()->where([(string)'_id' => $user_id])->asarray()->one();
                if(!empty($userData)) {
                    $fullname = $userData['fullname'];
                    $gender = $userData['gender'];
                    if($gender == 'Male') {
                        $label = 'You are not able to send message to '.$fullname.' becuase you have blocked him.';
                    } else {
                        $label = 'You are not able to send message to '.$fullname.' becuase you have blocked her.';
                    }
                } else {
                    $label = 'You are not able to send message becuase you have blocked this person.';
                }


                if($is_blocked['by'] == 'self') {
                    $result = array('status' => false, 'reason' => 'block', 'by' => 'self', 'isOnHitEnter' => $isOnHitEnter, 'label' => $label);
                    return json_encode($result, true);
                    exit;
                } else {
                    $result = array('status' => false, 'reason' => 'block', 'by' => 'other', 'isOnHitEnter' => $isOnHitEnter);
                    return json_encode($result, true);
                    exit;
                }
            } else {
                $result = array('status' => true, 'user_id' => $user_id, 'isOnHitEnter' => $isOnHitEnter);
                return json_encode($result, true);
                exit;
            }
        }

        $result = array('status' => false, 'isOnHitEnter' => $isOnHitEnter);
        return json_encode($result, true);
        exit;
    }

    public function actionGetCategoryWithGift() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        $selfBlock = array();
        $otherBlock = array();  
        $free = array();  
        if(isset($_POST['goarray']) && !empty($_POST['goarray'])) {
            $ids = $_POST['goarray'];
            
            foreach ($ids as $key => $id) {
                $is_blocked = SecuritySetting::isBlocked($id, $user_id);
                if($is_blocked['status'] == true) {
                    if($is_blocked['by'] == 'self') {
                        $name = $is_blocked['name'];
                        $selfBlock[] = $name;
                    } else {
                        $name = $is_blocked['name'];
                        $otherBlock[] = $name;
                    }
                } else {
                    $free[] = $id;
                }
            }

            $info = GiftCost::FetchGiftCost($user_id);
            $info = json_decode($info, true);
            if(!empty($info) && isset($info['cost'])) {
                $cost = $info['cost'];
                $totalCost = count($free) * $cost;

                $htmlContent = '';
                if(!empty($selfBlock)) {
                    $htmlContent .= implode(', ', $selfBlock) . ' blocked so you are able to send gift.<br/> ';
                }
                if(!empty($otherBlock)) {
                    $htmlContent .= implode(', ', $otherBlock) . ' isn\'t receiving messages from you at the moment<br/>';
                }

                $htmlContent .= 'Total cost for gift is '.$totalCost.' credits';
                return $htmlContent;
            }
        }

        return false;
    }
 
    public function actionCheckIsMute() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $email = $session->get('email_id');
            $user_id = (string)$session->get('user_id');
            $id = $_POST['id'];
            if($id) {
                //check for if user is muted or not...
                $is_mute = SecuritySetting::isMute($id, $user_id);
                return $is_mute; 
                exit; 
            }
        }
    }
 
    public function actionRecentMessagesUserList() { 
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id'); 
		if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
		{
			$url = $_SERVER['HTTP_REFERER'];
			$urls = explode('&',$url);
			$url = explode('=',$urls[1]);
			$user_id = $url[1];
		}  

        $info = Messages::recentMessagesUserList($user_id);
        return $info;
        exit;
    }

    public function actionRecentChatUserList() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        $info = Messages::recentChatUserList($user_id);
        return $info; 
        exit;
    } 
    
    public function actionGetRecentMessagesUsers() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        if(isset($user_id) && $user_id != '') {
            $info = Messages::getRecentMessagesUsers($user_id);            
            return $info;
            exit;
        }
    }
    
    /*public function actionGetRecentMessagesUsers() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        $thumb = $this->getimage($user_id,'thumb');
        $getRecentUsersWithDetails = array();
        if(isset($user_id) && $user_id != '') {
            $getRecentUsers = Yii::$app->cache->redis->hgetall($user_id); 
            if(!empty($getRecentUsers)) { 
                $getRecentUsersWithDetails = Messages::recentMessagesUserList($user_id, $getRecentUsers);
                return $getRecentUsersWithDetails;
            }
        }
        return false;
    }*/

    public function actionFetchGiftCost() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        if(isset($user_id) && $user_id != '') {
            $info = GiftCost::FetchGiftCost($user_id);
            return $info; 
            exit;
        }
        $result = array('status' => false);
        return json_encode($result, true);
        exit;
    }

    public function actionTempcheck() {
        if(isset($_POST['$data']) && !empty($_POST['$data'])) {
            $id = $_POST['$data']['id'];
            $thumb = $_POST['$data']['thumb'];
            $fullname = $_POST['$data']['fullname'];
            $city = $_POST['$data']['city'];
            $country = $_POST['$data']['country'];
            $status = $_POST['$data']['status'];
            $label = '';
            $localtime = date('h:i A');

            $_POST['$data']['last_logout_time'] = 1513920355;

            $statusHtml = '';
            if($status == 'online') {
                $statusHtml = '<span class="'+$status+'-dot"></span>';
            } else {
                if(isset($_POST['$data']['last_logout_time']) && $_POST['$data']['last_logout_time'] != '') {
                    $last_logout_time = $_POST['$data']['last_logout_time'];
                    $oldDate = Date('Y-m-d H:i', strtotime($last_logout_time));
                    $last_logout_time = Date('Y-m-d H:i', strtotime($last_logout_time));
                    $oldDate = new \DateTime($last_logout_time);
                    $newDate = new \DateTime();

                    $interval = $oldDate->diff($newDate);

                    if($interval->y == 0 && $interval->m == 0 && $interval->d == 0) {
                        $label = 'last seen '. $interval->h.'hr | ';
                    } else if($interval->y == 0 && $interval->m == 0 && $interval->d == 1) {
                        $just = Date('H:i', strtotime($last_logout_time));
                        $label = 'yesterday at '. $just . ' | ';
                    } else if($interval->y >0) {
                        $label = Date('d M, H:i', strtotime($last_logout_time)) . ' | ';
                    }  else {
                        $label = Date('d M Y, H:i', strtotime($last_logout_time)) . ' | ';
                    }
                }
            }

            $html = '<div class="imgholder"><img src="'.$thumb.'"></div><span class="desc-holder">'.$fullname.'</span>'.$statusHtml.'<span class="person_status">'.$label.'</span><span class="usercountry">|&nbsp;'.$country.' </span><span class="usertime">&nbsp;|&nbsp;'.$localtime.'</span>';

            return $html;

        }
    }

    public function actionXml() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST['$message']) && $_POST['$message'] != '') {
                $xml = $_POST['$message'];
                $info = Messages::XML($xml, $user_id); 
                return $info; 
            }
        }
    }    

    public function actionSethistory() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST['storedtemp']) && $_POST['storedtemp'] != '') {
                $storedtemp = $_POST['storedtemp'];
                $info = Messages::sethistory($storedtemp, $user_id); 
                return $info; 
            }
        }
    } 

    public function actionSinglemessage() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST['storedtemp']) && $_POST['storedtemp'] != '') {
                $xml = $_POST['storedtemp'];
                $info = Messages::singlemessage($xml, $user_id);
                return $info; 
            }
        }
    }

    public function actionGetbasicinfoouser() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST['id']) && $_POST['id'] != '') {
                $uid = $_POST['id'];
                $info = Messages::getbasicinfoouser($uid, $user_id);
                return $info; 
            }
        }
    }

    public function actionFilterlastmessagemine() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST) && !empty($_POST)) {

                $islesssec = false;
                $previousType = 'text';
                $islastmine = $_POST['islastmine'];
                $msg = $_POST['message'];
                $to = $_POST['to'];
                $datetime = time();
                $from = $_POST['from'];
                if($user_id == $to) {
                    $other = $from;
                } else {
                    $other = $to;
                }


                if(isset($_POST['datetime']) && $_POST['datetime'] != '') {
                    $olddatetime = $_POST['datetime'];
                    $olddatetime = date('Y-m-d H:i:s', $olddatetime);
                    $currentformat = date('Y-m-d H:i:s', $datetime);
                    
                    $start_date = new \DateTime($olddatetime);
                    $diff = $start_date->diff(new \DateTime($currentformat));
                    
                    if($diff->y == 0 && $diff->m == 0 && $diff->d == 0) { 
                        if($diff->h == 0 && $diff->i == 0) {
                            if($diff->s < 60) {
                                $islesssec = true;
                            } else {

                            }
                        }
                    } else {
                        $label = 'today';
                    }
                } else {
                    $label = 'today';
                }

                $datetimedisplays = date('m/d h:i', $datetime);
                
                if($islastmine == 'yes' && $from == $user_id) {
                    if($islesssec) {
                        $msg = '<br/>'.$msg;

                        if($previousType == 'text') {
                            $putblock = "$('.right-section').find('ul.current-messages').find('li#li_leftli_".$other."').find('ul.outer').find('li.msgli:last').find('.descholder').find('.msg-handle:last').find('p:last').append('$msg');";
                        } else {
                            $msg = '<div class="msg-handle" data-time="'.$datetime.'"><span class="timestamp">'.$datetimedisplays.'</span><span class="settings-icon"> <a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="'.$datetime.'"> <i class="zmdi zmdi-more"></i> </a> <ul id="'.$datetime.'" class="dropdown-content custom_dropdown persondropdown individiual_chat_setting"><div class="lds-css ng-scope"> <div style="width:100%;height:100%" class="lds-rolling-crntmsgpsndrp"> <div class="lststg"></div> </div> </div></ul> </span><p data-time='.$datetime.'>'.$msg.'</p><span class="select_msg_checkbox"> <input type="checkbox" class="filled-in" id="select_msg2" /> <label for="select_msg2"></label> </span> </div>'; 
                            $putblock = "$('.right-section').find('ul.current-messages').find('li#li_leftli_".$other."').find('ul.outer').find('li.msgli:last').find('.descholder').append('$msg');";
                        }
                    } else {
                        $msg = '<div class="msg-handle" data-time="'.$datetime.'"><span class="timestamp">'.$datetimedisplays.'</span><span class="settings-icon"> <a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="'.$datetime.'"> <i class="zmdi zmdi-more"></i> </a> <ul id="'.$datetime.'" class="dropdown-content custom_dropdown persondropdown individiual_chat_setting"><div class="lds-css ng-scope"> <div style="width:100%;height:100%" class="lds-rolling-crntmsgpsndrp"> <div class="lststg"></div> </div> </div></ul> </span><p data-time='.$datetime.'>'.$msg.'</p><span class="select_msg_checkbox"> <input type="checkbox" class="filled-in" id="select_msg2" /> <label for="select_msg2"></label> </span> </div>'; 
                            $putblock = "$('.right-section').find('ul.current-messages').find('li#li_leftli_".$other."').find('ul.outer').find('li.msgli:last').find('.descholder').append('$msg');";

                    }
                } else {
                    $msg = '<li class="msgli msg-outgoing time" data-time="'.$datetime.'"> <div class="checkbox-holder"> <div class="h-checkbox entertosend msg-checkbox"> <input type="checkbox" name="deleteselectedmsg" value=""> <label>&nbsp;</label> </div> </div> <div class="msgdetail-box"> <div class="descholder"> <div class="msg-handle" data-time="'.$datetime.'"><span class="timestamp">'.$datetimedisplays.'</span> <span class="settings-icon"> <a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="'.$datetime.'"> <i class="zmdi zmdi-more"></i> </a> <ul id="'.$datetime.'" class="dropdown-content custom_dropdown persondropdown individiual_chat_setting"><div class="lds-css ng-scope"> <div style="width:100%;height:100%" class="lds-rolling-crntmsgpsndrp"> <div class="lststg"></div> </div> </div></ul> </span> <p class="onw" data-time="'.$datetime.'">'.$msg.'</p> <span class="select_msg_checkbox"> <input type="checkbox" class="filled-in" id="select_msg7" /> <label for="select_msg7"></label> </span></div> </div> </div> </li>';
                    $putblock = "$('.right-section').find('ul.current-messages').find('li#li_leftli_".$other."').find('ul.outer').append('$msg');";

                }

                return $putblock;

            }
        }
    }

    public function actionFilterlastmessage() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST) && !empty($_POST)) {
                $putblock = '';
                $islesssec = false;
                $previousType = 'text';
                $islastmine = $_POST['islastmine'];
                $msg = $_POST['message'];
                $to = $_POST['to'];
                $datetime = time();
                $from = $_POST['from'];
                $isleftsection = $_POST['isleftsection'];
                $isrightsection = $_POST['isrightsection'];
                $isnullleft = $_POST['isnullleft'];
                if($user_id == $to) {
                    $other = $from;
                } else {
                    $other = $to;
                }
                $fullname = $this->getuserdata($uid,'fullname');
                $thumb = $this->getimage($from,'thumb');


                if(isset($_POST['datetime']) && $_POST['datetime'] != '') {
                    $olddatetime = $_POST['datetime'];
                    $olddatetime = date('Y-m-d H:i:s', $olddatetime);
                    $currentformat = date('Y-m-d H:i:s', $datetime);
                    
                    $start_date = new \DateTime($olddatetime);
                    $diff = $start_date->diff(new \DateTime($currentformat));
                    
                    if($diff->y == 0 && $diff->m == 0 && $diff->d == 0) { 
                        if($diff->h == 0 && $diff->i == 0) {
                            if($diff->s < 60) {
                                $islesssec = true;
                            } else {

                            }
                        }
                    } else {
                        $label = 'today';
                    }
                } else {
                    $label = 'today';
                }

                $datetimedisplays = date('m/d h:i', $datetime);
                
                if($isrightsection == 'no') {
                    $rightcontent = '<li class="mainli" id="li_leftli_'.$from.'"><div class="msgdetail-list nice-scroll"> <div class="msglist-holder images-container"> <ul class="outer"></ul></div></div></li>';
                    $putblock .= "$('.right-section').find('ul.current-messages').append('$rightcontent');";
                }

                  
                $assetsPath = '../../vendor/bower/travel/images/';


                if($isleftsection == 'yes') {
                    $leftcontent = '<h5>'.$fullname.'</h5><p><i class="msg-status mdi mdi-check"></i><i class="mdi mdi-reply"></i> '.$msg.'</p><span class="timestamp">'.$datetimedisplays.'</span>';
                    $putblock .= "$('#messages-inbox').find('ul.users-display').find('li a#leftli_".$from."').find('.descholder').html('$leftcontent');";
                } else {
                    $leftcontent = '<li data-time="'.$datetime.'"><a href="javascript:void(0)" class="active" id="leftli_'.$from.'" onclick="openMessage(this);"><span class="muser-holder"> <span class="imgholder"><img src="'.$thumb.'"></span><span class="online-dot"></span><span class="descholder"><h5>'.$fullname.'</h5><p><i class="msg-status mdi mdi-check"></i><i class="mdi mdi-reply"></i> '.$msg.'</p><span class="timestamp">'.$datetimedisplays.'</span></span></span></a></li>';
                    if($isnullleft == 'no') {
                        $putblock .= "$('#messages-inbox').find('ul.users-display').append('$leftcontent');";
                    } else {
                        $putblock .= "$('#messages-inbox').find('ul.users-display').html('$leftcontent');";
                    }
                }

                if($islastmine == 'yes' && $from == $user_id) {
                    if($islesssec) {
                        $msg = '<br/>'.$msg;
                        if($previousType == 'text') { 
                            $putblock .= "$('.right-section').find('ul.current-messages').find('li#li_leftli_".$from."').find('ul.outer').find('li.msgli:last').find('.descholder').find('.msg-handle:last').find('p:last').append('$msg')";
                        } else {
                            $msg = '<div class="msg-handle" data-time="'.$datetime.'"><span class="timestamp">'.$datetimedisplays.'</span><span class="settings-icon"> <a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="'.$datetime.'"> <i class="zmdi zmdi-more"></i> </a> <ul id="'.$datetime.'" class="dropdown-content custom_dropdown persondropdown individiual_chat_setting"><div class="lds-css ng-scope"> <div style="width:100%;height:100%" class="lds-rolling-crntmsgpsndrp"> <div class="lststg"></div> </div> </div></ul> </span><p data-time="'.$datetime.'">'.$msg.'</p><span class="select_msg_checkbox"> <input type="checkbox" class="filled-in" id="select_msg2" /> <label for="select_msg2"></label> </span> </div>'; 
                            $putblock .= "$('.right-section').find('ul.current-messages').find('li#li_leftli_".$from."').find('ul.outer').find('li.msgli:last').find('.descholder').append('$msg')";
                        }
                    } else {
                        $msg = '<div class="msg-handle" data-time="'.$datetime.'"><span class="timestamp">'.$datetimedisplays.'</span> <span class="settings-icon"> <a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="'.$datetime.'"> <i class="zmdi zmdi-more"></i> </a> <ul id="'.$datetime.'" class="dropdown-content custom_dropdown persondropdown individiual_chat_setting"><div class="lds-css ng-scope"> <div style="width:100%;height:100%" class="lds-rolling-crntmsgpsndrp"> <div class="lststg"></div> </div> </div></ul> </span><p data-time="'.$datetime.'">'.$msg.'</p><span class="select_msg_checkbox"> <input type="checkbox" class="filled-in" id="select_msg2" /> <label for="select_msg2"></label> </span></div>'; 
                        $putblock .= "$('.right-section').find('ul.current-messages').find('li#li_leftli_".$from."').find('ul.outer').find('li.msgli:last').find('.descholder').append('$msg')";
                    }
                } else {
                    $msg = '<li class="msgli received msg-income time" data-time='.$datetime.'> <div class="checkbox-holder"> <div class="h-checkbox entertosend msg-checkbox"> <input type="checkbox" name="deleteselectedmsg" value=""> <label>&nbsp;</label> </div> </div> <div class="msgdetail-box"><div class="imgholder"><img src='.$thumb.'></div> <div class="descholder"> <div class="msg-handle" data-time='.$datetime.'> <p class="onw" data-time='.$datetime.'>'.$msg.'<span class="timestamp">'.$datetimedisplays.'</span><span class="settings-icon"> <a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="'.$datetime.'"> <i class="zmdi zmdi-more"></i> </a> <ul id="'.$datetime.'" class="dropdown-content custom_dropdown persondropdown individiual_chat_setting"><div class="lds-css ng-scope"> <div style="width:100%;height:100%" class="lds-rolling-crntmsgpsndrp"> <div class="lststg"></div> </div> </div></ul> </span></p> <span class="select_msg_checkbox"> <input type="checkbox" class="filled-in" id="select_msg7" /> <label for="select_msg7"></label> </span> </div> </div> </div> </li>';
                    $putblock .= "$('.right-section').find('ul.current-messages').find('li#li_leftli_".$from."').find('ul.outer').append('$msg')";

                }

                return $putblock;

            }
        }
    }

    /* ============= START node loaded =================*/
    public function actionGetuserlabel() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');

        if(isset($_POST['result']) && !empty($_POST['result'])) {
            $id = $_POST['result']['id'];
            $codekey = $_POST['result']['codekey'];
            $status = $_POST['result']['status'];
            $muteUsers = array();
            $messageFiltering = array();

            $SecuritySetting = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
            if(!empty($SecuritySetting)) {
                $muteUsers = isset($SecuritySetting['mute_users']) ? $SecuritySetting['mute_users'] : '';
                $muteUsers = explode(',', $muteUsers);
                
                $messageFiltering = isset($SecuritySetting['message_filtering']) ? $SecuritySetting['message_filtering'] : '';
                $messageFiltering = explode(',', $messageFiltering);
            }

            if($id) {
                $userData = LoginForm::find()->where([(string)'_id' => $id])->asarray()->one();
                if(!empty($userData)) {
                    $last_logout_time = isset($userData['last_logout_time']) ? $userData['last_logout_time'] : time();
                    
                    $label = '';
                    $localtime = date('h:i A');
                    $blockprofile_img = '';
                    $blockicon_img= '';

                    $imageclass = 'imgholder dot_offline';
                    if($status == 'online') {
                        $imageclass = 'imgholder dot_online';
                    } else if ($status == 'away') {
                        $imageclass = 'imgholder dot_away';
                    }

                    if(in_array($id, $muteUsers)) {
                        $imageclass = 'imgholder dot_mute';
                    }
                    
                    if(in_array($id, $messageFiltering)) {
                        $imageclass = 'imgholder dot_block';
                        $blockprofile_img = 'blockprofile_img';
                        $blockicon_img = '<i class="mdi mdi-cancel blockicon_img blockicon_img"></i>';
                    }


                    if($status != 'online' && $status != 'away' ) {
                        $oldDate = Date('Y-m-d H:i', $last_logout_time);
                        $last_logout_time = Date('Y-m-d H:i', $last_logout_time);
                        $oldDate = new \DateTime($last_logout_time);
                        $newDate = new \DateTime();
                        $interval = $oldDate->diff($newDate);

                        if($interval->y == 0 && $interval->m == 0 && $interval->d == 0) {
                            if($interval->h <= 1) {
                                if($interval->i == 0) {
                                    $label = 'Last seen less than min';
                                } else {
                                    $label = 'Last seen '.$interval->i.'min';
                                }
                            } else if($interval->h == 1) {
                                $label = 'Last seen 1hr';
                            } else {
                                $label = 'Last seen '.$interval->h.'hrs';
                            }
                        } else if($interval->y == 0 && $interval->m == 0) {
                            if($interval->d == 1) {
                                $label = '1 day ago';
                            } else if($interval->d <= 7) {
                                $label = $interval->d.' days ago';
                            } else {
                                $label = 'Last seen days ago';
                            }
                        }  else {
                            $label = 'Last seen days ago';
                        }
                    } else {
                        $label = $status;
                    }

                    $result = array('person_status' => $label, 'usertime' => '|&nbsp;&nbsp;'.$localtime, 'imageclass' => $imageclass, 'blockprofile_img' => $blockprofile_img, 'blockicon_img' => $blockicon_img);

                    return json_encode($result, true);
                }
            }
        }
    }

    public function actionCalculatediff() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');

        if(isset($_POST['$data']) && !empty($_POST['$data'])) {
            $id = $_POST['$data']['id'];

            if($id) {
                $userData = LoginForm::find()->where([(string)'_id' => $id])->asarray()->one(); 
                if(!empty($userData)) {
                    $fullname = $userData['fullname'];
                    $thumb = $this->getimage($id,'thumb');
                    $city = trim($userData['city']);
                    $country = trim($userData['country']);
                    if($country == '') { 
                        $country = $city;
                    }
                    $status = $_POST['$data']['status'];
                    $last_logout_time = isset($userData['last_logout_time']) ? $userData['last_logout_time'] : time();
                    
                    $label = '';
                    $statuslabel = 'imgholder dot_offline';
                    if($status == 'online') {
                        $statuslabel = 'imgholder dot_online';
                    } else if ($status == 'away') {
                        $statuslabel = 'imgholder dot_away';
                    }

                    $SecuritySetting = SecuritySetting::find()->where(["user_id" => $user_id])->one();
                    if(!empty($SecuritySetting)) {
                        if(isset($SecuritySetting['mute_users'])) {
                            $muteData = $SecuritySetting['mute_users'];
                            $muteData = explode(",", $muteData);
                            if(in_array($id, $muteData)) {
                                $statuslabel = 'imgholder dot_mute';
                            }
                        }

                        if(isset($SecuritySetting['message_filtering']) && $SecuritySetting['message_filtering'] != '') { 
                            $message_filtering = $SecuritySetting['message_filtering'];
                            $message_filtering = explode(",", $message_filtering);
                            if(in_array($id, $message_filtering)) {
                                $statuslabel = 'imgholder dot_block';
                            }
                        }
                    }

                    if($status != 'online' && $status != 'away' ) {
                        $oldDate = Date('Y-m-d H:i', $last_logout_time);
                        $last_logout_time = Date('Y-m-d H:i', $last_logout_time);
                        $oldDate = new \DateTime($last_logout_time);
                        $newDate = new \DateTime();
                        $interval = $oldDate->diff($newDate);

                        if($interval->y == 0 && $interval->m == 0 && $interval->d == 0) {
                            if($interval->h <= 1) {
                                if($interval->i == 0) {
                                    $label = 'Last seen less than min';
                                } else {
                                    $label = 'Last seen '.$interval->i.'min';
                                }
                            } else if($interval->h == 1) {
                                $label = 'Last seen 1hr';
                            } else {
                                $label = 'Last seen '.$interval->h.'hrs';
                            }
                        } else if($interval->y == 0 && $interval->m == 0) {
                            if($interval->d == 1) {
                                $label = '1 day ago';
                            } else if($interval->d <= 7) {
                                $label = $interval->d.' days ago';
                            } else {
                                $label = 'Last seen days ago';
                            }
                        }  else {
                            $label = 'Last seen days ago';
                        }
                    } else {
                        $label = $status;
                    }

                    $html = '<div class="'.$statuslabel.'"><img src="'.$thumb.'"></div><span class="desc-holder">'.$fullname.'</span><span class="person_status">'.$label.'</span><span class="usercountry">|&nbsp;'.$country.'</span><span class="usertime"></span>';

                    return $html;
                }
            }
        }
    }

    public function actionCalculatediffnew() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');

        if(isset($_POST['$data']) && !empty($_POST['$data'])) {
            $id = $_POST['$data']['id'];

            if($id) {
                $userData = LoginForm::find()->where([(string)'_id' => $id])->asarray()->one();
                
                if(!empty($userData)) {
                    $fullname = $userData['fullname'];
                    $thumb = $this->getimage($id,'thumb');
                    $city = trim($userData['city']);
                    $country = trim($userData['country']);
                    if($country == '') {
                        $country = $city;
                    }
                    $status = $_POST['$data']['status'];
                    $last_logout_time = isset($userData['last_logout_time']) ? $userData['last_logout_time'] : time();
                    
                    $label = '';
                    $statuslabel = 'imgholder dot_offline';
                    if($status == 'online') {
                        $statuslabel = 'imgholder dot_online';
                    } else if ($status == 'away') {
                        $statuslabel = 'imgholder dot_away';
                    }

                    $SecuritySetting = SecuritySetting::find()->where(["user_id" => $user_id])->one();
                    if(!empty($SecuritySetting)) {
                        if(isset($SecuritySetting['mute_users'])) {
                            $muteData = $SecuritySetting['mute_users'];
                            $muteData = explode(",", $muteData);
                            if(in_array($id, $muteData)) {
                                $statuslabel = 'imgholder dot_mute';
                            }
                        }

                        if(isset($SecuritySetting['message_filtering']) && $SecuritySetting['message_filtering'] != '') { 
                            $message_filtering = $SecuritySetting['message_filtering'];
                            $message_filtering = explode(",", $message_filtering);
                            if(in_array($id, $message_filtering)) {
                                $statuslabel = 'imgholder dot_block';
                            }
                        }
                    }

                    if($status != 'online' && $status != 'away' ) {
                        $oldDate = Date('Y-m-d H:i', $last_logout_time);
                        $last_logout_time = Date('Y-m-d H:i', $last_logout_time);
                        $oldDate = new \DateTime($last_logout_time);
                        $newDate = new \DateTime();
                        $interval = $oldDate->diff($newDate);
                        
                        if($interval->y == 0 && $interval->m == 0 && $interval->d == 0) {
                            if($interval->h <= 1) {
                                if($interval->i == 0) {
                                    $label = 'Last seen less than min';
                                } else {
                                    $label = 'Last seen '.$interval->i.'min';
                                }
                            } else if($interval->h == 1) {
                                $label = 'Last seen 1hr';
                            } else {
                                $label = 'Last seen '.$interval->h.'hrs';
                            }
                        } else if($interval->y == 0 && $interval->m == 0) {
                            if($interval->d == 1) {
                                $label = '1 day ago';
                            } else if($interval->d <= 7) {
                                $label = $interval->d.' days ago';
                            } else {
                                $label = 'Last seen days ago';
                            }
                        }  else {
                            $label = 'Last seen days ago';
                        }
                    } else {
                        $label = $status;
                    }

                    $html = '<div class="gotohome"> <a href="javascript:void(0)" onclick="closeAddNewMsg()"><i class="mdi mdi-arrow-left"></i></a> </div> <div class="logo-holder" style="margin-top: -38px;"> <span class="top_img"> <img src="'.$thumb.'"/> </span> <a href="javascript:void(0)" class="mbl-logo page-name" onclick="contactInfo()">'.$fullname.'</a> <div class="top_message_status"> <span class="desc-holder">'.$fullname.'</span><span class="person_status" style="color: unset;position: unset;">'.$label.'</span><span class="usercountry">|&nbsp;'.$country.'</span><span class="usertime"></span> </div> </div>';

                    return $html;
                }
            }
        }
    }
    /* ============= END node loaded =================*/

    public function actionGetcontactinfo() {
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST['$id']) && $_POST['$id'] != '') {
                $id = $_POST['$id'];
                $info = Messages::getuserdetail($id, $user_id);
                return $info;
            }
        }
    }

    public function actionGetSearchUser()
    {
        $session = Yii::$app->session;
        $user_id = $userid =  $session->get('user_id');
        if(isset($_POST['$like']) && $_POST['$like'] != '') {
            $like = trim($_POST['$like']);
            $usrfrd = Connect::getuserFriendsWithLike($user_id, $like);
            $newArray = array();

            foreach ($usrfrd as $indexKey => $indexValue) {
                $id = (string)$indexValue['userdata']['_id'];
                $email = $indexValue['userdata']['email'];
                $fullname = $indexValue['userdata']['fname'] . ' ' . $indexValue['userdata']['lname'];
                $country =  $indexValue['userdata']['country'];
                $thumb = isset($indexValue['userdata']['thumb']) ? $indexValue['userdata']['thumb'] : '' ;
                if($thumb == '' || $thumb == undefined || $thumb == null) {
                    $thumb = $this->getimage($id,'thumb');
                }

                $current = array('id' => $id,
                'email' => $email,
                'fullname' => $fullname,
                'country' => $country,
                'thumb' => $thumb);
                $newArray[] = $current;
            }
            return json_encode($newArray);
        }


    }

    public function actionGetThread() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $user_id = $session->get('user_id');
            $id = $_POST['id'];
            $model = new \frontend\models\Messages();
            $data = $model->getThread($id, $user_id);
            return $data;
        }
    }  

    public function actionDeleteSelectedMessage() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $user_id = $session->get('user_id');
            $id = $_POST['id']; 
            $data = Messages::deleteselectedmessage($id, $user_id);
            return $data;
        }
    }

    public function actionAddstarmessage() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
            $id = $_POST['id']; 
            $data = StarMessages::addstarmessage($id, $user_id);
            return $data;
        }
    }

    public function actionAddstarmessagebulk() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
            $id = $_POST['id']; 
            $data = StarMessages::addstarmessagebulk($id, $user_id);
            return $data;
        }
    }  

    public function actionUnsavedmessage() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
            $id = $_POST['id']; 
            $data = StarMessages::unsavedmessage($id, $user_id);
            return $data;
        }
    }

    public function actionUnblockuser() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
            $id = $_POST['id']; 
            $data = Messages::unblockuser($id, $user_id);
            return $data;
        }
    }

    public function actionUnarchiveuser() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
            $id = $_POST['id']; 
            $data = Messages::unarchiveuser($id, $user_id);
            return $data;
        }
    }

    public function actionDeletemessage_sm() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
            $id = $_POST['id']; 
            $data = Messages::deletemessage_sm($id, $user_id);
            return $data;
        }
    }  

    public function actionBlockUser() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
            $id = $_POST['id'];
            $type = SecuritySetting::blockuser($id, $user_id);
            return $type;
        }
    }   
 
    public function actionMuteUser() {
        if(isset($_POST['$id']) && !empty($_POST['$id'])) {
            $session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
            $id = $_POST['$id'];
            $type = SecuritySetting::muteuser($id, $user_id);
            return $type; 
        }
    }  

    public function actionAddArchive() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
            $id = $_POST['id'];
            $model = new \frontend\models\SecuritySetting();
            $type = SecuritySetting::archiveuser($id, $user_id);
            return $type; 
        }
    }     

    public function actionMessageUserStatus() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
            $id = $_POST['id'];
            $model = new \frontend\models\SecuritySetting();
            $status = $model->messageuserstatus($id, $user_id);
           
            $blockedType = 'Block';
            if(isset($status['is_blocked'])) {
                $blockedType = 'Unblock'; 
            }

            $muteType = 'Mute';
            if(isset($status['is_mute'])) {
                $muteType = 'Unmute';
            }

            $archive = 'Archive';
            if(isset($status['is_archive'])) {
                $archive = 'Unarchive';
            }
            
            $returnLi ='<li><a href="javascript:void(0)" onclick="genmsgoptionsOpnCht()">Open in chat</a></li>  
            <li><a href="javascript:void(0)" onclick="genmsgoptionsPhtThd()">View in photos in thread</a></li>  
            <li><a href="javascript:void(0)" class="archive-setting" onclick="archiveChat()">'.$archive.'</a></li>   
            <li><a href="javascript:void(0)" class="mute-setting" onclick="manageMuteConverasion()">'.$muteType.' chat</a></li>
            <li class="deleteSocketConversation"><a href="javascript:void(0)">Delete conversation</a></li>
            <li><a href="javascript:void(0)" onclick="showMsgCheckbox()">Delete Messages</a></li>
            <li><a href="javascript:void(0)" class="block-setting" onclick="manageBlockConverasion(this)">'.$blockedType.' Messages</a></li>
            <li><a href="javascript:void(0)" class="block-setting" onclick="messagereporttoabuse(\''.$id.'\')">Report This Offer</a></li>';

            $status = json_encode(array('status' => true, 'lis' => $returnLi));
            return $status;
        }
    }     

    public function actionReportOffer() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if(isset($_POST) && !empty($_POST)) {
            $post = $_POST;
            $information = Abuse::abuseReport($post, $user_id);
            return $information;
            exit;
        }
    }

    public function actionCaldiffoperation() {
        if(isset($_POST['$data']) && !empty($_POST['$data'])) {
            $id = $_POST['$data']['id'];
            $thumb = $_POST['$data']['thumb'];
            $fullname = $_POST['$data']['fullname'];
            $city = $_POST['$data']['city'];
            $country = $_POST['$data']['country'];
            $status = $_POST['$data']['status'];
            $label = '';
            $localtime = date('h:i A');

            $_POST['$data']['last_logout_time'] = 1513920355;

            $statusHtml = '';
            if($status == 'online') {
                $statusHtml = '<span class="'+$status+'-dot"></span>';
            } else {
                if(isset($_POST['$data']['last_logout_time']) && $_POST['$data']['last_logout_time'] != '') {
                    $last_logout_time = $_POST['$data']['last_logout_time'];

                    $oldDate = Date('Y-m-d H:i', $last_logout_time);

                    $last_logout_time = Date('Y-m-d H:i', $last_logout_time);
                    $oldDate = new \DateTime($last_logout_time);
                    $newDate = new \DateTime();

                    $interval = $oldDate->diff($newDate);

                    if($interval->y == 0 && $interval->m == 0 && $interval->d == 0) {
                        $label = 'last seen '. $interval->h.'hr | ';
                    } else if($interval->y == 0 && $interval->m == 0 && $interval->d == 1) {
                        $just = Date('H:i', $last_logout_time);
                        $label = 'yesterday at '. $just . ' | ';
                    } else if($interval->y >0) {
                        $label = Date('d M, H:i', $last_logout_time) . ' | ';
                    }  else {
                        $label = Date('d M Y, H:i', $last_logout_time) . ' | ';
                    }
                }
            }

            $html = '<div class="imgholder"><img src="'.$thumb.'"></div><span class="desc-holder">'.$fullname.'</span>'.$statusHtml.'<span class="person_status">'.$label.'</span><span class="usercountry">|&nbsp;'.$country.'</span><span class="usertime">&nbsp;'.$localtime.'&nbsp;</span>';

            return $html;

        }
    }

    public function actionGetmsgsetoptions() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $id = $_POST['id'];
            //$muteLabel = 'Mute';
            $archiveLabel = 'Archive';

            $SecuritySetting = SecuritySetting::find()->where(["user_id" => $user_id])->one();
            if(!empty($SecuritySetting)) {
                /*if(isset($SecuritySetting['mute_users'])) {
                    $muteData = $SecuritySetting['mute_users'];
                    $muteData = explode(",", $muteData);
                    if(in_array($id, $muteData)) {
                        $muteLabel = 'Unmute';
                    }
                }*/

                if(isset($SecuritySetting['archive_users'])) {
                    $archiveData = $SecuritySetting['archive_users'];
                    $archiveData = explode(",", $archiveData);
                    if(in_array($id, $archiveData)) {
                        $archiveLabel = 'Unarchive';
                    }
                }
            }

            // check fot mute or unmute user..
            ?>
            <li><a href="javascript:void(0)" class="contact_info_action">Contact info</a></li>
            <li><a href="javascript:void(0)" onclick="selectMessageBox()">Select messages</a></li>
            <li><a href="javascript:void(0)" onclick="UnreadMessages()">Mark unread</a></li>
            <?php  /*
            <li><a href="javascript:void(0)" class="mute-setting" onclick="manageMuteConverasion()"><?=$muteLabel?> chat</a></li>
            */ ?>
            <li><a href="javascript:void(0)" onclick="archiveChat()"><?=$archiveLabel?> chat</a></li>
            <li onclick="clearmessages()"><a href="javascript:void(0)">Delete messages</a></li>
            <li class="deleteConvHistory"><a href="javascript:void(0)">Delete Chat</a></li>
            <?php
        }
    }

    public function actionSepmsgopt() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $id = $_POST['id'];
            $starLabel = 'Star message';

            $StarMessages = StarMessages::find()->where(["starmessage_user_id" => $user_id])->one();
            if(!empty($StarMessages)) {
                if(isset($StarMessages['starmessage_star_ids'])) {
                    $starmessage_star_ids = $StarMessages['starmessage_star_ids'];
                    $starmessage_star_ids = explode(",", $starmessage_star_ids);

                    $ISOA = array_intersect($id, $starmessage_star_ids);

                    if(!empty($ISOA)) {
                        $starLabel = 'Unstar message';
                    }
                }
            }

            ?>
            <li> <a>Reply</a> </li> 
            <li> <a>Forward message</a> </li> 
            <li class="addstarmessage"> <a><?=$starLabel?></a> </li> 
            <li class="deleteSigMessage"> <a>Delete message</a> </li> 
            <?php
        }
    }

    public function actionGetallsavedmsg() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        return StarMessages::getallsavedmsg($user_id);
        
    }

    public function actionGetparticularusersavedmsg() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
            $id = $_POST['id'];
            return StarMessages::getparticularusersavedmsg($id, $user_id);
        }
    }

    public function actionUptUsrSentence() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
            $id = $_POST['id'];
            $sentence = $_POST['sentence'];
            return Messages::UptUsrSentence($id, $user_id, $sentence);
        }
    }
    
    public function actionGetallarchivedusers() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        return Messages::getallarchivedusers($user_id);
        
    }

    public function actionGetallblockusers() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        return Messages::getallblockusers($user_id);        
    }

    public function actionGetmessageleftsetting() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        return Messages::getmessageleftsetting($user_id);        
    }

    public function actionClearmessages() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
            $id = $_POST['id'];
            return Messages::clearmessages($id, $user_id);
        }
    }

    public function actionCommunicationSettingsMessage() 
    {
        if (isset($_POST) & !empty($_POST)) {
            return CommunicationSettings::communicationsettings();
        } else {
            return $this->render('notification_setting');
        }
    }

    public function actionDomutemessage() 
    {
        if (isset($_POST) & !empty($_POST)) {
            return Messages::domutemessage();
        }
    }

    public function actionDoblockmessage() 
    {
        if (isset($_POST) & !empty($_POST)) {
            return Messages::doblockmessage();
        }
    }

    public function actionGetallgifts() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $Gifts = Gifts::getallgifts();

        if(isset($_POST) && !empty(isset($_POST))) {
            $catName = $_POST['catName'];
            $emovalue = $_POST['emovalue'];
            $mode = $_POST['mode'];
            $id = $_POST['id'];
            $fullname = $this->getuserdata($id,'fullname');
            $assetsPath = '../../vendor/bower/travel/images/';
            $counting = 1;
            $firstBox = '';
            $html = '';

            if(!empty($Gifts)) { ?>
                <div class="edit-emosticker">
                    <div class="custom_message_modal_header">
                        <p>
                            Send a gift to <span class="to_gift"><?=$fullname?></span>
                        </p>
                        <button class="close_modal_icon waves-effect" onclick="usegift_modal()">
                        <i class="mdi mdi-close mdi-20px material_close "></i>
                        </button>
                    </div>
                    <div class="usergift_content">
                        <div class="popup-content">
                            <div class="more-friends">
                                <p class="send_to">Also send to</p>
                                <a><i id="compose_addpersonAction" class="compose_addpersonActionShareWith zmdi zmdi-account-add dp48 custome-plus"></i></a>
                            </div> 

                            <div class="carousel_position">
                                <div class="carousel carousel-slider center" data-indicators="true">
                                    <div class="carousel-fixed-item center middle-indicator">
                                        <div class="left">
                                            <a href="Previo" class="movePrevCarousel middle-indicator-text waves-effect waves-light content-indicator"><i class="zmdi zmdi-chevron-left left  middle-indicator-text"></i></a>
                                        </div>

                                        <div class="right">
                                            <a href="Siguiente" class=" moveNextCarousel middle-indicator-text waves-effect waves-light content-indicator"><i class="zmdi zmdi-chevron-right right middle-indicator-text"></i></a>
                                        </div>
                                    </div>
                                    <?php
                                    foreach ($Gifts as $singleGift) {
                                        $digit = $singleGift['digit'];
                                        $code = $singleGift['code'];
                                        $image = $singleGift['image'];
                                        if($emovalue!="none") {
                                            if($emovalue==$code) {
                                               $firstBox = "<div class='carousel-item white white-text active' href=".$digit." data-class=".$code." data-cat=".$catName."> <img src=".$assetsPath.$image."></div>";
                                            } else {
                                                $html .="<div class='carousel-item white white-text' href=".$digit." data-class=".$code." data-cat=".$catName."> <img src=".$assetsPath.$image."></div>";
                                            }
                                        } else {
                                            if($counting == 1) {
                                                $firstBox = "<div class='carousel-item white white-text active' href=".$digit." data-class=".$code." data-cat=".$catName."> <img src=".$assetsPath.$image."></div>";
                                            } else {
                                                $html .="<div class='carousel-item white white-text' href=".$digit." data-class=".$code." data-cat=".$catName."> <img src=".$assetsPath.$image."></div>";
                                            }
                                        }

                                        $counting++;
                                    }

                                    ?>
                                    <?=$firstBox?><?=$html?>
                                </div>
                                
                            </div>                  
                            <div class="custom_textarea usergift_msg">
                                <textarea id="message" class="materialize-textarea" placeholder="Type your message here"></textarea>
                            </div>                  
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="gift_send">
                            <a href="javascript:void(0)" class="btn btn-sm waves-effect waves-light" onclick="sendmessagewithgift()">Send</a>
                        </div>
                    </div>
                </div>
                <div class="preview-emosticker">
                </div>
                <?php
            }
        }
    }

    public function actionGetfrdandfrdoffrd() 
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if(isset($user_id) && $user_id != 'undefined') {
            $itretion = $_POST['itretion'];
            return Connect::getfrdandfrdoffrd($user_id, $itretion);
        }
    }

    public function actionGetfrdandfrdoffrdsearch() 
    {
        $session = Yii::$app->session; 
        $user_id = (string)$session->get('user_id');
        if(isset($user_id) && $user_id != 'undefined') {
            $searchkey = isset($_POST['searchkey']) ? $_POST['searchkey'] : '';
            if($searchkey != '') {
                return Connect::getfrdandfrdoffrdsearch($user_id, $searchkey);
            }
        }
    }

    public function actionSendiconstatus() 
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        return Messages::sendiconstatus($user_id);
    }

    public function actionSupportteam() 
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $data = SupportTeam::getinfo();

        if(!empty($data)) { 
            $id = (string)$data['_id'];
            $image = $data['image'];
            $name = $data['name'];
            $assetsPath = '../../vendor/bower/travel/images/';
        ?>
            <li id="supportteamLI">
                <a href="javascript:void(0)" id="msg_<?=$id?>" onclick="openSupportTeam(this);">
                    <span class="muser-holder"> 
                        <span class="imgholder">
                            <img src="<?=$assetsPath?><?=$image?>"/>
                        </span>
                        <span class="descholder">
                            <h5><?=$name?></h5>
                            <span class="timestamp"></span>
                        </span>
                    </span>
                </a>
            </li>
        <?php
        }
    }
}

