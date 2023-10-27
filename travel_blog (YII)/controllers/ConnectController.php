<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\Controller;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\mongodb\ActiveRecord;
use frontend\models\Notification;
use frontend\models\SecuritySetting;
use frontend\models\Connect;
use frontend\models\SuggestConnect;
use frontend\models\UserForm;
use frontend\models\BlockConnect;
use frontend\models\Credits;
use frontend\models\LoginForm;
use frontend\models\UnfollowConnect;
use frontend\models\MuteConnect;

class ConnectController extends Controller
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
	  
    public function actionAddConnect()
    {
		$session = Yii::$app->session;
		$uid = (string)$session->get('user_id');
		$data 	= array(); 
		$connect = new Connect();
		$date = time();
		if(isset($uid) && $uid != '') {
			$authstatus = UserForm::isUserExistByUid($uid);
			if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
				$data['auth'] = $authstatus;
			} else {
				$requestcheckone = Connect::find()->where(['from_id' => $_POST['from_id'] , 'to_id' => $_POST['to_id']])->one();
				$requestchecktwo = Connect::find()->where(['to_id' => $_POST['from_id'] , 'from_id' => $_POST['to_id']])->one();
				if(!$requestcheckone && !$requestchecktwo)
				{
					$connect->from_id = $_POST['from_id'];
					$connect->to_id = $_POST['to_id'];
					$connect->action_user_id = $_POST['from_id'];
					$connect->status = '0';
					$connect->created_date = $date;
					$connect->updated_date = $date;
					$connect->insert();
					if($connect->_id != '')
					{
						$connect1 = new Connect();
						$date = time();
						$connect1->from_id = $_POST['to_id'];
						$connect1->to_id = $_POST['from_id'];
						$connect1->action_user_id = $_POST['from_id'];
						$connect1->status = '0';
						$connect1->created_date = $date;
						$connect1->updated_date = $date;
						$connect1->insert();

					$data['msg'] = 'Connect request sent';
					}
				}
				else
				{
					$session = Yii::$app->session;
					$uid = (string)$session->get('user_id');
					if($requestcheckone && ($uid == $requestcheckone['action_user_id']))
					{
						$data['msg'] = 'Accept connect request';
					}
					else
					{
						$data['msg'] =  'Connect request sent';
					}
				}
			}
		}
        else {
        	$data['auth'] = 'checkuserauthclassg';
        }
		return json_encode($data);		
    }
	
    public function actionAcceptConnect()
    {
        $connect = new Connect();
        $request = Connect::find()->where(['from_id' => $_POST['from_id'] , 'to_id' => $_POST['to_id']])->one();
        $label = '';
        if(!empty($request))
        {
            $date = time();
            $request->action_user_id = $_POST['to_id'];
            $request->status = '1';
            $request->updated_date = $date;
            $request->update();
			
            // Calling addcredits function for inserting record in Cridits table for Accepting Connect Request
            $cre_amt = 2;
            $cre_desc = 'addconnect';
            $status = '1';
            $details = (string)$_POST['to_id'];
            $credit = new Credits();
            $credit = $credit->addcredits($_POST['from_id'],$cre_amt,$cre_desc,$status,$details);
     
        }
        
        $request_second = Connect::find()->where(['from_id' => $_POST['to_id'] , 'to_id' => $_POST['from_id']])->one();
             
        if(!empty($request_second))
        {
            $date = time();
            $request_second->action_user_id = $_POST['to_id'];
            $request_second->status = '1';
            $request_second->updated_date = $date;
            $request_second->update();
            $label = 'Connect request accepted.';
			
            $srequest = new SuggestConnect();
            $srequest = SuggestConnect::find()->where(['connect_id' => $_POST['to_id'] , 'suggest_to' => $_POST['from_id']])->one();
            if($srequest)
            {
                $srequest->status = '1';
                $srequest->update();
            }
         
        }
        
        if(!empty($request) && !empty($request_second))
        {
            // Insert record in notification table also
            $from_id = $_POST['from_id'];
            $to_id = $_POST['to_id'];
            $notification = Notification::find()->where(['notification_type' => "connectrequestaccepted", 'from_connect_id' => "$from_id", 'user_id' => "$to_id"])->one();
            if($notification)
            {
                $notification->created_date = "$date";
                $notification->updated_date = "$date";
                $notification->update();
            }
            else
            {
                $notification =  new Notification();
                $notification->from_connect_id =   $_POST['from_id'];
                $notification->user_id = $_POST['to_id'];
                $notification->notification_type = 'connectrequestaccepted';
                $notification->is_deleted = '0';
                $notification->status = '1';
                $notification->created_date = "$date";
                $notification->updated_date = "$date";
                $notification->insert();
            }
        }

        return $label;

    }
	
	public function actionGenFrdAction()
    {
    	$result = array('status' => false);
    	if(isset($_POST['$id']) && $_POST['$id'] != '' ) {
    	if(isset($_POST['wall_user_id']) && $_POST['wall_user_id'] != '' ) {
    		$id = $_POST['$id'];
    		$wall_user_id = $_POST['wall_user_id'];
    		$session = Yii::$app->session;
	        $user_id = (string)$session->get('user_id');
	        if($user_id != 'undefined' && $user_id != '') {
	        	//check valid user............
	        	$isValidU = UserForm::find()->where(['_id' => $user_id])->asarray()->one();
	        	if(!empty($isValidU)) {
	        		// check user set or not connect request private
	        		$result_security = SecuritySetting::find()->where(['user_id' => $wall_user_id])->one();
					$request_setting = '';
					if (!empty($result_security)) {
						$request_setting = isset($result_security['connect_request']) ? $result_security['connect_request'] : '';
					}
					
					if($request_setting != 'Private') {
						$is_connect = Connect::find()->where(['from_id' => $user_id,'to_id' => "$wall_user_id",'status' => '1'])->one();
						if(!empty($is_connect)) {
							// do unconnect
							$is_connect->delete();
							$result = array('status' => true, 'code' => 'Unconnect user.', 'icon' => 'mdi mdi-account-plus');
							return json_encode($result, true);
						} else {
							$is_connect_request_sent = Connect::find()->where(['from_id' => "$user_id",'to_id' => "$wall_user_id",'status' => '0'])->one();
							if(!empty($is_connect_request_sent)) {
								//do cancel connect request
								$is_connect_request_sent->delete();
								$result = array('status' => true, 'code' => 'Cancel connect request', 'icon' => 'mdi mdi-account-plus');
								return json_encode($result, true);
							} else {
								// do send connect request
								Connect::deleteAll(['from_id' => $user_id , 'to_id' => $wall_user_id]);
								Connect::deleteAll(['to_id' => $wall_user_id , 'from_id' => $user_id]);

								$date = time();
								$fr = new Connect();
								$fr->from_id = $user_id;
								$fr->to_id = $wall_user_id;
								$fr->action_user_id = $user_id;
								$fr->status = '0';
								$fr->created_date = $date;
								$fr->updated_date = $date;
								$fr->insert();
								$result = array('status' => true, 'code' => 'Connect request sent.', 'icon' => 'mdi mdi-account-minus');
								return json_encode($result, true);
							}
						}

					}
	        	}
	        }
    	}
    	}
		return json_encode($result, true);
    }

    public function actionDeleteRequest()
    {
        $request_first = Connect::find()->where(['from_id' => $_POST['from_id'] , 'to_id' => $_POST['to_id']])->one();
        if(count($request_first)>0)
            $request_first->delete();
		
		$fromid = (string)$_POST['from_id'];
		$toid = (string)$_POST['to_id'];
		$userexist = BlockConnect::find()->where(['user_id' => $fromid])->one();
		$mute = new BlockConnect();
		if ($userexist)
		{
			if (strstr($userexist['block_ids'], $toid))
			{
				$mute = BlockConnect::find()->where(['user_id' => $fromid])->one();
				$mute->block_ids = str_replace($toid.',',"",$userexist['block_ids']);
				$muteids = $mute->block_ids;
				$mute->update();
				if(strlen($muteids) == 0)
				{
					$mute = BlockConnect::find()->where(['user_id' => $fromid])->one();
					$mute->delete();
				}
				return 1;
			}
		}
        
        $request_second = Connect::find()->where(['from_id' => $_POST['to_id'] , 'to_id' => $_POST['from_id']])->one();
        if(count($request_second)>0)
            $request_second->delete();
        
		$fromid = (string)$_POST['to_id'];
		$toid = (string)$_POST['from_id'];
		$userexist = BlockConnect::find()->where(['user_id' => $fromid])->one();
		$mute = new BlockConnect();
		if ($userexist)
		{
			if (strstr($userexist['block_ids'], $toid))
			{
				$mute = BlockConnect::find()->where(['user_id' => $fromid])->one();
				$mute->block_ids = str_replace($toid.',',"",$userexist['block_ids']);
				$muteids = $mute->block_ids;
				$mute->update();
				if(strlen($muteids) == 0)
				{
					$mute = BlockConnect::find()->where(['user_id' => $fromid])->one();
					$mute->delete();
				}
				return 1;
			}
		}   
    }
	
    public function actionSendInvitation()
    {
        $email = isset($_POST['connect_email'])?$_POST['connect_email']:'';
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $user_exists = UserForm::find()->where(['email' =>trim($email)])->one();
        $connect = new Connect();
        $label = '';
        if(empty($user_exists)) 
        {
            $date = time();
            $user = new UserForm();
            $user->email = $email;
            $user->status = '2';
            $user->created_date = $date;
            $user->updated_date = $date;
            $user->reference_user_id = (string)$uid;
            $user->insert();
            $new_user_id = $user->_id;
            $request_second = UserForm::find()->where(['_id' =>"$new_user_id"])->one();
       
            $connect->from_id = (string)$uid;
            $connect->to_id = (string)$new_user_id;
            $connect->action_user_id = (string)$uid;
            $connect->status = '1';
            $connect->created_date = $date;
            $connect->updated_date = $date;
            $connect->insert();
            if($connect->_id != '')
            {
                $connect1 = new Connect();
                $date = time();
                $connect1->from_id = (string)$new_user_id;
                $connect1->to_id = (string)$uid;
                $connect1->action_user_id = (string)$uid;
                $connect1->status = '1';
                $connect1->created_date = $date;
                $connect1->updated_date = $date;
                $connect1->insert();
			}
           $label = '1';
           $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
           $resetlink = substr($actual_link, 0, strpos($actual_link, "?")).'?id='.$new_user_id;
           
           try {
				$send = Yii::$app->mailer->compose()
				->setFrom('no-reply@iaminjapan.com')
				->setTo($email)
				->setSubject('I am in Japan - Somebody send invitation to use iaminjapan site.')
				->setHtmlBody('<html><head><meta charset="utf-8" /><title>I am in Japan</title></head><body style="margin:0;padding:0;background:#dfdfdf;"><div style="color: #353535; float:left; font-size: 13px;width:100%; font-family:Arial, Helvetica, sans-serif;text-align:center;padding:40px 0 0;"> <div style="width:600px;display:inline-block;"><img src="http://iaminjapan.com/frontend/web/images/logo.png" style="margin:0 0 10px;width:130px;float:left;"/> <div style="clear:both"></div><div style="border:1px solid #ddd;margin:0 0 10px;"><div style="background:#fff;padding:20px;border-top:10px solid #333;text-align:left;"><div style="color: #333;font-size: 13px;margin: 0 0 20px;">Hello,</div><div style="color: #333;font-size: 13px;">Your connect requested you to use iaminjapan site.</div><div style="color: #333;font-size: 13px;margin: 0 0 20px;">To complete signup process, <a href="'.$resetlink.'" target="_blank">click here</a>or paste the following link into your browser: '.$resetlink.'</div><div style="color: #333;font-size: 13px;">Thanks for using Iaminjapan!</div> <div style="color: #333;font-size: 13px;">The Iaminjapan Team</div></div></div><div style="clear:both"></div> <div style="width:600px;display:inline-block;font-size:11px;"><div style="color: #777;text-align: left;">&copy;  www.iaminjapan.com All rights reserved.</div> <div style="text-align: left;width: 100%;margin:5px  0 0;color:#777;">For anything you can reach us directly at <a href="contact@iaminjapan.com" style="color:#4083BF">contact@iaminjapan.com</a></div></div></div></div></body></html>')
				->send();
				} 
				catch (ErrorException $e) 
				{
					$label = $e->getMessage();
				}
        }
        else{
            $label = '0';
        }

        return $label;
    }
    
   	/* Function For The Connect List Setting For User */
	public function actionConnectlistSetting()
	{
		$model = new \frontend\models\SecuritySetting();
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		$result_security = SecuritySetting::find()->where(['user_id' => $user_id])->one();
		if($result_security) {
			$result_security->connect_list = ucwords($_POST['fs']);
			$result_security->update();
			return true;
		}
		else{
			$result_security = new \frontend\models\SecuritySetting();
			$result_security->user_id = $user_id;
			$result_security->connect_list = ucwords($_POST['fs']);
			$result_security->insert();
			return true;
		}	
	}
	
	public function actionNewConnections()
    { 
		$budge = 0;
        $model_connect = new Connect();
		$budge = $model_connect->connectRequestbadge();
        $session = Yii::$app->session; 
        $userid = (string)$session->get('user_id');
        if($userid != '' && $userid != 'undefined') {
			$request_budge = $model_connect->connectRequestbadge();
			$pending_requests = $model_connect->connectPendingRequests();
			?>

			<?php if($request_budge>0) { ?>
			<div class="fr-list not-area">
				<input type="hidden" name="new_budge_connect" id= "new_budge_connect" value="<?=$budge?>"/>
				<span class="not-title">Connect Requests</span>
				<div class="not-resultlist nice-scroll">
				<ul class="fr-listing">
				<?php
				foreach($pending_requests as $pending_request){ 
				$mutual_ctr = $model_connect->mutualconnectcount($pending_request['userdata']['_id']);
				$frnd_img = $this->getimage($pending_request['userdata']['_id'],'thumb');
				$uid = $pending_request['from_id'];
				?>
					<li id="request_<?=$pending_request['_id']?>">
						<?php $form = ActiveForm::begin(['id' => 'view_connect_request','options'=>['onsubmit'=>'return false;',],]); ?>
							<div class="fr-holder">
								<div class="img-holder">
									<a href="javascript:void(0)"><img class="img-responsive" src="<?= $frnd_img?>"></a>
								</div>
								<div class="desc-holder">
									<div class="desc">
										<a href="<?=Url::to(['userwall/index', 'id' => "$uid"])?>"><?=ucfirst($pending_request['userdata']['fname']).' '.ucfirst($pending_request['userdata']['lname'])?></a>
									</div>
									<div class="fr-btn-holder">
										<div class="accept_<?=$pending_request['from_id']?>">
											<button class="btn btn-primary btn-sm delete-connect btn-gray" onclick="removeConnect('<?=$pending_request['_id']?>','<?=$pending_request['from_id']?>','<?=$pending_request['to_id']?>','deleteRequest')">Delete</button>
											<button class="btn btn-primary btn-sm accept-connect" onclick="acceptConnectRequest('<?=$pending_request['from_id']?>','<?=$pending_request['to_id']?>')">Confirm</button>
										</div>
										<div class="showlabel showlabel_<?=$pending_request['from_id']?>">
											<label class="infolabel"><i class="zmdi zmdi-check"></i> Connections</label>
										</div>
									</div>
									<span class="acceptmsg_<?=$pending_request['from_id']?> request-accept dis-none"></span>
								</div>
								<input type="hidden" name="to_id" id="to_id" value="<?=$pending_request['_id']?>">
							</div>
						<?php ActiveForm::end() ;?>
					</li>
				<?php } ?>
				</ul></div>
				<span class="not-result bshadow">
					<a href="<?=Url::to(['site/travpeople'])?>">See More Requests <i class="mdi mdi-menu-right"></i></a>
				</span>
			</div>
			<?php } else { ?>
			<div class="fr-list not-area nopad">
				<input type="hidden" name="new_budge_connect" id= "new_budge_connect" value="<?=$budge?>"/>
				<span class="not-title">Connect Requests</span>
				<?php $this->getnolistfound('noconnectrequestfound');?>
			</div>
			<?php }
		} 
	
	}
	
	/* Function For The Connect List Setting For User */
	
	public function actionFetchconnectmenu()
    {
		$session = Yii::$app->session;
        $suserid = (string)$session->get('user_id');  
        $guserid = (string)$_POST['guser_id'];  
		$connectinfo = LoginForm::find()->select(['_id','fname','lname','country'])->where(['_id' => $guserid])->one();
		$connectid = (string)$connectinfo['_id'];
		$isconnect = Connect::find()->select(['_id'])->where(['from_id' => "$connectid",'to_id' => "$suserid",'status' => '1'])->one();
		$isconnectrequestsent = Connect::find()->select(['_id'])->where(['from_id' => "$connectid",'to_id' => "$suserid",'status' => '0'])->one();
		$is_connect_request_sent = Connect::find()->where(['from_id' => "$connectid",'to_id' => "$suserid",'status' => '0'])->one();
		$unfollowuser = UnfollowConnect::find()->where(['user_id' => "$suserid"])->andwhere(['like','unfollow_ids',$connectid])->one();
		if ($unfollowuser)
		{
			$folstatus = 'Unmute connect posts';
		}
		else
		{
			$folstatus = 'Mute connect posts';
		}
		$userexist = MuteConnect::find()->select(['mute_ids'])->where(['user_id' => "$suserid"])->one();
		if ($userexist)
		{
			if (strstr($userexist['mute_ids'], "$connectid"))
			{
					$getnot = 'Unmute notifications';
			}
			else
			{
					$getnot = 'Mute notifications';
			}
		}
		else
		{
				$getnot = 'Mute notifications';
		}

		$userblock = SecuritySetting::find()->select(['blocked_list'])->where(['user_id' => "$suserid"])->one();
		if (!empty($userblock))
		{
			if (strstr($userblock['blocked_list'], "$connectid"))
			{
					$getblock = 'Unblock';
			}
			else
			{
					$getblock = 'Block';
			}
		}
		else
		{
				$getblock = 'Block';
		}
		
		?>
		<?php if($isconnect){ ?>
		<li class="unconnect"><a href="javascript:void(0)" onclick="removeConnect('<?=$guserid?>','<?=$suserid?>','<?=$guserid?>', 'unconnect')">Unconnect</a></li>
		<?php } else if($isconnectrequestsent) { ?>
		<li class="cancleconnectrequest"><a href="javascript:void(0)" onclick="removeConnect('<?=$guserid?>','<?=$suserid?>','<?=$guserid?>', 'cancle_connect_request')">Cancel connect request</a></li>
		<?php } else { ?>
		<li class="addconnect"><a href="javascript:void(0)" onclick="addConnect('<?=$guserid?>')">Add connect</a></li>
		<?php } ?>
		<li><a href="<?=Url::to(['userwall/index', 'id' => $guserid])?>">View wall</a></li>
		<?php if($isconnect){ ?>
		<li><a href="#suggest-Connections-popup" class="suggest-Connections" onclick="connectList('<?=$connectinfo['_id']?>')">Suggest Connections</a></li>
		<li><a href="javascript:void(0)" onclick="getNotification(this, '<?=$connectinfo['_id']?>')" class="mute_connect_<?=$connectinfo['_id']?>"><?=$getnot?></a></li> 
		<?php } ?>
		
		<?php if($isconnect){ ?>
		<li><a href="javascript:void(0)" onclick="openchatboxfromwhoisaround('<?=$guserid?>');">Chat</a></li>
		<?php } else { ?>	
		<li><a href="#send-message-popup" class="popup-modal" onclick="fetchsendmessagecontent('<?= $guserid;?>');">Send message</a></li>
		<?php } ?>
		<?php if($isconnect){ ?>
		<li><a href="javascript:void(0)" onclick="opengiftboxfromwhoisaround('<?=$guserid?>');">Send gift</a></li>
		<?php } ?>
		<li><a href="javascript:void(0)" onclick="blockConnect(this, '<?=$connectinfo['_id']?>')" class="getblock_<?=$connectinfo['_id']?>"><?=$getblock?></a></li>
		<?php 
	}
}
