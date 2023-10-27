<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\mongodb\ActiveRecord;
use frontend\models\LoginForm;
use frontend\models\PostForm;
use frontend\models\Personalinfo;
use frontend\models\Page;
use frontend\models\UserForm;
use frontend\models\Vip;
use frontend\models\TravAdsVisitors;
use frontend\models\Order;
use frontend\models\UserMoney;

class AdsController extends Controller
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
	    
	    return $this->render('ads', array('checkuserauthclass' => $checkuserauthclass));
    }
    
    public function actionCreate() 
    {
    	$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');

        if(isset($user_id) && $user_id != '') {
        	$checkuserauthclass = UserForm::isUserExistByUid($user_id);
        } else {
        	$checkuserauthclass = 'checkuserauthclassg';
        }   

        return $this->render('create', array('checkuserauthclass' => $checkuserauthclass));
    }
	
	public function actionManage() 
    {
	    $session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		if(isset($user_id) && $user_id != '') {
		    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
		    if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
        		return $this->render('manage',array('user_id' => $user_id));
        	}
        }

        $url = Yii::$app->urlManager->createUrl(['ads/manage']);
		Yii::$app->getResponse()->redirect($url);
    }
	
	public function actionAdstat() 
    {
    	$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');

		if(isset($user_id) && $user_id != '') {
		    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
		    if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
				$baseUrl = $_POST['baseUrl'];
				$adid = $_POST['adid'];
				$ad = PostForm::find()->where(['_id' => $adid])->one();
				return $this->render('adstats',array('user_id' => $user_id,'baseUrl' => $baseUrl,'adid' => $adid,'ad' => $ad));
        	} 
        }

        $url = Yii::$app->urlManager->createUrl(['ads/manage']);
		Yii::$app->getResponse()->redirect($url);
    }
	
	public function actionMyads() 
    {
    	$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
 
		if(isset($user_id) && $user_id != '') {
		    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
		    if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
				$baseUrl = $_POST['baseUrl'];
				$myads = PostForm::getUserAds($user_id);
				return $this->render('myads',array('user_id' => $user_id,'baseUrl' => $baseUrl,'myads' => $myads));
        	}
        }

        $url = Yii::$app->urlManager->createUrl(['ads/manage']);
		Yii::$app->getResponse()->redirect($url);
    }
	
	public function actionEditad() 
    {
    	$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');

		if(isset($user_id) && $user_id != '') {
		    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
		    if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
				$baseUrl = $_POST['baseUrl'];
				$adid = $_POST['adid'];
				$ad = PostForm::find()->where(['_id' => $adid])->one();
				return $this->render('editad',array('user_id' => $user_id,'baseUrl' => $baseUrl,'adid' => $adid,'ad' => $ad));
			}
		}
		
		$url = Yii::$app->urlManager->createUrl(['ads/manage']);
		Yii::$app->getResponse()->redirect($url);
    }
	
	public function actionViewad() 
    {
    	$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');

		if(isset($user_id) && $user_id != '') {
		    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
		    if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
				$adid = $_POST['adid'];
				$type = $_POST['type'];
				$ad = PostForm::find()->where(['_id' => (string)$adid])->one();
				if($ad)
				{
					TravAdsVisitors::adInsertion($adid,$user_id,$type);
					return true;
				}
				else
				{
					return false;
				}
			}
        }

        $url = Yii::$app->urlManager->createUrl(['ads/manage']);
		Yii::$app->getResponse()->redirect($url);
    }
	
	public function actionNewad() 
    {
    	$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		$filepath = "uploads/ads/";
		$money = UserMoney::find()->where(['user_id' => $user_id])->one();
		if(isset($money) && !empty($money))
		{
			$session->set('user_money',$money['amount']);
		}
		else
		{
			$session->set('user_money',0);
		}	

		if(isset($user_id) && $user_id != '') {
		    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
		    if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {			
				if($_POST['adaction'] == 'add')
				{
					PostForm::deleteAll(['post_user_id' => "$user_id", 'is_ad' => '0','post_type' => 'ad']);
				}
				
				$adobj = $_POST['adobj']; 
				$adname = $_POST['adname'];
				if($adobj=='travstorefocus')
				{
					$travstorefocusitem = $_POST['travstorefocusitem'];
					$post = PostForm::find()->where(['_id' => $travstorefocusitem])->one();
				}
				else
				{
					if($_POST['adaction'] == 'edit')
					{
						$advtmnt_id = (string) $_POST['advrt_id'];
						$post = PostForm::find()->where(['_id' => $advtmnt_id])->one();
						$is_ad = $post['is_ad'];
												
						$t_date = time();
						$ad_e_date = $_POST['enddate']; 
						$ad_e_date = strtotime($ad_e_date.' 00:00:00');
						$early_date =  $post['adstartdate'];
						$datediff = $t_date - $early_date;
						
						$days = floor($datediff / (60 * 60 * 24));
						if(isset($post['ad_duration']) && !empty($post['ad_duration']) && $post['ad_duration'] > 0)
						{
							$old_per_day =  $post['adtotbudget'] / $post['ad_duration'];
						}
						else
						{
							$post['ad_duration'] = 1;
							$old_per_day =  $post['adtotbudget'] / $post['ad_duration'];
						}
						$old_used = $old_per_day * $days;
						$old_budget = (int) $post['adtotbudget'] - $old_used;
						
						$session->set('ad_id',$post['_id']);
						$session->set('ad_start_date',$t_date);
						$session->set('ad_end_date',$ad_e_date);
						$session->set('ad_min_budget',$_POST['min_budget']);
					}
					else
					{ 
						$post = new PostForm();
						$is_ad = '0';
						$old_budget = 0;
					} 
				}
				$post->adobj = "$adobj";
				$post->adname = "$adname";
				$post->is_ad = "$is_ad";
				$post->post_status = '1';
				$post->is_deleted = '0';
				$post->post_ip = $_SERVER['REMOTE_ADDR'];
				$post->post_type = 'ad';
				$post->post_user_id = "$user_id";
				$post->share_setting = "Disable";
				$post->comment_setting = "Enable";
				$post->post_privacy = "Public";
				$date = time();
				$today_date = date("M d, Y", $date);
				$post->post_created_date = "$date";
				$isvip = Vip::isVIP((string)$user_id);
				$impression = $this->getadrate($isvip,'impression');
				$action = $this->getadrate($isvip,'action');
				$click = $this->getadrate($isvip,'click');
				$post->rate_impression = $impression;
				$post->rate_action = $action;
				$post->rate_click = $click;
				if($adobj=='pagelikes')
				{
					$pagelikenames = $_POST['pagelikenames'];
					$pagelikescatch = substr($_POST['pagelikescatch'],0,70);
					$pagelikesheader = $_POST['pagelikesheader'];
					$pagelikestext = substr($_POST['pagelikestext'],0,140);
					$pagelikes = $_POST['pagelikes'];
					if($pagelikes != 'undefined') {
						$filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $pagelikes));
						$uniqueID = rand(999, 9999).time();
						$imageName = $uniqueID.'.png';
						if(file_put_contents($filepath.$imageName, $filterString)) {
							$post->adimage = $filepath.$imageName;
						} else {
							$post->adimage = "undefined";
						}
					} else {
						$post->adimage = "undefined";
					}
					$post->adid = "$pagelikenames";
					$post->adcatch = "$pagelikescatch";
					$post->adheadeline = "$pagelikesheader";
					$post->adtext = "$pagelikestext";
					//$post->adimage = "$pagelikes";
				}
				else if($adobj=='brandawareness')
				{
					$brandawarenesscatch = substr($_POST['brandawarenesscatch'],0,50);
					$brandawarenesstext = substr($_POST['brandawarenesstext'],0,140);
					$brandawarenesssite = $_POST['brandawarenesssite'];
					$brandawareness = $_POST['brandawareness'];
					if($brandawareness != 'undefined') {
						$filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $brandawareness));
						$uniqueID = rand(999, 9999).time();
						$imageName = $uniqueID.'.png';
						if(file_put_contents($filepath.$imageName, $filterString)) {
							$post->adimage = $filepath.$imageName;
						} else {
							$post->adimage = "undefined";
						}
					} else {
						$post->adimage = "undefined";
					}
					$post->adcatch = "$brandawarenesscatch";
					$post->adtext = "$brandawarenesstext";
					$post->adurl = "$brandawarenesssite";
				}
				else if($adobj=='websiteleads')
				{
					$websiteleadstitle = $_POST['websiteleadstitle'];
					$websiteleadslogo = $_POST['websiteleadslogo'];
					$websiteleadscatch = substr($_POST['websiteleadscatch'],0,70);
					$websiteleadsheader = $_POST['websiteleadsheader'];
					$websiteleadstext = substr($_POST['websiteleadstext'],0,140);
					$websiteleadssite = $_POST['websiteleadssite'];
					$websiteleads = $_POST['websiteleads'];
					if($websiteleads != 'undefined') {
						$filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $websiteleads));
						$uniqueID = rand(999, 9999).time();
						$imageName = $uniqueID.'.png';
						if(file_put_contents($filepath.$imageName, $filterString)) {
							$post->adimage = $filepath.$imageName;
						} else {
							$post->adimage = "undefined";
						}
					} else {
						$post->adimage = "undefined";
					}

					if($websiteleadslogo != 'undefined') {
						$filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $websiteleadslogo));
						$uniqueID = time().rand(999, 9999);
						$imageName = $uniqueID.'.png';
						if(file_put_contents($filepath.$imageName, $filterString)) {
							$post->adlogo = $filepath.$imageName;
						} else {
							$post->adlogo = "undefined";
						}
					} else {
						$post->adlogo = "undefined";
					}
					$post->adtitle = "$websiteleadstitle";
					$post->adcatch = "$websiteleadscatch";
					$post->adheadeline = "$websiteleadsheader";
					$post->adtext = "$websiteleadstext";
					$post->adurl = "$websiteleadssite";
				}
				else if($adobj=='websiteconversion')
				{
					$websiteconversiontitle = $_POST['websiteconversiontitle'];
					$websiteconversionlogo = $_POST['websiteconversionlogo'];
					$websiteconversioncatch = substr($_POST['websiteconversioncatch'],0,70);
					$websiteconversionheader = $_POST['websiteconversionheader'];
					$websiteconversiontext = substr($_POST['websiteconversiontext'],0,140);
					$websiteconversiontype = $_POST['websiteconversiontype'];
					$websiteconversionsite = $_POST['websiteconversionsite'];
					$websiteconversion = $_POST['websiteconversion'];
					if($websiteconversion != 'undefined') {
						$filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $websiteconversion));
						$uniqueID = rand(999, 9999).time();
						$imageName = $uniqueID.'.png';
						if(file_put_contents($filepath.$imageName, $filterString)) {
							$post->adimage = $filepath.$imageName;
						} else {
							$post->adimage = "undefined";
						}
					} else {
						$post->adimage = "undefined";
					}

					if($websiteconversionlogo != 'undefined') {
						$filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $websiteconversionlogo));
						$uniqueID = time().rand(999, 9999);
						$imageName = $uniqueID.'.png';
						if(file_put_contents($filepath.$imageName, $filterString)) {
							$post->adlogo = $filepath.$imageName;
						} else {
							$post->adlogo = "undefined";
						}
					} else {
						$post->adlogo = "undefined";
					}
					$post->adtitle = "$websiteconversiontitle";
					$post->adcatch = "$websiteconversioncatch";
					$post->adheadeline = "$websiteconversionheader";
					$post->adtext = "$websiteconversiontext";
					$post->adbtn = "$websiteconversiontype";
					$post->adurl = "$websiteconversionsite";
				}
				else if($adobj=='inboxhighlight')
				{
					$inboxhighlighttitle = $_POST['inboxhighlighttitle'];
					$inboxhighlightlogo = $_POST['inboxhighlightlogo'];
					$inboxhighlightcatch = substr($_POST['inboxhighlightcatch'],0,30);
					$inboxhighlightsubcatch = substr($_POST['inboxhighlightsubcatch'],0,35);
					$inboxhighlightheader = $_POST['inboxhighlightheader'];
					$inboxhighlighttext = substr($_POST['inboxhighlighttext'],0,140);
					$inboxhighlighttype = $_POST['inboxhighlighttype'];
					$inboxhighlightsite = $_POST['inboxhighlightsite'];
					$inboxhighlightimage = $_POST['inboxhighlightimage'];
					if($inboxhighlightimage != 'undefined') {
						$filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $inboxhighlightimage));
						$uniqueID = rand(999, 9999).time();
						$imageName = $uniqueID.'.png';
						if(file_put_contents($filepath.$imageName, $filterString)) {
							$post->adimage = $filepath.$imageName;
						} else {
							$post->adimage = "undefined";
						}
					} else {
						$post->adimage = "undefined";
					}

					if($inboxhighlightlogo != 'undefined') {
						$filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $inboxhighlightlogo));
						$uniqueID = time().rand(999, 9999);
						$imageName = $uniqueID.'.png';
						if(file_put_contents($filepath.$imageName, $filterString)) {
							$post->adlogo = $filepath.$imageName;
						} else {
							$post->adlogo = "undefined";
						}
					} else {
						$post->adlogo = "undefined";
					}
					$post->adtitle = "$inboxhighlighttitle";
					$post->adcatch = "$inboxhighlightcatch";
					$post->adsubcatch = "$inboxhighlightsubcatch";
					$post->adheadeline = "$inboxhighlightheader";
					$post->adtext = "$inboxhighlighttext";
					$post->adbtn = "$inboxhighlighttype";
					$post->adurl = "$inboxhighlightsite";
				}
				else if($adobj=='pageendorse')
				{
					$pagelikenames = $_POST['pagelikenames'];
					$pagelikescatch = substr($_POST['pagelikescatch'],0,70);
					$pagelikesheader = $_POST['pagelikesheader'];
					$pagelikestext = substr($_POST['pagelikestext'],0,140);
					$pageendorseimage = $_POST['pageendorseimage'];
					if($pageendorseimage != 'undefined') {
						$filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $pageendorseimage));
						$uniqueID = rand(999, 9999).time();
						$imageName = $uniqueID.'.png';
						if(file_put_contents($filepath.$imageName, $filterString)) {
							$post->adimage = $filepath.$imageName;
						} else {
							$post->adimage = "undefined";
						}
					} else {
						$post->adimage = "undefined";
					}
					$post->adid = "$pagelikenames";
					$post->adcatch = "$pagelikescatch";
					$post->adheadeline = "$pagelikesheader";
					$post->adtext = "$pagelikestext";
				}
				else if($adobj=='eventpromo')
				{
					$adevents = $_POST['adevents'];
					$eventpromocatch = substr($_POST['eventpromocatch'],0,70);
					$eventpromoheader = $_POST['eventpromoheader'];
					$eventpromotext = substr($_POST['eventpromotext'],0,140);
					$eventpromo = $_POST['eventpromo'];
					if($eventpromo != 'undefined') {
						$filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $eventpromo));
						$uniqueID = rand(999, 9999).time();
						$imageName = $uniqueID.'.png';
						if(file_put_contents($filepath.$imageName, $filterString)) {
							$post->adimage = $filepath.$imageName;
						} else {
							$post->adimage = "undefined";
						}
					} else {
						$post->adimage = "undefined";
					}
					$post->adid = "$adevents";
					$post->adcatch = "$eventpromocatch";
					$post->adheadeline = "$eventpromoheader";
					$post->adtext = "$eventpromotext";
				}
				$ads_loc = $_POST['ads_loc'];
				$post->adlocations = "$ads_loc";
				$minage = $_POST['minage'];
				if($minage < 0 || $minage > 100)
				{
					$minage = 0;
				}
				$post->adminage = (int)$minage;
				$maxage = $_POST['maxage'];
				if($maxage < 0 || $maxage > 100)
				{
					$maxage  = 100;
				}
				$post->admaxage = (int)$maxage;
				$ads_lang = $_POST['ads_lang'];
				$post->adlanguages = "$ads_lang";
				if(isset($_POST['male']) && ($_POST['male'] == 'male'))
				{
					$post->admale = "male";
				}
				else
				{
					$post->admale = null;
				}
				if(isset($_POST['female']) && ($_POST['female'] == 'female'))
				{
					$post->adfemale = "female";
				}
				else
				{
					$post->adfemale = null;
				}
				if(!isset($_POST['male']) && !isset($_POST['female']))
				{
					$post->admale = "male";
					$post->adfemale = "female";
				}
				$ads_pro = $_POST['ads_pro'];
				$post->adpro = "$ads_pro";
				$ads_int = $_POST['ads_int'];
				$post->adint = "$ads_int";
				$min_budget = $_POST['min_budget'];
				if($min_budget < 1)
				{
					$min_budget = 1;
				}
				if($_POST['adaction'] != 'edit')
				{
					$post->adbudget = (int)$min_budget;
				}
				$runat = $_POST['runat'];
				$post->adruntype = "$runat";
				
					$startdate = strtotime($_POST['startdate']);
					$startdate = date('Y-m-d', $startdate);

					$enddate = strtotime($_POST['enddate']);
					$enddate = date('Y-m-d', $enddate);

					$end_date = strtotime($enddate.' 00:00:00');
					$end_date = date("M d, Y", $end_date);
					if($_POST['adaction'] != 'edit')
					{
						$post->adstartdate = strtotime($startdate.' 00:00:00');
						$post->adenddate = strtotime($enddate.' 00:00:00');
					}
					$datediff = strtotime($enddate.' 00:00:00') - strtotime($startdate.' 00:00:00');
	                $days = floor($datediff / (60 * 60 * 24));
					$min_budget = $min_budget * ($days+1);
					
					$session->set('ad_duration',(int) $days+1);
					if($_POST['adaction'] != 'edit')
					{
						$post->ad_duration = (int) $days+1;
					}	
					
					if($_POST['adaction'] != 'edit')
					{
						$post->adbudget = (int)$min_budget;
					}
					
					$response['days'] = $days+1;
					$response['end_date'] = $end_date;
					if($_POST['adaction'] != 'edit')
					{
						$post->adtotbudget = (int)$min_budget;
					}
					
				if($adobj=='travstorefocus')
				{
					$travstorefocustype = $_POST['travstorefocustype'];
					$travstorefocussite = $_POST['travstorefocussite'];
					$post->adbtn = "$travstorefocustype";
					$post->adurl = "$travstorefocussite";
					$post->share_setting = "Enable";
					$post->update();
				}
				else
				{
					if($_POST['adaction'] == 'edit')
					{ 
						$post->update();
					}
					else
					{ 
						$post->insert();
					}
				}
				$response['msg'] = 'success';
				if($_POST['adaction'] == 'edit')
				{
					if($min_budget > $old_budget)
					{
						$response['total_amount'] = $min_budget - $old_budget;
					}
					else
					{
						$response['total_amount'] = 0;
					}
				}
				else
				{
					$response['total_amount'] = (int)$min_budget;
				}	
				
				$response['old_total_budget'] = (int) $old_budget;
				
				if($old_budget > $min_budget)
				{
					$response['benifit_amount'] = $old_budget - $min_budget;
				}
				else
				{
					$response['benifit_amount'] = 0;
				}	
				$response['ad_cost'] = $min_budget;
				return json_encode($response);
			}
		}
		
		$url = Yii::$app->urlManager->createUrl(['ads/manage']);
		Yii::$app->getResponse()->redirect($url);
    }
	
    public function actionAdsimagecrop()
    {
    	$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');

		if(isset($user_id) && $user_id != '') {
		    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
		    if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
		        $imgpaths = '../web/uploads/ads';
		        if (!file_exists($imgpaths))
		        {
					mkdir($imgpaths, 0777, true);
		        }
		        $dt = $user_id.'_'.time();
		        $imgUrl = $_POST['imgUrl'];
		        // original sizes
		        $imgInitW = $_POST['imgInitW'];
		        $imgInitH = $_POST['imgInitH'];
		        // resized sizes
		        $imgW = $_POST['imgW'];
		        $imgH = $_POST['imgH'];
		        // offsets
		        $imgY1 = $_POST['imgY1'];
		        $imgX1 = $_POST['imgX1'];
		        // crop box
		        $cropW = $_POST['cropW'];
		        $cropH = $_POST['cropH'];
		        // rotation angle
		        $angle = $_POST['rotation'];

		        $jpeg_quality = 100;

		        $output_filename = "uploads/ads/".$dt;

		        $what = getimagesize($imgUrl);

		        switch(strtolower($what['mime']))
		        {
					case 'image/png':
						$img_r = imagecreatefrompng($imgUrl);
						$source_image = imagecreatefrompng($imgUrl);
						$type = '.png';
						break;
					case 'image/jpeg':
						$img_r = imagecreatefromjpeg($imgUrl);
						$source_image = imagecreatefromjpeg($imgUrl);
						error_log("jpg");
						$type = '.jpeg';
						break;
					case 'image/gif':
						$img_r = imagecreatefromgif($imgUrl);
						$source_image = imagecreatefromgif($imgUrl);
						$type = '.gif';
						break;
					default: die('image type not supported');
		        }

		        // resize the original image to size of editor
		        $resizedImage = imagecreatetruecolor($imgW, $imgH);
		        imagecopyresampled($resizedImage, $source_image, 0, 0, 0, 0, $imgW, $imgH, $imgInitW, $imgInitH);
		        // rotate the rezized image
		        $rotated_image = imagerotate($resizedImage, -$angle, 0);
		        // find new width & height of rotated image
		        $rotated_width = imagesx($rotated_image);
		        $rotated_height = imagesy($rotated_image);
		        // diff between rotated & original sizes
		        $dx = $rotated_width - $imgW;
		        $dy = $rotated_height - $imgH;
		        // crop rotated image to fit into original rezized rectangle
		        $cropped_rotated_image = imagecreatetruecolor($imgW, $imgH);
		        imagecolortransparent($cropped_rotated_image, imagecolorallocate($cropped_rotated_image, 0, 0, 0));
		        imagecopyresampled($cropped_rotated_image, $rotated_image, 0, 0, $dx / 2, $dy / 2, $imgW, $imgH, $imgW, $imgH);
		        // crop image into selected area
		        $final_image = imagecreatetruecolor($cropW, $cropH);
		        imagecolortransparent($final_image, imagecolorallocate($final_image, 0, 0, 0));
		        imagecopyresampled($final_image, $cropped_rotated_image, 0, 0, $imgX1, $imgY1, $cropW, $cropH, $cropW, $cropH);
		        // finally output png image
		        //imagepng($final_image, $output_filename.$type, $png_quality);
		        imagejpeg($final_image, $output_filename.$type, $jpeg_quality);

		        // Store For Original Image..
		        $imageDataEncoded = base64_encode(file_get_contents($imgUrl));
		        $imageData = base64_decode($imageDataEncoded);
		        $source = imagecreatefromstring($imageData);
		        $imageSave = imagejpeg($source,'uploads/ads/ORI_'.$dt.$type,100);
		        imagedestroy($source);

		        $response = Array(
					"status" => 'success',
					"url" => $output_filename.$type
		        );
		        return json_encode($response);
		    }
    	}
    	
    	$url = Yii::$app->urlManager->createUrl(['ads/manage']);
		Yii::$app->getResponse()->redirect($url);

    }
    
    public function actionGettravitem()
    {
    	$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		$data = array();

		if(isset($user_id) && $user_id != '') {
		    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
		    if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
	            $postid = isset($_POST['id']) ? $_POST['id'] : '';
	            $result = PostForm::find()->where(['_id' => $postid])->one();
	            if($result)
	            {
	                if($_SERVER['HTTP_HOST'] == 'localhost')
	                {
	                    $baseUrl = '/iaminjapan-code/frontend/web';
	                }
	                else
	                {
	                    $baseUrl = '/frontend/web/assets/baf1a2d0';
	                }
	                $data['msg'] = 'success';
	                $data['name'] = $result['post_title'];
	                if(empty($result['image']))
	                {
	                	  
						$assetsPath = '../../vendor/bower/travel/images/';
	                    $result['image'] = $assetsPath.'travitem-default.png';
	                }
	                else
	                {
	                    $explode = explode(',', $result['image']);
	                    $result['image'] = $explode[0];
	                }
	                $data['img'] = $baseUrl.$result['image'];
	                if(empty($result['trav_price']))
	                {
	                    $result['trav_price'] = 'Price not added';
	                }
	                else
	                {
	                    $result['trav_price'] = '$'.$result['trav_price'];
	                }
	                $data['price'] = $result['trav_price'];
	                if(!isset($result['currentlocation']) && empty($result['currentlocation']))
	                {
	                    $result['currentlocation'] = 'Address not added';
	                }
	                $data['address'] = $result['currentlocation'];
	                $data['desc'] = $result['post_text'];
	            }
	            else
	            {
	                $data['msg'] = 'fail';
	            }
	            return json_encode($data);
        	}
        }

        $url = Yii::$app->urlManager->createUrl(['ads/manage']);
		Yii::$app->getResponse()->redirect($url);
    }
	
	public function actionSwitchad()
    {
    	$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		$data = array();

		if(isset($user_id) && $user_id != '') {
		    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
		    if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
	            if(isset($_POST['adid']) && !empty($_POST['adid']))
				{
					$adid = $_POST['adid'];
					$ad_exist = PostForm::find()->where(['_id' => "$adid"])->one();
					if($ad_exist['is_ad']=='1'){$adstatus='0';}
					else{$adstatus='1';}
					$ad_exist->is_ad = $adstatus;
					if($ad_exist->update())
					{
						$data['msg'] = 'success';
						$data['status'] = $ad_exist['is_ad'];
					}
					else
					{
						$data['msg'] = 'fail';
					}
				} else {
					$data['msg'] = 'fail';
				}          	
				return json_encode($data, true);
			}
		}

		$url = Yii::$app->urlManager->createUrl(['ads/manage']);
		Yii::$app->getResponse()->redirect($url);
    }
	
	public function actionCheckaudience() 
    {
    	$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		$locationLabel = '';
		$languageLabel = '';

		if(isset($user_id) && $user_id != '') { 
		    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
		    if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
	        	if(isset($_POST) && !empty($_POST)) {

		            $totaltravpeople = LoginForm::find()->where(['status' => '1'])->asarray()->all();
		            $totaltravpeoplecount = count($totaltravpeople);

		            $targetaud = 0;

		            $location = $_POST['location'];
		            if($location != '') {
		            	$location = explode(",", strtolower($location));
		            	if(count($location) == 1) {
		            		$locationLabel = $location[0];
		            	} else if(count($location) == 2) {
		            		$locationLabel = $location[0].' and '.$location[1];
		            	} else {
							$locationLabel = $location[0].' and <a href="javascript:void(0)" class="liveliketooltip" data-title="'.implode('<br/>', array_slice($location, 1)).'">'.(count($location) - 1).' others</a>';
		            	}
		            }

		            $minage = $_POST['minage'];
		            $maxage = $_POST['maxage'];
		            
		            $language = $_POST['language'];
		            if($language != '') {
		            	$language = explode(",", strtolower($language));
		            	if(count($language) == 1) {
		            		$languageLabel = $language[0];
		            	} else if(count($language) == 2) {
		            		$languageLabel = $language[0].' and '.$language[1];
		            	} else {
							$languageLabel = $language[0].' and <a href="javascript:void(0)" class="liveliketooltip" data-title="'.implode('<br/>', array_slice($language, 1)).'">'.(count($language) - 1).' others</a>';
		            	}
		            }

		            $gender = $_POST['gender'];
		            if($gender != '') {
		            	$gender = explode(",", strtolower($gender));
		            }
		            if(in_array('all', $gender)) {
		            	$gender[] = 'male';	
		            	$gender[] = 'female';	
		            }

		            $proficient = $_POST['proficient'];
		            if($proficient != '') {
		            	$proficient = explode(",", strtolower($proficient));
		            }

		            $interest = $_POST['interest'];
		            if($interest != '') {
		            	$interest = explode(",", strtolower($interest));
		            }
		            
		            $i = 0;
		            foreach($totaltravpeople as $totaltravuser)
		            {

		            	if(!empty($gender)) {
		            		$userGen = isset($totaltravuser['gender']) ? $totaltravuser['gender'] : '';
							$userGen = strtolower($userGen);
		            		if(!in_array($userGen, $gender)) {
		            			continue;
		            		}
		            	}
						if(!empty($location)) {
		            		$userLoc = isset($totaltravuser['country']) ? $totaltravuser['country'] : '';
							$userLoc = strtolower($userLoc);
		            		if(!in_array($userLoc, $location)) {
		            			continue;
		            		}
		            	}

						if(isset($totaltravuser['birth_date']) && $totaltravuser['birth_date'] != '') {
							$birthDate = isset($totaltravuser['birth_date']) ? $totaltravuser['birth_date'] : '';
							if($birthDate == ''){
								continue;
							} 
							else 
							{
								$birthDate = strtotime($birthDate);
								$birthDate = date('d/m/Y', $birthDate);
					            $birthDate = explode('/', $birthDate);		

								if(count($birthDate) == 3) {
									//get age from date or birthdate
									if(isset($birthDate[0]) && $birthDate[1] && $birthDate[2]) {
										$age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md")

										? ((date("Y") - $birthDate[2]) - 1)
										: (date("Y") - $birthDate[2]));

										if ($age >= 0) {
											$age = $age;
										} else {
											$age = 0;
										}

										if($age >= $minage && $age <= $maxage) {
										} else {
											continue;
										}
									} else {
										continue;
									}
								} else {
									continue;
								}
							}
						}

		            	$id = (string)$totaltravuser['_id'];

						$personalinfo = Personalinfo::find()->where([(string)'user_id' => $id])->asarray()->one();
						// check for language..
						if(!empty($language)) {
							if(!empty($personalinfo)) {
								$userLang = isset($personalinfo['language']) ? $personalinfo['language'] : '';
								$userLang = strtolower($userLang);
					            $userLang = str_replace(" ", ",", $userLang);
					            $userLang = str_replace(",,", ",", $userLang);
					            $userLang = str_replace(" ", ",", $userLang);
					            $userLang = explode(",", $userLang);
					            $userLang = array_filter($userLang);
					            $userLang = array_map('trim', $userLang);
								foreach ($language as $key => $slanguage) {
				                    if(!in_array($slanguage, $userLang)) {
				                        continue 2;
				                    }
				                }
							} else {
								continue;
							}
						}

						// check for language..
						if(!empty($proficient)) {
							if(!empty($personalinfo)) {
								$userOccu = isset($personalinfo['occupation']) ? $personalinfo['occupation'] : '';
								$userOccu = strtolower($userOccu);
					            $userOccu = str_replace(" ", ",", $userOccu);
					            $userOccu = str_replace(",,", ",", $userOccu);
					            $userOccu = str_replace(" ", ",", $userOccu);
					            $userOccu = explode(",", $userOccu);
					            $userOccu = array_filter($userOccu);
					            $userOccu = array_map('trim', $userOccu);
								foreach ($proficient as $key => $sproficient) {
				                    if(!in_array($sproficient, $userOccu)) {
				                        continue 2;
				                    }
				                }
							} else {
								continue;
							}
						}

						// check for language..
						if(!empty($interest)) {
							if(!empty($personalinfo)) {
								$userInte = isset($personalinfo['interests']) ? $personalinfo['interests'] : '';
								$userInte = strtolower($userInte);
					            $userInte = str_replace(" ", ",", $userInte);
					            $userInte = str_replace(",,", ",", $userInte);
					            $userInte = str_replace(" ", ",", $userInte);
					            $userInte = explode(",", $userInte);
					            $userInte = array_filter($userInte);
					            $userInte = array_map('trim', $userInte);
								foreach ($interest as $key => $sinterest) {
				                    if(!in_array($sinterest, $userInte)) {
				                        continue 2;
				                    }
				                }
							} else {
								continue;
							}
						}

						$i++;
		            }

		            $targetaud = $i;
		            $count = (int)(($targetaud / $totaltravpeoplecount)*100);
		            //return $count;
		            $data['count'] = $targetaud;
		            $data['meter'] = $count;
		            $data['languageLabel'] = $languageLabel;
		            $data['locationLabel'] = $locationLabel;
		            return json_encode($data);
		        }
		    }
        }

        $url = Yii::$app->urlManager->createUrl(['ads/manage']);
	    Yii::$app->getResponse()->redirect($url);
    }
	
	public function actionSuccessads() 
	{
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		$ad_id = $session->get('ad_id');
		$usermoney = $session->get('user_money');
		$ad_start_date = $session->get('ad_start_date');
		$ad_end_date = $session->get('ad_end_date');
		$ad_duration = $session->get('ad_duration');
		$ad_min_budget = $session->get('ad_min_budget');

		if(isset($user_id) && $user_id != '') {
		    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
		    if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
				if($_REQUEST['st'] == "Completed" || $_REQUEST['st'] == "Pending")
				{
					if(isset($ad_id) && !empty($ad_id))
					{
						$ad = PostForm::find()->where(['_id' => $ad_id,'post_user_id' => "$user_id",'is_ad' => '1'])->one();
						if(!empty($ad)){
							$adbudget = $ad_duration * $ad_min_budget;
							$ad->adtotbudget =  (int) $adbudget;
							$ad->adbudget =  (int) $adbudget;
							$ad->adstartdate = $ad_start_date;
							$ad->adenddate = $ad_end_date;
							$ad->ad_duration = $ad_duration;
							$ad->update();
						}
					}
					$result = PostForm::find()->where(['post_user_id' => "$user_id",'is_ad' => '0','post_type' => 'ad'])->one();
					if($result)
					{
						$result->is_ad = '1';
						$result->update();
					}
					
					$detail = (string)$result['_id'];					
					$item_number = $_REQUEST['tx'];
					$order_type = 'ads';
					$transaction_id = $_REQUEST['tx'];
					$amount = $_REQUEST['amt'];
					$status = $_REQUEST['st'];
					$curancy = $_REQUEST['cc'];
					$order = new Order();
					$order = $order->neworder($item_number,$transaction_id,$amount,$curancy,$status,$order_type,$detail);
					
					$money = UserMoney::find()->where(['user_id' => $user_id])->one();
					if(!empty($money))
					{
						$money->amount = 0;
						$money->update();
					}
					
					$st = "success";
				}
				else
				{
					$st = $_REQUEST['st'];
				}
				$url = Yii::$app->urlManager->createUrl(['ads/manage',$st => $st]);
				Yii::$app->getResponse()->redirect($url);
	        }
	    }
	    $url = Yii::$app->urlManager->createUrl(['ads/manage']);
	    Yii::$app->getResponse()->redirect($url);
    }
	
	public function actionSuccessCard() 
	{
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		$ad_id = $session->get('ad_id');
		$usermoney = $session->get('user_money');
		$ad_start_date = $session->get('ad_start_date');
		$ad_end_date = $session->get('ad_end_date');
		$ad_duration = $session->get('ad_duration');
		$ad_min_budget = $session->get('ad_min_budget');
		
		if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {

				$payableAmount = $_POST['amount'];			
				$nameArray = explode(' ',$_POST['name_on_card']);
				
				//Buyer information
				$firstName = $nameArray[0];
				$lastName = $nameArray[1];
				$city = 'Kolkata';
				$zipcode = '700091';
				$countryCode = 'IN';
				
				//Create an instance of PaypalPro class
				$paypal = new \frontend\models\PaypalPro();
				
				//Payment details
				$paypalParams = array(
					'paymentAction' => 'Sale',
					'amount' => $payableAmount,
					'currencyCode' => 'USD',
					'creditCardType' => $_POST['card_type'],
					'creditCardNumber' => trim(str_replace(" ","",$_POST['card_number'])),
					'expMonth' => $_POST['expiry_month'],
					'expYear' => $_POST['expiry_year'],
					'cvv' => $_POST['cvv'],
					'firstName' => $firstName,
					'lastName' => $lastName,
					'city' => $city,
					'zip'	=> $zipcode,
					'countryCode' => $countryCode,
				);
				$response = $paypal::paypalCall($paypalParams);
				
				$paymentStatus = strtoupper($response["ACK"]);
				if ($paymentStatus == "SUCCESS")
				{
					if(isset($ad_id) && !empty($ad_id))
					{
						$ad = PostForm::find()->where(['_id' => $ad_id,'post_user_id' => "$user_id",'is_ad' => '1'])->one();
						if(!empty($ad)){
							$adbudget = $ad_duration * $ad_min_budget;
								$ad->adtotbudget = (int) $adbudget;
								$ad->adbudget = (int) $adbudget;
								$ad->adstartdate = $ad_start_date;
								$ad->adenddate = $ad_end_date;
								$ad->ad_duration = $ad_duration;
								$ad->update();
							
						}
					}
					
					$result = PostForm::find()->where(['post_user_id' => "$user_id",'is_ad' => '0','post_type' => 'ad'])->one();
					if($result)
					{
						$result->is_ad = "1";
						$result->update();	
					
						$detail = (string)$result['_id'];
						$item_number = $response['TRANSACTIONID'];
						$transaction_id = $response['TRANSACTIONID'];
						$order_type = 'ads';
						$amount = $response['AMT'];
						$status = $response['ACK'];
						$curancy = $response['CURRENCYCODE'];
						$order = new Order();
						$order = $order->neworder($item_number,$transaction_id,$amount,$curancy,$status,$order_type,$detail);	
						$st = "success";
						
						$money = UserMoney::find()->where(['user_id' => $user_id])->one();
						if(!empty($money))
						{
							$money->amount = 0;
							$money->update();
						}
					}
					$url = Yii::$app->urlManager->createUrl(['ads/manage',$st => $st]);
					Yii::$app->getResponse()->redirect($url);
				}
				else 
				{
					$st = $response['ACK'];
					$url = Yii::$app->urlManager->createUrl(['ads/manage',$st => $st]);
					Yii::$app->getResponse()->redirect($url);
				}				
			}
		}
		$url = Yii::$app->urlManager->createUrl(['ads/manage']);
	    Yii::$app->getResponse()->redirect($url);
    }
	
	public function actionTotalMoney() 
	{
		$money = UserMoney::usertotalmoney();
		$total = (isset($money[0])) ? $money[0]['totalmoney'] : '0';
		if($total > 0)
		{
			return $total;
		}
		else
		{
			return 0;
		}
	}

	public function actionManualpublish() 
	{
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		$ad_id = $session->get('ad_id');
		$usermoney = $session->get('user_money');
		$ad_start_date = $session->get('ad_start_date');
		$ad_end_date = $session->get('ad_end_date');
		$ad_duration = $session->get('ad_duration');
		$ad_min_budget = $session->get('ad_min_budget');
		
		if(isset($_POST['dif_amount']) && $_POST['dif_amount'] > 0 && $_POST['benifit_amount'] == 0) {
			$getDiffAmount = $_POST['dif_amount'];
			$money = UserMoney::find()->where(['user_id' => $user_id])->one();
			if(!empty($money))
			{
				$amt = $getDiffAmount;
				$money->amount = (int)$amt;
				$money->update();
				
			}
		}
				
		if(isset($_POST['benifit_amount']) && $_POST['benifit_amount'] > 0) {
			$benifit_amount = $_POST['benifit_amount'];
			$money = UserMoney::find()->where(['user_id' => $user_id])->one();
			if(!empty($money))
			{
				$amt = $money['amount'] + $benifit_amount;
				$money->amount = (int)$amt;
				$money->update();			
			}
		}
		
		if(isset($ad_id) && !empty($ad_id))
		{
			$ad = PostForm::find()->where(['_id' => $ad_id,'post_user_id' => "$user_id",'is_ad' => '1'])->one();
			if(!empty($ad)){
				$adbudget = $ad_duration * $ad_min_budget;
				$ad->adtotbudget = (int) $adbudget;
				$ad->adbudget = (int) $adbudget;
				$ad->adstartdate = $ad_start_date;
				$ad->adenddate = $ad_end_date;
				$ad->ad_duration = $ad_duration;
				$ad->update();
			}				
		}
		
		$result = PostForm::find()->where(['post_user_id' => "$user_id",'is_ad' => '0','post_type' => 'ad'])->one();
		if($result)
		{
			$result->is_ad = '1';
			$result->direct_publish = 'yes';
			$result->update();
		}
		
		return true;	

	}	
	
	public function actionUpdateusermoney() 
	{
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		
		if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
				$money = UserMoney::usertotalmoney();
				if(isset($_POST['getDiffAmount'])) {
					$getDiffAmount = $_POST['getDiffAmount'];
					$money = UserMoney::find()->where(['user_id' => $user_id])->one();
					if(!empty($money))
					{
						$money->amount = (int)$getDiffAmount;
						$money->update();
						return true;
						exit;
					}
				}
			}
		}
		return false;
		exit;
	} 

	public function actionCalculateUserAdsAmount() 
	{
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		
		if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {

				$money = UserMoney::usertotalmoney();
				if(isset($_POST['getDiffAmount'])) {
					$getUserAmount = $_POST['amount'];
					$money = UserMoney::find()->where(['user_id' => $user_id])->one();
					if(!empty($money))
					{
						$money->amount = (int)$getDiffAmount;
						$money->update();
						return true;
						exit;
					}
				}
			}
		}
		return false;
		exit;
	}

	public function actionGetpagelogo() 
	{

		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		
		if(isset($user_id) && $user_id != '') {
			if(isset($_POST['id']) && $_POST['id'] != '') {
	            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
	            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
	            	$id = $_POST['id']; 
	            	$src = Page::getlogo($id);
	            	$result = array('status' => true, 'src' => $src);
	            	return json_encode($result, true);
				}
			}
		}
		
		$result = array('status' => false);
		return json_encode($result, true);
	}
}
?>