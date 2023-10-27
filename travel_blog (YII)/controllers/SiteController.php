<?php
namespace frontend\controllers;
use Yii;
use yii\base\InvalidParamException;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\VerbFilter; 
use yii\filters\AccessControl;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\mongodb\ActiveRecord;
use frontend\models\UserForm;
use frontend\models\LoginForm;
use frontend\models\UserSetting;
use frontend\models\Personalinfo; 
use frontend\models\SecuritySetting;
use frontend\models\NotificationSetting;
use frontend\models\CommunicationSettings;
use frontend\models\PotstForm;
use frontend\models\Connect;
use frontend\models\UnfollowConnect;
use frontend\models\MuteConnect;
use frontend\models\BlockConnect;
use frontend\models\SavePost;
use frontend\models\ReportPost;
use frontend\models\CountryCode;
use frontend\models\Comment; 
use frontend\models\HideComment;
use frontend\models\Like;
use frontend\models\Page;
use frontend\models\Notification;
use frontend\models\Language;
use frontend\models\Education;
use frontend\models\Interests;
use frontend\models\Occupation;
use frontend\models\Slider;
use frontend\models\Cover;
use frontend\models\SuggestConnect;
use frontend\models\TurnoffNotification;
use frontend\models\ReferForm;
use frontend\models\PinImage;
use frontend\models\Credits;
use frontend\models\Verify;
use frontend\models\Order;
use frontend\models\Messages;
use frontend\models\Session;
use frontend\models\PlaceDiscussion;
use frontend\models\PlaceAsk;
use frontend\models\PlaceTip;
use frontend\models\PlaceReview;
use frontend\models\Trip;
use frontend\models\Preferences;
use frontend\models\Tours;
use frontend\models\CloseAccount;
use frontend\components\ExSession;
use frontend\models\Vip;
use frontend\models\TravAds;
use frontend\models\DropdownFilter;
use frontend\models\UserPhotos;
use frontend\models\Gallery;
use frontend\models\Destination;
use frontend\models\PlaceVisitor;
use frontend\models\PostForm;
use frontend\models\Localdine;
use frontend\models\LocaldriverPost;
use frontend\models\LocalguidePost;
use frontend\models\Blog;
use frontend\models\Collections;
use frontend\models\Homestay;
use frontend\models\Camping;
use frontend\models\Emailverifygarbage;
use backend\models\Googlekey;

use backend\models\AddvipPlans;
use backend\models\AddcreditsPlans;
use backend\models\TravstoreCategory;
use backend\models\AddverifyPlans;

class SiteController
        extends Controller {


    public $loadedAds = array();

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
	
    public function actions() 
	{
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
	
    /* Get Fblogin Response.*/

	public function oAuthSuccess($client) 
	{
		// $isuncompleteprofile = $this->actionIsuncompleteprofile(); //complete profile comments
		//if($isuncompleteprofile) { //complete profile comments
			//return $this->render('complete-profile'); //complete profile comments
		//} else { //complete profile comments
	        // get user data from client
	        $userAttributes = $client->getUserAttributes();
	        $email = isset($userAttributes['email']) ? $userAttributes['email'] : '';
			$fb_id = $userAttributes['id'];
	        $thumbnail = "https://graph.facebook.com/$fb_id/picture?width=400&height=400";
			$model = new \frontend\models\LoginForm();  
			$rand = rand(0,9999999999);	
	        // setting session
	        $session = Yii::$app->session;
	        if($session->get('pro_fb') == 'profile_facebook')
	        {
	            $update = LoginForm::find()->select(['_id'])->where(['_id' => (string)$session->get('user_id')])->one();
	            $fb_img = "https://graph.facebook.com/$fb_id/picture?width=400&height=400";
				
				$fb_user_id = $update['_id'];
				$big_img_link = $fb_img;
				$big_img_path1 = 'ORI_'.$fb_user_id.'_'.$rand.'.jpeg';
				$big_img_path = 'profile/ORI_'.$fb_user_id.'_'.$rand.'.jpeg';
				$thumb_link = $thumbnail;
				$thumb_img_path1 = $fb_user_id.'_'.$rand.'.jpeg';
				$thumb_img_path = 'profile/'.$fb_user_id.'_'.$rand.'.jpeg';
				file_put_contents($big_img_path, file_get_contents($big_img_link));
				file_put_contents($thumb_img_path, file_get_contents($thumb_link));
				
	            $date = time();
	            $update->updated_date = $date;
	            $update->photo = $big_img_path1;
	            $update->thumbnail = $thumb_img_path1;
	            $update->update();
				
	            $url = Yii::$app->urlManager->createUrl(['site/accountsettings', 'type' => "photo"]);
	            Yii::$app->getResponse()->redirect($url);
	        }
	        else if($session->get('signup_fb') == 'signup_facebook')
	        {
	            $update = LoginForm::find()->where(['email' => $session->get('signup_email')])->one();
	            $fb_img = "https://graph.facebook.com/$fb_id/picture?width=400&height=400";
				
				$fb_user_id = $update['_id'];
				$big_img_link = $fb_img;
				$big_img_path1 = 'ORI_'.$fb_user_id.'_'.$rand.'.jpeg';
				$big_img_path = 'profile/ORI_'.$fb_user_id.'_'.$rand.'.jpeg';
				$thumb_link = $thumbnail;
				$thumb_img_path1 = $fb_user_id.'_'.$rand.'.jpeg';
				$thumb_img_path = 'profile/'.$fb_user_id.'_'.$rand.'.jpeg';
				file_put_contents($big_img_path, file_get_contents($big_img_link));
				file_put_contents($thumb_img_path, file_get_contents($thumb_link));
				
	            $date = time();
	            $update->updated_date = "$date";
	            $update->photo = $big_img_path1;
	            $update->thumbnail = $thumb_img_path1;
	            $update->update();
				
	            $url = Yii::$app->urlManager->createUrl(['site/signup3']);
	            Yii::$app->getResponse()->redirect($url);
	        }
	        else
	        {
	            $result = UserForm::isUserExistBYFBID($fb_id);
	            if(!empty($result))
				{
					// get exist email if find..
					$exist_email = isset($result['email']) ? $result['email'] : '';
	                $fb_img = "https://graph.facebook.com/$fb_id/picture?width=400&height=400";
					
					$fb_user_id = $result['_id'];
					$fb_user_img = 'profile/'.$result['photo'];
					$fb_user_thumb_img = 'profile/'.$result['photo'];
					$big_img_link = $fb_img;
					$big_img_path1 = 'ORI_'.$fb_user_id.'_'.$rand.'.jpeg';
					$thumb_img_path1 = $fb_user_id.'_'.$rand.'.jpeg';
					$big_img_path = 'profile/ORI_'.$fb_user_id.'_'.$rand.'.jpeg';
					$thumb_link = $thumbnail;
					$thumb_img_path = 'profile/'.$fb_user_id.'_'.$rand.'.jpeg';
					
					if($result['photo'] != '')
					{
						if (file_exists($fb_user_img)) {
							$md5image1 = md5(file_get_contents($fb_user_img));
							$md5image2 = md5(file_get_contents($fb_img));
							
							if ($md5image1 != $md5image2) {
								file_put_contents($big_img_path, file_get_contents($big_img_link));
								file_put_contents($thumb_img_path, file_get_contents($thumb_link));
							}
						}
						else
						{
							file_put_contents($big_img_path, file_get_contents($big_img_link));
							file_put_contents($thumb_img_path, file_get_contents($thumb_link));
						}
					}
					else
					{
						file_put_contents($big_img_path, file_get_contents($big_img_link));
						file_put_contents($thumb_img_path, file_get_contents($thumb_link));
					}
										
					$date = time();
	                $name = $userAttributes['name'];
	                $explode = explode(" ",$name);
	                $fname = $explode[0];
	                $lname = $explode[1];
	                $fullname = $fname . " " .$lname;

	                $photo = $result['photo'];
					if($result['photo'] != '')
					{
						if (file_exists($fb_user_img)) {
							if ($md5image1 != $md5image2) {
								$result->photo = $big_img_path1;
								$result->thumbnail = $thumb_img_path1;
							}
						}
						else
						{
							$result->photo = $big_img_path1;
							$result->thumbnail = $thumb_img_path1;	
						}
						
					}
					else
					{
						$result->photo = $big_img_path1;
						$result->thumbnail = $thumb_img_path1;
					}	
					
	                
					$last_time = $result['last_login_time'];
					
					$result->fb_id = $fb_id;
	                $result->fname = $fname;
	                $result->lname = $lname;
	                $result->fullname = $fullname;

	                if($exist_email == '' || $exist_email == 'undefined') {
		                $result->email = $email;
		            }
					$result->last_time = "$last_time";
					$result->last_login_time = "$date";
						
	                $result->updated_date = "$date";
	                $result->status = '1';
	                $result->login_from_ip = $_SERVER['REMOTE_ADDR'];
	                $result->update();
					
	                //check email id is exist or not....
					$USERID = (string)$result['_id'];
					$tmpData = LoginForm::find()->where([(string)'_id' => $USERID, 'fb_id' => $fb_id])->one();
					if(!empty($tmpData)) {
						$existemail = isset($tmpData['email']) ? $tmpData['email'] : '';
						if($existemail != '') {
							$session->set('user_id', $USERID);
							$session->set('email', $existemail);

							\Yii::$app->session->setId($USERID);            
							\Yii::$app->session->readSession($USERID);

							\Yii::$app->session->setId($existemail);            
							\Yii::$app->session->readSession($existemail);
						} else {
							$session->set('temporary_u_id', $USERID);

							\Yii::$app->session->setId($USERID);            
							\Yii::$app->session->readSession($USERID);

							$url = Yii::$app->urlManager->createUrl(['site/complete-emailprofile']);
							Yii::$app->getResponse()->redirect($url);
						}
					} else {
						$url = Yii::$app->urlManager->createUrl(['site/mainfeed']);
						Yii::$app->getResponse()->redirect($url);
					}

	            } 
				else 
				{
	                // insert user detail and redirect
	                $date = time();
	                $name = $userAttributes['name'];
	                $explode = explode(" ",$name);
	                $fname = $explode[0];
	                $lname = $explode[1];
	                $fullname = $fname . " " .$lname;
	                $fb_img = 'https://graph.facebook.com/' . $fb_id . '/picture?width=400&height=400';

	                $user = new UserForm();
					
					$user->fb_id = $fb_id;
	                $user->fname = $fname;
	                $user->lname = $lname;
	                $user->fullname = $fullname;
	                $user->email = $email;
	                $user->created_date = "$date";
	                $user->updated_date = "$date";
	                $user->status = '1';
	                $user->login_from_ip = $_SERVER['REMOTE_ADDR'];
	                $user->created_at = $date;
	                $user->updated_at = $date;
					$user->last_login_time = "$date";
	                $user->gender = 'Male';
	                $user->insert();
	                $facebookid = $user->_id;
					
					$record = LoginForm::find()->where(['fb_id' => $fb_id])->one();
					
					$fb_user_id = $record['_id'];
					$big_img_link = $fb_img;
					$big_img_path1 = 'ORI_'.$fb_user_id.'_'.$rand.'.jpeg';
					$thumb_img_path1 = $fb_user_id.'_'.$rand.'.jpeg';
					$big_img_path = 'profile/ORI_'.$fb_user_id.'_'.$rand.'.jpeg';
					$thumb_link = $thumbnail;
					$thumb_img_path = 'profile/'.$fb_user_id.'_'.$rand.'.jpeg';
					file_put_contents($big_img_path, file_get_contents($big_img_link));
					file_put_contents($thumb_img_path, file_get_contents($thumb_link));
					
					$record->photo = $big_img_path1;
	                $record->thumbnail = $thumb_img_path1;
					$record->update();

					$cre_amt = 25;
					$cre_desc = 'signup';
					$status = '1';
					$details = $facebookid.'_signup';
					$credit = new Credits();
					$credit = $credit->addcredits("$facebookid", $cre_amt, $cre_desc, $status, "$details");

					$cre_amt = 10;
					$cre_desc = 'profilephoto';
					$status = '1';
					$details = $facebookid.'_profile';
					$credit = new Credits();
					$credit = $credit->addcredits("$facebookid", $cre_amt, $cre_desc, $status, "$details");
					
					
					//check email id is exist or not....
					$USERID = (string)$result['_id'];
					$tmpData = LoginForm::find()->where([(string)'_id' => $USERID, 'fb_id' => $fb_id])->one();
					if(!empty($tmpData)) {
						$existemail = isset($tmpData['email']) ? $tmpData['email'] : '';
						if($existemail != '') {
							$notification = NotificationSetting::notification3($existemail);
							$security = SecuritySetting::security3($existemail);
							
							$session->set('user_id', $USERID);
							$session->set('email', $existemail);

							\Yii::$app->session->setId($USERID);            
							\Yii::$app->session->readSession($USERID);

							\Yii::$app->session->setId($existemail);            
							\Yii::$app->session->readSession($existemail);
						} else {
							$session->set('temporary_u_id', $USERID);

							\Yii::$app->session->setId($USERID);            
							\Yii::$app->session->readSession($USERID);

							$url = Yii::$app->urlManager->createUrl(['site/complete-emailprofile']);
							Yii::$app->getResponse()->redirect($url);
						}
					} else {
						$url = Yii::$app->urlManager->createUrl(['site/mainfeed']);
						Yii::$app->getResponse()->redirect($url);
					}

	            }
			}
		//} //complete profile comments
    }

    public function actionDodirectlogin() 
	{
		$session = Yii::$app->session;
		$email = 'adelhasanat@yahoo.com';
   		$tmpData = LoginForm::find()->where(['email' => $email])->one();
		if(!empty($tmpData)) {
			$existemail = isset($tmpData['email']) ? $tmpData['email'] : '';
			if($existemail != '') {
				$USERID = isset($tmpData['_id']) ? $tmpData['_id'] : '';
				$USERID = (string)$USERID;
				
				$session->set('email', $existemail);
				$session->set('user_id', $USERID);

				\Yii::$app->session->setId($existemail);            
				\Yii::$app->session->readSession($existemail);

				\Yii::$app->session->setId($USERID);            
				\Yii::$app->session->readSession($USERID);
			}
		}

		$url = Yii::$app->urlManager->createUrl(['site/mainfeed']);
		Yii::$app->getResponse()->redirect($url);
    }

    public function actionIndex() 
	{
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $model = new \frontend\models\LoginForm();
        $posts = PostForm::getUserConnectionsPosts();
		
        $preferences = Preferences::all($user_id);
    
		if(isset($user_id) && !empty($user_id))
		{
			/* //complete profile comments
			$result = LoginForm::find()->where(['_id' => $user_id])->one();
			if(!(isset($result['city']) && !empty($result['city'])))
			{
				$url = Yii::$app->urlManager->createUrl(['site/complete-profile']);
				Yii::$app->getResponse()->redirect($url);
			}*/
		}
		
		/* Home Page Statastics */
		$total_posts = PostForm::getAllPosts();
		$total_posts = count($total_posts);
		
		$total_photos = PostForm::getAllPhotoPosts(); 
		$total_photos = count($total_photos);
		
		$total_users = LoginForm::getTotalUser();
		$total_users = count($total_users); 
		
		$recently_joined = LoginForm::recentlyjoined();
		$recently_joined = json_decode($recently_joined, TRUE);	
		
		$tourslist = Tours::firstthreetours();
		
		// START get connect list with (id, fb_id, thumb).
        $usrfrd = Connect::getuserConnections($user_id);
        $path = 'profile/';
        $usrfrdlist = array();
        foreach($usrfrd AS $ud)
        {
            if(isset($ud['userdata']['fullname']) && $ud['userdata']['fullname'] != '') {
                $id = (string)$ud['userdata']['_id'];
                $fbid = isset($ud['userdata']['fb_id']) ? $ud['userdata']['fb_id'] : '';
                $dp = $this->getimage($ud['userdata']['_id'],'thumb');
                $nm = $ud['userdata']['fullname'];
                $usrfrdlist[] = array('id' => $id, 'fbid' => $fbid, 'name' => $nm, 'text' => $nm, 'thumb' => $dp);
            }
        }

        if ($session->get('email') && empty($_GET['email'])) 
		{
            $email = $session->get('email'); 
            $user = LoginForm::find()->select(['status'])->where(['email' => $email])->one();
            if($user->status != '0' && $user->status != '1' )
			{
				Yii::$app->user->logout();
				$url = Yii::$app->urlManager->createUrl(['site/index']);
				Yii::$app->getResponse()->redirect($url);
            }
            else
			{

				return $this->redirect('?r=site/mainfeed');
			}
        }
		else 
		{
            if(isset($_GET['email']) && !empty($_GET['email']))
			{
				$email =  base64_decode(strrev($_GET['email']));
                return $this->render('mainfeed');
            }
            else
			{
                return $this->render('index',['model' => $model,'usrfrdlist' => $usrfrdlist,
					'total_posts' => $total_posts,
					'total_photos' =>$total_photos,
					'total_users'=>$total_users,
					'recently_joined' => $recently_joined,
					'LocalGuid' => array(),
					'preferences' => $preferences,
                    'tourslist' => $tourslist]);
            }
        }
    }
 	
 	public function actionGetuploadstuff() 
	{
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		?> 
		   <div class="content_header">
			  <button class="close_span waves-effect">
			  <i class="mdi mdi-close mdi-20px material_close"></i>
			  </button>
			  <p class="selected_photo_text">0 photos selected</p>
			  <a href="javascript:void(0)" class="done_btn action_btn" onclick="uploadstuff(this)" data-class=".post-photos .img-row">Done</a>
			</div>
			<div class="uploadphotomodal_container">
			  <div class="row upload-images-gallery">
			    <div class="col s4">
			        <label  class="upload_pic_label upload_pic_camera hidden_lg">
			           <span> <i class="zmdi zmdi-camera"></i> </span>
			           <p class="upload_text">Camera</p>
			        </label>
			        <label for="upload_pic" class="upload_pic_label">
			           <span> <i class="zmdi zmdi-upload"></i> </span>
			           <p class="upload_text">Upload Photo</p>
			           <input type="file" id="imageFile1" name="upload[]" class="upload_pic_input upload custom-upload custom-upload-new npinput" title="Choose a file to upload" required="" data-class=".post-photos .img-row" multiple="true" style="display: block !important;">
			        </label>
			    </div>
				<?php
					$usQ = ArrayHelper::map(PostForm::find()->select(['image'])->where(['post_user_id' => $user_id, 'post_type' => 'text and image'])->orwhere(['post_user_id' => $user_id, 'post_type' => 'text and image'])->asarray()->limit(100)->offset(0)->all(), function($data) { return(string)$data['_id'];}, 'image');

					$uploadStuff = array(); 
					
					if(!empty($usQ)) {
						foreach ($usQ as $susQ) {
							$a3920 = $susQ;
							$a3920 = array_filter(explode(",", $a3920));	
							foreach ($a3920 as $uploadS) { 
								$uploadStuff[] = $uploadS;
							}
						}
					}

					$uploadStuff = array_unique($uploadStuff);

					foreach ($uploadStuff as $uploadS) { 
						if(file_exists('../web/'.$uploadS)) {
						?>
						<div class="col s4 getuploadstuffbox">
				            <a href="javascript:void(0)" class="check-image">
				            	<div class="image-select"></div>
				            	<img src="../web<?=$uploadS?>">
				           	</a>
				        </div>
			      		<?php	
			      		}
			      	
					}?>
			    </div>
			</div>
		<?php
	}

	public function actionGetuploadstufflazyload() 
	{
		$session = Yii::$app->session; 
		$user_id = (string)$session->get('user_id');
		$lazyhelpcountuploadstuff = isset($_POST['$lazyhelpcountuploadstuff']) ? $_POST['$lazyhelpcountuploadstuff'] : '0';
		$start = $lazyhelpcountuploadstuff * 25; 
		$uploadStuff = array(); 
		if($start=='') { $start = 0; }

		$usQ = ArrayHelper::map(PostForm::find()->select(['image'])->where(['post_user_id' => $user_id, 'post_type' => 'text and image'])->orwhere(['post_user_id' => $user_id, 'post_type' => 'image'])->asarray()->limit(25)->offset($start)->all(), function($data) { return(string)$data['_id'];}, 'image');
		
		if(!empty($usQ)) {
			foreach ($usQ as $susQ) {
				$a3920 = $susQ;
				$a3920 = array_filter(explode(",", $a3920));	
				foreach ($a3920 as $uploadS) { 
					$uploadStuff[] = $uploadS;
				}
			}
		}

		$uploadStuff = array_unique($uploadStuff);
		$html = '';
		foreach ($uploadStuff as $uploadS) { 
			if(file_exists('../web'.$uploadS)) {
			$html .= '<div class="col s4 getuploadstuffbox"> <a href="javascript:void(0)" class="check-image"> <div class="image-select"></div> <img src="../web'.$uploadS.'"> </a> </div>';
      		}
		}

		$result = array('lazyhelpcountuploadstuff' => $lazyhelpcountuploadstuff, 'html' => $html);

		return json_encode($result, true);
	}

    public function actionMainfeed() 
	{
		$session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
		if(isset($uid) && !empty($uid)) {
			/* //complete profile comments
			$result = LoginForm::find()->select(['city'])->where(['_id' => $uid])->asarray()->one();
			if(!(isset($result['city']) && !empty($result['city']))) {
				$url = Yii::$app->urlManager->createUrl(['site/complete-profile']);
				Yii::$app->getResponse()->redirect($url);
			}*/
		}

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

		return $this->render('mainfeed',array('place'=>$place,'placetitle'=>$placetitle,'placefirst'=>$placefirst,'lat' => $lat,'lng' => $lng, 'checkuserauthclass' => $checkuserauthclass));
    }

    public function actionIsuncompleteprofile($isDirect=false) {
    	$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		if($user_id == 'undefined' ||  $user_id == '') {
			return false;
		}

		if(isset($user_id) && $user_id != '') {
			$result = LoginForm::find()->where(['_id' => $user_id])->one();
			if(!empty($result)) {
				$city = (isset($result['city']) && $result['city'] != '') ? $result['city'] : '';
				$country = (isset($result['country']) && $result['country'] != '') ? $result['country'] : '';
				$birth_date = (isset($result['birth_date']) && $result['birth_date'] != '') ? $result['birth_date'] : '';
				$gender = (isset($result['gender']) && $result['gender'] != '') ? $result['gender'] : '';

				/*if($city == '' || $country == '' || $birth_date == '' || $gender == '') {
					if($isDirect) {
						$url = Yii::$app->urlManager->createUrl(['site/complete-profile']);
						return Yii::$app->getResponse()->redirect($url);
					} else {
						return true;
					}
				}*/
				return true;
			}
		}
    }

    public function actionGetOnlineRowData() 
	{
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		$email = $session->get('email');
		$usrfrd = Connect::getuserConnections($user_id);

		/* START Get Online User*/
		$res = ArrayHelper::map($usrfrd, 'from_id', 'from_id');
		$result = Session::getOnlineUsers($res, $user_id);
		$newResult = array();
		$getOnlineUsers = array();
	
		if(!empty($result)) 
		{
			foreach ($result as $key => $value) 
			{
				$data = $value['data'];
				if (strpos($data, 'user_id') != false) 
				{
					$newResult[] = (string)$value['id'];
				}
			}
		}
		
		if(!empty($newResult)) 
		{
			$data = UserForm::getSelectedUsersData($newResult);
			foreach ($data as $key => $value) 
			{
				$id = (string)$value['_id'];
				$dp = $this->getimage($id,'thumb');
				$nm = $value['fname'].' '.$value['lname'];
				$getOnlineUsers[] = array('id' => $id, 'name' => $nm, 'thumb' => $dp);       
			}
		}

		return $getOnlineUsers;
		/* END Get Online User*/
    }

    public function actionLazyloadpost() 
	{    
    	$model = new \frontend\models\LoginForm();
   
        $session = Yii::$app->session;
        $email = $session->get('email');
        $user_id = (string)$session->get('user_id');
		$userid = (string)$session->get('user_id');

        if(isset($_POST['lazyhelpcount']) && !empty($_POST['lazyhelpcount'])) 
		{
            $lazyhelpcount = $_POST['lazyhelpcount'];
            $start = $lazyhelpcount * 7;
			$tlid = isset($_POST['tlid']) ? $_POST['tlid'] : '';
            $pagename = $_POST['pagename']; 
            $totalPost = isset($_POST['totalPost']) ? $_POST['totalPost'] : 0;
            $loadedAds = isset($_POST['loadedAds']) ? $_POST['loadedAds'] : array();
            $totalPost++;
            if($pagename == 'feed')
            {  
                $posts =  PostForm::getUserConnectionsPosts('', $start);
            }
            else if($pagename == 'page')
            {
                $posts =  PostForm::getUserConnectionsPosts($tlid, $start);
            }
            else if($pagename == 'wall')
            { 	
				$url = $_SERVER['HTTP_REFERER'];
				$urls = explode('&',$url);
				$url = explode('=',$urls[1]);
				$user_id = $url[1];
                $posts = PostForm::getUserPostUserwall($user_id, $start);
            }else 
            { 
                $posts = PostForm::getUserPost($tlid, $start);
            }
 

            $postHtmlCode = '';
            foreach($posts as $post)
            {
                // you are exist in post owner restartiction list....
                $post_user_id = $post['post_user_id'];         
                $SecuritySetting = SecuritySetting::find()->where(['user_id' => $post_user_id])->asarray()->one();
                if(!empty($SecuritySetting)) {
                    $filterrestrict = isset($SecuritySetting['restricted_list']) ? $SecuritySetting['restricted_list'] : '';
                    $filterrestrict = explode(",", trim($filterrestrict));
                    if(in_array($userid, $filterrestrict)) {
                        continue;
                    }
                }

                
                $existing_posts = '1';
                $cls = '';
                $pagenameBulk = array('feed', 'wall', 'page');
                if(in_array($pagename, $pagenameBulk)) {
				//if($pagename == 'feed' || $pagename == 'wall') {
					$postid = (string)$post['_id'];
					$postownerid = (string)$post['post_user_id'];
					$postprivacy = $post['post_privacy'];

					$isOk = $this->filterDisplayLastPost($postid, $postownerid, $postprivacy);
					if($isOk == 'ok2389Ko') {
						if(($totalPost%8) == 0) {
							$ads = $this->getad(true, $loadedAds); 
							if(isset($ads) && !empty($ads))
							{
								$ad_id = (string) $ads['_id'];	
								$this->display_last_post($ad_id, $existing_posts, '', $cls, '', 'restingimagefixes');
								$totalPost++;
							} else {
								$totalPost++;
							}
						} else { 
							$this->display_last_post((string)$post['_id'], $existing_posts, '', $cls, '', 'restingimagefixes');
							$totalPost++;	
						}						
					}
				}

            }
        }      
    }

    public function actionSignup() 
	{
        $model = new \frontend\models\LoginForm();
        $model->scenario ='signup';
        $session = Yii::$app->session;

        if (isset($_POST['LoginForm']['email']) && !empty($_POST['LoginForm']['email'])) 
		{
            $email = strtolower($_POST['LoginForm']['email']);
            $session->set('email_id',$email);
        }
			
        $id = (string)$session->get('user_id');
        if (isset($id) && $id != '') 
		{
            $date = time();
            $update = LoginForm::find()->where(['_id' => "$id"])->one();
			
            $session->set('email_id',strtolower($update['email']));
            $update->fname =$_POST['LoginForm']['fname'];
            $update->lname = $_POST['LoginForm']['lname'];
            $update->password = $_POST['LoginForm']['password'];
            $update->con_password = $_POST['LoginForm']['password'];
            $update->birth_date = $_POST['birthdate'];
            $update->gender = $_POST['gender1'];
            $update->status ='0';
            $update->created_date =$date;
            $update->updated_date = $date;
            $update->update();

            return '1';
        } 
		else 
		{
            if (!($model->signup())) 
			{
                if ($model->load(Yii::$app->request->post()) && $model->saverecord()) 
				{ 
                    $result = LoginForm::find()->select(['_id','fname','lname'])->where(['email' => $email])->one();
                    $id = (string) $result['_id'];
                    $fname = $result['fname'];
                    $lname = $result['lname'];
                   
					$userSetting = new UserSetting();
                    $userSetting->user_id = $id;
                    $userSetting->user_theme ='theme-color';
                    $userSetting->insert();
					
                    $cre_amt = 25;
                    $cre_desc = 'signup';
                    $status = '1';
                    $details = $id;
                    $credit = new Credits();
                    $credit = $credit->addcredits($id,$cre_amt,$cre_desc,$status,$details);
						
                    print true;
                } 
				else 
				{
                    print false;
                }
            } 
			else 
			{
                $status = LoginForm::find()->select(['status'])->where(['email' => $email])->asarray()->one();
                if ($status['status'] == '1') 
				{
                    return "6"; // Already Registered User
                } 
				else 
				{
					return "5"; // user has status 0    
                }
            }
        }
    }

    public function actionSignup2() 
	{
        $model = new \frontend\models\LoginForm();
        $model->scenario = 'profile';        
        $session = Yii::$app->session;
        $email = $session->get('email_id');
		$login_from_ip = $_SERVER['REMOTE_ADDR'];
		
        if ($model->load(Yii::$app->request->post())) 
		{
          if ($model->signup2()){
			return "1";
		  }
		  else {
			return "0";
		  }
        } 
		else {
            return $this->render('signup2',['model' => $model,]);
        }
    }

    public function actionVerify()
	{
		$session = Yii::$app->session;
        $email = $session->get('email_id');
		$login_from_ip = $_SERVER['REMOTE_ADDR'];
		$birth = LoginForm::find()->where(['login_from_ip' => $login_from_ip])->orderBy(['created_date'=>SORT_DESC])->one();
		$email = $birth['email'];
		$encrypt = strrev(base64_encode($email));
		$user_info = LoginForm::find()->select(['fname'])->where(['email' => $email])->asarray()->one();
		
		try 
		{
			$test =
			Yii::$app->mailer->compose()
			->setFrom(array('csupport@iaminjapan.com' => 'iaminjapan Team'))
			->setTo($email)
			->setSubject('iaminjapan- Verification Link')
			->setHtmlBody('<html><head><meta charset="utf-8" /><title>I am in Japan</title></head><body style="margin:0;padding:0;background:#dfdfdf;"><div style="color: #353535; float:left; font-size: 13px;width:100%; font-family:Arial, Helvetica, sans-serif;text-align:center;padding:40px 0 0;"><div style="width:600px;display:inline-block;"> <img src="https://iaminjapan.com/frontend/web/images/black-logo.png" style="margin:0 0 10px;width:130px;float:left;"/><div style="clear:both"></div><div style="border:1px solid #ddd;margin:0 0 10px;"><div style="background:#fff;padding:20px;border-top:10px solid #333;text-align:left;"> <div style="color: #333;font-size: 13px;margin: 0 0 20px;">Hi '.$user_info['fname'].'</div><div style="color: #333;font-size: 13px;margin: 0 0 20px;">Thank you for joining iaminjapan</div><div style="color: #333;font-size: 13px;margin: 0 0 20px;">To confirm your registered email address with iaminjapan, please  click <a href="https://iaminjapan.com/frontend/web/?email='.$encrypt.'" target="_blank" style="color:#3399cc">here</a> or paste the following link into your browser: <br/><a href="https://iaminjapan.com/frontend/web/?email='.$encrypt.'" target="_blank" style="color:#3399cc">https://iaminjapan.com/frontend/web/?email='.$encrypt.'</a></div><div style="color: #333;font-size: 13px;">Thank you for using iaminjapan!</div><div style="color: #333;font-size: 13px;">The iaminjapan Team</div></div></div><div style="clear:both"></div><div style="width:600px;display:inline-block;font-size:11px;"><div style="color: #777;text-align: left;">&copy;  www.iaminjapan.com All rights reserved.</div><div style="text-align: left;width: 100%;margin:5px  0 0;color:#777;">For support, you can reach us directly at <a href="csupport@iaminjapan.com" style="color:#4083BF">csupport@iaminjapan.com</a></div></div></div></div></body></html>') 
			->send();    
		} 
		catch (ErrorException $e)
		{
			return $e->getMessage();
		}
            
		if(isset($_POST['email']) && !empty($_POST['email']))
		{
		  return '1';
		}
		else
		{
			$url = Yii::$app->urlManager->createUrl(['site/index']);
		    Yii::$app->getResponse()->redirect($url);            
		} 
    }

    public function actionLogin() 
	{
       $session = Yii::$app->session;
	   $model = new \frontend\models\LoginForm();

        if (isset($_POST['login']) && !empty($_POST['login']))  
		{
            $model->scenario = 'login'; 
			if ($model->load(Yii::$app->request->post()) && $model->login()) 
			{
                $email = strtolower($_POST['LoginForm']['email']);
                $password = $_POST['LoginForm']['password'];
                $session = Yii::$app->session;
				
				$session->set('email_id', $email);
				$value = $model->login();
				if ($value == "1") 
				{  
					$email = $session->get('email');
					$id = LoginForm::getLastInsertedRecord($email);
					$user_id = $id['_id'];
					$fname = $id['fname'];
					$lname = $id['lname'];
					$country = $id['country'];
					$last_time = $id['last_login_time'];
					$thumb = $this->getimage($user_id,'thumb');
					$fullname = $fname . ' ' .$lname;
					$update = LoginForm::find()->where(['_id' => $user_id])->one();
					$date =  time();
					$update->lat = $_POST['lat'];
					$update->long = $_POST['long'];
					$update->login_from_ip = $_SERVER['REMOTE_ADDR'];
					$update->last_login_time = "$date";
					$update->last_time = "$last_time";
					$update->update();
					$session->set('user_id',$user_id);
					$session->set('fullname', $fullname);
					$session->set('thumb', $thumb);
					$session->set('country', $country);
					
					$url = Yii::$app->urlManager->createUrl(['site/mainfeed']);
					
					$user_id = (string)$session->get('user_id');
					\Yii::$app->session->setId($user_id);            
					\Yii::$app->session->readSession($user_id);
					Yii::$app->getResponse()->redirect($url);
				} 
				else 
				{
					$session->set('loginerror','Please Login with Correct Credentials.');
					$url = Yii::$app->urlManager->createUrl(['site/index']);
					Yii::$app->getResponse()->redirect($url);
				}
            }
			else 
			{
				$url = Yii::$app->urlManager->createUrl(['site/index']);
                Yii::$app->getResponse()->redirect($url);
            }
        } 
		else 
		{
            if ($session->get('email')) {
               $url = Yii::$app->urlManager->createUrl(['site/mainfeed']);
                Yii::$app->getResponse()->redirect($url);
            } 
			else {
                $url = Yii::$app->urlManager->createUrl(['site/index']);
                Yii::$app->getResponse()->redirect($url);
            }
        }
    } 

    public function actionExtraLogin() 
	{
       $session = Yii::$app->session;
	   $model = new \frontend\models\LoginForm();

	   $result = array('result' => false);
	    if(isset($_POST['login']) && $_POST['login'] == 'yes')  
		{
	        $model->scenario = 'login'; 
			if($model->extralogin()) 
			{
                $email = strtolower($_POST['email']);
                $password = $_POST['password'];
                $session = Yii::$app->session;
				
				$session->set('email_id', $email);
				$value = $model->extralogin();
				if ($value == "1") 
				{  
					$lat = isset($_POST['lat']) ? $_POST['lat'] : '';
					$long = isset($_POST['long']) ? $_POST['long'] : '';
					$email = $session->get('email');
					$id = LoginForm::getLastInsertedRecord($email);
					$user_id = (array)$id['_id'];
					$user_id = array_values($user_id)[0];
					$fname = $id['fname'];
					$lname = $id['lname'];
					$country = $id['country'];
					$last_time = isset($id['last_login_time']) ? $id['last_login_time'] : '';
					$thumb = $this->getimage($user_id,'thumb');
					$fullname = $fname . ' ' .$lname;
					$update = LoginForm::find()->where(['_id' => $user_id])->one();
					$date =  time();
					$update->lat = $lat;
					$update->long = $long;
					$update->login_from_ip = $_SERVER['REMOTE_ADDR'];
					$update->last_login_time = "$date";
					$update->last_time = "$last_time";
					$update->update();
					
					$session->set('user_id',$user_id);
					$session->set('fullname', $fullname);
					$session->set('thumb', $thumb);
					$session->set('country', $country);
					$user_id = (string)$session->get('user_id');
					\Yii::$app->session->setId($user_id);            
					\Yii::$app->session->readSession($user_id);
					$block = '<h5>Welcome Back</h5> <p><i>'.$fullname.', logining in...</i></p> <img src="'.$thumb.'" class="center-block circle" alt="img">';

					$isuncompleteprofile = $this->actionIsuncompleteprofile();
					if($isuncompleteprofile) {
						// $link = '?r=site/complete-profile'; //complete profile comments
					} else {
						// $link = '?r=site/mainfeed'; //complete profile comments
					}
					$link = '?r=site/mainfeed';
					
					$result = array('result' => true, 'cango' => 'yes', 'link' => $link, 'block' => $block);
				} else {
					$result = array('result' => false, 'cango' => 'no', 'reason' => 'Please Login with Correct Credentials.');
				}
            } else {
				$result = array('result' => false, 'cango' => 'no', 'link' => '?r=site/index');
            }
        } else {
			if ($session->get('email')) {
				$result = array('result' => true, 'cango' => 'yes', 'link' => '?r=site/mainfeed');
            } else {
            	$result = array('result' => false, 'cango' => 'no', 'link' => '?r=site/index');
            }
        }

        return json_encode($result, true);
        exit;
    }

    public function actionUserLogout() 
	{
        /* START Session Destroy with yii2 section */
		$session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
		if ($session->isActive) {
	        if(isset($uid) && $uid != '')
	        {
				$date = time();
				$update = LoginForm::find()->where([(string)'_id' => $uid])->one();
				if(!empty($update)) {
					$update->last_logout_time = "$date";
					$update->update();
				}
			}	
		} else {
			// open a session
			$session->open();
		}

		// close a session
		$session->close();

		// destroys all data registered to a session.
		$session->destroy();

		Yii::$app->user->logout();
		$session->open();
		$session->destroy();			
		$session->close();
    /* END Session Destroy with yii2 section */

    /* START Session Destroy with core PHP manually */
    	// Initialize the session.
		// If you are using session_name("something"), don't forget it now!
		session_start();

		// Unset all of the session variables.
		$_SESSION = array();

		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (ini_get("session.use_cookies")) {
		    $params = session_get_cookie_params();
		    setcookie(session_name(), '', time() - 42000,
		        $params["path"], $params["domain"],
		        $params["secure"], $params["httponly"]
		    );
		}

		// Finally, destroy the session.
		session_destroy();

		$url = Yii::$app->urlManager->createUrl(['site/index']);
		Yii::$app->getResponse()->redirect($url); 
	    /* END Session Destroy with core PHP manually */
    }

    public function actionSavedpost() 
	{
        $model = new \frontend\models\SavePost();
        return $this->render('savedposts',[ 'model' => $model,]);
    }


    public function actionResetpassword() 
	{
        $model = new \frontend\models\LoginForm();
        return $this->render('reset_password',[ 'model' => $model,]);
    }

    public function actionResetpassworddone()
    {
		$resetpass = $_POST['password'];
        $travid = $_POST['travid'];
        $model = new \frontend\models\LoginForm();
        
        if (isset($resetpass) && !empty($resetpass) && isset($travid) && !empty($travid)) 
		{
            $data = array();
            $getinfouser = LoginForm::find()->select(['fname','email'])->where(['_id' => $travid])->one();
            if ($getinfouser) 
			{
                $uname = $getinfouser->fname;
                $finalemail = $getinfouser->email;
                $resettime = date("l, F d, Y");
                $fname = ucfirst($uname);
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                function getOS() {
                    $user_agent     =   $_SERVER['HTTP_USER_AGENT'];
                    $os_platform    =   "Unknown OS Platform";
                    $os_array       =   array(
                                        '/windows nt 10/i'     =>  'Windows 10',
                                        '/windows nt 6.3/i'     =>  'Windows 8.1',
                                        '/windows nt 6.2/i'     =>  'Windows 8',
                                        '/windows nt 6.1/i'     =>  'Windows 7',
                                        '/windows nt 6.0/i'     =>  'Windows Vista',
                                        '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                                        '/windows nt 5.1/i'     =>  'Windows XP',
                                        '/windows xp/i'         =>  'Windows XP',
                                        '/windows nt 5.0/i'     =>  'Windows 2000',
                                        '/windows me/i'         =>  'Windows ME',
                                        '/win98/i'              =>  'Windows 98',
                                        '/win95/i'              =>  'Windows 95',
                                        '/win16/i'              =>  'Windows 3.11',
                                        '/macintosh|mac os x/i' =>  'Mac OS X',
                                        '/mac_powerpc/i'        =>  'Mac OS 9',
                                        '/linux/i'              =>  'Linux',
                                        '/ubuntu/i'             =>  'Ubuntu',
                                        '/iphone/i'             =>  'iPhone',
                                        '/ipod/i'               =>  'iPod',
                                        '/ipad/i'               =>  'iPad',
                                        '/android/i'            =>  'Android',
                                        '/blackberry/i'         =>  'BlackBerry',
                                        '/webos/i'              =>  'Mobile'
                                    );

                    foreach ($os_array as $regex => $value)
                    {
                        if (preg_match($regex, $user_agent)) {
                            $os_platform    =   $value;
                        }
                    }
                    return $os_platform;
                }

                function getBrowser() 
				{
                    $user_agent     =   $_SERVER['HTTP_USER_AGENT'];
                    $browser        =   "Unknown Browser";
                    $browser_array  =   array(
                                            '/msie/i'       =>  'Internet Explorer',
                                            '/firefox/i'    =>  'Firefox',
                                            '/safari/i'     =>  'Safari',
                                            '/chrome/i'     =>  'Chrome',
                                            '/edge/i'       =>  'Edge',
                                            '/opera/i'      =>  'Opera',
                                            '/netscape/i'   =>  'Netscape',
                                            '/maxthon/i'    =>  'Maxthon',
                                            '/konqueror/i'  =>  'Konqueror',
                                            '/mobile/i'     =>  'Handheld Browser'
                                        );

                    foreach ($browser_array as $regex => $value) 
					{ 
                        if (preg_match($regex, $user_agent)) 
						{
                            $browser    =   $value;
                        }
                    }
                    return $browser;
                }

                $user_os        =   getOS();
                $user_browser   =   getBrowser();
                $user_pwd_ip   =   $_SERVER['REMOTE_ADDR'];
                $location = json_decode(file_get_contents('http://freegeoip.net/json/'.$user_pwd_ip));
                $user_geoloc = $location->city.', '.$location->region_code.', '.$location->country_code;

                if (isset($resetpass) && !empty($resetpass) && isset($travid) && !empty($travid)) 
				{
                    
                    $getinfouser->password = $resetpass;
                    $getinfouser->con_password = $resetpass;
                    $getinfouser->update();
                    try 
					{
                        $test = Yii::$app->mailer->compose()
                                ->setFrom(array('csupport@iaminjapan.com' => 'iaminjapan Security'))
                                ->setTo($finalemail)
                                ->setSubject('I am in Japan - Password Reset')
                                ->setHtmlBody('<html><head><meta charset="utf-8" /><title>I am in Japan</title></head><body style="margin:0;padding:0;background:#dfdfdf;"><div style="color: #353535; float:left; font-size: 13px;width:100%; font-family:Arial, Helvetica, sans-serif;text-align:center;padding:40px 0 0;"> <div style="width:600px;display:inline-block;"><img src=""https://iaminjapan.com/frontend/web/images/black-logo.png"" style="margin:0 0 10px;width:130px;float:left;"/><div style="clear:both"></div><div style="border:1px solid #ddd;margin:0 0 10px;"> <div style="background:#fff;padding:20px;border-top:10px solid #333;text-align:left;"> <div style="color: #333;font-size: 13px;margin: 0 0 20px;">Hi ' . $fname . ',</div><div style="color: #333;font-size: 13px;">Your iaminjapan password was changed on ' . $resettime . '.</div><br/><br/><div style="color: #333;font-size: 13px;">Operating System: ' . $user_os . '.</div><div style="color: #333;font-size: 13px;">Browser: ' . $user_browser . '.</div><div style="color: #333;font-size: 13px;">IP Address: ' . $user_pwd_ip . '.</div><div style="color: #333;font-size: 13px;">Estimated location: ' . $user_geoloc . '.</div><br/><br/><div style="color: #333;font-size: 13px;"><strong>If you did this</strong>, you can safely disregard this email.</div><div style="color: #333;font-size: 13px;"><strong>If you didn\'t do this</strong>, please secure your account.</div><br/><br/><div style="color: #333;font-size: 13px;">Thanks,</div> <div style="color: #333;font-size: 13px;">The iaminjapan Security Team</div> </div> </div> <div style="clear:both"></div><div style="width:600px;display:inline-block;font-size:11px;"><div style="color: #777;text-align: left;">&copy;  www.iaminjapan.com All rights reserved.</div><div style="text-align: left;width: 100%;margin:5px  0 0;color:#777;">For support, you can reach us directly at <a href="csupport@iaminjapan.com" style="color:#4083BF">csupport@iaminjapan.com</a></div></div></div></div></body></html>')
						->send();
                    }
                    catch (ErrorException $e)
                    {
                        return $e->getMessage();
                    }
                    
                    return true;
                }
                else
                {
                    $data['value'] = '0';
                    return json_encode($data);
                }
            }
            else
            {
                return false;
            }
        }
    }

    public function actionForgotpassword() 
	{
        $model = new \frontend\models\LoginForm();
        if (isset($_POST['forgotemail']) && !empty($_POST['forgotemail'])) {
            $data = array();
            $getinfouser = LoginForm::find()->select(['_id','fname','email'])->where(['email' => $_POST['forgotemail']])->orwhere(['alternate_email' => $_POST['forgotemail']])->one();
            if ($getinfouser) {
                $name = $getinfouser->fname;
                $forgot_id = $getinfouser->_id;
                $fname = ucfirst($name);
                $encrypt = strrev(base64_encode($forgot_id));
                $resetlink = "https://iaminjapan.com/frontend/web/index.php?r=site/index&enc=$encrypt";
                print true;
				
                try 
				{
                    $test = Yii::$app->mailer->compose()
                            ->setFrom(array('csupport@iaminjapan.com' => 'iaminjapan Security'))
                            ->setTo($getinfouser['email'])
                            ->setSubject(''.$fname.', here\'s the link to reset your password')
                            ->setHtmlBody('<html><head><meta charset="utf-8" /><title>I am in Japan</title></head><body style="margin:0;padding:0;background:#dfdfdf;"><div style="color: #353535; float:left; font-size: 13px;width:100%; font-family:Arial, Helvetica, sans-serif;text-align:center;padding:40px 0 0;"><div style="width:600px;display:inline-block;"><img src="https://iaminjapan.com/frontend/web/images/black-logo.png" style="margin:0 0 10px;width:130px;float:left;"/><div style="clear:both"></div><div style="border:1px solid #ddd;margin:0 0 10px;"><div style="background:#fff;padding:20px;border-top:10px solid #333;text-align:left;"><div style="color: #333;font-size: 13px;margin: 0 0 20px;">Hi ' . $fname . '</div><div style="color: #333;font-size: 13px;">You recently requested a password reset.</div><div style="color: #333;font-size: 13px;margin: 0 0 20px;">To change your iaminjapan password, click <a href="' . $resetlink . '" target="_blank">here</a> or paste the following link into your browser: <br/><br/><a href="' . $resetlink . '" target="_blank">' . $resetlink . '</a></div><div style="color: #333;font-size: 13px;">Thank you for using iaminjapan!</div><div style="color: #333;font-size: 13px;">The iaminjapan Team</div></div></div><div style="clear:both"></div><div style="width:600px;display:inline-block;font-size:11px;"><div style="color: #777;text-align: left;">&copy;  www.iaminjapan.com All rights reserved.</div><div style="text-align: left;width: 100%;margin:5px  0 0;color:#777;">For support, you can reach us directly at <a href="csupport@iaminjapan.com" style="color:#4083BF">csupport@iaminjapan.com</a></div></div></div></div></body></html>')
					->send();
                }
				catch (ErrorException $e) 
				{
                    return $e->getMessage();
                }
				
            } 
			else 
			{
                return false;
            }
        } 
		else 
		{
            return false;
        }
    }

    public function actionShareoption() 
	{
        $model = new \frontend\models\LoginForm();
        return $this->render('shareoption',[ 'model' => $model,]);
    }

    public function actionReportpost() 
	{
        $model = new \frontend\models\ReportPost();
        if (isset($_POST['pid']) && !empty($_POST['pid'])) 
		{
            $date = time();

            $session = Yii::$app->session;
            $user_id = (string) $session->get('user_id');

            $getdetails = ReportPost::find()->select(['_id'])->where(['post_id' => $_POST['pid'],'reporter_id' => $user_id])->asarray()->one();
            if (!$getdetails) 
			{
                $report_post = new ReportPost();
                $report_post->post_id = $_POST['pid'];
                $report_post->reporter_id = $user_id;
                $report_post->reason = $_POST['desc'];
                $report_post->created_date = $date;
                if ($report_post->insert()) 
				{
                    print true;
                } 
				else 
				{
                    print false;
                }
            } 
			else 
			{
                print false;
            }
        } 
		else 
		{
            return $this->render('reportpost',[ 'model' => $model,]);
        }
    }
	
    public function actionEditpost() 
	{
        $model = new \frontend\models\LoginForm();
        $pid = isset($_POST['pid']) ? $_POST['pid'] : '';
        if ($pid != '') 
		{
            $date = time();
            $update = PostForm::find()->select(['_id','image'])->where(['_id' => $pid])->one();
            $text = $update['post_text'];
            $image = $update['image'];

            if ((isset($_FILES['imageFilepost']['name']) && !empty($_FILES['imageFilepost']['name'])) || isset($_POST['imageFile2']) && !empty($_POST['imageFile2'])) 
            {
                $im =  '';
                $url = '../web/uploads/';
                $urls = '/uploads/';
                $imageBulkArray = array();

            	if(isset($_FILES['imageFilepost']['name']) && !empty($_FILES['imageFilepost']['name'])) {
                	$imgcount = count($_FILES['imageFilepost']['name']);
            		for ($i =0;$i < $imgcount;$i++) 
					{
	                    $name = $_FILES["imageFilepost"]["name"][$i];
	                    $tmp_name = $_FILES["imageFilepost"]["tmp_name"][$i];
	                    if (isset($name) && $name != "") 
						{
	                        move_uploaded_file($tmp_name, $url . $date . $name);
	                        $img = $urls . $date . $name;
	                        $imageBulkArray[] = $img;
	                    } 
	                }
            	}


                if (isset($_POST['imageFile2']) && count($_POST["imageFile2"]) >0) 
			    {
			    	$imageFile2 = array_values($_POST['imageFile2']);

			    	foreach ($imageFile2 as $simageFile2) {
		    			if(file_exists($simageFile2)) {
	                        $image_extn = end(explode('.', $simageFile2));
	                        $rand = rand(111,999).'_'.time();
	                        $newname = $url.$date.$rand.'.'.$image_extn;

	                        copy($simageFile2, $newname);
	                        
	                        $newname = str_replace('../web/uploads/', '/uploads/', $newname);
	                        $img = $newname;
	                        $imageBulkArray[] = $img;

	                    }		
			    	}
	            }
	            

	            $data1 = array();
	            if($image != '') {
					$data1 = explode(",", $image);
		          	$data1 = array_filter($data1);
	            }

	            $data2 = array();
	            if(isset($imageBulkArray) && !empty($imageBulkArray)) {
	            	$data2 = $imageBulkArray;
	            }

	            $newData = array_merge($data1, $data2);

	            if(!empty($newData)) {
	            	$newData = implode(",", $newData);
	            	$newData = $newData.',';
	            	$update->image = $newData;
	            } else {
	            	$update->image = '';
	            } 
                
                if (isset($_POST['desc']) && !empty($_POST['desc'])) 
                {
                    $update->post_text = ucfirst($_POST['desc']);
                    $update->post_type = 'text and image';
                } 
                else if (isset($text) && !empty($text)) 
                {
                    $update->post_type = 'text and image';
                } 
                else 
                {
                    $update->post_type = 'image';
                }
            }
			else {
                if (isset($_POST['desc']) && !empty($_POST['desc'])) {
                    $update->post_text = ucfirst($_POST['desc']);
                    if (isset($image) && !empty($image)) {
                        $update->post_type = 'text and image';
                    } else {
                        $update->post_type = 'text';
                    }
                }
            }


            $update->post_title = isset($_POST['title']) ? ucfirst($_POST['title']) : '';
            $update->post_tags = isset($_POST['posttags']) ? $_POST['posttags'] : '';
            $post_privacy = isset($_POST['post_privacy']) ? trim($_POST['post_privacy']) : '';

            if(trim($post_privacy) == 'Custom') {
            	if(isset($_POST['customids']) && !empty($_POST['customids'])) {
		    		$ids = $_POST['customids'];
            		if(is_array($ids)) {
		    			$ids = implode(',', $ids);
            		}
	    			$update->customids = $ids;
	    		}
    		} else {
    			$update->customids = '';
    		}

            $update->post_privacy = $post_privacy;
            $update->currentlocation = isset($_POST['edit_current_location']) ? $_POST['edit_current_location'] : '';
            $update->share_setting = isset($_POST['share_setting']) ? $_POST['share_setting'] : '';
            $update->comment_setting = isset($_POST['comment_setting']) ? $_POST['comment_setting'] : '';
            $update->post_created_date = "$date";
            if(isset($_POST['pagereviewrate']) && !empty($_POST['pagereviewrate']))
            {
                if($_POST['pagereviewrate'] > 5)
                {
                    $_POST['pagereviewrate'] = '5';
                }
                else if($_POST['pagereviewrate'] < 1)
                {
                    $_POST['pagereviewrate'] = '1';
                }
                else
                {
                    $_POST['pagereviewrate'] = $_POST['pagereviewrate'];
                }
                $update->rating = $_POST['pagereviewrate'];
            }
			if(isset($_POST['placereviewrate']) && !empty($_POST['placereviewrate']))
            {
                if($_POST['placereviewrate'] > 5)
                {
                    $_POST['placereviewrate'] = '5';
                }
                else if($_POST['placereviewrate'] < 1)
                {
                    $_POST['placereviewrate'] = '1';
                }
                else
                {
                    $_POST['placereviewrate'] = $_POST['placereviewrate'];
                }
                $update->placereview = $_POST['placereviewrate'];
            }
			if(isset($_POST['edit_trav_cat']) && !empty($_POST['edit_trav_cat']))
            {
                $update->trav_cat = $_POST['edit_trav_cat'];
				$update->post_privacy = 'Public';
				$update->trav_price = $_POST['edit_trav_price'];
            }
			if($update['is_trip'] == '1')
            {
				$update->post_privacy = 'Public';
            }
			if(isset($update['placetype']))
            {
				$update->share_setting = 'Disable';
				$update->comment_setting = $_POST['comment_setting'];
            }
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
            
            if((string)$last_insert_id != '') {
				$post = PostForm::find()->where([(string)'_id' => (string)$last_insert_id])->one();
				if(!empty($post)) {
					$postid = (string)$post['_id'];
					$postownerid = (string)$post['post_user_id'];
					$postprivacy = $post['post_privacy'];
					$isOk = $this->filterDisplayLastPost($postid, $postownerid, $postprivacy);
					if($isOk == 'ok2389Ko') {
						$this->display_last_post($postid);
					}
				}
			}

			
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

    public function actionUnfollowconnect() 
	{
        $loginmodel = new \frontend\models\LoginForm();
        if (isset($_POST['fid']) && !empty($_POST['fid'])) 
		{
			$fid = (string) $_POST['fid'];
            $data = array();
			
            $session = Yii::$app->session;
            $email = $session->get('email_id');
            $user_id = (string) $session->get('user_id');

            $userexist = UnfollowConnect::find()->where(['user_id' => $user_id])->one();
            $unfollow = new UnfollowConnect();
            if ($userexist) 
            {
                if (strstr($userexist['unfollow_ids'],$fid))
                {
                    $unfollow = UnfollowConnect::find()->where(['user_id' => $user_id])->one();
                    $unfollow->unfollow_ids = str_replace($fid.',',"",$userexist['unfollow_ids']);
                    $unfollowids = $unfollow->unfollow_ids;
                    $unfollow->update();
                    if(strlen($unfollowids) == 0)
                    {
                        $unfollow = UnfollowConnect::find()->where(['user_id' => $user_id])->one();
                        $unfollow->delete();
                    }
                    return 1;
                }
                else
                {
                    $unfollow = UnfollowConnect::find()->where(['user_id' => $user_id])->one();
                    $unfollow->unfollow_ids = $userexist['unfollow_ids'].$fid.',';
                    if ($unfollow->update())
                    {
                        return 2;
                    }
                    else
                    {
                        return 0;
                    }
                }
            }
            else
            {
                $unfollow->user_id = $user_id;
                $unfollow->unfollow_ids = $fid.',';
                if ($unfollow->insert())
                {
                    return 2;
                }
                else
                {
                    return 0;
                }
            }
        }
    }

	public function actionBlockconnect()
	{
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string) $session->get('user_id');
        $label = 'Block';
        if (isset($_POST['fid']) && !empty($_POST['fid'])) {
            $fid = (string) $_POST['fid'];
			
			$data = SecuritySetting::find()->where(['user_id' => $user_id])->one();
			if(!empty($data)) {
				if(isset($data['blocked_list']) && $data['blocked_list'] != '') {
					$blockIds = $data['blocked_list'];
					$blockIds = explode(',', $blockIds);
					if(!empty($blockIds)) {
						if(in_array($fid, $blockIds)) {
							if (($key = array_search($fid, $blockIds)) !== false) {
							    unset($blockIds[$key]);
							    $label = 'Unblock';
							}
						} else {
							$blockIds[] = $fid;
						}
					} else {
						$blockIds[] = $fid;
					}
				} else {
					$blockIds[] = $fid;
				}


				$blockIds = implode(',', $blockIds);
				if(empty($blockIds)) {
					$data->blocked_list = '';
					$data->update();
					$result = array('status' => true, 'label' => $label);
					return json_encode($result, true);
				} else {
					$data->blocked_list = $blockIds;
					$data->update();
					$result = array('status' => true, 'label' => $label);
					return json_encode($result, true);
				}
			} else {
				$data = new SecuritySetting();
				$data->user_id = $user_id;
				$data->blocked_list = "$fid";
				$data->save();
				$result = array('status' => true, 'label' => $label);
				return json_encode($result, true);
			}
		}
    }
	
    public function actionMuteconnect()
	{
        $loginmodel = new \frontend\models\LoginForm();
        if (isset($_POST['fid']) && !empty($_POST['fid'])) 
		{
			$fid = (string) $_POST['fid'];
			$data = array();
			$session = Yii::$app->session;
            $email = $session->get('email_id');
            $user_id = (string) $session->get('user_id');

            $userexist = MuteConnect::find()->select(['_id','mute_ids'])->where(['user_id' => $user_id])->one();
            $mute = new MuteConnect();
            if ($userexist)
            {
                if (strstr($userexist['mute_ids'], $fid))
                {
                    $mute = MuteConnect::find()->select(['_id'])->where(['user_id' => $user_id])->one();
                    $mute->mute_ids = str_replace($fid.',',"",$userexist['mute_ids']);
                    $muteids = $mute->mute_ids;
                    $mute->update();
                    if(strlen($muteids) == 0)
                    {
                        $mute = MuteConnect::find()->select(['_id'])->where(['user_id' => $user_id])->one();
                        $mute->delete();
                    }
                    return 1;
                }
                else
                {
                    $mute = MuteConnect::find()->select(['_id'])->where(['user_id' => $user_id])->one();
                    $mute->mute_ids = $userexist['mute_ids'].$fid.',';
                    if ($mute->update())
                    {
                        return 2;
                    }
                    else
                    {
                        return 0;
                    }
                }
            }
            else
            {
                $mute->user_id = $user_id;
                $mute->mute_ids = $fid.',';
                if ($mute->insert())
                {
                    return 2;
                }
                else
                {
                    return 0;
                }
            }
        }
    }

	public function actionSharenowwithconnections() 
	{
        $lmodel = new \frontend\models\LoginForm();
        $pmodel = new \frontend\models\PostForm();
        $fmodel = new \frontend\models\Connect();

        $session = Yii::$app->session;
        $user_id = (string) $session->get('user_id');
        $result_security = SecuritySetting::find()->select(['my_post_view_status'])->where(['user_id' => $user_id])->one();
        if ($result_security) 
		{
            $post_privacy = $result_security['my_post_view_status'];
        } 
		else 
		{
            $post_privacy = 'Public';
        }

        if (isset($_POST['keyword']) && !empty($_POST['keyword'])) {
            $data = array();

            $session = Yii::$app->session;
            $email = $session->get('email_id');
            $user_id = (string) $session->get('user_id');
            $getusers = Connect::userlistsuggetions($_POST['keyword']);
            foreach ($getusers as $getuser) 
			{
                $uid = $getuser['_id'];
                $usrname = $getuser['fname'] . " " . $getuser['lname'];
                $connections_to = Connect::find()->select(['_id'])->where(['from_id' => "$uid",'to_id' => "$user_id",'status' => '1'])->one();
                $connections_from = Connect::find()->select(['_id'])->where(['from_id' => "$user_id",'to_id' => "$uid",'status' => '1'])->one();
                if ($connections_to || $connections_from) 
				{
                    $dp = $this->getimage($getuser['_id'],'thumb');
                    ?>
                    <div class="tb-share-box" onClick="selectName('<?=$usrname?>','<?=$uid?>');">
                        <input type="hidden" value="<?=$uid?>" id="frndid" name="frndid"/>
                        <img style="height: 30px; width:30px; float:left;" alt="user-photo" class="img-responsive" src="<?= $dp ?>">
                        <span class="share-sp"><?=$usrname?></span>
                    </div>
                    <?php
                }
            }
        }
        if (isset($_POST['spid']) && !empty($_POST['spid']) && isset($user_id) && !empty($user_id))
		{
            $data = array();
			$session = Yii::$app->session;
            $email = $session->get('email_id');
            $user_id = (string) $session->get('user_id');

            $getpostinfo = PostForm::find()->where(['_id' => $_POST['spid'],])->one();
            if(isset($_POST['current_location']) && !empty($_POST['current_location']))
            {
                $currentlocation = $_POST['current_location'];
            }
            else
            {
                $currentlocation = '';
            }
            if ($getpostinfo)
			{
                $date = time();
                $sharepost = new PostForm();
               
                if (!empty($getpostinfo['post_type'])) 
				{
                    $sharepost->post_type = $getpostinfo['post_type'];
                }
                if (!empty($_POST['desc'])) 
				{
                    $sharepost->post_text = ucfirst($_POST['desc']);
                }
				else 
				{
                    $sharepost->post_text = '';
                }
				if(strstr($_SERVER['HTTP_REFERER'],'r=tripexperience'))
				{
					$sharepost->share_trip = '1';
				}
                $sharepost->post_status = '1';
                $sharepost->post_created_date = "$date";
                $sharepost->post_tags = (isset($_POST['posttags']) && !empty($_POST['posttags'])) ? $_POST['posttags'] : '';
                if ($_POST['sharewall'] == 'own_wall') 
				{
                    $puser = $user_id;
                }
				else 
				{
                    $puser = $_POST['frndid'];
                }
                if(isset($_POST['post_privacy']) && !empty($_POST['post_privacy']))
                {
                    $postprivacy = $_POST['post_privacy'];
                }
                else
                {
                    $postprivacy = 'Public';
                }

                $sharepost->post_user_id = $puser;
                $sharepost->shared_from = $getpostinfo['post_user_id'];
                $sharepost->currentlocation = $currentlocation;
                $sharepost->post_privacy = $postprivacy;

                if($postprivacy == 'Custom') {
                	if(isset($_POST['customids']) && !empty($_POST['customids'])) {
	                    $custom = $_POST['customids'];
	                    $custom = implode(',', $custom);
	                    $sharepost->customids = $custom;
	                }
                } else {
                    $sharepost->customids = '';
                }

                if (!empty($getpostinfo['image'])) 
				{
                    $sharepost->image = $getpostinfo['image'];
                }
                if (!empty($getpostinfo['link_title'])) 
				{
                    $sharepost->link_title = ucfirst($getpostinfo['link_title']);
                }
                if (!empty($getpostinfo['link_description'])) 
				{
                    $sharepost->link_description = ucfirst($getpostinfo['link_description']);
                }
                if (!empty($getpostinfo['album_title'])) 
				{
                    $sharepost->album_title = $getpostinfo['album_title'];
                }
                if (!empty($getpostinfo['album_place'])) 
				{
                    $sharepost->album_place = $getpostinfo['album_place'];
                }
                if (!empty($getpostinfo['album_img_date'])) 
				{
                    $sharepost->album_img_date = $getpostinfo['album_img_date'];
                }
                if (!empty($getpostinfo['is_album'])) 
				{
                    $sharepost->is_album = $getpostinfo['is_album'];
                }

                $posttags = isset($_POST['posttags']) ? $_POST['posttags'] : '';
                if($posttags != 'null')
                {
                    $gsu_id = $getpostinfo['post_user_id'];
                    $sec_result_set = SecuritySetting::find()->where(['user_id' => "$gsu_id"])->one();
                    if ($sec_result_set)
                    {
                        $tag_review_setting = $sec_result_set['review_tags'];
                    }
                    else
                    {
                        $tag_review_setting = 'Disabled';
                    }
                    if($tag_review_setting == "Enabled")
                    {
                        $review_tags = "1";
                    }
                    else
                    {
                        $review_tags = "0";
                    }
                }
                else
                {
                    $review_tags = "0";
                }
                if(isset($getpostinfo['parent_post_id']) && !empty($getpostinfo['parent_post_id']))
                {
                    $parid = $getpostinfo['parent_post_id'];
                }
                else
                {
                    $parid = $_POST['spid'];
                }

                
				$puid = $getpostinfo['post_user_id'];
                $sharepost->parent_post_id = $parid;
                $sharepost->is_timeline = '1';
                $sharepost->is_deleted = $review_tags;
                $sharepost->shared_by = $user_id;
                $sharepost->share_setting = $_POST['share_setting'];
                $sharepost->comment_setting = $_POST['comment_setting'];
                $sharepost->post_ip = $_SERVER['REMOTE_ADDR'];
                $sharepost->insert();
                $last_insert_id = $sharepost->_id;

				if((string)$puid != (string)$user_id)
				{
					$cre_amt = 1;
					$cre_desc = 'sharepost';
					$status = '1';
					$details = $user_id;
					$credit = new Credits();
					$credit = $credit->addcredits($puid,$cre_amt,$cre_desc,$status,$details);
				}
				
                $result_security = SecuritySetting::find()->where(['user_id' => "$puser"])->one();
                if ($result_security)
                {
                    $tag_review_setting = $result_security['review_posts'];
                }
                else
                {
                    $tag_review_setting = 'Disabled';
                }
                // Insert record in notification table also
                $notification =  new Notification();
                $notification->share_id =   "$last_insert_id";
                $notification->post_id = "$last_insert_id";
                $notification->user_id = $puser;
                $notification->notification_type = 'sharepost';
                $notification->review_setting = $tag_review_setting;
                $notification->is_deleted = '0';
                $notification->status = '1';
                $notification->created_date = "$date";
                $notification->updated_date = "$date";
                $post_details = PostForm::find()->where(['_id' => $_POST['spid']])->one();
                $notification->post_owner_id = $post_details['post_user_id'];
                $notification->tag_id = $post_details['post_tags'];
                $notification->shared_by = $user_id;
                if($post_details['post_user_id'] != $user_id && $post_details['post_privacy'] != "Private")
                {
                    $notification->insert();
                    $_POST['posttags'] = '';
                    $tag_connections = explode(',',$_POST['posttags']);
                    $tag_count = count($tag_connections);
                    if($posttags != 'null')
                    {
                        for ($i = 0; $i < $tag_count; $i++)
                        {
                            $result_security = SecuritySetting::find()->select(['review_posts'])->where(['user_id' => "$tag_connections[$i]"])->one();
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
                            $notification->is_deleted = $review_tags;
                            $notification->status = '1';
                            $notification->created_date = "$date";
                            $notification->updated_date = "$date";
                            $notification->insert();
                        }
                    }
                }
				$pardetails = PostForm::find()->where(['_id' => "$parid"])->one();
                if ($last_insert_id) 
				{
                    $sharepost = PostForm::find()->where(['_id' => $parid])->one();
                   
                    $sharepost->share_by = $sharepost['share_by'] . $user_id . ',';
                    if ($sharepost->update()) 
					{
						if(!isset($pardetails['placetype']))
						{
							if((string)$last_insert_id != '') {
								$post = PostForm::find()->where([(string)'_id' => (string)$last_insert_id])->one();
								if(!empty($post)) {
									$postid = (string)$post['_id'];
									$postownerid = (string)$post['post_user_id'];
									$postprivacy = $post['post_privacy'];
									$isOk = $this->filterDisplayLastPost($postid, $postownerid, $postprivacy);
									if($isOk == 'ok2389Ko') {
										$this->display_last_post($postid);
									}
								}
							}
						}
                    }
					else {
                    }
                }
				else {
                   
                }
                
            } else {
                print false;
            }
        }
        if (isset($_POST['shareid']) && !empty($_POST['shareid'])) 
		{
            $data = array();

            $session = Yii::$app->session;
            $email = $session->get('email_id');
            $user_id = (string) $session->get('user_id');

            $getpostinfo = PostForm::find()->where(['_id' => $_POST['shareid'],])->one();
            if ($getpostinfo) 
			{
                $date = time();
                $sharepost = new PostForm();
               
                if (!empty($getpostinfo['post_type'])) 
				{
                    $sharepost->post_type =
					$getpostinfo['post_type'];
                }
                if (!empty($getpostinfo['post_text'])) 
				{
                    $sharepost->post_text =
					$getpostinfo['post_text'];
                }
                $sharepost->post_status = '1';
                $sharepost->is_deleted = '0';
                $sharepost->post_privacy = 'Public';
                $sharepost->post_created_date = "$date";
                $sharepost->post_user_id = $user_id;
                $sharepost->shared_from = $getpostinfo['post_user_id'];
                if (!empty($getpostinfo['image'])) 
				{
                    $sharepost->image =
					$getpostinfo['image'];
                }
                if (!empty($getpostinfo['link_title'])) 
				{
                    $sharepost->link_title =
					$getpostinfo['link_title'];
                }
                if (!empty($getpostinfo['link_description'])) 
				{
                    $sharepost->link_description =
					$getpostinfo['link_description'];
                }
                $last_insert_id = $sharepost->insert();
                if ($last_insert_id) 
				{
                   $last_insert_id =  $sharepost->_id;
                    $sharepost = PostForm::find()->where(['_id' => $_POST['shareid'],])->one();
                    $sharepost->share_by = $getpostinfo['share_by'] . $user_id . ',';
                    if ($sharepost->update()) 
                    {
                    	if((string)$last_insert_id != '') {
							$post = PostForm::find()->where([(string)'_id' => (string)$last_insert_id])->one();
							if(!empty($post)) {
								$postid = (string)$post['_id'];
								$postownerid = (string)$post['post_user_id'];
								$postprivacy = $post['post_privacy'];
								$isOk = $this->filterDisplayLastPost($postid, $postownerid, $postprivacy);
								if($isOk == 'ok2389Ko') {
									$this->display_last_post($postid);
								}
							}
						}
                    }
                }
            }
        }
    }


    public function actionUpload()
    {
        $this->layout = 'ajax_layout';
        $model = new PostForm();
        $session = Yii::$app->session;
        $email = $session->get('email');
        $userid = $user_id = (string)$session->get('user_id');
		$notification =  new Notification();
		if(isset($userid) && $userid != '') {
		$authstatus = UserForm::isUserExistByUid($userid);
		if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
			$data['auth'] = $authstatus;
			return $authstatus;
		} else {
		  	$date = time();
	        $post_status = '0';
	        if (empty($_POST['test']))
	        {
	            $_POST['test'] = ''; 
	        }
	        if (empty($_POST['title']))
	        {
	            $_POST['title'] = ''; 
	        }
	        
	        $pgname = isset($_POST['pagename']) ? $_POST['pagename'] : ''; 
	        $purifier = new HtmlPurifier();
	        $text = HtmlPurifier::process($_POST['test']);
	        $post = new PostForm();

	        if($pgname == 'wall' && $userid != $_POST['tlid'])
	        {
	            $post->is_timeline = '1';
	            $user_id = $_POST['tlid'];
	            $post->shared_by = "$userid";
	            $result_security = SecuritySetting::find()->select(['my_post_view_status'])->where(['user_id' => $user_id])->one();
	            if ($result_security)
	            {
	                $_POST['post_privacy'] = $result_security['my_post_view_status'];
	            }
	            else
	            {
	                $_POST['post_privacy'] = 'Public';
	            }
	        }
        
	        if($pgname == 'page')
	        {
	            $page_details = Page::Pagedetails($_POST['tlid']);
	            if($page_details['created_by'] == $user_id)
	            {
	                $tlid = $_POST['tlid'];
	            }
	            else
	            {
	                $tlid = $user_id;
	            }
	            if($page_details['gen_post_review'] == 'on' && $page_details['created_by'] != $user_id)
	            {
	                $post_status = '5';
	            }
	            $post->is_timeline = '1';
	            $post->shared_by = "$user_id";
	            $post->pagepost = '1';
	            $_POST['post_privacy'] = 'Public';
	            $user_id = $_POST['tlid'];
	            $replace = explode(",",$page_details['gen_page_filter']);
	            $text = str_ireplace($replace, '', $text);
	            $page_id = $_POST['tlid'];
	            $post->page_id = "$page_id";
	        }
			
			
			if($pgname == 'tripexperience')
	        {
				$post->is_trip = '1';
				//$_POST['post_privacy'] = 'Public';
				$loc = $_POST['current_location'];
				$countrys = explode(',',$loc);
				$country = trim(end($countrys));
				$continent = $this->getcontinent($country);
				if($continent != null)
				{
					$post->continent =  $continent;
				}
				$post->country =  $country;
	        }

	        if($pgname == 'tripstory')
	        {
				$post->is_trip = '1';
				//$_POST['post_privacy'] = 'Public';
				$loc = $_POST['current_location'];
				$countrys = explode(',',$loc);
				$country = trim(end($countrys));
				$continent = $this->getcontinent($country);
				if($continent != null)
				{
					$post->continent =  $continent;
				}
				$post->country =  $country;
	        }

	        if (!empty($_POST['link_description']))
	        {
	            $title = $_POST['link_title'];
	            $description = $_POST['link_description'];
	            $image = $_POST['link_image'];
	            $url = $_POST['link_url'];

	            $post->post_type = 'link';
	            $post->link_title = ucfirst($title);
	            $post->image = $image;
	            $post->post_text = ucfirst($url);
	            $post->link_description = $description;
	            $post->post_created_date = "$date";
	            $post->post_user_id = "$user_id";
	            $post->is_deleted = "$post_status";
	            $post->post_status = '1';
	        }
	        else 
	        {
	            $post->post_status = '1';
	            $post->post_text = ucfirst($text);
	            $post->post_type = 'text';
	            $post->post_created_date = "$date";
	            $post->post_user_id = "$user_id";

	            if(isset($_POST['counter'])){$_POST['counter']=$_POST['counter'];}else{$_POST['counter']=0;}
	 
                $img = '';
                $im = '';
                $url = '../web/uploads/';
                $urls = '/uploads/';

                $imageBulkArray = array();


	            if (isset($_FILES['imageFile1']) && count($_FILES["imageFile1"]["name"]) >0) 
			    {
					$imgcount = count($_FILES["imageFile1"]["name"]);
					for ($i = $_POST['counter']; $i < $imgcount; $i++)
	                {
						if (isset($_FILES["imageFile1"]["name"][$i]) && $_FILES["imageFile1"]["name"][$i] != "") 
						{
	                        if ($text == '') { $post->post_type = 'image'; }
	                        else { $post->post_type = 'text and image'; }
	                        
	                        $image_extn = explode('.',$_FILES["imageFile1"]["name"][$i]);
$image_extn = end($image_extn);
	                        $rand = rand(111,999).'_'.time();
	                        move_uploaded_file($_FILES["imageFile1"]["tmp_name"][$i], $url.$date.$rand.'.'.$image_extn);
	                        
	                        $img = $urls.$date.$rand.'.'.$image_extn;
	                        $imageBulkArray[] = $img;
	                    }
	                }
	            }

	            if (isset($_POST['imageFile2']) && count($_POST["imageFile2"]) >0) 
			    {
			    	$imageFile2 = array_values($_POST['imageFile2']);

			    	foreach ($imageFile2 as $simageFile2) {
		    			if(file_exists($simageFile2)) {
	                        
	                        if ($text == '') { $post->post_type = 'image'; }
	                        else { $post->post_type = 'text and image'; }
	                        
	                        $image_extn = end(explode('.', $simageFile2));
	                        $rand = rand(111,999).'_'.time();
	                        $newname = $url.$date.$rand.'.'.$image_extn;

	                        copy($simageFile2, $newname);
	                        
	                        $newname = str_replace('../web/uploads/', '/uploads/', $newname);
	                        $img = $newname;
	                        $imageBulkArray[] = $img;

	                    }		
			    	}
	            }

	            if(!empty($imageBulkArray)) {
		            $imageBulkArray = implode(',', $imageBulkArray);
		            $imageBulkArray = $imageBulkArray.',';
            		$post->image = $imageBulkArray;
		        } else {
		        	$post->image = '';
		        }
	        }

	        if(isset($_POST['current_location']) && !empty($_POST['current_location']) && $_POST['current_location']!='undefined')
	        {
	            $post->currentlocation = $_POST['current_location'];
	        }

	        $post->custom_share = (isset($_POST['sharewith']) && !empty($_POST['sharewith'])) ? $_POST['sharewith'] : '';
	        $post->custom_notshare = (isset($_POST['sharenot']) && !empty($_POST['sharenot'])) ? $_POST['sharenot'] : '';
	        $post->anyone_tag = (isset($_POST['customchk']) && !empty($_POST['customchk'])) ? $_POST['customchk'] : '';
	        $post->post_tags = (isset($_POST['posttags']) && !empty($_POST['posttags'])) ? $_POST['posttags'] : '';
	    
	        $post->post_title = ucfirst($_POST['title']);
	        $post->share_setting = $_POST['share_setting'];
	        $post->comment_setting = $_POST['comment_setting'];
	        $post->post_privacy = $_POST['post_privacy'];
	        $post->customids = $_POST['custom'];
	        $post->is_deleted = "$post_status";
	        $post->post_ip = $_SERVER['REMOTE_ADDR'];
	        if($pgname == 'place')
	        {
	            $post->share_setting = 'Disable';
				$post->post_privacy = 'Public';
				$post->placetype = $_POST['placetype'];
				$post->placetitlepost = $_POST['placetitlepost'];
				if($_POST['placetype'] == 'reviews')
				{
					if($_POST['placereview'] > 5){$_POST['placereview'] = '5';}
					else if($_POST['placereview'] < 1){$_POST['placereview'] = '1';}
					else{$_POST['placereview'] = $_POST['placereview'];}
					$post->placereview = (int)$_POST['placereview'];
				}
	        }
	        $post->insert();

	        $last_insert_id =  $post->_id;
	        
	        if($_POST['post_privacy'] != 'Private')
	        {
	            // Insert record in notification table also
	          
	            $notification->post_id =   "$last_insert_id";
	            $notification->user_id = "$user_id";
	            $notification->notification_type = 'post';
	            $notification->is_deleted = '0';
	            $notification->created_date = "$date";
	            $notification->updated_date = "$date";
	            if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
	            {
	                $page_id = Page::Pagedetails($user_id);
	                $usrid = $page_id['created_by'];
	                $notification->user_id = (string)$session->get('user_id');
	                $notification->page_id = "$user_id";
	                $notification->entity = 'page';
	                $notification->notification_type = 'onpagewall';
	                $notification->post_owner_id = "$usrid";
	                $notification->status = "1";
	                if($page_id['not_add_post'] == 'on')
	                {
	                    $notification->insert();
	                }
	            }
	            if($pgname == 'wall')
	            {
	                $notification->user_id = "$userid";
	                $notification->shared_by = $_POST['tlid'];
	                if($userid != $_POST['tlid'])
	                {
	                    $notification->notification_type = 'onwall';
	                }
	            }
				
				$notificationignorebulk = array("page", "travstore", "tripexperience", "place");
				if(!in_array($pgname, $notificationignorebulk)) {
	            {
	                $notification->insert();
	            }
		        }
		        
		        if($_POST['posttags'] != 'null')
		        {
		            // Insert record in notification table also
		            $tag_connections = explode(',',$_POST['posttags']);
		            $tag_count = count($tag_connections);
		            for ($i = 0; $i < $tag_count; $i++)
		            {
		                $result_security = SecuritySetting::find()->select(['review_posts'])->where(['user_id' => "$tag_connections[$i]"])->one();
		                if ($result_security)
		                {
		                    $tag_review_setting = $result_security['review_posts'];
		                }
		                else
		                {
		                    $tag_review_setting = 'Disabled';
		                }
		                $notificationignorebulk = array("page", "travstore", "tripexperience");
						$notification = new Notification();
		                $notification->post_id =   "$last_insert_id";
		                $notification->user_id = $tag_connections[$i];
		                $notification->notification_type = 'tag_connect';
		                $notification->review_setting = $tag_review_setting;
		                $notification->is_deleted = '0';
		                $notification->status = '1';
		                $notification->created_date = "$date";
		                $notification->updated_date = "$date";
		                if(!in_array($pgname, $notificationignorebulk)) {
		                    $notification->insert();
		                }
		            }
		        }
				
				if($post_status == '0') {
					if((string)$last_insert_id != '') {
						$post = PostForm::find()->where([(string)'_id' => (string)$last_insert_id])->one();
						if(!empty($post)) {
							$postid = (string)$post['_id'];
							$postownerid = (string)$post['post_user_id'];
							$postprivacy = $post['post_privacy'];
							$isOk = $this->filterDisplayLastPost($postid, $postownerid, $postprivacy);
							if($isOk == 'ok2389Ko') {
								$this->display_last_post($postid);
							}
						}
					}					
				} else {
					if($page_details['gen_post_review'] == 'on' && $page_details['created_by'] != $user_id) {
						$this->display_review_message();
					} else {
						if((string)$last_insert_id != '') {
							$post = PostForm::find()->where([(string)'_id' => (string)$last_insert_id])->one();
							if(!empty($post)) {
								$postid = (string)$post['_id'];
								$postownerid = (string)$post['post_user_id'];
								$postprivacy = $post['post_privacy'];
								$isOk = $this->filterDisplayLastPost($postid, $postownerid, $postprivacy);
								if($isOk == 'ok2389Ko') {
									$this->display_last_post($postid);
								}
							}
						}
					}
				}
			}
		}
		}
		else {
        	return 'checkuserauthclassg';
        }
    }

    public function actionNewPost() 
	{
        $posts = PostForm::getUserConnectionsPosts('updates');
        $count_post = count($posts);
        return $count_post;
    }

    public function actionAccountsettings() 
	{
	    $model = new \frontend\models\UserSetting();
        $model1 = new \frontend\models\LoginForm();
        $model2 = new \frontend\models\Personalinfo();
		$model->scenario = 'basicinfo';
		$session = Yii::$app->session;
        $email = $session->get('email');

        $id = LoginForm::getLastInsertedRecord($email);
        $user_id = (string) $id['_id'];
        
        if(isset($_POST['birth_date']) && !empty($_POST['birth_date']))
		{
            $_POST['LoginForm']['birth_date'] = $_POST['birth_date']; 
            $_POST['birth_date_access'] = 'Private'; 
  
        }

        $record_user = LoginForm::find()->where(['_id' => $user_id])->one();
        $record_personal = Personalinfo::find()->where(['user_id' => $user_id])->one();
        $record_setting = UserSetting::find()->where(['user_id' => $user_id])->one();

        if (isset($_POST) && !empty($_POST)) {
            $data = array();
            if(isset($_POST['about']) && ($_POST['livesin']) && ($_POST['walloccupation']) && ($_POST['wallinterests']) && ($_POST['walllanguage']) && ($_POST['walleducation'])) {
				if($_POST['walloccupation'] == 'null') {
					$_POST['walloccupation'] = '';				
				}

				if($_POST['wallinterests'] == 'null') {
					$_POST['wallinterests'] = '';
				}

				if($_POST['walllanguage'] == 'null') {
					$_POST['walllanguage'] = '';
				}
				
				if($_POST['walleducation'] == 'null') {
					$_POST['walleducation'] = '';
				}
				
				/* Start For Inserting New Language in Language */
				$record_language = ArrayHelper::map(Language::find()->all(), 'name', 'name');
				$lang = explode(",",$_POST['walllanguage']);
				$ans=array_diff($lang,$record_language);
            	if(isset($ans) && !empty($ans)) {
					$insert_language = new Language();
					foreach($ans AS $language_diff) {
						$insert_language->name = ucfirst($language_diff);
						$insert_language->insert();
					}                  
				}
				
				/* End For Inserting New Language in Language */
				
				/* Start For Inserting New Language in Education */
				$record_education = ArrayHelper::map(Education::find()->all(), 'name', 'name');
           		$edu = explode(",",$_POST['walleducation']);
				$edu_differance=array_diff($edu,$record_education);
				if(isset($edu_differance) && !empty($edu_differance)) {
					$insert_education = new Education();
					foreach($edu_differance AS $edu_diff) {
						$insert_education->name = ucfirst($edu_diff);
						$insert_education->insert();
					}                  
				}
				/* End For Inserting New Language in Education*/
				
				/* Start For Inserting New interests in Education */
				$record_interests = ArrayHelper::map(Interests::find()->all(), 'name', 'name');
				$int = explode(",",$_POST['wallinterests']);
                $interests_differance=array_diff($int,$record_interests);
                if(isset($interests_differance) && !empty($interests_differance)) {
                    $insert_interests = new Interests();
                    foreach($interests_differance AS $int_diff) {
                        $insert_interests->name = ucfirst($int_diff);
                        $insert_interests->insert();
                    }                  
                }
				/* End For Inserting New interests in Education*/
				
				/* Start For Inserting New interests in occupation*/
				$record_occupation = ArrayHelper::map(Occupation::find()->all(), 'name', 'name');
				$ocu = explode(",",$_POST['walloccupation']);
                $occupation_differance=array_diff($ocu,$record_occupation);
                if(isset($occupation_differance) && !empty($occupation_differance)) {
                    $insert_occupation = new Occupation();
                    foreach($occupation_differance AS $ocu_diff) {
                        $insert_occupation->name = ucfirst($ocu_diff);
                        $insert_occupation->insert();
                    }                  
                }
				/* End For Inserting New interests in occupation*/
				
                if (!empty($record_personal)) 
				{
					$GApiKeyL = $GApiKeyP = Googlekey::getkey();

					$prepAddr = str_replace(' ','+',$_POST['livesin']);
					$prepAddr = str_replace("'",'',$prepAddr);
					$geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?key='.$GApiKeyL.'&address='.$prepAddr.'&sensor=false');
					$output = json_decode($geocode);
					$latitude = $output->results[0]->geometry->location->lat;
					$longitude = $output->results[0]->geometry->location->lng;
			
					$record_user->citylat = "$latitude";
					$record_user->citylong = "$longitude";
					$record_user->city = $_POST['livesin'];
					$record_user->country = $_POST['country'];
					$record_user->country_code = $_POST['country_code'];
					$record_user->update();
					$record_personal->about = $_POST['about'];
					$record_personal->occupation = ucfirst($_POST['walloccupation']);
					$record_personal->interests = ucfirst($_POST['wallinterests']);
					$record_personal->language = ucfirst($_POST['walllanguage']);
					$record_personal->education = ucfirst($_POST['walleducation']);
					$record_personal->update();
                }
                else
                {
					$record_personal = new Personalinfo();
                    $record_personal->user_id = $user_id;
                    $record_personal->about = $_POST['about'];
					$record_personal->occupation = ucfirst($_POST['walloccupation']);
					$record_personal->interests = ucfirst($_POST['wallinterests']);
					$record_personal->language = ucfirst($_POST['walllanguage']);
					$record_personal->education = ucfirst($_POST['walleducation']);					
                    $record_personal->insert();
                }

                $data[] = $record_personal['about'];
                $data[] = $record_personal['occupation'];
                $data[] = $record_personal['interests'];
                $data[] = $record_personal['language'];
                $data[] = $record_personal['education'];
                $data[] = $record_user['city'];
                $data[] = $record_user['country'];
				$data[] = $record_user['country_code'];
                return json_encode($data);
                exit;
            }

            if (isset($_POST['LoginForm']['fname']) && !empty($_POST['LoginForm']['fname'])) 
			{
				$record_user->fname = $_POST['LoginForm']['fname'];
                $record_user->lname = $_POST['LoginForm']['lname'];
                $record_user->fullname = $_POST['LoginForm']['fname'] . " " . $_POST['LoginForm']['lname'];
                $record_user->update(); 

                $data[] = $record_user->fname;
                $data[] = $record_user->lname;

                $data = $data[0] . " " . $data[1];
            }

            if (isset($_POST['LoginForm']['email']) && !empty($_POST['LoginForm']['email'])) 
			{
                $eml2 = $_POST['LoginForm']['email'];
                $record = LoginForm::find()->select(['email'])->where(['email' => $eml2])->one();

                if (!empty($record_setting)) 
				{
                    $record_setting->email_access = $_POST['email_access'];
                    $record_setting->update();
                }
				else 
				{
                    $insert_setting = new UserSetting();
                    $insert_setting->user_id = $user_id;
                    $insert_setting->email_access = $_POST['email_access'];
                    $insert_setting->insert();
                }
                if ($email == $eml2) 
				{
                    $data[] = '1';
				} 
				else
				{
                    if (!empty($record)) 
					{
						$data[] = '0';
                    } 
					else
					{
                        $record_user->email = $eml2;
                        $record_user->update();
                        $session->set('email',$eml2);

                        $data[] = $record_user->email;
                    }
                }
            }

            if (isset($_POST['LoginForm']['alternate_email']) && !empty($_POST['LoginForm']['alternate_email'])) 
			{
                $record_user->alternate_email = $_POST['LoginForm']['alternate_email'];
                $record_user->update();

                if (!empty($record_setting)) 
				{
                    $record_setting->alternate_email_access = $_POST['alternate_email_access'];
                    $record_setting->update();
                }
				else 
				{
                    $insert_setting = new UserSetting();
                    $insert_setting->user_id = $user_id;
                    $insert_setting->alternate_email_access = $_POST['alternate_email_access'];
                    $insert_setting->insert();
                }

                $data[] = $record_user->alternate_email;
            }
            if (isset($_POST['LoginForm']['password']) && !empty($_POST['LoginForm']['password'])) 
			{
                $record_user->password = $_POST['LoginForm']['password'];
                $record_user->con_password = $_POST['LoginForm']['con_password'];
                $date = time();
                $record_user->pwd_changed_date = "$date";
                $record_user->update();
                
                $time = Yii::$app->EphocTime->time_pwd_changed(time(),$record_user['pwd_changed_date']);
                $data[] = 'Password updated '.$time;
            }
            if (isset($_POST['LoginForm']['phone']) && !empty($_POST['LoginForm']['phone'])) 
			{
				$record_user->phone = $_POST['LoginForm']['phone'];
                $record_user->update();

                if (!empty($record_setting)) 
				{
                    $record_setting->mobile_access = $_POST['mobile_access'];
                    $record_setting->update();
                }
				else 
				{
                    $insert_setting = new UserSetting();
                    $insert_setting->user_id = $user_id;
                    $insert_setting->mobile_access = $_POST['mobile_access'];
                    $insert_setting->insert();
                }

                $data[] = $record_user->phone;
            }
            if (isset($_POST['LoginForm']['city']) && !empty($_POST['LoginForm']['city']))
			{                
                if (isset($_POST['country']) && !empty($_POST['country'])) 
				{
					$record_user->country = $_POST['country'];
					$record_user->update();
				}
            
	            if (isset($_POST['isd_code']) && !empty($_POST['isd_code'])) 
				{
	                $record_user->isd_code = $_POST['isd_code'];
	                $record_user->update();
	            }
				if (isset($_POST['contry_code']) && !empty($_POST['contry_code'])) 
				{
	                $record_user->country_code = $_POST['country_code'];
	                $record_user->update();
	            }

            	$GApiKeyL = $GApiKeyP = Googlekey::getkey();

				$record_user->city = $_POST['LoginForm']['city'];
				$prepAddr = str_replace(' ','+',$_POST['LoginForm']['city']);
				$prepAddr = str_replace("'",'',$prepAddr);
				$geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?key='.$GApiKeyL.'&address='.$prepAddr.'&sensor=false');
                $output = json_decode($geocode);
                $latitude = $output->results[0]->geometry->location->lat;
                $longitude = $output->results[0]->geometry->location->lng;
				
                $record_user->citylat = "$latitude";
                $record_user->citylong = "$longitude";
                $record_user->update();

                $data[] = $record_user->city;
                $data[] = $record_user->country;
                $data[] = $record_user->isd_code;

            }
            if (isset($_POST['LoginForm']['country']) && !empty($_POST['LoginForm']['country'])) 
			{
				$record_user->country = $_POST['LoginForm']['country'];
                $record_user->update();
				$data[] = $record_user->country;
            }
            if (isset($_POST['LoginForm']['gender']) && !empty($_POST['LoginForm']['gender'])) 
			{
				$record_user->gender = $_POST['LoginForm']['gender'];
                $record_user->update();

                $data[] = $record_user->gender;
                if (!empty($record_setting)) 
				{
                    $record_setting->gender_access = $_POST['gender_access'];
                    $record_setting->update();
                } 
				else 
				{
                    $insert_setting = new UserSetting();
                    $insert_setting->user_id = $user_id;
                    $insert_setting->gender_access = $_POST['gender_access'];
                    $insert_setting->insert();
                }
            }
            if (isset($_POST['LoginForm']['birth_date']) && !empty($_POST['LoginForm']['birth_date'])) 
			{
				$western_date_formate = date('F d, Y',strtotime($_POST['LoginForm']['birth_date']));
                $record_user->birth_date = $_POST['LoginForm']['birth_date'];
                $record_user->update();
                $data[] = $western_date_formate;
                if (!empty($record_setting)) 
				{
                    $record_setting->birth_date_access = $_POST['birth_date_access'];
                    $record_setting->update();
                } 
				else
				{
                    $insert_setting = new UserSetting();
                    $insert_setting->user_id = $user_id;
                    $insert_setting->birth_date_access = $_POST['birth_date_access'];
                    $insert_setting->insert();
                }
            }
            
            if (isset($_POST['Personalinfo']['hometown']) && !empty($_POST['Personalinfo']['hometown'])) 
			{
                if (!empty($record_personal)) 
				{
                    $record_personal->hometown = $_POST['Personalinfo']['hometown'];
                    $record_personal->update();

                    $data[] = $record_personal->hometown;
                }
				else 
				{
                    $insert_personal = new Personalinfo();
                    $insert_personal->user_id = $user_id;
                    $insert_personal->hometown = $_POST['Personalinfo']['hometown'];
                    $insert_personal->insert();

                    $data[] = $record_personal->hometown;
                }
            }
            if (isset($_POST['language'])) 
			{

				$record_language = ArrayHelper::map(Language::find()->all(), 'name', 'name');
				$lang = $_POST['language'];

				if(empty($lang) || $lang == '') {
					$lang = array();
				}

                $ans=array_diff($lang,$record_language);
                if(isset($ans) && !empty($ans))
				{
                    $insert_language = new Language();
                    foreach($ans AS $language_diff)                  
                    {
                        $insert_language->name = ucfirst($language_diff);
                        $insert_language->insert();
                    }                  
                }
               
                $lang_list = implode(",",$lang);
                if (!empty($record_personal)) 
				{
                    $record_personal->language = ucfirst($lang_list);
                    $record_personal->update();

                    $data[] = $record_personal->language;
                }
				else 
				{
                    $insert_personal = new Personalinfo();
                    $insert_personal->user_id = $user_id;
                    $insert_personal->language = ucfirst($lang_list);
                    $insert_personal->insert();

                    $data[] = $insert_personal->language;
                }
                if (isset($_POST['language_access']) && !empty($_POST['language_access'])) 
				{    
                    if (!empty($record_setting)) 
					{
                        $record_setting->language_access = $_POST['language_access'];
                        $record_setting->update();
                    } 
					else 
					{
                        $insert_setting = new UserSetting();
                        $insert_setting->user_id = $user_id;
                        $insert_setting->language_access = $_POST['language_access'];
                        $insert_setting->insert();
                    }
                }
            }

            if (isset($_POST['Personalinfo']['religion']) && !empty($_POST['Personalinfo']['religion'])) 
			{
                if (!empty($record_personal)) 
				{
                    $record_personal->religion = $_POST['Personalinfo']['religion'];
                    $record_personal->update();
                    $data[] = $record_personal->religion;
                }
				else 
				{
                    $insert_personal = new Personalinfo();
                    $insert_personal->user_id = $user_id;
                    $insert_personal->religion =$_POST['Personalinfo']['religion'];
                    $insert_personal->insert();

                    $data[] = $record_personal->religion;
                }

                if (!empty($record_setting)) 
				{
                    $record_setting->religion_access = $_POST['religion_access'];
                    $record_setting->update();
                } 
				else 
				{
                    $insert_setting = new UserSetting();
                    $insert_setting->user_id =$user_id;
                    $insert_setting->religion_access = $_POST['religion_access'];
                    $insert_setting->insert();
                }
            }
            if (isset($_POST['Personalinfo']['political_view']) && !empty($_POST['Personalinfo']['political_view'])) 
			{
                if (!empty($record_personal)) 
				{
                    $record_personal->political_view = $_POST['Personalinfo']['political_view'];
                    $record_personal->update();
                    $data[] = $record_personal->political_view;
                } 
				else 
				{
                    $insert_personal = new Personalinfo();
                    $insert_personal->user_id = $user_id;
                    $insert_personal->political_view = $_POST['Personalinfo']['political_view'];
                    $insert_personal->insert();

                    $data[] = $insert_personal->political_view;					
                }

                if (!empty($record_setting)) 
				{
                    $record_setting->political_view_access = $_POST['political_view_access'];
                    $record_setting->update();
                } 
				else 
				{
                    $insert_setting = new UserSetting();
                    $insert_setting->user_id = $user_id;
                    $insert_setting->political_view_access = $_POST['political_view_access'];
                    $insert_setting->insert();
                }
            }

            if (isset($_POST['Personalinfo']['about']) && !empty($_POST['Personalinfo']['about'])) 
			{
                if (!empty($record_personal)) 
				{
                    $record_personal->about = $_POST['Personalinfo']['about'];
                    $record_personal->update();

                    $data[] = $record_personal->about;
                } 
				else
				{
                    $insert_personal = new Personalinfo();
                    $insert_personal->user_id = $user_id;
                    $insert_personal->about = $_POST['Personalinfo']['about'];
                    $insert_personal->insert();
                    $data[] = $insert_personal->about;
                }
            }

            if (isset($_POST['education'])) 
			{
                $record_education = ArrayHelper::map(Education::find()->all(), 'name', 'name');
               
                $edu =$_POST['education'];
                if(empty($edu) || $edu == '') {
					$edu = array();
				}

                $edu_differance=array_diff($edu,$record_education);
                
                if(isset($edu_differance) && !empty($edu_differance))
				{
                    $insert_education = new Education();
                    foreach($edu_differance AS $edu_diff)                  
                    {
                        $insert_education->name = ucfirst($edu_diff);
                        $insert_education->insert();
                    }                  
                }
                
                $edu_list =implode(",",$edu);
                if (!empty($record_personal)) 
				{
                    
                    $record_personal->education =ucfirst($edu_list);
                    $record_personal->update();

                    $data[] =$record_personal->education;
                } 
				else 
				{
                    $insert_personal = new Personalinfo();
                    $insert_personal->user_id =$user_id;
                    $insert_personal->education = ucfirst($edu_list);
                    $insert_personal->insert();
                    $data[] = $insert_personal->education;
                }
            }

            if (isset($_POST['interests'])) 
			{         
                $record_interests = ArrayHelper::map(Interests::find()->all(), 'name', 'name');
               
                $int = $_POST['interests'];
                if(empty($int) || $int == '') {
					$int = array();
				}
                $interests_differance=array_diff($int,$record_interests);
                
                if(isset($interests_differance) && !empty($interests_differance))
				{
                    $insert_interests = new Interests();
                    foreach($interests_differance AS $int_diff)                  
                    {
                        $insert_interests->name = ucfirst($int_diff);
                        $insert_interests->insert();
                    }                  
                }
                
                $int_list = implode(",",$int);
                if (!empty($record_personal)) 
				{ 
                    $record_personal->interests = ucfirst($int_list);
                    $record_personal->update();
                    $data[] = $record_personal->interests;
                } 
				else 
				{
                    $insert_personal = new Personalinfo();
                    $insert_personal->user_id = $user_id;
                    $insert_personal->interests = ucfirst($int_list);
                    $insert_personal->insert();
                    $data[] = $insert_personal->interests;
                }
            }

            if (isset($_POST['occupation']))
			{

                $record_occupation = ArrayHelper::map(Occupation::find()->all(), 'name', 'name');
                $ocu =$_POST['occupation'];
                if(empty($ocu) || $ocu == '') {
					$ocu = array();
				}
				
				$occupation_differance=array_diff($ocu,$record_occupation);
                
                if(isset($occupation_differance) && !empty($occupation_differance))
				{
                    $insert_occupation = new Occupation();
                    foreach($occupation_differance AS $ocu_diff)                  
                    {
                        $insert_occupation->name = ucfirst($ocu_diff);
                        $insert_occupation->insert();
                    }                  
                }
                
                $ocu_list = implode(",",$ocu);
                if (!empty($record_personal)) 
				{
					$record_personal->occupation = ucfirst($ocu_list);
                    $record_personal->update();

                    $data[] = $record_personal->occupation;
			    } 
				else 
				{
                    $insert_personal = new Personalinfo();
                    $insert_personal->user_id = $user_id;
                    $insert_personal->occupation = ucfirst($ocu_list);
                    $insert_personal->insert();
                    $data[] = $insert_personal->occupation;
                }
            }

            return json_encode($data, true);

           
        }
		else 
		{
            $session = Yii::$app->session;
            $uid = (string)$session->get('user_id');
            $model = new \frontend\models\LoginForm();
            if ($session->get('email'))
            {

                // START get connect list with (id, fb_id, thumb).
                $usrfrd = Connect::getuserConnections($uid);
                $usrfrdlist = array();
                foreach($usrfrd AS $ud)
                {
                    if(isset($ud['userdata']['fullname']) && $ud['userdata']['fullname'] != '') {
                        $id = (string)$ud['userdata']['_id'];
                        $fbid = isset($ud['userdata']['fb_id']) ? $ud['userdata']['fb_id'] : '';
                        $dp = $this->getimage($ud['userdata']['_id'],'thumb');

                        $nm = $ud['userdata']['fullname'];
                        $usrfrdlist[] = array('id' => $id, 'fbid' => $fbid, 'name' => $nm, 'text' => $nm, 'thumb' => $dp);
                    }
                }

                return $this->render('accountsettings',['model' => $model,'model1' => $model1,'model2' => $model2,'usrfrdlist' => $usrfrdlist]);
            }
            else
            {
                return $this->goHome();
            }

		}
    }

    public function actionAccountsettingsecuritysettings() {
        $session = Yii::$app->session;
        $user_id = (string) $session->get('user_id');
		$security = SecuritySetting::find()->where(['user_id' => $user_id])->one();


		$view_photos_custom = isset($_POST['view_photos_custom']) ? $_POST['view_photos_custom'] : array() ;
		$my_post_view_status_custom = isset($_POST['my_post_view_status_custom']) ? $_POST['my_post_view_status_custom'] : array() ;
		$add_public_wall_custom = isset($_POST['add_public_wall_custom']) ? $_POST['add_public_wall_custom'] : array() ;
		$add_post_on_your_wall_view_custom = isset($_POST['add_post_on_your_wall_view_custom']) ? $_POST['add_post_on_your_wall_view_custom'] : array() ;
		
		if(!empty($security)) {
            
	        if(isset($_POST['security_questions'])) {
				$security->security_questions = $_POST['security_questions'];
				if($_POST['security_questions'] == 'eml_ans') {
					$security->eml_ans = $_POST['securityanswer']; 
				} else if($_POST['security_questions'] == 'born_ans') {
					$security->born_ans = $_POST['securityanswer'];
				} else if($_POST['security_questions'] == "gf_ans") {
					$security->gf_ans = $_POST['securityanswer'];
				}
			}

	        if(isset($_POST['my_view_status'])) {
			     $security->my_view_status = $_POST['my_view_status'];
	        }
	        if(isset($_POST['my_post_view_status'])) {
                $my_post_view_status = $_POST['my_post_view_status'];
                if($my_post_view_status == 'Custom') {
                    $my_post_view_status_custom = $_POST['my_post_view_status_custom'];
                    $my_post_view_status_custom = implode(",", $my_post_view_status_custom);
                    $security->my_post_view_status_custom = $my_post_view_status_custom;
                } else {
                    $security->my_post_view_status_custom = '';
                }
                $security->my_post_view_status = $my_post_view_status;
	        }
	        if(isset($_POST['connect_request'])) {
				 $security->connect_request = $_POST['connect_request'];
	        }
	        if(isset($_POST['add_public_wall'])) {
           		$add_public_wall = $_POST['add_public_wall'];
                if($add_public_wall == 'Custom') {
                    $add_public_wall_custom = $_POST['add_public_wall_custom'];
                    $add_public_wall_custom = implode(",", $add_public_wall_custom);
                    $security->add_public_wall_custom = $add_public_wall_custom;
                } else {
                    $security->add_public_wall_custom = '';
                }
                $security->add_public_wall = $add_public_wall;
	        }
	        if(isset($_POST['view_photos'])) {
                $view_photos = $_POST['view_photos'];
                if($view_photos == 'Custom') {
                    $view_photos_custom = $_POST['view_photos_custom'];
                    $view_photos_custom = implode(",", $view_photos_custom);
                    $security->view_photos_custom = $view_photos_custom;
                } else {
                    $security->view_photos_custom = '';
                }
                $security->view_photos = $view_photos;
	        }
	        if(isset($_POST['review_posts'])) {
				 $security->review_posts = $_POST['review_posts'];
	        }
	        if(isset($_POST['review_tags'])) {
				 $security->review_tags = $_POST['review_tags'];
	        }
	        if(isset($_POST['connect_list'])) {
				 $security->connect_list = $_POST['connect_list'];
	        }
	        if(isset($_POST['add_post_on_your_wall_view'])) {
            	$add_post_on_your_wall_view = $_POST['add_post_on_your_wall_view'];
                if($add_post_on_your_wall_view == 'Custom') {
                    $add_post_on_your_wall_view_custom = $_POST['add_post_on_your_wall_view_custom'];
                    $add_post_on_your_wall_view_custom = implode(",", $add_post_on_your_wall_view_custom);
                    $security->add_post_on_your_wall_view_custom = $add_post_on_your_wall_view_custom;
                } else {
                    $security->add_post_on_your_wall_view_custom = '';
                }
                $security->add_post_on_your_wall_view = $add_post_on_your_wall_view;
	        }

            $security->update();

            $result = array('success' => true);
            return json_encode($result, true);
	    }
	    
	    $result = array('success' => false);
        return json_encode($result, true);
    }

    public function actionBlockingsave() {
    	$session = Yii::$app->session;
        $email = $session->get('email'); 
        $user_id = (string) $session->get('user_id');
        
        if(isset($_POST) && !empty($_POST)) {
        	$restricted_list_label = isset($_POST['restricted_list_label']) ? $_POST['restricted_list_label'] : array();
        	$restricted_list_label = implode(',', $restricted_list_label);

        	$blocked_list_label = isset($_POST['blocked_list_label']) ? $_POST['blocked_list_label'] : array();
        	$blocked_list_label = implode(',', $blocked_list_label);

        	$message_filter_label = isset($_POST['message_filter_label']) ? $_POST['message_filter_label'] : array();
        	$message_filter_label = implode(',', $message_filter_label);

        	$request_filter_label = isset($_POST['request_filter_label']) ? $_POST['request_filter_label'] : array();
        	$request_filter_label = implode(',', $request_filter_label);
        	$security = SecuritySetting::find()->where(['user_id' => $user_id])->one();
	        if(!empty($security)) {
		        $security->restricted_list = $restricted_list_label; 
	            $security->blocked_list = $blocked_list_label;
	            $security->message_filtering = $message_filter_label;
	            $security->request_filter = $request_filter_label;
	            $security->update();

	            $result = array('success' => true);
	            return json_encode($result, true);
		    }
		}

		$result = array('success' => false);
	    return json_encode($result, true);
    }

    public function actionAccountsettingbasicinformation() 
	{
		
		$session = Yii::$app->session;
	    $user_id = (string) $session->get('user_id');
	    $sessionemail = $session->get('email');

		if (isset($_POST) && !empty($_POST)) {
			$fname = $_POST['fname'];
			$lname = $_POST['lname'];
			$email = $_POST['email'];
			$alternate_email = $_POST['alternate_email'];
			$city = $_POST['city'];
			$country = $_POST['country'];
			$isd_code = $_POST['isd_code'];
			$phone = $_POST['phone'];
			$about = $_POST['about'];
			$language = isset($_POST['language']) ? $_POST['language'] : '';
			$interests = isset($_POST['interests']) ? $_POST['interests'] : '';
			$education = isset($_POST['education']) ? $_POST['education'] : '';
			$occupation = isset($_POST['occupations']) ? $_POST['occupations'] : '';
			$errormsg = '';
			$isError = false;
			$onlyAllowAlpha = '/^[a-zA-Z\s]+$/';
			$emailregex = '/^([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+/';
			$phoneregex = '/([0-9\s\-]{1,})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/';
			$callback = array('success' => true);

			if($fname == "") {
				$errormsg = 'Enter first name.';
				$isError = true;
			} else if(strlen($fname) < 2) {
				$errormsg = 'Minimum 2 characters allowed.';
				$isError = true;
			} else if(!preg_match($onlyAllowAlpha, $fname)) {
				$errormsg = 'Characters only allowed.';
				$isError = true;
			} else if($lname == "") {
				$errormsg = 'Enter last name.';
				$isError = true;
			} else if(strlen($lname) < 2) {
				$errormsg = 'Minimum 2 characters allowed.';
				$isError = true;
			} else if(!preg_match($onlyAllowAlpha, $lname)) {
				$errormsg = 'Characters only allowed';
				$isError = true;
			} else if($email =="") {
				$errormsg = 'Enter Email.';
				$isError = true;
			} else if(!preg_match($emailregex, $email)) {
				$errormsg = 'Enter valid email.';
				$isError = true;
			} else if($alternate_email =="") {
				$errormsg = 'Enter alternate email.';
				$isError = true;
			} else if(!preg_match($emailregex, $alternate_email)) {
				$errormsg = 'Enter valid alternate email.';
				$isError = true;
			} else if($city =="") {
				$errormsg = 'Enter city.';
				$isError = true;
			} else if($country =="") {
				$errormsg = 'Enter country.';
				$isError = true;
			} else if($isd_code =="") {
				$errormsg = 'Enter isd code.';
				$isError = true;
			} else if($phone =="") {
				$errormsg = 'Enter phone number.';
				$isError = true;
			} else if(!preg_match($phoneregex, $phone)) {
				$errormsg = 'Enter valid phone number.';
				$isError = true;
			} else if($about =="") {
				$errormsg = 'Enter about.';
				$isError = true;
			}

			if($isError) {
				$callback = array('success' => false, 'msg' => $errormsg);
				return json_encode($callback, true);
				die;
			} else {
				
				if($email != $sessionemail) {
					$isEmailexist = LoginForm::find()->where(['email' => $email])->count();
					if($isEmailexist>0) {
						$errormsg = 'EmailId is already exist.';
						$isError = true;		
						$callback = array('success' => false, 'msg' => $errormsg);
					}
				}

			    $record_user = LoginForm::find()->where(['_id' => $user_id])->one();
			    $record_user->fname = $fname;
	            $record_user->lname = $lname;
	            $record_user->fullname = $fname . " " . $lname;
	            $record_user->email = $email;
	            $record_user->alternate_email = $alternate_email;
				$record_user->phone = $phone;
	        	$record_user->country = $country;
			    $record_user->isd_code = $isd_code;
	            //$record_user->country_code = $country_code;
				$record_user->city = $city;
		        
	            $GApiKeyL = $GApiKeyP = Googlekey::getkey();

				$prepAddr = str_replace(' ','+',$city);
				$prepAddr = str_replace("'",'',$prepAddr);
				$geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?key='.$GApiKeyL.'&address='.$prepAddr.'&sensor=false');
	            $output = json_decode($geocode, true);
	            if($output['status']=='OK') {
	            	$latitude = isset($output['results'][0]['geometry']['location']['lat']) ? $output['results'][0]['geometry']['location']['lat'] : "";
			        $longitude = isset($output['results'][0]['geometry']['location']['lng']) ? $output['results'][0]['geometry']['location']['lng'] : "";

		            //$latitude = $output->results[0]->geometry->location->lat;
		            //$longitude = $output->results[0]->geometry->location->lng;
				    $record_user->citylat = (string)$latitude;
		            $record_user->citylong = (string)$longitude;
		        }
		        
		        $record_user->update();

			    $record_personal = Personalinfo::find()->where(['user_id' => $user_id])->one();
				$record_personal->about = $about;
	        
	            if(empty($language)) {
	            	$record_personal->language = '';
	            } else {
					$record_language = ArrayHelper::map(Language::find()->all(), 'name', 'name');
					$ans=array_diff($language,$record_language);
		            if(isset($ans) && !empty($ans)) {
		                $insert_language = new Language();
		                foreach($ans AS $language_diff) {
		                    $insert_language->name = ucfirst($language_diff);
		                    $insert_language->insert();
		                }                  
		            }
		            $lang_list = implode(",",$language);
		            $record_personal->language = ucfirst($lang_list);
	            }

	            if(empty($education)) {
	            	$record_personal->education = '';
	            } else {
		            $record_education = ArrayHelper::map(Education::find()->all(), 'name', 'name');
		            $edu_differance=array_diff($education,$record_education);
		            if(isset($edu_differance) && !empty($edu_differance)) {
		                $insert_education = new Education();
		                foreach($edu_differance AS $edu_diff) {
		                    $insert_education->name = ucfirst($edu_diff);
		                    $insert_education->insert();
		                }                  
		            }
		            $edu_list =implode(",",$education);
		            $record_personal->education =ucfirst($edu_list);
	            }

	            if(empty($interests)) {
	            	$record_personal->interests = '';
	            } else {
		            $record_interests = ArrayHelper::map(Interests::find()->all(), 'name', 'name');
		           
		            $interests_differance=array_diff($interests,$record_interests);
		            if(isset($interests_differance) && !empty($interests_differance)) {
		                $insert_interests = new Interests();
		                foreach($interests_differance AS $int_diff) {
		                    $insert_interests->name = ucfirst($int_diff);
		                    $insert_interests->insert();
		                }                  
		            }
		            $int_list = implode(",",$interests);
		            $record_personal->interests = ucfirst($int_list);
	            }

	            if(empty($occupation)) {
	            	$record_personal->occupation = '';
	            } else {
		            $record_occupation = ArrayHelper::map(Occupation::find()->all(), 'name', 'name');
		            $occupation_differance=array_diff($occupation,$record_occupation);
		            if(isset($occupation_differance) && !empty($occupation_differance)) {
		                $insert_occupation = new Occupation();
		                foreach($occupation_differance AS $ocu_diff) {
		                    $insert_occupation->name = ucfirst($ocu_diff);
		                    $insert_occupation->insert();
		                }                  
		            }
		            
		            $ocu_list = implode(",",$occupation);
					$record_personal->occupation = ucfirst($ocu_list);
	            }

	            $record_personal->update();

            	$callback = array('success' => true);
				return json_encode($callback, true);
				die;
        	}
	    }

	    $callback = array('success' => false);
		return json_encode($callback, true);
		die;
	}
    
	public function actionAccountsettings2() 
	{
		$model = new \frontend\models\UserSetting();
        $model1 = new \frontend\models\LoginForm();
        $model2 = new \frontend\models\Personalinfo();
		$session = Yii::$app->session;
		$email = $session->get('email');
		$id = LoginForm::getLastInsertedRecord($email);
        $user_id = (string) $id['_id'];
		
		$record_user = LoginForm::find()->where(['_id' => $user_id])->one(); 
        $record_personal = Personalinfo::find()->where(['user_id' => $user_id])->one();
        $record_setting = UserSetting::find()->where(['user_id' => $user_id])->one();
    	
    	$about = '';    
    	$walloccupation = '';    
    	$wallinterests = '';    
    	$walllanguage = '';    
    	$walleducation = '';    
    	$livesin = '';    
    	$country = '';    
    	$au_amazing = '';    
    	$au_visited = '';    
    	$au_livedin = '';    
    	$country_code = '';    
    	$birth_date = '';    
    	$gender = '';    
    	$birth_date_privacy = '';    
    	$birth_date_privacy_custom = '';    
    	$gender_privacy = '';    
    	$gender_privacy_custom = '';    
    	$latitude = '';    
    	$longitude = '';   
    	$au_visited_str = ''; 
    	$au_livedin_str = ''; 
    	$au_visited_data = ''; 
    	$au_livedin_data = ''; 
    	$int_html = '';

		if(isset($_POST['about']) && $_POST['about'] != 'undefined' && $_POST['about'] != 'null' && $_POST['about'] != '') {
			$about = $_POST['about'];
		}

		if(isset($_POST['walloccupation'])) {
			$ocu = $_POST['walloccupation'];
			if(empty($ocu) || $ocu == '') {
				$ocu = array();
			}
			$ocu = array_filter($ocu);
			$walloccupation = implode(',', $ocu);

			/* Start For Inserting New interests in occupation*/
			$record_occupation = ArrayHelper::map(Occupation::find()->all(), 'name', 'name');
		    $occupation_differance=array_diff($ocu,$record_occupation);
			
			if(isset($occupation_differance) && !empty($occupation_differance))
			{
				$insert_occupation = new Occupation();
				foreach($occupation_differance AS $ocu_diff)                  
				{
					$insert_occupation->name = ucfirst($ocu_diff);
					$insert_occupation->insert();
				}                  
			}
			/* End For Inserting New interests in occupation*/
		}
		if(isset($_POST['wallinterests'])) {
			$int = $_POST['wallinterests'];
			if(empty($int) || $int == '') {
				$int = array();

			}
			$int = array_filter($int);
			foreach ($int as $createbox) {
				$int_html .= "<span class='inline-obj tagSpan'>$createbox</span>";
			}
			$wallinterests = implode(',', $int);

			
			/* Start For Inserting New interests in Education*/
			$record_interests = ArrayHelper::map(Interests::find()->all(), 'name', 'name');
			$interests_differance=array_diff($int,$record_interests);
			
			if(isset($interests_differance) && !empty($interests_differance)){
				$insert_interests = new Interests();
				foreach($interests_differance AS $int_diff)                  
				{
					$insert_interests->name = ucfirst($int_diff);
					$insert_interests->insert();
				}                  
			}
			/* End For Inserting New interests in Education*/
		}
		if(isset($_POST['walllanguage'])) {
			$lang = $_POST['walllanguage'];
			if(empty($lang) || $lang == '') {
				$lang = array();
			}
			$lang = array_filter($lang);
			$walllanguage = implode(',', $lang);

			/* Start For Inserting New Language in Language*/
			$record_language = ArrayHelper::map(Language::find()->all(), 'name', 'name');	 
			$ans=array_diff($lang,$record_language);
			
			if(isset($ans) && !empty($ans))
			{
				$insert_language = new Language();
				foreach($ans AS $language_diff)                  
				{
					$insert_language->name = ucfirst($language_diff);
					$insert_language->insert();
				}                  
			}
			/* End For Inserting New Language in Language*/
		}
		if(isset($_POST['walleducation'])) {
			$edu = $_POST['walleducation'];
			if(empty($edu) || $edu == '') {
				$edu = array();
			}
			$edu = array_filter($edu);
			$walleducation = implode(',', $edu);

			/* Start For Inserting New Language in Education*/
			$record_education = ArrayHelper::map(Education::find()->all(), 'name', 'name');
			$edu_differance=array_diff($edu,$record_education);

			if(isset($edu_differance) && !empty($edu_differance)) {
				$insert_education = new Education();
				foreach($edu_differance AS $edu_diff) {
					$insert_education->name = ucfirst($edu_diff);
					$insert_education->insert();
				}                  
			}
		    /* End For Inserting New Language in Education*/
			
		}
		if(isset($_POST['livesin']) && $_POST['livesin'] != 'undefined' && $_POST['livesin'] != 'null' && $_POST['livesin'] != '') {
			$livesin = $_POST['livesin'];
		}
		if(isset($_POST['country']) && $_POST['country'] != 'undefined' && $_POST['country'] != 'null' && $_POST['country'] != '') {
			$country = $_POST['country'];
		}
		if(isset($_POST['au_amazing']) && $_POST['au_amazing'] != 'undefined' && $_POST['au_amazing'] != 'null' && $_POST['au_amazing'] != '') {
			$au_amazing = $_POST['au_amazing'];
		}
		if(isset($_POST['au_visited']) && $_POST['au_visited'] != 'undefined' && $_POST['au_visited'] != 'null' && !empty($_POST['au_visited'])) {
			$au_visited = array_filter($_POST['au_visited']);
			if(!empty($au_visited)) {
				foreach ($au_visited as $au_visiteds) {
					$au_visited_str .= '<div class="chip">'.$au_visiteds.'</div>';
					$au_visited_data .= $au_visiteds.'@@@';
				}
			}
		}
		if(isset($_POST['au_livedin']) && $_POST['au_livedin'] != 'undefined' && $_POST['au_livedin'] != 'null' && !empty($_POST['au_livedin'])) {
			$au_livedin = array_filter($_POST['au_livedin']);
			if(!empty($au_livedin)) {
				foreach ($au_livedin as $au_livedins) {
					$au_livedin_str .= '<div class="chip">'.$au_livedins.'</div>';
					$au_livedin_data .= $au_livedins.'@@@';
 				}
			}
		}
		if(isset($_POST['country_code']) && $_POST['country_code'] != 'undefined' && $_POST['country_code'] != 'null' && $_POST['country_code'] != '') {
			$country_code = $_POST['country_code'];
		}
		if(isset($_POST['birth_date']) && $_POST['birth_date'] != 'undefined' && $_POST['birth_date'] != 'null' && $_POST['birth_date'] != '') {
			$birth_date = $_POST['birth_date'];
		}
		if(isset($_POST['gender']) && $_POST['gender'] != 'undefined' && $_POST['gender'] != 'null' && $_POST['gender'] != '') {
			$gender = $_POST['gender'];
		}
		if(isset($_POST['birth_date_privacy']) && $_POST['birth_date_privacy'] != 'undefined' && $_POST['birth_date_privacy'] != 'null' && $_POST['birth_date_privacy'] != '') {
			$birth_date_privacy = $_POST['birth_date_privacy'];
			if($birth_date_privacy == 'Custom') {
				$birth_date_privacy_custom = implode(",", $_POST['birth_date_privacy_custom']);
			}
		}
		if(isset($_POST['gender_privacy']) && $_POST['gender_privacy'] != 'undefined' && $_POST['gender_privacy'] != 'null' && $_POST['gender_privacy'] != '') {
			$gender_privacy = $_POST['gender_privacy'];
			if($gender_privacy == 'Custom') {
				$gender_privacy_custom = implode(",", $_POST['gender_privacy_custom']);
			}
		}
		
		if($livesin != '') {
			/*$prepAddr = str_replace(' ','+',$livesin);
			$prepAddr = str_replace("'",'',$prepAddr);
			$geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false');
			$output = json_decode($geocode);
			$latitude = $output->results[0]->geometry->location->lat;
			$longitude = $output->results[0]->geometry->location->lng;*/
		}

		if (!empty($record_user)) {
			$record_user->citylat = "$latitude";
			$record_user->citylong = "$longitude";
			$record_user->city = $livesin;
			$record_user->country = $country;
			$record_user->country_code = $country_code;
			$record_user->birth_date = $birth_date;
			$record_user->birth_date_privacy = $birth_date_privacy;
			$record_user->birth_date_privacy_custom = $birth_date_privacy_custom;
			$record_user->gender = $gender;
			$record_user->gender_privacy = $gender_privacy;
			$record_user->gender_privacy_custom = $gender_privacy_custom;
			$record_user->update();
		}

		if (!empty($record_personal)) 
		{
			$record_personal->about = $about;
			$record_personal->occupation = ucfirst($walloccupation);
			$record_personal->interests = ucfirst($wallinterests);
			$record_personal->language = ucfirst($walllanguage);
			$record_personal->education = ucfirst($walleducation);
			$record_personal->amazing_things = ucfirst($au_amazing);
			$record_personal->visited_countries = ucfirst($au_visited_data);
			$record_personal->lived_countries = ucfirst($au_livedin_data);
			$record_personal->update();
		}
		else
		{
			$record_personal = new Personalinfo();
			$record_personal->user_id = $user_id;
			$record_personal->about = $about;
			$record_personal->occupation = ucfirst($walloccupation);
			$record_personal->interests = ucfirst($wallinterests);
			$record_personal->language = ucfirst($walllanguage);
			$record_personal->education = ucfirst($walleducation);	
			$record_personal->amazing_things = ucfirst($au_amazing);
			$record_personal->visited_countries = ucfirst($au_visited_data);
			$record_personal->lived_countries = ucfirst($au_livedin_data);				
			$record_personal->insert();
		}                                                   

		$data['about'] = $record_personal['about'];
		$data['occupation'] = $record_personal['occupation'];
		$data['interests'] = $record_personal['interests'];
		$data['language'] = $record_personal['language'];
		$data['education'] = $record_personal['education'];
		$data['amazing_things'] = $record_personal['amazing_things'];
		$data['visited_countries'] = $au_visited_str;
		$data['livesin'] = $au_livedin_str;
		$data['city'] = $record_user['city'];
		$data['country'] = $record_user['country'];
		$data['country_code'] = $record_user['country_code'];
		$data['birth_date'] = $record_user['birth_date'];
		$data['gender'] = $record_user['gender'];
		$data['birth_date_privacy'] = $record_user['birth_date_privacy'];
		$data['gender_privacy'] = $record_user['gender_privacy'];
		$data['interests_html'] = $int_html;
		return json_encode($data);
		exit;
	}	
	
    public function actionUserCover()
    {
        $session = Yii::$app->session;
        $email = $session->get('email');
        $user_id = (string) $session->get('user_id');
        if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
        {
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $user_id = $url[1];
        }
        $result = LoginForm::find()->select(['_id'])->where(['_id' => $user_id])->one();
        if($result)
        {
            $cover = LoginForm::find()->where(['_id' => $user_id])->one();
            $cover->cover_photo = str_replace('-thumb','',$_POST['covername']);
            if($cover->update())
            {
                return '1';
            }
        }
    }
	
    public function actionCover()
    {
		$model = new \frontend\models\Cover();
        
	if (isset($_FILES) && !empty($_FILES)) 
	{
		$imgcount = count($_FILES["Cover"]["name"]['cover_image']);
        $load = isset($_POST["load"]) ? $_POST["load"] : '1';
        $date =  time();
            $img = '';
            $cover = new Cover();
            $newData = array();
            for ($i =0;$i < $imgcount;$i++) 
			{
				$name = $_FILES["Cover"]["name"]['cover_image'][$i];
                if($name != '') 
				{
                    $tmp_name = $_FILES["Cover"]["tmp_name"]['cover_image'][$i];
					if (isset($name) && $name != "") 
					{
						$url = '../web/uploads/cover/';
						$urls = '/uploads/cover/';
						move_uploaded_file($tmp_name, $url . $date . $name);
						$img = $date . $name;
                     } 
                    
					$cover->cover_image = $img;
					$cover->insert();
					if($load == 2) 
					{
                        $newData[] = $img;
                    }
                }
            }
           
            if($load == 1) 
			{
                $all_cover = ArrayHelper::map(Cover::find()->all(), 'cover_image', 'cover_image');
             
                $all_cover['status'] = true;
                $newData = json_encode($all_cover);
            }
            
            if($load == 2) 
			{
                $newData = json_encode($newData);
            }
             return $newData;
             exit;
        }
       
        return $this->render('cover',['model' => $model,]);
      }

    public function actionProfilePicture()
    {
       $model = new \frontend\models\LoginForm();
       $post_model = new \frontend\models\PostForm();
       $model->scenario = 'profile_picture';

       if ($model->load(Yii::$app->request->post())) 
	   {
			$session = Yii::$app->session;
		   $email = $session->get('email'); 
		   $profile_picture = $_FILES['LoginForm']['name']['photo'];

		   if(isset($_POST['web_cam_img']) && !empty($_POST['web_cam_img']))
		   {
				$test = $_POST['web_cam_img'];
				$rest = array_pop(explode('/', $test));

				$record = LoginForm::find()->select(['_id'])->where(['email' => $email])->one();

				$record->photo = $rest;
				$record->thumbnail = $rest;
				$record->update();

				$url = Yii::$app->urlManager->createUrl(['site/profile-picture']);
				Yii::$app->getResponse()->redirect($url); 
			}
			else
			{
				if($model->validate())
				{
					$chars = '0123456789';
					$count = mb_strlen($chars);
					for ($i = 0, $rand = ''; $i < 8; $i++) 
					{
						$index = rand(0, $count - 1);
						$rand .= mb_substr($chars, $index, 1);
					}

					$profile_picture = $rand . $profile_picture;
					$record = LoginForm::find()->select(['_id'])->where(['email' => $email])->one();
					
					$model->photo = UploadedFile::getInstance($model, 'photo');
					$model->photo->saveAs('profile/'.$profile_picture);    
					
					$data = str_replace('data:image/png;base64,', '', $_POST['imagevalue']);
					$data = str_replace(' ', '+', $data);
					$data = base64_decode($data);
					$file = 'profile/thumb_'.$profile_picture;
					$success = file_put_contents($file, $data);

					$record->thumbnail = 'thumb_'.$profile_picture;
					$record->photo = $profile_picture;
					$record->update();
					
					$date = time();
					$post = new PostForm();
					$post->post_status = '1';
					$post->post_type = 'profilepic';
					$post->is_profilepic = '1';
					$post->is_deleted = '0';
					$post->post_privacy = 'Public';
					$post->image = 'thumb_'.$profile_picture;
					$post->post_created_date = "$date";
					$post->post_user_id = (string)$record['_id'];
					$post->insert();

					$url = Yii::$app->urlManager->createUrl(['site/profile-picture']);
					Yii::$app->getResponse()->redirect($url); 
				}
				else
				{
					return $this->render('profile_picture', ['model' => $model,]);
				}
		   }
       }
       else
       {
		   return $this->render('profile_picture', ['model' => $model,]);
       }
    }
    
    /* security Settings by markand */

    public function actionSecuritySetting() 
	{
       $model = new \frontend\models\SecuritySetting(); 
        if (Yii::$app->request->post()) 
		{  
            return $model->security(); 
        } 
		else 
		{
            return $this->render('basicinfo',['model' => $model,]);
        }
    }

    /* Blocking Settings by markand */

    public function actionBlocking() 
	{
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		
		if(isset($user_id) && $user_id != '') {
			if(isset($_POST)) { 
	            return SecuritySetting::blocking1();
	        } else {
	            return $this->render('blocking',['model' => $model]);
	        }
	    } 
    }

    public function actionNotificationSetting() 
	{
        $model = new \frontend\models\NotificationSetting();
        if (isset($_POST) & !empty($_POST) && $model->notification()) {
            return $model->notification();
        } 
		else 
		{
            return $this->render('notification_setting',['model' => $model,]);
        }
    }

    public function actionCurl_fetch() 
	{
        $session =  Yii::$app->session;
        $user_id = (string) $session->get('user_id');
        $data = array();
        $url = trim($_POST['url']);

        $tags = get_meta_tags($url);
		
        if (isset($tags['title']) && !empty($tags['title'])) 
		{
            $data['title'] = $tags['title'];
            $title = $tags['title'];
        } 
		else 
		{
            function getBetween($url,$start,$end) 
			{
                $r = explode($start,$url);
                if(isset($r[1])) 
				{
                    $r = explode($end,$r[1]);
                    return $r[0];
                }
                return '';
            }

            $start = "www.";
            $end = ".";
            $title =getBetween($url,$start,$end);
            $data['title'] = $title;
            
            if($data['title'] == '')
			{
                $result = parse_url($url);
                $data['title'] =  $result['host'];
            }
        }
        if (isset($tags['description']) && !empty($tags['description'])) 
		{
            $data['desc'] = $tags['description'];
            $description = $tags['description'];
        } 
		else 
		{
            $data['desc'] = 'Description';
            $description = 'Desc';
        }
        if (isset($tags['twitter:image']) && !empty($tags['twitter:image'])) 
		{
            $data['image'] = $tags['twitter:image'];
            $image = $tags['twitter:image'];
        } 
		else
		{
            $data['image'] = "No Image";
            $image = 'default.png';
        }
        $data['url'] = $url;
        return json_encode($data);
    }

    public function actionPhone() {

        $model = new LoginForm();
        $session =  Yii::$app->session;
        $email =  $session->get('email_id');

        if (!empty($_POST["phone"])) 
		{
            $phone = $_POST["phone"];
            $result = LoginForm::find()->select(['_id'])->where(['phone' => $phone])->one();
            $count = count($result);

            if ($count > 0) 
			{	
                $query = LoginForm::find()->where(['phone' => $phone,'email' => $email])->one();
                if (!empty($query)) 
				{
                    return "1";
                }
				else 
				{
                    return "0";
                } 
            }
			else 
			{
                return "1";  
            }
        }
    }
    
    public function actionPassword() 
	{
		$model = new LoginForm();
		$session = Yii::$app->session;
		$email = $session->get('email');
		$result = LoginForm::find()->select(['fname','password'])->where(['email' => $email])->one();
		$password = $result['password'];
		
		$data = array();
		$data[] = $password;
		try 
		{
			$test =
			Yii::$app->mailer->compose()
			->setFrom(array('csupport@iaminjapan.com' => 'iaminjapan Security Team'))
			->setTo($email)
			->setSubject('iaminjapan- Password Change Notification')
			->setHtmlBody('<html><head><meta charset="utf-8" /><title>I am in Japan</title></head><body style="margin:0;padding:0;background:#dfdfdf;"><div style="color: #353535; float:left; font-size: 13px;width:100%; font-family:Arial, Helvetica, sans-serif;text-align:center;padding:40px 0 0;"><div style="width:600px;display:inline-block;"> <img src="https://iaminjapan.com/frontend/web/images/black-logo.png" style="margin:0 0 10px;width:130px;float:left;"/><div style="clear:both"></div><div style="border:1px solid #ddd;margin:0 0 10px;"><div style="background:#fff;padding:20px;border-top:10px solid #333;text-align:left;"> <div style="color: #333;font-size: 13px;margin: 0 0 20px;">Hi '.$result['fname'].'</div><div style="color: #333;font-size: 13px;margin: 0 0 20px;">Your PassWord is Changed Successfully</div><div style="color: #333;font-size: 13px;margin: 0 0 20px;"></div><div style="color: #333;font-size: 13px;">Thank you for using iaminjapan!</div><div style="color: #333;font-size: 13px;">The iaminjapan Team</div></div></div><div style="clear:both"></div><div style="width:600px;display:inline-block;font-size:11px;"><div style="color: #777;text-align: left;">&copy;  www.iaminjapan.com All rights reserved.</div><div style="text-align: left;width: 100%;margin:5px  0 0;color:#777;">For support, you can reach us directly at <a href="csupport@iaminjapan.com" style="color:#4083BF">csupport@iaminjapan.com</a></div></div></div></div></body></html>') 
			->send();    
		} 
		catch (ErrorException $e)
		{
			return $e->getMessage();
		}

		return json_encode($data); 
	}

    public function actionCheckLogin()
    {
		if (!empty($_POST["lemail"]) && !empty($_POST["lpassword"])) 
		{
            $session = Yii::$app->session;
            $email = $session->set('email_id',strtolower($_POST['lemail']));
			 
			$data = array();
			$lemail = strtolower($_POST["lemail"]);
			$lpassword = $_POST["lpassword"];
			$model = new LoginForm();
			$record = LoginForm::find()->where(['email' => $lemail, 'password' => $lpassword])->one();
			if ($record)
			{
				$querypass = $record;
				if ($querypass)
				{
					$querypassverify = $querypass;
					if ($querypassverify)
					{
						if($querypassverify['status'] == '3')
						{
							$data['value'] ='5';
						}
						else if($querypassverify['status'] == '4')
						{
							$data['value'] ='7';
						}
						else if($querypassverify['status'] == '10')
						{
							$data['value'] ='10';
						}
						else
						{
							$data['value'] ='4';
						}
						return json_encode($data);
					}
					else
					{
						$data['value'] ='5';
						return json_encode($data);
					}
				}
				else
				{
					$data['value'] ='3';
					return json_encode($data);
				}
			}
			else
			{
				$data['value'] ='2';
				return json_encode($data);
			}
		}
		else
		{
			return true;
		}
    }

    public function actionCheckEmail() 
	{
        if (isset($_POST["lemail"]) && !empty($_POST["lemail"])) 
		{
            $lemail = strtolower($_POST["lemail"]);
            $model = new LoginForm();
            $record = LoginForm::find()->select(['_id'])->where(['email' => $lemail])->one();
            if (!empty($record) && $record['status'] == '2') 
			{
                print false;
            } 
			else 
			{
                if ($record) 
				{
					print true;
                } 
				else
				{
                    print false;
                }
            }
        }
		else 
		{
            print false;
        }
    }
    
    public function actionCheckForgotEmail() 
	{
        if (isset($_POST["fpemail"]) && !empty($_POST["fpemail"]))
        {
            $lemail = strtolower($_POST["fpemail"]);
            $model = new LoginForm();
            $record = LoginForm::find()->select(['_id','status'])->where(['email' => $lemail])->one();
            if($record)
            {
                if($record['status'] != '1'){return 2;}
                else{return 1;}
            }
            else
            {
                return 0;
            }
        }
        else
        {
            return 0;
        }
    }

    public function actionGetemail() 
	{
        $model = new \frontend\models\LoginForm();
        $email = $_POST['search'];

        $eml_id = LoginForm::find()->where(['like','email',$email])->all();
        $listData = ArrayHelper::map($eml_id,'email','email');
   
        foreach ($listData as $email) 
		{
            return '<span class="display_box" align="left"><span style="font-size:9px; color:#999999">"' . $email . '"</span></span>';
        }
	}

    public function actionDeletePhoto()
    {
        $session = Yii::$app->session;
        $post_id = (string)$_POST['pid'];
        $uid = (string)$session->get('user_id');
		
		if(isset($uid) && $uid != '') {
			$authstatus = UserForm::isUserExistByUid($uid);
			if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
				$data['auth'] = $authstatus;
				return $authstatus;
			} 
			else {
				$deletepicture = PostForm::find()->select(['_id'])->where(['_id' => $post_id])->one();
				$deletepicture->is_deleted = '1';
				if($deletepicture->update())
				{
					return 1;
				}
				else
				{
					return 0;
				}
			}
		}
		else {
        	return 'checkuserauthclassg';
        }
    }

    public function actionDeletePost() 
	{
        return PostForm::DeletePostCleanUp($_POST['pid'],$_POST['post_user_id']);
    }
	
      public function actionDeletePostComment() 
	{
        if (!empty($_POST['comment_id']) && isset($_POST['comment_id'])) 
		{
            $comment_id = (string) $_POST["comment_id"];
            $session = Yii::$app->session;
            $user_id = (string) $session->get('user_id');
            $model = new Comment();
            $record = Comment::find()->select(['post_id'])->where(['_id' => $comment_id])->one();
            if ($record) {
				$record->delete();
				$totalcomment = Comment::find()->where(['post_id' => "$record->post_id",'status' => '1'])->count();
				$data['ctr'] = $totalcomment;
				$data['post_id'] = "$record->post_id";
				$data['comment_id'] = $comment_id;
				return json_encode($data);
            }
			else 
			{
                print false;
            }
        } 
		else 
		{
            print false;
        }
    }

    public function actionHidePostComment() 
	{
		$session = Yii::$app->session;
		$user_id = (string) $session->get('user_id');
		if(isset($user_id) && $user_id != '') {
		$authstatus = UserForm::isUserExistByUid($user_id);
		if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
			$data['auth'] = $authstatus;
			return $authstatus;
		} 
		else {
			
        if (isset($_POST['comment_id']) && !empty($_POST['comment_id'])) 
		{
            $comment_id = (string) $_POST["comment_id"];
            
            $hidecomment = new HideComment();
            $userexist = HideComment::find()->select(['comment_ids'])->where(['user_id' => $user_id])->one();
            if ($userexist) 
			{
                if (strstr($userexist['comment_ids'],$comment_id)) {
                    print true;
                } 
				else
				{
                    $hidecomment = HideComment::find()->select(['comment_ids'])->where(['user_id' => $user_id])->one();
                    $hidecomment->comment_ids = $userexist['comment_ids'] . ',' . $comment_id;
                    if ($hidecomment->update()) 
					{
                        print true;
                    }
					else 
					{
                        print false;
                    }
                }
            }
			else 
			{
                $hidecomment->user_id = $user_id;
                $hidecomment->comment_ids = $comment_id;
                if ($hidecomment->insert()) 
				{
                    print true;
                }
				else 
				{
                    print false;
                }
            }
        }
		}
		}
		else {
        	return 'checkuserauthclassg';
        }
    }

    public function actionSearch() 
	{
        $session = Yii::$app->session;
        $suserid = (string)$session->get('user_id');
        $model = new \frontend\models\LoginForm();
        if (isset($_GET['key']) && !empty($_GET['key'])) 
		{
            $email = $_GET['key'];
            $eml_id = LoginForm::find()->select(['_id','fname','lname','fullname','email','city','photo','gender'])
                    ->orwhere(['like','fname',$email])
                    ->orwhere(['like','lname',$email])
                    ->orwhere(['like','fullname',$email])
                    ->andwhere(['status'=>'1'])
                    ->orderBy([$email => SORT_ASC])
                    ->limit(7)
                    ->all();

            $json = array();

            $i = 0;
            if (!empty($eml_id)) 
			{ ?>
				<div class="sresult-list nice-scroll">
				<ul>
				<?php
					foreach ($eml_id as $val) 
					{
						$data = array();
						$data[] = $val->fname;
						$data[] = $val->email;
						$data[] = $val->lname;
						$data[] = $val->photo;
						$data[] = (string) $val->_id;
						$data[] = $val->gender;
						$guserid = (string)$val->_id;

						$block = BlockConnect::find()->where(['user_id' => $guserid])->andwhere(['like','block_ids',$suserid])->one();
						if(!$block)
						{
							$result_security = SecuritySetting::find()->where(['user_id' => $guserid])->one();
							if($result_security)
							{
								$lookup_settings = $result_security['my_view_status'];
							}
							else
							{
								$lookup_settings = 'Public';
							}
							$is_connect = Connect::find()->where(['from_id' => $guserid,'to_id' => $suserid,'status' => '1'])->one();
							if(($lookup_settings == 'Public') || ($lookup_settings == 'Connections' && ($is_connect || $guserid == $suserid)) || ($lookup_settings == 'Private' && $guserid == $suserid)) 
							{
								?>
								<li>
									<a href="index.php?r=userwall%2Findex&id=<?= $val->_id ?>" class="search-link">
										<span class="display-box">
											<span class="img-holder">
								<?php
									$dp = $this->getimage($val['_id'],'photo');
									if(empty($val->city)){$val->city = '&nbsp;';}
								?>
												<img src="<?= $dp?>" alt="">
											</span>
											<span class="desc-holder">
												<p style="color: black;"><?=$val->fname?>&nbsp;<?=$val->lname?></p>
												<span><?=$val->city?></span>
											</span>
										</span>
									</a>
								</li>
						<?php } 
						} 
					} ?>
				</ul>
				</div>
				<span class="more-sresult bshadow">
						<a href="<?= Url::to(['site/travconnections', 'name' => "$email"]); ?>">See More People
							<i class="mdi mdi-menu-right"></i>
						</a>
				</span>
				<script type="text/javascript">
					$('.search-result').removeClass('nopad');
				</script>
            <?php }
			else { ?>
            <div class="noresult"><p>Sorry, no result found</p></div>
			<script type="text/javascript">
				$('.noresult').parents('.search-result').addClass('nopad');
			</script>
            <?php
            }
        }
    }

    public function actionTravpeople() 
	{
        $session = Yii::$app->session;
        $uid =(string)$session->get('user_id');
        if ($uid) {
            $connections = Connect::userlist();
			$suggetion_requests = SuggestConnect::find()->where(['suggest_to' => "$uid"])->all();
			$pending_requests = Connect::connectPendingRequests();
            return $this->render('travpeople',array('connections' => $connections,'suggetion_requests' => $suggetion_requests, 'pending_requests'=>$pending_requests));
        } else {
            return $this->goHome();
        }
    }
    
    public function actionTravnotifications() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $model = new \frontend\models\LoginForm();
        if ($session->get('email'))
        { 
            $model_notification = new Notification();
            $notifications = $model_notification->getAllNotification();
            return $this->render('travnotifications', array('notifications' => $notifications));
        }
        else
        {
            return $this->goHome();
        }
    }

	public function actionAddvip() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $email = $session->get('email');
        if ($session->get('email'))
        {
			$model = new \backend\models\AddvipPlans;
			$vip_plans = $model->getVipPlans();
			if(count($vip_plans)==0)
			{
				$vipplans=array("10"=>"1","9"=>"3","8"=>"6","7"=>"12");
				foreach($vipplans as $x=>$x_value)
				{
				  $model->amount = (int)$x;
				  $model->months = (int)$x_value;
				  $model->insert();
				}
			}
			$vip_plans = $model->getVipPlans();
			if(isset($_GET['success']) && $_GET['success'] != '') {
				
				$success = $_GET['success'];
			return $this->render('joinvip',['vip_plans' =>$vip_plans, 'success' => $success]);
			} else {
			return $this->render('joinvip',['vip_plans' =>$vip_plans]);
				
			}
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionVipplans() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $email = $session->get('email');
        if ($session->get('email'))
        {
			$model = new \backend\models\AddvipPlans;
			$vip_plans = $model->getVipPlans();
			if(count($vip_plans)==0)
			{
				$vipplans=array("10"=>"1","9"=>"3","8"=>"6","7"=>"12");
				foreach($vipplans as $x=>$x_value)
				{
				  $model->amount = (int)$x;
				  $model->months = (int)$x_value;
				  $model->insert();
				}
			}
			$vip_plans = $model->getVipPlans();
			return $this->render('vipplans',['vip_plans' =>$vip_plans]);
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionCredits() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $email = $session->get('email');
        if ($session->get('email'))
        {
			$model = new AddcreditsPlans();
			$credits_plans = $model->getCreditsPlans();
			if(count($credits_plans)==0)
			{
				$creplans=array("500"=>"5","1150"=>"10","1800"=>"15","2500"=>"20","3250"=>"25");
				foreach($creplans as $x=>$x_value)
				{
					  $model->credits = (int)$x;
					  $model->amount = (int)$x_value;
					  $model->insert();
				}
			}
			$credits_plans = $model->getCreditsPlans();	
			return $this->render('credits',['credits_plans' =>$credits_plans]);
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionAddcredits() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $email = $session->get('email');
        if ($session->get('email'))
        {
			$model = new AddcreditsPlans();
			$credits_plans = $model->getCreditsPlans();
			if(count($credits_plans)==0)
			{
				$creplans=array("200"=>"5","500"=>"10","800"=>"15","1000"=>"20");
				foreach($creplans as $x=>$x_value)
				{
					$model->credits = (int)$x;
					$model->amount = (int)$x_value;
					$model->insert();
				}
			}
			$credits_plans = $model->getCreditsPlans();	
			return $this->render('creditsplans',['credits_plans' =>$credits_plans]);
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionCreditshistory() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
		
        $email = $session->get('email');
        if ($session->get('email'))
        {
			return $this->render('creditshistory');
        }
        else
        {
            return $this->goHome();
        }
    }
	
	/* Start Function For Transfer Credits Into Connect's Account*/ 
	
	public function actionTransfercredits() 
	{
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $email = $session->get('email');
        if ($session->get('email'))
        {
			if(isset($_POST['connect_name']) && !empty($_POST['connect_name']))
			{
				$uid = $_POST['connect_name'];
				$cre_amt = (integer) $_POST['amount'];
				$details = $user_id;
				$cre_desc = 'transfercredits';
				$status = '1';
				
				$totalcredits = Credits::usertotalcredits();
				$totalusercredits = (isset($totalcredits[0])) ? $totalcredits[0]['totalcredits'] : '0';
				
				if($totalusercredits >= $cre_amt)
				{ 
					/* Start Add Transferd Credits to Beneficiary's Account */
						$credit = new Credits();
						$credit = $credit->addcredits($uid,$cre_amt,$cre_desc,$status,$details);
					/* End Add Transferd Credits to Beneficiary's Account */
					
					/* Start Deduct Transferd Credits from Payer's Account */
						$credit2 = new Credits();
						$credit2 = $credit2->addcredits($details,-$cre_amt,$cre_desc,$status,$uid);
					/* End Deduct Transferd Credits from Payer's Account */
					
					/* Start Notification for Credits Below 100 */
					$remaining_credits = $totalusercredits - $cre_amt;
					if($remaining_credits <= 100)
					{
						$date = time();
						$notification =  new Notification();
						$notification->user_id = "$user_id";
						$notification->notification_type = 'low_credits';
						$notification->credits = $remaining_credits;
						$notification->is_deleted = '0';
						$notification->status = '1'; 
						$notification->created_date = "$date";
						$notification->updated_date = "$date";
						$notification->insert();
					}	
					
					/* End Notification for Credits Below 100 */
					
					$totalcredits = Credits::usertotalcredits();
					$total = (isset($totalcredits[0])) ? $totalcredits[0]['totalcredits'] : '0';
					
					$total_len = strlen($total);
					$total = str_split($total);
					
					$return = ''; 
					if($total_len <= 1)
					{
						$return.= '<span>0</span>';
						$return.= '<span>0</span>';
					}
					else if($total_len == 2)
					{
						$return.= '<span>0</span>';
					}
					for($i = 0; $i< $total_len; $i++)
					{
						 $return.= "<span>".$total[$i]."</span>";
					}
					
					return $return;
					exit;
				}
				else
				{
					return 'error';
				}
			}
			
			 // START get connect list with (id, fb_id, thumb).
            $usrfrd = Connect::getuserConnections($user_id);
            $usrfrdlist = array(); 
            foreach($usrfrd AS $ud) {
                if(isset($ud['userdata']['fullname']) && $ud['userdata']['fullname'] != '') {
                    $id = (string)$ud['userdata']['_id'];
                    $fbid = isset($ud['userdata']['fb_id']) ? $ud['userdata']['fb_id'] : '';
                    $dp = $this->getimage($ud['userdata']['_id'],'thumb');
                    $nm = $ud['userdata']['fullname'];
                    $usrfrdlist[] = array('id' => $id, 'fbid' => $fbid, 'name' => $nm, 'text' => $nm, 'thumb' => $dp);
                }
            }
			return $this->render('transfercredits', array('usrfrdlist' => $usrfrdlist));
        }
        else
        {
            return $this->goHome();
        }
    }
	/* Start Function For Finding Cridit Plan Amount*/
	
	public function actionCreditplanamount() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $email = $session->get('email');
		
		$id = $_POST['selected_credit_plan'];
		
		$credit_amount = AddcreditsPlans::find()->where(['_id' => "$id"])->one();
	    $amount = $credit_amount['amount'];
		return $amount;
	}
	
	/* Start Function For Adding New Entry In Verify */
	
	public function actionVerifyme() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $email = $session->get('email');
        if ($session->get('email'))
        {
			if(isset($_REQUEST['st']) && !empty($_REQUEST['st']))
			{
				if($_REQUEST['st'] == "Completed" || $_REQUEST['st'] == "Pending")
				{
					$curdate = date('d-m-Y');
					$ended_date = date('d-m-Y',strtotime('+1 year'));
					
					$record = Verify::find()->select(['_id'])->where(['user_id' => $uid,'status' => "0"])->orderBy(['joined_date'=>SORT_DESC])->one();
					if($record)
					{
						$record->status = '1';
						$record->update();
						
						$item_number = $_REQUEST['tx'];
						$transaction_id = $_REQUEST['tx'];
						$order_type = 'verify';
						$detail = '1 Year';
						$amount = $_REQUEST['amt'];
						$status = $_REQUEST['st'];
						$curancy = $_REQUEST['cc'];
						$order = new Order();
						$order = $order->neworder($item_number,$transaction_id,$amount,$curancy,$status,$order_type,$detail);
					}
					
				}
			}

			$model = new \backend\models\AddverifyPlans;
			$verify_plans = $model->getVerifyPlans();
			if(count($verify_plans)==0)
			{
				$verifyplans=array("1"=>"12","1"=>"6");
				foreach($verifyplans as $x=>$x_value)
				{
					  $model->amount = (int)$x;
					  $model->months = (int)$x_value;
					  $model->insert();
				}
			}
			return $this->render('verify',['verify_plans' =>$verify_plans]);
		   
        }
        else
        {
            return $this->goHome();
        }
    }
	
	/* Start Function For Checking If User Is Verified or Not */
	
	public function actionCheckverify() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $email = $session->get('email');
		
			$isverify = Verify::isVerify($uid);
			
			if($isverify)
			{
				return 1;
			}
			else
			{
				$id = $_POST['selected_verify_plan'];
				$record = AddverifyPlans::find()->select(['months'])->where(['_id' => "$id"])->one();
				$tot_month = $record['months'];
				$mon = 'month';
				if($tot_month > 1){$mon .= 's';}
				$tot_month = '+'.$tot_month.' '.$mon;

				$date = time();
				$curdate = date('d-m-Y');
				$enddate = strtotime($tot_month);
				$enddate = date('d-m-Y',$enddate);

				Verify::deleteAll(['user_id' => "$uid",'status' => '0']);
				
				$verify = new Verify();
				$verify->user_id = $uid;
				$verify->joined_date = "$curdate";
				$verify->ended_date="$enddate";
				$verify->status = '0';
				$verify->insert();
				
				return 0;
			}
    }    
    
    public function actionMainfeedback() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $model = new \frontend\models\LoginForm();
        if ($session->get('email'))
        {
            return $this->render('mainfeedback');
        }
        else
        {
            return $this->render('mainfeedback');
        }
    }
    
    public function actionBlock() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $model = new \frontend\models\LoginForm();
        if ($session->get('email'))
        {
            return $this->render('block');
        }
        else
        {
            return $this->goHome();
        }
    }
     
    public function actionTravpage() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
		 
		if(isset($uid) && $uid != '') { 
        	$authstatus = UserForm::isUserExistByUid($uid); 
        	$checkuserauthclass = $authstatus;
        } else {
        	$checkuserauthclass = 'checkuserauthclassg';
        }
	
        return $this->render('travpages',array('checkuserauthclass' => $checkuserauthclass));
        
    }
    
    public function actionVerifypage() 
    {
        if (isset($_GET['encpage']) && !empty($_GET['encpage']))
        {
            $session =Yii::$app->session;
            $uid = (string)$session->get('user_id');
            $enc = $_GET['encpage'];
            $page_id = $enc;
            $pageid =  base64_decode(strrev($page_id));
            $page = Page::find()->select(['id'])->where(['page_id' => "$pageid"])->one();
            $page->is_deleted = '1';
            $page->update();
            $cre_amt = 5;
            $cre_desc = 'addpage';
            $status = '1';
            $details = (string)$pageid;
            $credit = new Credits();
            $credit = $credit->addcredits($uid,$cre_amt,$cre_desc,$status,$details);
            $session =Yii::$app->session;
            if ($session->get('email'))
            {
                $url = Yii::$app->urlManager->createUrl(['site/travpage']);
                Yii::$app->getResponse()->redirect($url);
            }
            else
            {
                return $this->goHome();
            }
        }
    }
    
    public function actionTravpost() 
	{
        $session =Yii::$app->session;
        $uid = (string)$session->get('user_id');
		$postid = $_GET['postid'];
        $session->set('postid',$postid);
        $model = new \frontend\models\LoginForm();
        if ($session->get('email')) 
		{
			return $this->render('travpost'); 
        } 
		else
		{
            return $this->goHome();
        }
    }
    
    public function actionTravVisitors() 
	{
        $session =Yii::$app->session;
        $uid = (string)$session->get('user_id');
        
        $model = new \frontend\models\LoginForm();
        if ($session->get('email')) {
                return $this->render('travvisitors');
        } else {
            return $this->goHome();
        }
    }
    
    
    public function actionTravconnections() 
	{
        $session =Yii::$app->session;
        $uid = (string)$session->get('user_id');
       
        if(isset($email) && !empty($email)) {
            $email = $_GET['name'];
        } else {
            $email = '';
        }

        $session->set('name',$email);

        $model = new \frontend\models\LoginForm();
        
		$model_connect = new Connect();
		$connections = $model_connect->searchconnect();
		return $this->render('travconnections',array('connections' => $connections));
       
    }

    public function actionCoverPhoto() 
	{
        $model = new \frontend\models\LoginForm();
        $post_model = new \frontend\models\PostForm();

        if ($model->load(Yii::$app->request->post())) 
		{
            $session = Yii::$app->session;
            $email = $session->get('email');
            $cover_photo = $_FILES['LoginForm']['name']['cover_photo'];
            
            $chars = '0123456789';
            $count = mb_strlen($chars);

            for ($i = 0, $rand = ''; $i < 8; $i++) 
			{
                $index = rand(0,$count - 1);
                $rand .= mb_substr($chars,$index,1);
            }

            $cover_photo = $rand . $cover_photo;
            $record = LoginForm::find()->select(['_id'])->where(['email' => $email])->one();
			
			$model->cover_photo = UploadedFile::getInstance($model,'cover_photo');

            $model->cover_photo->saveAs('profile/' . $cover_photo);

            $record->cover_photo = $cover_photo;
            $record->update();
			
            $date = time();
            $post = new PostForm();
            $post->post_status = '1';
            $post->post_type = 'image';
            $post->is_deleted = '0';

            $post->post_privacy = 'Public';
            $post->image = $cover_photo;
            $post->post_created_date = "$date";
            $post->post_user_id = (string) $record['_id'];
            $post->is_coverpic = '1';
            $post->insert();

            $url = Yii::$app->urlManager->createUrl(['site/cover-photo']);
            Yii::$app->getResponse()->redirect($url);
        }
		else 
		{
            return $this->render('cover_photo',['model' => $model,]);
        }
    }

    public function actionIsdCode() 
	{
        $model = new LoginForm();
        $session = Yii::$app->session;
        $email = $session->get('email_id');

        if (!empty($_POST["country"])) 
		{
            $country = strtoupper($_POST["country"]);
            $result = CountryCode::find()->select(['isd_code'])->where(['country_name' => $country])->one();
            $result['isd_code'];
			
            if (!empty($result)) 
			{
                $isd_code = $result['isd_code'];
                return $isd_code;
            }
        }
    }
	
	public function actionThemeChange() 
	{
        $model = new UserSetting();
        $session = Yii::$app->session;
        $user_id = (string) $session->get('user_id');
		
	    $color = $_GET['color'];
		$userSetting = new UserSetting();
		$userSetting = UserSetting::find()->select(['_id'])->where(['user_id' => $user_id])->one();
		
		if(!empty($userSetting))
		{
			$userSetting->user_theme = $color;
			if($userSetting->update())
			{
				return "success";
				exit();
			}
			else
			{
				return "fail";
				exit();
			}
		}
		else
		{
			$userSetting2 = new UserSetting();
			$userSetting2->user_id =$user_id;
			$userSetting2->user_theme =$color;
			if($userSetting2->insert())
			{
				return "success";
				exit();
			}
			else
			{
				return "fail";
				exit();
			}
			
		}
    }
    
    public function actionApprove()
    {
        $session = Yii::$app->session;
        $user_id = (string) $session->get('user_id');
        $post_id = $_POST['post_id'];
        $ntype = $_POST['ntype'];

        $notification = new Notification();
        if($ntype == 'timeline') {$fname = 'share_id';$ftype = 'sharepost';}
        elseif ($ntype == 'tagconnect') {$fname = 'post_id';$ftype = 'tag_connect';}
        
        $notification = Notification::find()->select(['_id'])->where(['notification_type' => $ftype,'user_id' => $user_id,$fname => $post_id])->one();

        if(!empty($notification))
        {
            $notification->review_setting = 'Disabled';
            if($notification->update())
            {
                return "1";
                exit();
            }
            else
            {
                return "0";
                exit();
            }
        }
        else
        {
            return "0";
            exit();
        }
    }
    
     public function actionApprovetags()
    {
        $session = Yii::$app->session;
        $user_id = (string) $session->get('user_id');
        $post_id = $_POST['post_id'];
        $notification = new Notification();
        $post = PostForm::find()->select(['_id'])->where(['is_deleted' => '1','shared_from' => $user_id,'_id' => (string)$post_id])->one();
		$notification = Notification::find()->where(['notification_type' => 'tag_connect','is_deleted' => '1','post_id' => (string)$post_id])->one();

        if(!empty($notification) && !empty($post))
        {
            $notification->is_deleted = '0';
            $post->is_deleted = '0';
            if($notification->update() && $post->update())
            {
                return "1";
                exit();
            }
            else
            {
                return "0";
                exit();
            }
        }
        else
        {
            return "0";
            exit();
        }
    }
	
	public function actionSearchConnections()
	{
        $session = Yii::$app->session;
        $suserid = (string)$session->get('user_id');
        $model = new \frontend\models\LoginForm();
        $isEmpty = true;
		$keys = isset($_POST['key']) ? trim($_POST['key']) : '';
		$word = '';
		$online = array();
		  
		$assetsPath = '../../vendor/bower/travel/images/';

		if ($keys != '') {
			$eml_id = LoginForm::find()
	        ->where(['like','fname', $keys])
	        ->orwhere(['like','lname', $keys])
	        ->orwhere(['like','fullname', $keys])
	        //->andwhere(['in', (string)'_id', $ids])
	        ->andwhere(['status' => '1'])
	        ->orderBy(['_id'=>SORT_DESC])
	        ->asArray()
	        ->all();
		} else {
			$eml_id = LoginForm::find()->where(['status'=>'1'])->asArray()->all();
		}


		$json =array();

		$i = 0;
			foreach ($eml_id as $val)
			{
				$data = array();
				$name = $val['fullname'];
                $fname = isset($val['fname']) ? $val['fname'] : '';
                $lname = isset($val['lname']) ? $val['lname'] : '';

				if($keys != '') {
                    if (stripos($fname, $keys) === 0 || stripos($lname, $keys) === 0 || stripos($name, $keys) === 0) {
					} else {
                        continue;
                    }
                }

				$guserid = (string)$val['_id'];
				$city = isset($val['city']) ? $val['city'] : '';
                $country  = isset($val['country']) ? $val['country'] : '';
                $address = $city.', '.$country;
                $address = trim($address);
                $address = explode(",", $address);
                $address = array_filter($address);
                $addressLabel = '&nbsp;';
                if(count($address) >1) {
                    $first = reset($address);
                    $last = end($address);
                    $addressLabel = 'Lives in ' . $first.', '.$last;
                } else if(count($address) == 1) {
                    $addressLabel = 'Lives in '. implode(", ", $address);
                } else {
                    $personalinfo = Personalinfo::find()->where(['user_id' => $guserid])->asArray()->one();
                    if(!empty($personalinfo)) {
                        $personalinfo = $personalinfo['occupation'];
                        $personalinfo = explode(',', $personalinfo);
                        $personalinfo = array_values($personalinfo);
                        if(count($personalinfo) >2) {
                            $tempCount = count($personalinfo) - 1;
                            $tempNames = array_slice($personalinfo, 1);

                            $addressLabel = $personalinfo[0] . ' and <a href="javascript:void(0)" class="liveliketooltip" data-title="'.implode('<br/>', $tempNames).'">'.(count($personalinfo) - 1).' others</a>';     
                        } else if (count($personalinfo) >1) {
                            $addressLabel = $personalinfo[0] .' and ' . $personalinfo[1];  
                        } else if (count($personalinfo) == 1) { 
                            $addressLabel = $personalinfo[0];  
                        }
                    }
                }

                $ctr = Connect::mutualconnectcount($guserid);
                $result_security = SecuritySetting::find()->where(['user_id' => $guserid])->asarray()->one();
                $connect_list = isset($result_security['connect_list']) ? $result_security['connect_list'] : '';
                $mutualLabel =  '';
                $totalconnections = Connect::find()->where(['to_id' => (string)$guserid, 'status' => '1'])->count();
                if($connect_list == 'Public') {
                    if($totalconnections>1) {
                        $mutualLabel = $totalconnections .' Connections';
                    } else if($totalconnections == 1) {
                        $mutualLabel = '1 Connect';
                    }
                } else if($connect_list == 'Private') {
                    if($ctr >0) {
                        $mutualLabel =  $ctr.' Mutual Connections';
                    }
                } else if($connect_list == 'Connections') {
                    if(!empty($isconnect)) {
                        if($totalconnections>1) {
                            $mutualLabel = $totalconnections .' Connections';
                        } else if($totalconnections == 1) {
                            $mutualLabel = '1 Connect';
                        }   
                    } else {
                        if($ctr >0) {
                            $mutualLabel =  $ctr.' Mutual Connections';
                        }   
                    }
                }

				$isvip = Vip::isVIP($guserid);
				$isVerify = Verify::isVerify($guserid);
				
				$is_connect = Connect::find()->where(['from_id' => $guserid,'to_id' => $suserid,'status' => '1'])->one();
				
				if($is_connect) 
				{

				$uniq_id = rand(9999, 9999999);
				$isEmpty = false;
				$dp = $this->getimage($guserid,'thumb');
				?>
				<div class="col s12 m3 l3 connect_<?=$guserid?>">
				   <div class="person-box">
					  <div class="imgholder">
						 <img src="<?= $dp?>"/>
						 <div class="overlay">
					 	<?php if($isvip == true) { ?>                                                           
                        <div class="vip-span"><img src="<?=$assetsPath?>/vip-tag.png"/></div>
                        <?php } ?>  
						 <?php if($isVerify) { ?>
						 <span class="online-mark"><i class="zmdi zmdi-check"></i></span>
						 <?php } ?>
						 <div class="more-span mobile-none-who add-icon_<?=$guserid?>">	
							 <div class="dropdown dropdown-custom ">
							  <a href="javascript:void(0)" class="dropdown-button more_btn" data-activates="as<?=$uniq_id?>" onclick="fetchconnectmenu('<?=$guserid?>')"><i class="mdi mdi-chevron-down"></i></a>
							  <ul id="as<?=$uniq_id?>" class="dropdown-content custom_dropdown cancle-popu fetchconnectmenu">
							  <center><div class="lds-css ng-scope"> <div class="lds-rolling lds-rolling100"> <div></div> </div></div></center>
							  </ul>
							</div>
						</div>
						</div>
					 </div>
					 <div class="descholder">
					 	<h5>
                        <a href="<?=Url::to(['userwall/index', 'id' => $guserid])?>">
                            <span class="etext" style="color: #000;"><?=$name?></span>
                            <?php 
                            if(array_key_exists($guserid, $online)) { ?>
                                <span class="online-dot"><i class="zmdi zmdi-check"></i></span>
                            <?php } ?>
                        </a></h5>
						<p><?=$addressLabel?></p>
						<p class="info"><?=$mutualLabel?></p>
					</div>
				</div>							
			</div>
			<?php } } 

			if($isEmpty) {
			 	$this->getnolistfound('noconnectfound');
			}
    }

    public function actionProfileImageCrop()
    {
    	if(isset($_POST['file']) && $_POST['file'] != '') {
	        $model = new \frontend\models\LoginForm();
	        $session = Yii::$app->session;
	        if($session->get('email_id') != ''){
			   $email = $session->get('email_id'); 
			} else {
				$email = $session->get('email'); 
			} 

			if(isset($email) && !empty($email))
			{
				$email = $email;
			}
			else
			{
				$login_from_ip = $_SERVER['REMOTE_ADDR'];
				$birth = LoginForm::find()->where(['login_from_ip' => $login_from_ip])->orderBy(['created_date'=>SORT_DESC])->one();
				$email = $birth['email'];
			}
			if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
			{
				$url = $_SERVER['HTTP_REFERER'];
				$urls = explode('&',$url);
				$url = explode('=',$urls[1]);
				$userid = $url[1];
				$update = LoginForm::find()->where(['_id' => (string)$userid])->one();
			}
			else
			{
				$update = LoginForm::find()->where(['email' => $email])->one();
			}

	        if($update->email != '' || $update->_id != '') {
	            $dt = time();
	            $fnm = $update->_id.'_'.$dt;

	            $rawImageString = $_POST['file'];
				$filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $rawImageString));
				$imageName = $fnm.'.png';
				;
				if(file_put_contents("profile/".$imageName, $filterString)) {
					if(isset($update['thumbnail'])) {
						if(file_exists('profile/'.$update['thumbnail'])) {
							unlink('profile/'.$update['thumbnail']);
						}
						if(file_exists('profile/ORI_'.$update['thumbnail'])) {
							unlink('profile/ORI_'.$update['thumbnail']);
						}
					}

					$update->photo = 'ORI_'.$imageName;
	            	$update->thumbnail = $imageName;
				}

	            $update->update();
	            
	            $response = Array(
	                "status" => 'success',
	                "url" => 'profile/'.$imageName
	            );
	            $date = time();
	            $post = new PostForm();
	            $post->post_status = '1';
	            $post->post_type = 'profilepic';
	            $post->is_profilepic = '1';
	            $post->is_deleted = '0';
	            $post->post_privacy = 'Public';
	            $post->image = $imageName;
	            $post->post_created_date = "$date";
	            $post->post_user_id = (string)$update->_id;
	            $post->insert();
	            if(!strstr($_SERVER['HTTP_REFERER'],'r=page'))
	            {
	                $post_user_id = (string)$update->_id;

	                $chk_credit = Credits::find()->where(['user_id' => $post_user_id])->andwhere(['credits_desc' => 'profilephoto'])->one();
	                if(empty($chk_credit)){
	                $cre_amt = 10;
	                $cre_desc = 'profilephoto';
	                $status = '1';
	                $details = 'Profile_'.(string)$update->_id;
	                $credit = new Credits();
	                $credit = $credit->addcredits((string)$update->_id,$cre_amt,$cre_desc,$status,$details);
	                }
	            }
	            return json_encode($response, true);
	        }
	    }
    }

    public function actionCoverImageCrop()
    {
        $session = Yii::$app->session;
        $email = $session->get('email_id'); 
        $userid = (string)$session->get('user_id');
		$update = LoginForm::find()->where(['_id' => (string)$userid])->one();
		$rand = $userid .'_'.time();
		$date =time();

        if($userid != '') {
        	if(isset($_POST['images']) && $_POST['images'] != '' &&  $_POST['images'] != 'undefined') {
				$rawImageString = $_POST['images'];
				$filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $rawImageString));
				$imageName = $rand.'.png';

				$filepath = "uploads/cover/";
				if(file_put_contents($filepath.$imageName, $filterString)) {
					if(file_put_contents($filepath.'thumbs/thumb_'.$imageName, $filterString)) {
						$update->cover_photo = $imageName;
						$update->update();

						$cover = new Cover();
						$cover->cover_image = $imageName;
						$cover->created_at = $date;
						$cover->save();
					}
				}
	            $date =time();
	            $post =new PostForm();
	            $post->post_status ='1';
	            $post->post_type ='image';
	            $post->is_deleted ='0';
	            $post->post_privacy ='Public';
	            $post->image = $imageName;
	            $post->post_created_date ="$date";
	            $post->post_user_id =(string) $userid;
				if(isset($page_owner) && !empty($page_owner)) {
					$post->page_owner = $page_owner;
				}
	            $post->is_coverpic = '1';
	            $post->insert();
	            return true;
	        }
        }
    }

    public function actionDirectsetcover()
    {
        $model = new \frontend\models\LoginForm();
        $session = Yii::$app->session;
        $email = $session->get('email_id'); 
        $userid = (string)$session->get('user_id');
		$update = LoginForm::find()->where(['_id' => (string)$userid])->one();
		$rand = $userid .'_'.time();
		$date =time();

        if($userid != '') {
        	if(isset($_POST['$imgSrc']) && $_POST['$imgSrc'] != '' &&  $_POST['$imgSrc'] != 'undefined') {
                $rawImageString = $_POST['$imgSrc'];
                $rawImageString = basename($rawImageString);
                $rawImageString = str_replace("thumb_","", $rawImageString);
                $update->cover_photo = $rawImageString;
				$update->update();

	            $date =time();
	            $post =new PostForm();
	            $post->post_status ='1';
	            $post->post_type ='image';
	            $post->is_deleted ='0';
	            $post->post_privacy ='Public';
	            $post->image = $rawImageString;
	            $post->post_created_date ="$date";
	            $post->post_user_id =(string) $userid;
				if(isset($page_owner) && !empty($page_owner))
				{
					$post->page_owner = $page_owner;
				}
	            $post->is_coverpic = '1';
	            $post->insert();
	            return true;
	        }
        }
    }
 
public function actionEditPostPreSet()
{
	$time = time();
    $session = Yii::$app->session;
      
	$assetsPath = '../../vendor/bower/travel/images/';
    $baseUrl = '../../vendor/bower/travel/images/';
    $userid = $user_id = (string)$session->get('user_id');
	if(isset($userid) && $userid != '') {
	$authstatus = UserForm::isUserExistByUid($userid);
	if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
		$data['auth'] = $authstatus;
		return $authstatus;
	} 
	else {
    $postid = isset($_POST['editpostid']) ? $_POST['editpostid'] : '';
    $post = PostForm::find()->where(['_id' => $postid])->one();
    $pageid = $post['post_user_id'];
	$fullname = $this->getuserdata($user_id,'fullname');
	$post_type = '';
	$photoStop = 'no';
	
    if($post['pagepost']=='1') 
    {
        $page_details = Page::Pagedetails($post['post_user_id']); $post['post_user_id'] = $page_details['created_by'];
    }
    else 
    {
        $post['post_user_id'] = $post['post_user_id']; 
    }

    if(isset($post['shared_from']) && !empty($post['shared_from'])) {
		$photoStop = 'yes';	
    } else if(isset($post['is_page_review']) && $post['is_page_review'] == '1') {
		$photoStop = 'yes';	
	} else if(isset($post['placetype']) && ($post['placetype'] == 'reviews' || $post['placetype'] == 'ask')) {
		$photoStop = 'yes';	
	}


    if($userid == $post['post_user_id'] || $userid == $post['shared_by']) 
    { 
        $post['post_user_id'] = $pageid;
        $my_post_view_status = $post['post_privacy'];
        if($my_post_view_status == 'Private') {$post_dropdown_class = 'lock';}
        else if($my_post_view_status == 'Connections') {$post_dropdown_class = 'account';}
        else if($my_post_view_status == 'Custom') {$post_dropdown_class = 'settings';}
        else {$post_dropdown_class = 'earth';}

         
        if(isset($post['is_page_review']) && !empty($post['is_page_review'])) {$class = 'expanded expandReview';}
        else if(isset($post['placetype']) && $post['placetype'] == 'reviews') {$class = 'expanded expandReview';}
        else{$class = '';}
		if(isset($post['placetype']) && $post['placetype'] == 'reviews')
		{
			$place_type = 'reviews';
			$from_place_page = 'yes';
			$is_place_data = 'yes';
			$post_type = 'review';
		}
		else if(isset($post['placetype']) && $post['placetype'] == 'tip')
		{
			$place_type = 'tip';
			$from_place_page = 'yes';
			$is_place_data = 'yes';
			$post_type = 'tip';
		}
		else if(isset($post['placetype']) && $post['placetype'] == 'ask')
		{
			$place_type = 'ask';
			$from_place_page = 'yes';
			$is_place_data = 'yes';
			$post_type = 'question';
		}
		else
		{
			$place_type = 'none';
			$from_place_page = 'no';
			$is_place_data = 'no';
			$post_type = 'Post';
		}

		$posttag = '';
	    if(isset($post['post_tags']) && !empty($post['post_tags'])) {
	        $posttag = explode(",", $post['post_tags']);
	    }

	    $taginfomatiom = ArrayHelper::map(UserForm::find()->where(['IN', '_id',  $posttag])->all(), 'fullname', (string)'_id');

	    $nkTag = array();
	    $nvTag = array();

	    $i=1;
	    foreach ($taginfomatiom as $key => $value) {
	        $nkTag[] = (string)$value; 
	        $nvTag[] = $key;
	        if($i != 1) {
	            $content[] = $key;
	        }
	        $i++;
	    } 

	    if(isset($content) && !empty($content)) {
	        $content = implode("<br/>", $content); 
	    }

	    $tagstr = '';
	    $currentlocation = isset($post['currentlocation']) ? $post['currentlocation'] : '';
	    $current_location = $this->getshortcityname($currentlocation);

        ?>
		<div class="hidden_header">
			<div class="content_header">
				<button class="close_span cancel_poup waves-effect">
					<i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
				</button>
				<p class="modal_header_xs">Edit post</p>
				<span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
				<a type="button" class="post_btn action_btn post_btn_xs postbtn savebtn active_post_btn waves-effect" onclick="edit_post('<?=$post['_id']?>');">Save</a>
			</div>
		</div>               
        <div class="modal-content <?php if(isset($post['placetype']) && $post['placetype'] == 'reviews'){ ?> reviews-column<?php } ?>">					
				
				<div class="new-post active <?=$class?> <?php if((isset($post['is_page_review']) && !empty($post['is_page_review'])) || (isset($post['placetype']) && $post['placetype'] == 'reviews')){ ?> expanded expandReview<?php } ?>">
					<div class="clear"></div>
						<div class="top-stuff">
							<?php if(!(isset($post['is_page_review']) && !empty($post['is_page_review'])) || !(isset($post['placetype']) && $post['placetype'] == 'reviews')){ ?>
								<div class="postuser-info">
									<div class="img-holder">
									<?php 
										$dpimg = $this->getimage($post['user']['_id'],'photo');
									?>
									<img src="<?= $dpimg?>">
									</div>
									<div class="desc-holder">
										<p class="profile_name"><?=$fullname?></p>
										<?php 
										if(!empty($taginfomatiom)) {
								            if(count($taginfomatiom) > 1) {
								            	if(count($taginfomatiom) > 2) {
								            		$label = (count($taginfomatiom) - 1).' Others';
								            	} else {
								            		$label = '1 Other';
								            	}
								                $tagstr =  "<span>&nbsp;with&nbsp;</span><span class='tagged_person_name compose_addpersonAction' id='compose_addpersonAction'>" . $nvTag[0] . "</span><span>&nbsp;and&nbsp;</span><span class='pa-like sub-link livetooltip compose_addpersonAction tagged_person_name' title='".$content."'>".$label."</span>";
								            } else {
								                $tagstr =  "<span>&nbsp;with&nbsp;</span><span class='tagged_person_name compose_addpersonAction' id='compose_addpersonAction'>" . $nvTag[0] . "</span>";
								            }
								        }
										?>
										<label id="tag_person">
											<?=$tagstr?>
											<?php /*
											<?php if($current_location != '') { ?>
													at <?=$current_location?>
											<?php } ?>
											*/ ?>
								        </label>
										<?php if(empty($post['trav_item']) && $from_place_page == 'no'){?>
											
											<div class="public_dropdown_container damagedropdown">
												<a class="dropdown_text dropdown-button-left editpostcreateprivacylabel" onclick="privacymodal(this)" href="javascript:void(0)" data-modeltag="editpostcreateprivacylabel" data-fetch="yes" data-label="editpost">
													<span id="post_privacy" class="post_privacy_label active_check"><?=$my_post_view_status?></span>
													<i class="zmdi zmdi-caret-down"></i>
												</a>
											</div>
										
										<?php } ?>
									
									</div>
								</div>
							<?php } ?>
							<?php if($place_type != 'ask') { ?>
								<div class="settings-icon">
									<a class="dropdown-button" href="javascript:void(0)" data-activates="editpost_settings">
										<i class="zmdi zmdi-more"></i>
									</a>
									<ul id="editpost_settings" class="dropdown-content custom_dropdown">
										<?php if($is_place_data == 'no'){ ?>
											<li class="disable_share">
												<a href="javascript:void(0)">
													<?php if($post['share_setting'] == 'Disable') { ?>
														<input type="checkbox" class="toolbox_disable_sharing" id="<?=$time?>toolbox_disable_sharing" checked/>
													<?php } else  { ?>
														<input type="checkbox" class="toolbox_disable_sharing" id="<?=$time?>toolbox_disable_sharing"/>
													<?php } ?>
													<label for="<?=$time?>toolbox_disable_sharing">Disable Sharing</label>
												</a>
											</li>
										<?php } ?>
										<?php if($place_type != 'ask') { ?>
											<li class="disable_comment">
												<a href="javascript:void(0)">
													<?php if($post['comment_setting'] == 'Disable') { ?>
														<input type="checkbox" class="toolbox_disable_comments" id="<?=$time?>toolbox_disable_comments" checked />
													<?php } else  { ?>
														<input type="checkbox" class="toolbox_disable_comments" id="<?=$time?>toolbox_disable_comments"/>
													<?php } ?>
													<label for="<?=$time?>toolbox_disable_comments">Disable Comments</label>
												</a>
											</li>
										<?php } ?>
									</ul>
								</div>
							<?php } ?>
								
						</div>
						<?php
							if(isset($post['share_setting']) && !empty($post['share_setting']))
							{
								if($post['share_setting'] == 'Enable')
								{
									?>
									<input type="hidden" name="share_setting" class="share_setting" value="Enable"/>
									<?php
								}
								else
								{
									?>
									<input type="hidden" name="share_setting" class="share_setting" value="Disable"/>
									<?php
								}	
							}
							else
							{
								?>
								<input type="hidden" name="share_setting" class="share_setting" value="Enable"/>
								<?php
							}
						?>
						
						<?php
							if(isset($post['comment_setting']) && !empty($post['comment_setting']))
							{
								if($post['comment_setting'] == 'Enable')
								{
									?>
									<input type="hidden" name="comment_setting" class="comment_setting" value="Enable"/>
									<?php
								}
								else
								{
									?>
									<input type="hidden" name="comment_setting" class="comment_setting" value="Disable"/>
									<?php
								}	
							}
							else
							{
								?>
								<input type="hidden" name="comment_setting" class="comment_setting" value="Enable"/>
								<?php
							}
						?>
							<div class="npost-content">
								<?php if(!(isset($post['shared_from']) && !empty($post['shared_from']))) { ?>
										<div class="compose_post_title title_post_container npost-title" style="display: block">
											<?php
											 $post_title = (isset($post['post_title']) && !empty($post['post_title'])) ? $post['post_title'] : '';
											 if(empty($post['trav_item']))
											 {
												$place_title = "post";
											 }else{
												$place_title = "item"; 
											 }	 
											?>
											<input type="text" class="title post_title" placeholder="Title of this <?=$post_type;?>" value="<?=$post_title?>">																								
										</div>
									<?php } ?>	
									<div class="clear"></div>

						<?php if(isset($post['is_page_review']) && !empty($post['is_page_review'])) { ?> 
							<div class="rating-stars setRating cus_starts">
								<span>Edit your rating</span>&nbsp;&nbsp;
								<?php if(isset($post['rating']) && ($post['rating'] == '1' || $post['rating'] == 1)) { ?>
									<span onmouseout="resettostart(this)">
									<i class="mdi mdi-star ratecls1 ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="1" onmouseover="ratingJustOver(this)" onclick="pickrate(this,1)"></i> 
									<i class="mdi mdi-star ratecls2 ratecls3 ratecls4 ratecls5" data-value="2" onmouseover="ratingJustOver(this)" onclick="pickrate(this,2)"></i>
									<i class="mdi mdi-star ratecls3 ratecls4 ratecls5" data-value="3" onmouseover="ratingJustOver(this)" onclick="pickrate(this,3)"></i>
									<i class="mdi mdi-star ratecls4 ratecls5" data-value="4" onmouseover="ratingJustOver(this)" onclick="pickrate(this,4)"></i>
									<i class="mdi mdi-star ratecls5" data-value="5" onmouseover="ratingJustOver(this)" onclick="pickrate(this,5)"></i>
									</span>&nbsp;&nbsp;
									<span class="star-text">Poor</span>
								<?php } else if(isset($post['rating']) && ($post['rating'] == '2' || $post['rating'] == 2)) { ?>
									<span onmouseout="resettostart(this)">
									<i class="mdi mdi-star ratecls1 ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="1" onmouseover="ratingJustOver(this)" onclick="pickrate(this,1)"></i> 
									<i class="mdi mdi-star ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="2" onmouseover="ratingJustOver(this)" onclick="pickrate(this,2)"></i>
									<i class="mdi mdi-star ratecls3 ratecls4 ratecls5" data-value="3" onmouseover="ratingJustOver(this)" onclick="pickrate(this,3)"></i>
									<i class="mdi mdi-star ratecls4 ratecls5" data-value="4" onmouseover="ratingJustOver(this)" onclick="pickrate(this,4)"></i>
									<i class="mdi mdi-star ratecls5" data-value="5" onmouseover="ratingJustOver(this)" onclick="pickrate(this,5)"></i>
									</span>&nbsp;&nbsp;
									<span class="star-text">Good</span>
								<?php } else if(isset($post['rating']) && ($post['rating'] == '3' || $post['rating'] == 3)) { ?>
									<span onmouseout="resettostart(this)">
									<i class="mdi mdi-star ratecls1 ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="1" onmouseover="ratingJustOver(this)" onclick="pickrate(this,1)"></i> 
									<i class="mdi mdi-star ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="2" onmouseover="ratingJustOver(this)" onclick="pickrate(this,2)"></i>
									<i class="mdi mdi-star ratecls3 ratecls4 ratecls5 active" data-value="3" onmouseover="ratingJustOver(this)" onclick="pickrate(this,3)"></i>
									<i class="mdi mdi-star ratecls4 ratecls5" data-value="4" onmouseover="ratingJustOver(this)" onclick="pickrate(this,4)"></i>
									<i class="mdi mdi-star ratecls5" data-value="5" onmouseover="ratingJustOver(this)" onclick="pickrate(this,5)"></i>
									</span>&nbsp;&nbsp;
									<span class="star-text">Better</span>
								<?php } else if(isset($post['rating']) && ($post['rating'] == '4' || $post['rating'] == 4)) { ?>
									<span onmouseout="resettostart(this)">
									<i class="mdi mdi-star ratecls1 ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="1" onmouseover="ratingJustOver(this)" onclick="pickrate(this,1)"></i> 
									<i class="mdi mdi-star ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="2" onmouseover="ratingJustOver(this)" onclick="pickrate(this,2)"></i>
									<i class="mdi mdi-star ratecls3 ratecls4 ratecls5 active" data-value="3" onmouseover="ratingJustOver(this)" onclick="pickrate(this,3)"></i>
									<i class="mdi mdi-star ratecls4 ratecls5 active" data-value="4" onmouseover="ratingJustOver(this)" onclick="pickrate(this,4)"></i>
									<i class="mdi mdi-star ratecls5" data-value="5" onmouseover="ratingJustOver(this)" onclick="pickrate(this,5)"></i>
									</span>&nbsp;&nbsp;
									<span class="star-text">Superb</span>
								<?php } else if(isset($post['rating']) && ($post['rating'] == '5' || $post['rating'] == 5)) { ?>
									<span onmouseout="resettostart(this)">
									<i class="mdi mdi-star ratecls1 ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="1" onmouseover="ratingJustOver(this)" onclick="pickrate(this,1)"></i> 
									<i class="mdi mdi-star ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="2" onmouseover="ratingJustOver(this)" onclick="pickrate(this,2)"></i>
									<i class="mdi mdi-star ratecls3 ratecls4 ratecls5 active" data-value="3" onmouseover="ratingJustOver(this)" onclick="pickrate(this,3)"></i>
									<i class="mdi mdi-star ratecls4 ratecls5 active" data-value="4" onmouseover="ratingJustOver(this)" onclick="pickrate(this,4)"></i>
									<i class="mdi mdi-star ratecls5 active" data-value="5" onmouseover="ratingJustOver(this)" onclick="pickrate(this,5)"></i>
									</span>&nbsp;&nbsp;
									<span class="star-text">Excellent</span>
								<?php } ?>
							</div>
							<div class="clear"></div>
						<?php } ?>
						
						<?php if(isset($post['placetype']) && $post['placetype'] == 'reviews'){
							$placereview = $post['placereview'];
							if($placereview == 5){$placetitle = 'Excellent';}
							else if($placereview == 4){$placetitle = 'Superb';}
							else if($placereview == 3){$placetitle = 'Better';}
							else if($placereview == 2){$placetitle = 'Good';}
							else if($placereview == 1){$placetitle = 'Poor';}
							else{$placetitle = 'Roll over stars, then click to rate';}
						    ?>
							<div class="rating-stars setRating editRating" onmouseout="setStarText(this,6)" data-prid="<?=$post["_id"]?>">
								<span>Edit your rating</span>
								<?php for($i=5;$i>0;$i--) { ?>
									<i class="mdi mdi-star <?php if($placereview >= $i){ ?>active<?php } ?>" data-value="<?=$i?>" onmouseover="setStarText(this,'<?=$i?>')" onclick="setRating(this,'<?=$i?>')"></i>
								<?php } ?>
								<span class="star-text"><?=$placetitle;?></span>
							</div>
							<input type="hidden" id="placereviewrate<?=$post["_id"]?>" value="<?=$placereview?>">
							<div class="clear"></div>
						<?php } ?>
									<div class="desc post_comment_box">
										<textarea class="textInput npinput desc materialize-textarea comment_textarea" id="textInput"><?= $post['post_text'] ?></textarea>
										
									</div>
									<?php if($photoStop == 'no') { ?>
									<div class="post-photos">
										<div class="img-row">
											<?php
												if(isset($post['image']) && !empty($post['image'])) {
													$eximgs = explode(',',$post['image'],-1);
													foreach ($eximgs as $eximg) {
														if (file_exists('../web'.$eximg)) {  
															$iname = $this->getimagefilename($eximg);
															$picsize = '';
															$val = getimagesize('../web'.$eximg);
															$picsize .= $val[0] .'x'. $val[1] .', ';
															if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}?>
													<div class="img-box" id="imgbox_<?=$iname?>">
													<a href="javascript:void(0)" class="listalbum-box">
														<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" alt="" class="thumb-image <?=$imgclass?> ">
														<?php if(!(isset($post['shared_from']) && !empty($post['shared_from']))) { ?>
														<a href="javascript:void(0)" data-class="ep-delpic" class="removePhotoFile popup-imgdel" onclick="delete_image('<?= $iname ?>','<?= $eximg ?>','<?= $post['_id'] ?>')"><i class="mdi mdi-close	"></i>
														</a>
														<?php } ?>
													</a>
													</div>
												<?php }  }  } ?>
												<?php if($place_type != 'reviews' && $place_type != 'ask'){ ?>
												<?php if(!(isset($post['shared_from']) && !empty($post['shared_from']))) { ?>
												<div class='img-box customuploadbox'><div class='custom-file addimg-box'><div class='addimg-icon'><i class="zmdi zmdi-plus zmdi-hc-lg"></i></div><input type='file' name='upload' class='upload custom-upload remove-custom-upload' title='Choose a file to upload' required='' data-class='.editpost-popup-<?=$post['_id']?> .post-photos .img-row' multiple='true'/></div></div>
												<?php } ?>
												<?php } ?>
										</div>
									</div>
									<?php 
									} else { 

									$getSharePostContent = PostForm::find()->where([(string)'_id' => $post['parent_post_id']])->asarray()->one();	
									?>
									<div class="org-post">                          
										<div class="post-list">							
											<div class="post-holder <?php if(isset($getSharePostContent['is_trip'])){?>tripexperince-post<?php } ?>"> 
												<?php if(isset($getSharePostContent['page_id']) && $getSharePostContent['page_id'] != '') {
													$page_id = $getSharePostContent['page_id'];
													$dp = $this->getpageimage($page_id);
													$page_details = Page::Pagedetails($page_id);
													$pagelike = Like::getLikeCount($page_id);
													$pagecover = $this->getuserdata($page_id,'cover_photo');
													if(isset($pagecover) && !empty($pagecover))
													{
														$cover_photo = "uploads/cover/".$pagecover;
													}
													else
													{
														$cover_photo = $assetsPath."wallbanner.jpg";
													}
												?>
												<input type="hidden" value="page_<?=$page_id?>" id="spid" name="spid"/>
												<div class="post-content share-feedpage">
													<div class="shared-box shared-category">										
														<div class="post-img-holder">
															<div class="post-img">
																<div class="pimg-holder">
																	<div class="bannerimg" style="background:url('<?=$cover_photo?>') center top no-repeat;background-size:cover;"></div>
																	<div class="profileimg"><img src="<?=$dp?>"/></div>
																</div>
															</div>
														</div>
														<div class="share-summery">											
															<div class="sharedpost-title">
																<b><a href="<?=Url::to(['page/index', 'id' => "$page_id"])?>" style="color: black"><?=$page_details['page_name']?></a></b>
															</div>
															<div class="sharedpost-tagline"><?=$page_details['short_desc']?></div>
															<div class="sharedpost-subtitle"><?=$page_details['category']?></div>
															<div class="sharedpost-desc"><?=$pagelike?> people liked this</div>
														</div>											
													</div>
												</div>
												<?php } else if(isset($getSharePostContent['is_trip']) && $getSharePostContent['is_trip'] != '') {
													$trip_id = $getSharePostContent['is_trip'];
												$time = Yii::$app->EphocTime->time_elapsed_A(time(),$getSharePostContent['post_created_date']);
												$dpimg = $this->getimage($getSharePostContent['user']['_id'],'photo');
												$id =  $getSharePostContent['user']['_id'];
												?>	
													<div class="post-topbar">
														<div class="post-userinfo">
								
															<div class="img-holder">
																<div id="profiletip-1" class="profiletipholder">
																	<span class="profile-tooltip tooltipstered">
																		<img class="circle" src="<?= $dpimg;?>">
																	</span>
																	
																</div>
																
															</div>
															<div class="desc-holder">
																<span>By </span><a href="<?=Url::to(['userwall/index', 'id' => "$id"])?>"><?=ucfirst($getSharePostContent['user']['fname']).' '.ucfirst($getSharePostContent['user']['lname'])?></a>
																
																<span class="timestamp"><?= $time;?><span class="glyphicon glyphicon-globe"></span></span>
															</div>
														</div>										
													</div>
													
													<div class="post-content tripexperince-post share-feedpage">
														<div class="pdetail-holder">
															<?php if($getSharePostContent['post_title'] != null) { ?>
															<div class="post-details">
																<div class="post-title">
																	<?= $getSharePostContent['post_title'] ?>
																</div>												
															</div>
															<?php } ?>
															<div class="trip-summery">
																<div class="location-info">
																	<h5><i class="zmdi zmdi-pin"></i> <?= $getSharePostContent['currentlocation'];?></h5>
																	<i class="mdi mdi-menu-right"></i>
																	<a href="javascript:void(0)" onclick="openViewMap(this)">View on map</a>
																</div>											
															</div>
															<div class="map-holder dis-none">
																<iframe width="600" height="450" frameborder="0" src="https://maps.google.it/maps?q=<?= $getSharePostContent['currentlocation'];?>&output=embed"></iframe>
															</div>
															<a class="overlay-link postdetail-popup popup-modal" data-postid="<?=$getSharePostContent['_id']?>" href="javascript:void(0)">&nbsp;</a>
														</div>
														<?php if($getSharePostContent['post_type'] == 'text' && (isset($getSharePostContent['trav_item']) && $getSharePostContent['trav_item'] == '1')){
															$getSharePostContent['post_type'] = 'text and image';
														}?>
														<?php if(($getSharePostContent['post_type'] == 'image' || $getSharePostContent['post_type'] == 'text and image') && $getSharePostContent['is_coverpic'] == null) {
															$cnt = 1;
															$eximgs = explode(',',$getSharePostContent['image'],-1);
															if(isset($getSharePostContent['trav_item']) && $getSharePostContent['trav_item']== '1')
															{
																if($getSharePostContent['image'] == null)
																{
																	$eximgs[0] = '/uploads/travitem-default.png';
																}
																$eximgss[] = $eximgs[0];
																$eximgs = $eximgss;										
															}
															$totalimgs = count($eximgs);
															$imgcountcls="";
															if($totalimgs == '1'){$imgcountcls = 'one-img';}
															if($totalimgs == '2'){$imgcountcls = 'two-img';}
															if($totalimgs == '3'){$imgcountcls = 'three-img';}
															if($totalimgs == '4'){$imgcountcls = 'four-img';}
															if($totalimgs == '5'){$imgcountcls = 'five-img';}
															if($totalimgs > '5'){$imgcountcls = 'more-img';}
														?>
														<div class="post-img-holder">
															<div class="post-img <?= $imgcountcls?> gallery swipe-gallery">
																<?php
																foreach ($eximgs as $eximg) {
																if (file_exists('../web'.$eximg)) {
																$picsize = '';
																$val = getimagesize('../web'.$eximg);
																$iname = $this->getimagename($eximg);
																 $inameclass = $this->getimagefilename($eximg);
																 $pinit = PinImage::find()->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
																 if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
																 
																
																$picsize .= $val[0] .'x'. $val[1] .', ';
																if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';} ?>
																
																<a href="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-imgid="<?=$inameclass?>" data-size="1600x1600"  data-med="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-med-size="1024x1024" data-author="Folkert Gorter" data-pinit="<?=$pinval?>" class="imgpin pimg-holder <?= $imgclass?>-box <?php if($cnt > 5){?>extraimg<?php } ?> <?php if($cnt ==5 && $totalimgs > 5){?>more-box<?php } ?>">
																	<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" class="<?= $imgclass?>"/>
																	<?php if($cnt == 5 && $totalimgs > 5){?>
																		<span class="moreimg-count"><i class="mdi mdi-plus"></i><?= $totalimgs - $cnt +1;?></span>
																	<?php } ?>
																</a>
																<?php } $cnt++; } ?>
															</div>
														</div>
														<?php } ?>
														<div class="pdetail-holder">
															<div class="post-details">		
																<div class="post-desc">
																	<?php if(strlen($getSharePostContent['post_text'])>187){ ?>
																		<div class="para-section">
																			<div class="para">
																				<p><?= $getSharePostContent['post_text'] ?></p>
																			</div>
																			<a href="javascript:void(0)" class="readlink">Read More</a>
																		</div>
																	<?php }else{ ?>										
																		<p><?= $getSharePostContent['post_text'] ?></p>
																	<?php } ?>
																	
																</div>
															</div>
															<a class="overlay-link postdetail-popup popup-modal" data-postid="<?=$getSharePostContent['_id']?>" href="javascript:void(0)">&nbsp;</a>
														</div>												
													</div>
													
													<!-- end shared trip experience -->
											
													<input type="hidden" value="<?=$getSharePostContent['_id']?>" id="spid" name="spid"/>
												<?php 
												} else {
													if(isset($getSharePostContent['is_page_review']) && $getSharePostContent['is_page_review'] == '1') { 
														if(!(isset($getSharePostContent['image']) && $getSharePostContent['image'] != '')) { 
																$cls = 'review-share';
															} 
													} else if(isset($getSharePostContent['trav_item']) && $getSharePostContent['trav_item'] == '1') {
														$cls = 'travelstore-ad';
													} else { 
														if(isset($getSharePostContent['image']) && !empty($getSharePostContent['image'])) {
															$cls = 'share-feedpage';
														} else { 
															$cls = 'review-share'; 
														} 
													}

													?>
													<div class="post-content <?=$cls?>">
														<input type="hidden" value="<?=$getSharePostContent['_id']?>" id="spid" name="spid"/>
														<div class="post-details">
															<?php if($getSharePostContent['post_title'] != null) { ?>
															<div class="post-title"><?= $getSharePostContent['post_title'] ?></div>
															<?php } ?>
															<?php if(isset($getSharePostContent['trav_price']) && $getSharePostContent['trav_price'] != null) { ?>
															<div class="post-price" style="display:block;">$<?= $getSharePostContent['trav_price'] ?></div>
															<?php } ?>
															<?php if((isset($getSharePostContent['trav_item']) && !empty($getSharePostContent['trav_item']) && $getSharePostContent['currentlocation'] != null)){ ?>
																<div class="post-location" style="display:block"><i class="zmdi zmdi-pin"></i><?= $getSharePostContent['currentlocation'] ?></div>
															<?php } ?>
															<?php if($getSharePostContent['post_type'] != 'link' && $getSharePostContent['post_type'] != 'profilepic'){ ?>
															<div class="post-desc">
																<?php if(strlen($getSharePostContent['post_text'])>187){ ?>
																	<div class="para-section">
																		<div class="para">
																			<p><?= $getSharePostContent['post_text'] ?></p>
																		</div>
																		<a href="javascript:void(0)" class="readlink">Read More</a>
																	</div>
																<?php }else{ ?>										
																	<p><?= $getSharePostContent['post_text'] ?></p>
																<?php } ?>														
															</div>
															<?php } ?>
															<?php if(isset($getSharePostContent['is_page_review']) && !empty($getSharePostContent['is_page_review'])){ ?>
															<div class="rating-stars non-editable">
															<?php for($i=0;$i<5;$i++)
															{ ?>
																	<i class="mdi mdi-star <?php if($i < $getSharePostContent['rating']){ ?>active<?php } ?>"></i>
															<?php }
															?>
															</div>
															<?php } ?>
														</div>
														<?php if($getSharePostContent['post_type'] == 'text' && (isset($getSharePostContent['trav_item']) && $getSharePostContent['trav_item']== '1')){
															$getSharePostContent['post_type'] = 'text and image';
														}?>
														<?php if(($getSharePostContent['post_type'] == 'image' || $getSharePostContent['post_type'] == 'text and image') &&  !in_array("is_coverpic", $getSharePostContent)) {
															$cnt = 1;
															$eximgs = explode(',',$getSharePostContent['image'],-1);
															if(isset($getSharePostContent['trav_item']) && $getSharePostContent['trav_item']== '1')
															{
																if($getSharePostContent['image'] == null)
																{
																	$eximgs[0] = '/uploads/travitem-default.png';
																}
																$eximgss[] = $eximgs[0];
																$eximgs = $eximgss;										
															}
															$totalimgs = count($eximgs);
															$imgcountcls="";
															if($totalimgs == '1'){$imgcountcls = 'one-img';}
															if($totalimgs == '2'){$imgcountcls = 'two-img';}
															if($totalimgs == '3'){$imgcountcls = 'three-img';}
															if($totalimgs == '4'){$imgcountcls = 'four-img';}
															if($totalimgs == '5'){$imgcountcls = 'five-img';}
															if($totalimgs > '5'){$imgcountcls = 'more-img';}
														?>
														<div class="post-img-holder">
															<div class="post-img <?= $imgcountcls?> gallery swipe-gallery">
																<?php
																foreach ($eximgs as $eximg) {
																if (file_exists('../web'.$eximg)) {
																$picsize = '';
																$val = getimagesize('../web'.$eximg);
																$iname = $this->getimagename($eximg);
																 $inameclass = $this->getimagefilename($eximg);
																 $pinit = PinImage::find()->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
																 if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
																$picsize .= $val[0] .'x'. $val[1] .', ';
																if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';} ?>
																	<a href="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-imgid="<?=$inameclass?>" data-size="1600x1600"  data-med="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-imgid="<?=$inameclass?>" data-med-size="1024x1024" data-author="Folkert Gorter" data-pinit="<?=$pinval?>" class="imgpin pimg-holder <?= $imgclass?>-box <?php if($cnt > 5){?>extraimg<?php } ?> <?php if($cnt ==5 && $totalimgs > 5){?>more-box<?php } ?>">
																		<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" class="<?= $imgclass?>"/>
																		<?php if($cnt == 5 && $totalimgs > 5){?>
																			<span class="moreimg-count"><i class="mdi mdi-plus"></i><?= $totalimgs - $cnt +1;?></span>
																		<?php } ?>
																	</a>
																<?php } $cnt++; } ?>
															</div>
														</div>
														<?php } ?>
														<?php if($getSharePostContent['post_type'] == 'image' && $getSharePostContent['is_coverpic'] == '1' && file_exists('uploads/cover/'.$getSharePostContent['image'])) { ?>
															<div class="post-img-holder">
																<div class="post-img one-img gallery swipe-gallery">
																<?php
																$eximg = '/uploads/cover/'.$getSharePostContent['image'];
																
																if (file_exists('../web'.$eximg)) {
																$picsize = '';
																$val = getimagesize('uploads/cover/'.$getSharePostContent['image']);
																$iname = $this->getimagename($eximg);
																 $inameclass = $this->getimagefilename($eximg);
																 $pinit = PinImage::find()->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
																 if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
																$picsize .= $val[0] .'x'. $val[1] .', ';
																if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}?>
																	<a href="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/uploads/cover/'.$getSharePostContent['image'] ?>" data-imgid="<?=$inameclass?>" data-size="1600x1600"  data-med="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/uploads/cover/'.$getSharePostContent['image'] ?>" data-med-size="1024x1024" data-author="Folkert Gorter" data-pinit="<?=$pinval?>" class="imgpin pimg-holder <?= $imgclass?>-box">
																		<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/uploads/cover/'.$getSharePostContent['image'] ?>" class="<?= $imgclass?>"/>
																	</a>
																	<?php } ?>
																</div>
															</div>
														<?php } ?>
														<?php if($getSharePostContent['post_type'] == 'profilepic' && file_exists('profile/'.$getSharePostContent['image'])) { ?>
															<div class="post-img-holder">
																<div class="post-img one-img gallery swipe-gallery">
																<?php
																 $eximg = '/profile/'.$getSharePostContent['image'];
																
																if (file_exists('../web'.$eximg)) {
																$picsize = '';
																$val = getimagesize('profile/'.$getSharePostContent['image']);
																$iname = $this->getimagename($eximg);
																 $inameclass = $this->getimagefilename($eximg);
																 $pinit = PinImage::find()->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
																 if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
																$picsize .= $val[0] .'x'. $val[1] .', ';
																if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}?>
																	
																	<a href="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/profile/'.$getSharePostContent['image'] ?>" data-imgid="<?=$inameclass?>" data-size="1600x1600"  data-med="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/profile/'.$getSharePostContent['image'] ?>" data-med-size="1024x1024" data-author="Folkert Gorter" data-pinit="<?=$pinval?>" class="imgpin pimg-holder <?= $imgclass?>-box">
																		<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/profile/'.$getSharePostContent['image'] ?>" class="<?= $imgclass?>"/>
																	</a>
																	<?php } ?>
																</div>
															</div>
														<?php } ?>
														<?php if($getSharePostContent['post_type'] == 'link'){ ?>
															<div class="pvideo-holder">
																<?php if($getSharePostContent['image'] != 'No Image'){ ?>
																	<div class="img-holder"><img src="<?= $getSharePostContent['image'] ?>"/></div>
																<div class="desc-holder">
																<?php } ?>
																	<h4><a href="<?= $getSharePostContent['post_text']?>" target="_blank"><?= $getSharePostContent['link_title'] ?></a></h4>
																	<p><?= $getSharePostContent['link_description'] ?></p>
																<?php if($getSharePostContent['image'] != 'No Image'){ ?>
																</div>
																<?php } ?>
															</div>
														<?php } ?>
													</div>
													<div class="clear"></div>
													
													<?php 
													if(!empty($taginfomatiom)) {
											            if(count($taginfomatiom) > 1) {
											            	if(count($taginfomatiom) > 2) {
											            		$label = (count($taginfomatiom) - 1).' Others';
											            	} else {
											            		$label = '1 Other';
											            	}
											                $tagstr =  "<span>&nbsp;with&nbsp;</span><span class='tagged_person_name compose_addpersonAction' id='compose_addpersonAction'>" . $nvTag[0] . "</span><span>&nbsp;and&nbsp;</span><a href='javascript:void(0)' class='pa-like sub-link livetooltip compose_addpersonAction' title='".$content."'>".$label."</a></span>";
											            } else {
											                $tagstr =  "<span>&nbsp;with&nbsp;</span><a href=".Url::to(['userwall/index', 'id' => $nkTag[0]]) ." class='sub-link compose_addpersonAction'>" . $nvTag[0] . "</a>";
											            }
											        }
													?>
													
													<div class="sharepost-info"><?=$tagstr?></div>
												<?php } ?>
											</div> 
										</div>
										<div class="show-fullpost-holder">
											<a href="javascript:void(0)" class="show-fullpost">Show All <span class="glyphicon glyphicon-arrow-down"></span></a>
										</div>
									</div>
									<?php } ?>

									<div class="location_parent">
										<label id="selectedlocation"  class="edit_selected_loc">
										<?php
											$editloc = isset($post['currentlocation']) ? $post['currentlocation'] : '';
											if(isset($editloc) && !empty($editloc)){
										?>
									    <div class='location_div' data-query="all" onfocus="filderMapLocationModal(this)">
											<span class='tagged_location'></span>
											<span class='tagged_location_name'><?=$editloc?></span>
										</div>
										<a href='javascript:void(0)'  class='removelocation'><i class='mdi mdi-close	'></i></a>
										</label>
									<?php								
										}
									?>
									  </label>
									</div>											
								</div>
				</div>
			
			</div>
			<div class="modal-footer">
				<div class="post-bcontent <?=$class?> <?php if((isset($post['is_page_review']) && !empty($post['is_page_review'])) || (isset($post['placetype']) && $post['placetype'] == 'reviews')){ ?> expanded expandReview<?php } ?>">
					<div class="footer_icon_container">
						<?php if($photoStop == 'no') { ?>
							<button class="comment_footer_icon waves-effect" id="compose_edituploadphotomodalAction">
								<i class="zmdi zmdi-camera"></i>
							</button>-
						<?php } ?>
						<?php if(empty($post['trav_item']) && $place_type != 'tip' && $place_type != 'ask'){?>
						<button class="comment_footer_icon compose_addpersonAction waves-effect" id="compose_addpersonAction">
							<i class="zmdi zmdi-account"></i>
						</button>
						<?php } ?>
						<?php if($is_place_data == 'no' && (!(isset($post['is_page_review']) && $post['is_page_review'] == '1'))) { ?>
						<button class="comment_footer_icon waves-effect" data-query="all" onfocus="filderMapLocationModal(this)">
							<i class="zmdi zmdi-pin"></i>
						</button>
						<?php } ?>
						<?php if(!(isset($post['shared_from']) && !empty($post['shared_from']))) { ?>
						<button class="comment_footer_icon compose_titleAction waves-effect" id="compose_edittitleAction">
							<img src="<?=$assetsPath?>addtitleBl.png">
						</button>
						<?php } ?>
					</div>
					<div class="public_dropdown_container_xs damagedropdown">
						<a class="dropdown_text dropdown-button editpostcreateprivacylabel" onclick="privacymodal(this)" href="javascript:void(0)" data-modeltag="editpostcreateprivacylabel" data-fetch="yes" data-label="editpost">
							<span id="post_privacy2" class="post_privacy_label"><?=$my_post_view_status?></span>
							<i class="zmdi zmdi-caret-up zmdi-hc-lg"></i>
						</a>

					</div>
					
					<div class="post-bholder">
						<input type="hidden" class="link_title_pid" />
						<input type="hidden" class="link_url" />
						<input type="hidden" class="link_description" />
						<input type="hidden" class="link_image" />
						<?php if(!(empty($post['trav_item']) && empty($post['is_trip']) && $from_place_page == 'no')){?>										
						<?php } ?>
						
						<div class="hidden_xs">
							<span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
							<a href="javascript:void(0)" class="btngen-center-align close_modal open_discard_modal waves-effect">cancel</a>
							<a href="javascript:void(0)" class="mainbtn postbtn  btngen-center-align waves-effect" onclick="edit_post('<?=$post['_id']?>');" type="button" name="post">Save</a>
						</div>
					</div>
				</div>
			</div>
		</div>
    <?php }
		}
		}
		else {
			return 'checkuserauthclassg';
		}	
}
    	
	public function actionReportPostPreSet()
	{
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
		$user = LoginForm::find()->where(['_id' => $userid])->one();
		$username = $user['fullname'];
        $postid = isset($_POST['reportpostid']) ? $_POST['reportpostid'] : '';
        $post = PostForm::find()->where(['_id' => $postid])->one();
		$page = Page::find()->where(['page_id' => (string) $postid])->one();
		$status = $session->get('status');
		  
		$assetsPath = '../../vendor/bower/travel/images/';
        ?>

			<div class="popup-title">
				<a class="popup-modal-dismiss close-popup" href="javascript:void(0)"><i class="mdi mdi-close"></i></a>
			</div>
			<div class="popup-content">
				<div class="new-post active">
					<form id="frm_edit_post" enctype="multipart/form-data">
							<div class="top-stuff">
								<div class="postuser-info">
										<div class="img-holder">
										<?php 
											$dpimg = $this->getimage($userid,'thumb');
										?>
										<img src="<?= $dpimg?>">
										</div>
										<div class="desc-holder"><a href="javascript:void(0)"><?= $username?></a></div>
								</div>
							</div>
							<div class="clear"></div>
							<div class="npost-content">
									<div class="post-mcontent">                                                     
											<div class="desc paddfix limit-tt more">
												<textarea class="desc" id="textInput" placeholder="<?php if($status != '10') { ?>Reason for reporting<?php } else { ?>Reason for Flaging<?php } ?>"></textarea>
											</div>
									</div>
									<div class="post-bcontent">
											<div class="post-bholder">
											<?php if($status != '10') { ?>
													<button class="btn btn-primary" onclick="reportpost('<?=$post['_id']?>');" type="button" name="post" >Report</button>
											<?php 
											} 
											else 
											{
												if(isset($post) && !empty($post)){
											?>
											<button class="btn btn-primary" onclick="flagPost('<?=$post['post_user_id']?>','<?=$post['_id']?>');" type="button" name="post" >Flag Post</button>
											<?php	
											} else if (isset($page) && !empty($page)) { ?>
												<button class="btn btn-primary" onclick="flag_page('<?=$page['page_id']?>','<?=$page['created_by']?>','<?=$userid?>');" type="button" name="post" >Flag Page</button>
												<?php	
												}
												else 
												{
													?>
													Yes
													<?php
												}	
											}
											?>	
										</div>
									</div>
							</div>
					</form>
				</div>
            </div>
        <?php 
    }
	
	public function actionSharePostPreSet()
	{
		$time = time();
	    $pid = $_POST['pid'];
	    $session = Yii::$app->session;
	    $userid = $user_id = (string)$session->get('user_id');
	      
		$assetsPath = '../../vendor/bower/travel/images/';
	    $tagstr = '';
		if(isset($userid) && $userid != '') {
			$authstatus = UserForm::isUserExistByUid($userid);
			if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
				$data['auth'] = $authstatus;
				return $authstatus;
			} 
			else {
				$baseUrl = '/iaminjapan-code/frontend/web/assets/3079b4a8';
				$fullname = $this->getuserdata($user_id,'fullname');
			    if($_SERVER['HTTP_HOST'] == 'localhost')
			    {
			        $baseUrl = '/iaminjapan-code/frontend/web';
			    }
			    else
			    {
			        $baseUrl = '/frontend/web/assets/baf1a2d0';
			    }
				$baseUrl2 = '/iaminjapan-code/frontend/web/assets/3079b4a8';

			    if(strstr($pid,'page_'))
			    {
			        $type = 'page';
			        $dpforpopup = $this->getimage($userid,'thumb');
			        $post['user']['fname'] = $this->getuserdata($userid,'fname');
			        $post['user']['lname'] = $this->getuserdata($userid,'lname');
			        $ptitle = $pt = $post['post_tags'] = $post['currentlocation'] = null;
			        $my_post_view_status = 'Public';
			        $post_dropdown_class = 'globe';
			        $post['_id'] = $pid;
			        $page_id = substr($pid,5);
			        $sharefunction = "shareEntity('page')";
			    }
			    else if(strstr($pid,'trip_'))
			    {
			        $type = 'trip';
			        $dpforpopup = $this->getimage($userid,'thumb');
			        $post['user']['fname'] = $this->getuserdata($userid,'fname');
			        $post['user']['lname'] = $this->getuserdata($userid,'lname');
			        $ptitle = $pt = $post['post_tags'] = $post['currentlocation'] = null;
			        $my_post_view_status = 'Public';
			        $post_dropdown_class = 'globe';
			        $post['_id'] = $pid;
			        $trip_id = substr($pid,5);
			        $sharefunction = "shareEntity('trip')";
			    }
			    else
			    {
			        $type = '';
			        $parent = PostForm::find()->where(['_id' => $pid])->one();
			        if(isset($parent['parent_post_id']) && !empty($parent['parent_post_id']))
			        {
			            $pid = $parent['parent_post_id'];
			        }
			        else
			        {
			            $pid = $_POST['pid'];
			        }
			        $post = PostForm::find()->where(['_id' => $pid])->one();
			        if($post['user']['status']=='44')
			        {
			            $dpforpopup = $this->getpageimage($post['user']['_id']);
			        }
			        else
			        {
			            $dpforpopup = $this->getimage($post['user']['_id'],'thumb');
			        }
			        $my_post_view_status = $post['post_privacy'];
			        
			        if($my_post_view_status == 'Private') {$post_dropdown_class = 'lock';}
		            else if($my_post_view_status == 'Connections') {$post_dropdown_class = 'account';}
		            else if($my_post_view_status == 'Custom') {$post_dropdown_class = 'settings';}
		            else {$post_dropdown_class = 'earth';}

			        $ptitle = $post['post_title'];
			        if(isset($ptitle) && !empty($ptitle)) { $ptitle = $ptitle;} else {$ptitle = 'Post title';}
			        $pt = $post['post_text'];
			        if(isset($pt) && !empty($pt)) { $pt = $pt;} else {$pt = 'Post Description';}
			        $sharefunction = 'sharePost()';
					
					$posttag = '';
				    
				    if(isset($post['post_tags']) && !empty($post['post_tags'])) {
				        $posttag = explode(",", $post['post_tags']);
				    }
					
					$taginfomatiom = ArrayHelper::map(UserForm::find()->where(['IN', '_id',  $posttag])->all(), 'fullname', (string)'_id');

					$nkTag = array();
					$nvTag = array();

					$i=1;
					foreach ($taginfomatiom as $key => $value) {
						$nkTag[] = (string)$value; 
						$nvTag[] = $key;
						if($i != 1) {
							$content[] = $key;
						}
						$i++;
					}

					if(isset($content) && !empty($content)) {
						$content = implode("<br/>", $content); 
					}
			    }
			    ?>
			    <div class="hidden_header">
					<div class="content_header">
						<button class="close_span cancel_poup waves-effect">
							<i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
						</button>
						<p class="modal_header_xs">Share post</p>
						<a type="button" class="post_btn action_btn active_post_btn post_btn_xs sharebtn close_modal waves-effect" onclick="<?=$sharefunction?>" id="share_post_<?=$post['_id']?>">Share</a>
					</div>
				</div>
			    <div class="modal-content">
					<div class="new-post active">
						<div class="top-stuff">
							<div class="postuser-info">
								<div class="img-holder"><img src="<?= $dpforpopup?>"/></div>
								<div class="desc-holder">
									<span class="profile_name share_profile_name"><?=$fullname?></span>
									<label id="tag_person"></label>
									<div class="public_dropdown_container damagedropdown">
										<a class="dropdown_text dropdown-button-left sharepostcreateprivacylabel" onclick="privacymodal(this)" href="javascript:void(0)" data-modeltag="sharepostcreateprivacylabel" data-fetch="no" data-label="sharepost">
											<span id="post_privacy" class="post_privacy_label active_check">
											<?= $my_post_view_status ?>
											</span>
											<i class="zmdi zmdi-caret-down"></i>
										</a>
									</div>
								
								</div>
							</div>							
							<div class="settings-icon comment_setting_icon">
								<a class="dropdown-button " href="javascript:void(0)" data-activates="sharepost_settings">
									<i class="zmdi zmdi-more"></i>
								</a>
								<ul id="sharepost_settings" class="dropdown-content custom_dropdown">
									<li class="disable_share">
										<a href="javascript:void(0)">
											<input type="checkbox" class="toolbox_disable_sharing" id="<?=$time?>toolbox_disable_sharing" />
											<label for="<?=$time?>toolbox_disable_sharing">Disable Sharing</label>
										</a>
									</li>
									<li class="disable_comment">
										<a href="javascript:void(0)">
											<input type="checkbox" class="toolbox_disable_comments" id="<?=$time?>toolbox_disable_comments" />
											<label for="<?=$time?>toolbox_disable_comments">Disable Comments</label>
										</a>
									</li>
								</ul>
							</div>
							<div class="sharing-option share_dropdown_container">
								<?php if(!strstr($pid,'page_')){ ?>
									<a class="share_post_text dropdown-button" href="javascript:void(0)" data-activates="share_options">
										<span>
											Share on your wall
										</span>
										<i class="zmdi zmdi-caret-down"></i>
									</a>
									<ul id="share_options" class="dropdown-content custom_dropdown share_post_dropdown share_post_new">
										<li>
											<a href="javascript:void(0)" class="share-privacy">
											  Share on your wall
											</a>
										</li>
										<li>
											<a href="javascript:void(0)" class="share-to-connections share-privacy compose_addpersonActionShareWith">
											  Share on a connect's wall
											</a>
										</li>
										<li>
											<a href="javascript:void(0)" class="share-as-message share-privacy">
											  Share via message
											</a>
										</li>
										<li>
											<a href="javascript:void(0)" onclick="tbpostonfb('<?=$pid?>','<?=$ptitle?>','<?=$pt?>')" class="share-privacy">
											  Share on Facebook
											</a>
										</li>
										<input type="hidden" id="sharewall" value="own_wall"/>
										<input type="hidden" name="share_setting" id="share_setting" value="Enable"/>
										<input type="hidden" name="comment_setting" id="comment_setting" value="Enable"/>
									</ul>
								<?php } ?>
								
							</div>
							 
						</div>
						<div class="npost-content">                     
							<div class="share-connections">
								<span class="title">Share with :</span>
								<div class="input-holder"></div>
							</div>
							<div class="share-message">
								<span class="title">Receipent:</span>
								<div class="input-holder">
									<div class="sliding-middle-out anim-area underlined">
										<select class="userselect2" id="frndid"></select>
									</div>
								</div>
							</div>
							<div class="post-mcontent">                         
								<div class="desc post_comment_box">								
									<textarea id="share_desc" placeholder="Say something about this..." class="materialize-textarea comment_textarea"></textarea>
								</div>
								<div class="org-post mt-0">                          
									<div class="post-list">							
										<div class="post-holder <?php if(strstr($pid,'trip_')){?>tripexperince-post<?php } ?>"> 
											<?php if(strstr($pid,'page_')){
												$dp = $this->getpageimage($page_id);
												$page_details = Page::Pagedetails($page_id);
												$pagelike = Like::getLikeCount($page_id);
												$pagecover = $this->getcoverpicforpage($page_id,'cover_photo');
												if(isset($pagecover) && !empty($pagecover))
												{
													$cover_photo = "uploads/cover/".$pagecover;
												}
												else
												{
													$cover_photo = $assetsPath."wallbanner.jpg";
												}
											?>
											<input type="hidden" value="page_<?=$page_id?>" id="spid" name="spid"/>
											<div class="post-content share-feedpage">
												<div class="shared-box shared-category">										
													<div class="post-img-holder">
														<div class="post-img">
															<div class="pimg-holder">
																<div class="bannerimg" style="background:url('<?=$cover_photo?>') center top no-repeat;background-size:cover;"></div>
																<div class="profileimg"><img src="<?=$dp?>"/></div>
															</div>
														</div>
													</div>
													<div class="share-summery">											
														<div class="sharedpost-title">
															<a href="<?=Url::to(['page/index', 'id' => "$page_id"])?>"><?=$page_details['page_name']?></a>
														</div>
														<div class="sharedpost-tagline"><?=$page_details['short_desc']?></div>
														<div class="sharedpost-subtitle"><?=$page_details['category']?></div>
														<div class="sharedpost-desc"><?=$pagelike?> people liked this</div>
													</div>											
												</div>
											</div>
											<?php } else if(strstr($pid,'trip_')){
												$trip = Trip::getTripDetails($trip_id);
												$trip_name = $trip['trip_name'];
												$trip_summary = $trip['trip_summary'];
												$trip_stops = explode('**',$trip['end_to'],-1);
											?>
											<input type="hidden" value="trip_<?=$trip_id?>" id="spid" name="spid"/>
											<div class="post-content share-feedpage">													
												<div class="post-details">
													<div class="post-title"><?=$trip_name?></div>
													<?php if(isset($trip_summary) && !empty($trip_summary)){ ?>
													<div class="post-desc">
														<?php if(strlen($trip_summary)>187){ ?>
															<div class="para-section">
																<div class="para">
																	<p><?=$trip_summary?></p>
																</div>
																<a href="javascript:void(0)" class="readlink">Read More</a>
															</div>
														<?php }else{ ?>										
															<p><?=$trip_summary?></p>
														<?php } ?>
														
													</div>
													<?php } ?>
												</div>
												<div class="trip-summery">
													<div class="route-holder">
														<label>Stops :</label>
														<ul class="triproute">
															<?php foreach ($trip_stops as $name) { ?>
															<li><?=$name?></li>
															<?php } ?>
														</ul>
													</div>
													<div class="location-info">
														<h5><i class="zmdi zmdi-pin"></i> Trip Route</h5>
														<i class="mdi mdi-menu-right"></i>
														<a href="javascript:void(0)" onclick="openViewMap(this,'<?=$trip_id?>')">View on map</a>
													</div>											
												</div>
												<div class="map-holder" id="trip-map-share-<?=$trip_id?>"></div>
											</div>
											<?php } else if((isset($post['is_trip']) && !empty($post['is_trip']))) {
											$time = Yii::$app->EphocTime->time_elapsed_A(time(),$post['post_created_date']);
											$dpimg = $this->getimage($post['user']['_id'],'photo');
											$id =  $post['user']['_id'];
											?>	
												<div class="post-topbar">
													<div class="post-userinfo">
							
														<div class="img-holder">
															<div id="profiletip-1" class="profiletipholder">
																<span class="profile-tooltip tooltipstered">
																	<img class="circle" src="<?= $dpimg;?>">
																</span>
																
															</div>
															
														</div>
														<div class="desc-holder">
															<span>By </span><a href="<?=Url::to(['userwall/index', 'id' => "$id"])?>"><?=ucfirst($post['user']['fname']).' '.ucfirst($post['user']['lname'])?></a>
															
															<span class="timestamp"><?= $time;?><span class="glyphicon glyphicon-globe"></span></span>
														</div>
													</div>										
												</div>
												
												<div class="post-content tripexperince-post share-feedpage">
													<div class="pdetail-holder">
														<?php if($post['post_title'] != null) { ?>
														<div class="post-details">
															<div class="post-title">
																<?= $post['post_title'] ?>
															</div>												
														</div>
														<?php } ?>
														<div class="trip-summery">
															<div class="location-info">
																<h5><i class="zmdi zmdi-pin"></i> <?= $post['currentlocation'];?></h5>
																<i class="mdi mdi-menu-right"></i>
																<a href="javascript:void(0)" onclick="openViewMap(this)">View on map</a>
															</div>											
														</div>
														<div class="map-holder dis-none">
															<iframe width="600" height="450" frameborder="0" src="https://maps.google.it/maps?q=<?= $post['currentlocation'];?>&output=embed"></iframe>
														</div>
														<a class="overlay-link postdetail-popup popup-modal" data-postid="<?=$post['_id']?>" href="javascript:void(0)">&nbsp;</a>
													</div>
													<?php if($post['post_type'] == 'text' && $post['trav_item']== '1'){
														$post['post_type'] = 'text and image';
													}?>
													<?php if(($post['post_type'] == 'image' || $post['post_type'] == 'text and image') && $post['is_coverpic'] == null) {
														$cnt = 1;
														$eximgs = explode(',',$post['image'],-1);
														if(isset($post['trav_item']) && $post['trav_item']== '1')
														{
															if($post['image'] == null)
															{
																$eximgs[0] = '/uploads/travitem-default.png';
															}
															$eximgss[] = $eximgs[0];
															$eximgs = $eximgss;										
														}
														$totalimgs = count($eximgs);
														$imgcountcls="";
														if($totalimgs == '1'){$imgcountcls = 'one-img';}
														if($totalimgs == '2'){$imgcountcls = 'two-img';}
														if($totalimgs == '3'){$imgcountcls = 'three-img';}
														if($totalimgs == '4'){$imgcountcls = 'four-img';}
														if($totalimgs == '5'){$imgcountcls = 'five-img';}
														if($totalimgs > '5'){$imgcountcls = 'more-img';}
													?>
													<div class="post-img-holder">
														<div class="post-img <?= $imgcountcls?> gallery swipe-gallery">
															<?php
															foreach ($eximgs as $eximg) {
															if (file_exists('../web'.$eximg)) {
															$picsize = '';
															$val = getimagesize('../web'.$eximg);
															$iname = $this->getimagename($eximg);
															 $inameclass = $this->getimagefilename($eximg);
															 $pinit = PinImage::find()->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
															 if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
															 
															
															$picsize .= $val[0] .'x'. $val[1] .', ';
															if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';} ?>
															
															<a href="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-imgid="<?=$inameclass?>" data-size="1600x1600"  data-med="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-med-size="1024x1024" data-author="Folkert Gorter" data-pinit="<?=$pinval?>" class="imgpin pimg-holder <?= $imgclass?>-box <?php if($cnt > 5){?>extraimg<?php } ?> <?php if($cnt ==5 && $totalimgs > 5){?>more-box<?php } ?>">
																<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" class="<?= $imgclass?>"/>
																<?php if($cnt == 5 && $totalimgs > 5){?>
																	<span class="moreimg-count"><i class="mdi mdi-plus"></i><?= $totalimgs - $cnt +1;?></span>
																<?php } ?>
															</a>
															<?php } $cnt++; } ?>
														</div>
													</div>
													<?php } ?>
													<div class="pdetail-holder">
														<div class="post-details">		
															<div class="post-desc">
																<?php if(strlen($post['post_text'])>187){ ?>
																	<div class="para-section">
																		<div class="para">
																			<p><?= $post['post_text'] ?></p>
																		</div>
																		<a href="javascript:void(0)" class="readlink">Read More</a>
																	</div>
																<?php }else{ ?>										
																	<p><?= $post['post_text'] ?></p>
																<?php } ?>
																
															</div>
														</div>
														<a class="overlay-link postdetail-popup popup-modal" data-postid="<?=$post['_id']?>" href="javascript:void(0)">&nbsp;</a>
													</div>												
												</div>
												
												<!-- end shared trip experience -->
										
												<input type="hidden" value="<?=$post['_id']?>" id="spid" name="spid"/>
											<?php 
											} else { 
												if(isset($post['is_page_review']) && $post['is_page_review'] == '1') { 
													if(!(isset($post['image']) && $post['image'] != '')) { 
															$cls = 'review-share';
														} 
												} else if(isset($post['trav_item']) && $post['trav_item'] == '1') {
													$cls = 'travelstore-ad';
												} else { 
													if(isset($post['image']) && !empty($post['image'])) {
														$cls = 'share-feedpage';
													} else { 
														$cls = 'review-share'; 
													} 
												}

												?>
												<div class="post-content <?=$cls?>">
													<input type="hidden" value="<?=$post['_id']?>" id="spid" name="spid"/>
													<div class="post-details">
														<?php if($post['post_title'] != null) { ?>
														<div class="post-title"><?= $post['post_title'] ?></div>
														<?php } ?>
														<?php if(isset($post['trav_price']) && $post['trav_price'] != null) { ?>
															<div class="post-price" style="display:block;">$<?= $post['trav_price'] ?></div>
														<?php } ?>
														<?php if((isset($post['trav_item']) && !empty($post['trav_item']) && $post['currentlocation'] != null)){ ?>
															<div class="post-location" style="display:block"><i class="zmdi zmdi-pin"></i><?= $post['currentlocation'] ?></div>
														<?php } ?>
														<?php if($post['post_type'] != 'link' && $post['post_type'] != 'profilepic'){ ?>
														<div class="post-desc">
															<?php if(strlen($post['post_text'])>187){ ?>
																<div class="para-section">
																	<div class="para">
																		<p><?= $post['post_text'] ?></p>
																	</div>
																	<a href="javascript:void(0)" class="readlink">Read More</a>
																</div>
															<?php }else{ ?>										
																<p><?= $post['post_text'] ?></p>
															<?php } ?>														
														</div>
														<?php } ?>
														<?php if(isset($post['is_page_review']) && !empty($post['is_page_review'])){ ?>
														<div class="rating-stars non-editable">
														<?php for($i=0;$i<5;$i++)
														{ ?>
																<i class="mdi mdi-star <?php if($i < $post['rating']){ ?>active<?php } ?>"></i>
														<?php }
														?>
														</div>
														<?php } ?>
													</div>
													<?php if($post['post_type'] == 'text' && $post['trav_item']== '1'){
														$post['post_type'] = 'text and image';
													}?>
													<?php if(($post['post_type'] == 'image' || $post['post_type'] == 'text and image') &&  (!isset($post['is_coverpic']))) {
														$cnt = 1;
														$eximgs = explode(',',$post['image'],-1);
														if(isset($post['trav_item']) && $post['trav_item']== '1')
														{
															if($post['image'] == null)
															{
																$eximgs[0] = '/uploads/travitem-default.png';
															}
															$eximgss[] = $eximgs[0];
															$eximgs = $eximgss;										
														}
														$totalimgs = count($eximgs);
														$imgcountcls="";
														if($totalimgs == '1'){$imgcountcls = 'one-img';}
														if($totalimgs == '2'){$imgcountcls = 'two-img';}
														if($totalimgs == '3'){$imgcountcls = 'three-img';}
														if($totalimgs == '4'){$imgcountcls = 'four-img';}
														if($totalimgs == '5'){$imgcountcls = 'five-img';}
														if($totalimgs > '5'){$imgcountcls = 'more-img';}
													?>
													<div class="post-img-holder">
														<div class="post-img <?= $imgcountcls?> gallery swipe-gallery">
															<?php
															foreach ($eximgs as $eximg) {
															if (file_exists('../web'.$eximg)) {
															$picsize = '';
															$val = getimagesize('../web'.$eximg);
															$iname = $this->getimagename($eximg);
															 $inameclass = $this->getimagefilename($eximg);
															 $pinit = PinImage::find()->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
															 if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
															$picsize .= $val[0] .'x'. $val[1] .', ';
															if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';} ?>
																<a href="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-imgid="<?=$inameclass?>" data-size="1600x1600"  data-med="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-imgid="<?=$inameclass?>" data-med-size="1024x1024" data-author="Folkert Gorter" data-pinit="<?=$pinval?>" class="imgpin pimg-holder <?= $imgclass?>-box <?php if($cnt > 5){?>extraimg<?php } ?> <?php if($cnt ==5 && $totalimgs > 5){?>more-box<?php } ?>">
																	<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" class="<?= $imgclass?>"/>
																	<?php if($cnt == 5 && $totalimgs > 5){?>
																		<span class="moreimg-count"><i class="mdi mdi-plus"></i><?= $totalimgs - $cnt +1;?></span>
																	<?php } ?>
																</a>
															<?php } $cnt++; } ?>
														</div>
													</div>
													<?php } ?>
													<?php if($post['post_type'] == 'image' && $post['is_coverpic'] == '1' && file_exists('uploads/cover/'.$post['image'])) { ?>
														<div class="post-img-holder">
															<div class="post-img one-img gallery swipe-gallery">
															<?php
															$eximg = '/uploads/cover/'.$post['image'];
															
															if (file_exists('../web'.$eximg)) {
															$picsize = '';
															$val = getimagesize('uploads/cover/'.$post['image']);
															$iname = $this->getimagename($eximg);
															 $inameclass = $this->getimagefilename($eximg);
															 $pinit = PinImage::find()->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
															 if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
															$picsize .= $val[0] .'x'. $val[1] .', ';
															if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}?>
																<a href="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/uploads/cover/'.$post['image'] ?>" data-imgid="<?=$inameclass?>" data-size="1600x1600"  data-med="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/uploads/cover/'.$post['image'] ?>" data-med-size="1024x1024" data-author="Folkert Gorter" data-pinit="<?=$pinval?>" class="imgpin pimg-holder <?= $imgclass?>-box">
																	<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/uploads/cover/'.$post['image'] ?>" class="<?= $imgclass?>"/>
																</a>
																<?php } ?>
															</div>
														</div>
													<?php } ?>
													<?php if($post['post_type'] == 'profilepic' && file_exists('profile/'.$post['image'])) { ?>
														<div class="post-img-holder">
															<div class="post-img one-img gallery swipe-gallery">
															<?php
															 $eximg = '/profile/'.$post['image'];
															
															if (file_exists('../web'.$eximg)) {
															$picsize = '';
															$val = getimagesize('profile/'.$post['image']);
															$iname = $this->getimagename($eximg);
															 $inameclass = $this->getimagefilename($eximg);
															 $pinit = PinImage::find()->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
															 if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
															$picsize .= $val[0] .'x'. $val[1] .', ';
															if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}?>
																
																<a href="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/profile/'.$post['image'] ?>" data-imgid="<?=$inameclass?>" data-size="1600x1600"  data-med="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/profile/'.$post['image'] ?>" data-med-size="1024x1024" data-author="Folkert Gorter" data-pinit="<?=$pinval?>" class="imgpin pimg-holder <?= $imgclass?>-box">
																	<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/profile/'.$post['image'] ?>" class="<?= $imgclass?>"/>
																</a>
																<?php } ?>
															</div>
														</div>
													<?php } ?>
													<?php if($post['post_type'] == 'link'){ ?>
														<div class="pvideo-holder">
															<?php if($post['image'] != 'No Image'){ ?>
																<div class="img-holder"><img src="<?= $post['image'] ?>"/></div>
															<div class="desc-holder">
															<?php } ?>
																<h4><a href="<?= $post['post_text']?>" target="_blank"><?= $post['link_title'] ?></a></h4>
																<p><?= $post['link_description'] ?></p>
															<?php if($post['image'] != 'No Image'){ ?>
															</div>
															<?php } ?>
														</div>
													<?php } ?>
												</div>
												<div class="clear"></div>
												
												<?php 
												if(!empty($taginfomatiom)) {
										            if(count($taginfomatiom) > 1) {
										            	if(count($taginfomatiom) > 2) {
										            		$label = (count($taginfomatiom) - 1).' Others';
										            	} else {
										            		$label = '1 Other';
										            	}
										                $tagstr =  "<span>&nbsp;with&nbsp;</span><span class='tagged_person_name compose_addpersonAction' id='compose_addpersonAction'>" . $nvTag[0] . "</span><span>&nbsp;and&nbsp;</span><a href='javascript:void(0)' class='pa-like sub-link livetooltip compose_addpersonAction' title='".$content."'>".$label."</a></span>";
										            } else {
										                $tagstr =  "<span>&nbsp;with&nbsp;</span><a href=".Url::to(['userwall/index', 'id' => $nkTag[0]]) ." class='sub-link compose_addpersonAction'>" . $nvTag[0] . "</a>";
										            }
										        }
												?>
												
												<div class="sharepost-info"><?=$tagstr?></div>
											<?php } ?>
										</div> 
									</div>
									<div class="show-fullpost-holder">
										<a href="javascript:void(0)" class="show-fullpost">Show All <span class="glyphicon glyphicon-arrow-down"></span></a>
									</div>
								</div>							
								<div class="location_parent">
									<label id="selectedlocation" class="share_selected_loc"></label>
								</div>
							</div>
						</div>
					</div>
			    </div>
				<div class="modal-footer">			
					<div class="post-bcontent">
						<div class="footer_icon_container">
							<button class="comment_footer_icon compose_addpersonAction waves-effect" id="compose_addpersonAction">
								<i class="zmdi zmdi-hc-lg zmdi-account"></i>
							</button>
							<button class="comment_footer_icon waves-effect" data-query="all" onfocus="filderMapLocationModal(this)">
								<i class="zmdi zmdi-hc-lg zmdi-pin"></i>
							</button>
						</div>
						<div class="public_dropdown_container_xs damagedropdown">
							<a class="dropdown_text dropdown-button-left sharepostcreateprivacylabel" onclick="privacymodal(this)" href="javascript:void(0)" data-modeltag="sharepostcreateprivacylabel" data-fetch="no" data-label="sharepost">
								<span id="post_privacy" class="post_privacy_label active_check">
								<?= $my_post_view_status ?>
								</span>
								<i class="zmdi zmdi-caret-down"></i>
							</a>
						</div>
						<div class="post-bholder">
							<div class="hidden_xs">
								<span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
								<a href="javascript:void(0)" class="btngen-center-align close_modal open_discard_modal waves-effect">cancel</a>
								<a href="javascript:void(0)" class="mainbtn btngen-center-align waves-effect" onclick="<?=$sharefunction?>" id="share_post_<?=$post['_id']?>">Share</a>
							</div>
						</div>
					</div>
			    </div>
				<?php
			}
		}
		else {
	    	return 'checkuserauthclassg';
	    }
	}

    public function actionShareentity() 
    {
        $session = Yii::$app->session;
        $user_id = (string) $session->get('user_id');
        if(isset($_POST['spid']) && !empty($_POST['spid']) && isset($user_id) && !empty($user_id))
        {
			$spid = $_POST['spid'];
			$col_id = '';
			$date = time();
            $sharepost = new PostForm();
            $data = array();
            if(isset($_POST['current_location']) && !empty($_POST['current_location']))
            {
                $currentlocation = $_POST['current_location'];
            }
            else
            {
                $currentlocation = '';
            }
			
            if (!empty($_POST['desc'])) 
            {
                $sharepost->post_text = ucfirst($_POST['desc']);
            }
            else 
            {
                $sharepost->post_text = '';
            }
            $sharepost->post_status = '1';
            $sharepost->post_created_date = "$date";
            $sharepost->post_tags = (isset($_POST['posttags']) && !empty($_POST['posttags'])) ? $_POST['posttags'] : '';
            if ($_POST['sharewall'] == 'own_wall' || empty($_POST['frndid'])) 
            {
                $puser = $user_id;
            }
            else 
            {
                $puser = $_POST['frndid'];
            }
            if(isset($_POST['post_privacy']) && !empty($_POST['post_privacy']))
            {
                $postprivacy = $_POST['post_privacy'];
            }
            else
            {
                $postprivacy = 'Public';
            }
            $sharepost->post_user_id = $puser;
            $sharepost->currentlocation = $currentlocation;
            $sharepost->post_privacy = $postprivacy;
            $posttags = $_POST['posttags'];
            if($posttags != 'null')
            {
                $gsu_id = $puser;
                $sec_result_set = SecuritySetting::find()->where(['user_id' => "$gsu_id"])->one();
                if ($sec_result_set)
                {
                    $tag_review_setting = $sec_result_set['review_tags'];
                }
                else
                {
                    $tag_review_setting = 'Disabled';
                }
                if($tag_review_setting == "Enabled")
                {
                    $review_tags = "1";
                }
                else
                {
                    $review_tags = "0";
                }
            }
            else
            {
                $review_tags = "0";
            }
            $sharepost->post_ip = $_SERVER['REMOTE_ADDR'];
            $sharepost->parent_post_id = $_POST['spid'];
            $sharepost->is_timeline = '1';
            $sharepost->is_deleted = $review_tags;
            $sharepost->shared_by = $user_id;
            $sharepost->shared_from = 'shareentity';
            $sharepost->share_setting = 'Disable';
            $sharepost->comment_setting = 'Enable';
            if($sharepost->insert())
            {
				
				$notification =  new Notification();
				$notification->user_id = "$user_id";
				$notification->is_deleted = '0';
				$notification->status = '1';
				$notification->created_date = "$date";
				$notification->updated_date = "$date";
				$notification->insert();
				
            }
        }
        else
        {
            //return false;
        }
    }

    public function actionSuggestconnectlist()
    {
        $session = Yii::$app->session;
        $suserid = $user_id = (string)$session->get('user_id');
        $fid = (string)$_POST['fid'];
        $vals = Connect::getuserConnections($user_id);
        $z = 0;
          
		$assetsPath = '../../vendor/bower/travel/images/';
        ?>
    	<div class="content-box bshadow">
			<div class="cbox-title">						
				Suggest connections
				<button class="modal_discard">
				  <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
				</button>
			</div> 
			<div class="cbox-desc">
				<div class="connections-grid">
					<div class="row">
			            <div class="popup-content">
			                <div class="content-holder nsidepad nbpad">
			                    <div class="suggest-connections">
			                     <div class="cbox-desc"> 
			                            <div class="connections-grid">
			                                <div class="row suggest_connect">
			                                <?php foreach ($vals as $val) {
			                                    $data = array();
			                                    $guserid = (string)$val['userdata']['_id'];
			                                    if($guserid == $fid) {
			                                    	continue;
			                                    }
			                                    $suggestdp = $this->getimage($guserid,'photo');
			                                    $link = Url::to(['userwall/index', 'id' => $guserid]);
			                                    $is_connect = Connect::find()->where(['from_id' => $fid,'to_id' => $suserid,'status' => '1'])->one();

			                                    if(empty($is_connect)) {
			                                    	continue;
			                                    }

			                                    $frnd_img = $this->getimage($guserid, 'thumb');
			                                    $isverify = Verify::isVerify($guserid);
			                                    $fullname = $val['userdata']['fullname'];
			                                    $city = $val['userdata']['city'];
			                                    
			                                    $ctr = Connect::mutualconnectcount($guserid);
			                                    $result_security = SecuritySetting::find()->where(['user_id' => $guserid])->asarray()->one();
			                                    $connect_list = isset($result_security['connect_list']) ? $result_security['connect_list'] : '';
                								$mutualLabel =  '';
			                                    $totalconnections = Connect::find()->where(['to_id' => (string)$guserid, 'status' => '1'])->count();
								                if($connect_list == 'Public') {
								                    if($totalconnections>1) {
								                        $mutualLabel = $totalconnections .' Connections';
								                    } else if($totalconnections == 1) {
								                        $mutualLabel = '1 Connect';
								                    }
								                } else if($connect_list == 'Private') {
								                    if($ctr >0) {
								                        $mutualLabel =  $ctr.' Mutual Connections';
								                    }
								                } else if($connect_list == 'Connections') {
								                    if(!empty($isconnect)) {
								                        if($totalconnections>1) {
								                            $mutualLabel = $totalconnections .' Connections';
								                        } else if($totalconnections == 1) {
								                            $mutualLabel = '1 Connect';
								                        }   
								                    } else {
								                        if($ctr >0) {
								                            $mutualLabel =  $ctr.' Mutual Connections';
								                        }   
								                    }
								                }

								                $isSuggested = SuggestConnect::find()->where(['user_id' => $user_id, 'connect_id' => $guserid, 'suggest_to' => $fid])->one();
								                	$id = $guserid;
			                                		?>
													<div class="grid-box" id="request_<?=$guserid?>">
														<input type="hidden" name="to_id" id="to_id" value="<?=$guserid?>">
														<div class="connect-box">
															<div class="imgholder">
																<img src="<?= $frnd_img?>"/>
																<?php if($isverify) { ?>
																<span class="online-mark"><i class="zmdi zmdi-check"></i></span>
																<?php } ?>
															</div>
															<div class="descholder">
																<a href="<?=Url::to(['userwall/index', 'id' => "$id"])?>" class="userlink">
																	<span><?=$fullname?></span>
																</a>
																<span class="info"><?=$city?></span>
																<span class="info mutual"><?=$mutualLabel?></span>
																<div class="btn-area travconnections_<?=$guserid?>">
																	<?php if(empty($isSuggested)) { ?>
																	<a class="btn btn-primary btn-sm" href="javascript:void(0)" id="send_<?=$guserid?>" onclick="suggestConnect('<?=$guserid?>', '<?=$fid?>')">Suggest</a>
																	<span class="btn btn-primary btn-sm btn-gray" style="display: none;" id="done_<?=$guserid?>"></span>
																	<?php } else { ?>
																	<span class="btn btn-primary btn-sm btn-gray" id="done_<?=$guserid?>">Suggested</span>
																	<?php } ?>
																</div>
																<span class="requestsent acceptmsg_<?=$guserid?>" class="request-accept dis-none"></span>
															</div>																					
														</div>
													</div>
			                                	<?php 
			                            		
			                            	} ?>
			                                </div>
			                            </div>
			                        </div>
			                    </div>
			                </div>
			            </div>
			        </div>
			    </div>
			</div>
		</div>
		<?php
    }
        
    public function actionSuggestconnect()
    {
		if (isset($_POST['connectid']) && !empty($_POST['connectid']) && isset($_POST['suggestto']) && !empty($_POST['suggestto']))
		{
            $fid = (string) $_POST['connectid'];
            $sto = (string) $_POST['suggestto'];
            $data = array();
			$session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
			
			$suggest = new SuggestConnect();
            $suggestexist = SuggestConnect::find()->where(['user_id' => $user_id,'connect_id' => $fid,'suggest_to' => $sto,'status' => '0'])->one();
            if (!$suggestexist)
            {
                $date = time();
                $suggest->user_id = $user_id;
                $suggest->connect_id = $fid;
                $suggest->suggest_to = $sto;
                $suggest->created_at = "$date";
                $suggest->status = '0';
                $suggest->insert();
                $data['status'] = '1';
                $data['msg'] = 'Suggested';
            }
            else
            {
                $data['status'] = '2';
                $data['msg'] = 'Suggested';
            }
        }
        else
        {
            $data['status'] = '0';
            $data['msg'] = 'Suggetion failed';
		}
        return json_encode($data);
    }
	
	public function actionRemoveProfilePicture()
	{
		$session = Yii::$app->session;
		$suserid = (string)$session->get('user_id');
		if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
		{
			$url = $_SERVER['HTTP_REFERER'];
			$urls = explode('&',$url);
			$url = explode('=',$urls[1]);
			$suserid = $url[1];
		}
		if($suserid != '')
		{
			$update = LoginForm::find()->where(['_id' =>$suserid])->one();
			$update->photo = null;
			$update->thumbnail = null;
			$update->update();		
			return true;
		}
	}
	
	/* Start Turn Off Notification From This Post */
	
	public function actionTurnoffNotification()
	{
        $loginmodel = new \frontend\models\LoginForm();
        if (isset($_POST['pid']) && !empty($_POST['pid'])) {

            $pid = (string) $_POST['pid'];

            $data = array();

            $session = Yii::$app->session;
            $user_id = (string) $session->get('user_id');

            $userexist = TurnoffNotification::find()->where(['user_id' => $user_id])->one();
            $ton = new TurnoffNotification();
            if ($userexist)
			{
                if (strstr($userexist['post_ids'], $pid))
				{
					$ton = TurnoffNotification::find()->where(['user_id' => $user_id])->one();
					$ton->post_ids = str_replace($pid.',',"",$userexist['post_ids']);
					$tonids = $ton->post_ids;
					$ton->update();
					if(strlen($tonids) == 0)
					{
						$ton = TurnoffNotification::find()->where(['user_id' => $user_id])->one();
						$ton->delete();
					}
                    return 1;
                }
				else
				{
                    $ton = TurnoffNotification::find()->where(['user_id' => $user_id])->one();
                    $ton->post_ids = $userexist['post_ids'].$pid.',';
                    if ($ton->update())
					{
                        return 2;
                    }
					else
					{
                        return 0;
                    }
                }
            }
			else
			{
                $ton->user_id = $user_id;
                $ton->post_ids = $pid.',';
                if ($ton->insert())
				{
                    return 2;
                }
				else
				{
                    return 0;
                }
            }
        }
    }
	
	/* End Turn Off Notification From This Post */
    
    public function actionAddreferal()
    {
        $refer = new ReferForm();
        $date = time();
        $refer->user_id = $_POST['user_id'];
        $refer->referal_id = $_POST['referal_id'];
        $refer->referal_point = $_POST['referal_point'];
        $refer->referal_text = ucfirst($_POST['referal_text']);
        $refer->referred_date = "$date";
        $refer->is_deleted = '0';
        $refer->date = date("d-m-Y");
        $refer->insert();
    }

/*	public function actionPinimage()
	{
		$data 	= array(); 
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		$imagename = (string)$_POST['iname'];
		$post_id = (string)$_POST['pid'];
		$date = time();
		if(isset($user_id) && $user_id != '') {
			$authstatus = UserForm::isUserExistByUid($user_id);
			if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
				$data['auth'] = $authstatus;
			} else {
				$pinit = PinImage::find()->where(['user_id' => $user_id,'imagename' => $imagename])->one();

				if($pinit)
				{
					if($pinit['is_saved'] == '1'){$pinvalue = '0';}
					else{$pinvalue = '1';}
					$pinit->is_saved = $pinvalue;
					$pinit->pinned_at = $date;
					if($pinit->update())
					{
						if($pinvalue){return 2;}
						else{return 1;}
					}
					else
					{
						return 0;
					}  
				}
				else
				{
					$pinit = new PinImage();
					$pinit->user_id = $user_id;
					$pinit->imagename = $imagename;
					$pinit->post_id = $post_id;
					$pinit->is_saved = '1';
					$pinit->pinned_at = $date;
					if($pinit->insert())
					{
						$is_img_owner = PostForm::find()->where(['like','image',$imagename])->one();
						$img_owner = (string)$is_img_owner['post_user_id'];
						if($img_owner != $user_id)
						{
							$cre_amt = 1;
							$cre_desc = 'pinimage';
							$status = '1';
							$details = $user_id.'::'.$imagename;
							$credit = new Credits();
							$credit = $credit->addcredits($img_owner,$cre_amt,$cre_desc,$status,$details);
						}	
						return 2;
					}
					else
					{
						return 0;
					}	
				}
			}
	    } else {
	    	$data['auth'] = 'checkuserauthclassg';
	    }
		return json_encode($data);			
	}*/

	public function actionPinimage()
	{
		$data 	= array(); 
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		$result = array();
		if(isset($_POST) && !empty($_POST)) {
			$post = $_POST;
			$imagename = $post['iname'];
			$temp = pathinfo($imagename);
			$inameclass = $temp['filename'];
			$post_id = $post['pid'];
			$type = $post['type'];
			$date = time();
			$typeArray = array('PostForm', 'PlaceReview', 'PlaceTip', 'PlaceDiscussion', 'PlaceAsk');

			if($type == 'PostForm') {
				$data = PostForm::find()->where([(string)'_id' => $post_id])->one();
			} else if ($type == 'PlaceReview') {
				$data = PlaceReview::find()->where([(string)'_id' => $post_id])->andWhere(['not','flagger', "yes"])->one();
			} else if ($type == 'PlaceTip') {
				$data = PlaceTip::find()->where([(string)'_id' => $post_id])->andWhere(['not','flagger', "yes"])->one();
			} else if ($type == 'PlaceDiscussion') {
				$data = PlaceDiscussion::find()->where([(string)'_id' => $post_id])->andWhere(['not','flagger', "yes"])->one();
			} else if ($type == 'PlaceAsk') {
				$data = PlaceAsk::find()->where([(string)'_id' => $post_id])->andWhere(['not','flagger', "yes"])->one();
			}

			if(!empty($data)) {
				if(isset($data->pinned) && $data->pinned == 'yes') {
					// do unpinned
					$data->pinned = '';
					$data->update();
					$image = '../web/uploads/gallery/'.$imagename;

					Gallery::deleteAll(['post_id' => $post_id, 'image' => $image]);

					$result = array('status' => true, 'label' => 'unpin', 'inameclass' => $inameclass);
					return json_encode($result, true);
				} else {
					// do pinned
					$data->pinned = 'yes';
					$data->update();

					$title = isset($data->post_title) ? $data->post_title : '';	
					$description = isset($data->post_text) ? $data->post_text : '';	
					$location = isset($data->currentlocation) ? $data->currentlocation : '';	
					$taggedconnections = isset($data->post_tags) ? $data->post_tags : '';	
					$privacy = isset($data->post_privacy) ? $data->post_privacy : '';	
					$image = '';
					if(file_exists('../web/uploads/'.$imagename)) {
						copy('../web/uploads/'.$imagename, '../web/uploads/gallery/'.$imagename);
						$image = '../web/uploads/gallery/'.$imagename;
					}

					$Gallery = new Gallery();
					$Gallery->user_id = $user_id;
					$Gallery->image = $image;
					$Gallery->title = $title;
					$Gallery->description = $description;
					$Gallery->location = $location;
					$Gallery->tagged_connections = $taggedconnections;
					$Gallery->visible_to = $privacy;
					$Gallery->store = $type;
					$Gallery->type = 'userwall';
					$Gallery->place = '';
            		$Gallery->placetitle = '';
					$Gallery->post_id = $post_id;
					$Gallery->created_at = time();
					$Gallery->insert();
					
					$result = array('status' => true, 'label' => 'pin', 'inameclass' => $inameclass);
					return json_encode($result, true);
				}
			}
		}
		return json_encode($result, true);	
	}
	
    /* Start Feedback Mail*/

    public function actionFeedbackmail() {

	    $model = new \frontend\models\LoginForm();

	    $session = Yii::$app->session;
	    $email = $session->get('email_id');
	    $user_id = strrev(base64_encode($session->get('user_id')));

	    $user_info = LoginForm::find()->where(['email'=> $email])->one();

	    $rand_user = LoginForm::find()->where(['status'=> '1'])->all();
	    $randomchoice = rand(0,count($rand_user));

	    $rand_user = $rand_user[$randomchoice]; 
	    $rand_user_id = strrev(base64_encode($rand_user->_id));
	    $rand_user_name = $rand_user->fname;
	    
	    $link = "https://iaminjapan.com/frontend/web/index.php?r=site/mainfeedback&refid=$rand_user_id&userid=$user_id";

	    try 
		{
			$test = Yii::$app->mailer->compose()
			//->setFrom('no-reply@iaminjapan.com')
			->setFrom(array('csupport@iaminjapan.com' => 'iaminjapan Team'))
			->setTo($email)
			->setSubject('I am in Japan- Feedback')
			->setHtmlBody('<html><head><meta charset="utf-8" /><title>I am in Japan</title></head><body style="margin:0;padding:0;background:#dfdfdf;"><div style="color: #353535; float:left; font-size: 13px;width:100%; font-family:Arial, Helvetica, sans-serif;text-align:center;padding:40px 0 0;"><div style="width:600px;display:inline-block;"> <img src="https://iaminjapan.com/frontend/web/images/black-logo.png" style="margin:0 0 10px;width:130px;float:left;"/><div style="clear:both"></div><div style="border:1px solid #ddd;margin:0 0 10px;"><div style="background:#fff;padding:20px;border-top:10px solid #333;text-align:left;"><div style="color: #333;font-size: 13px;margin: 0 0 20px;">Hi '.$user_info['fname'].'</div><div style="color: #333;font-size: 13px;margin: 0 0 20px;">We would appreciate your valuable feedback for '.$rand_user_name.'. </div> <div style="color: #333;font-size: 13px;margin: 0 0 20px;">Please take a minute to say how you felt by click <a href="'.$link.'" target="_blank" style="color:#3399cc">here</a> or paste the following link into your browser: <br/><a href="'.$link.'" target="_blank" style="color:#3399cc">'.$link.'</a></div> <div style="color: #333;font-size: 13px;">Thanks</div> <div style="color: #333;font-size: 13px;">The iaminjapan Team</div> </div> </div> <div style="clear:both"></div> <div style="width:600px;display:inline-block;font-size:11px;"> <div style="color: #777;text-align: left;">&copy;  www.iaminjapan.com All rights reserved.</div> <div style="text-align: left;width: 100%;margin:5px  0 0;color:#777;">For support, you can reach us directly at <a href="csupport@iaminjapan.com" style="color:#4083BF">csupport@iaminjapan.com</a></div></div></div></div></body> </html>')
			->send();			
			return "1";
		}
		catch (ErrorException $e) 
		{
			return "0";
		}
    }
	
	/* End Feedback Mail*/
	
    public function actionGetOnlineUsers() 
	{
        $model = new \frontend\models\LoginForm();
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $email = $session->get('email');
        
        $getOnlineUsers = $this->actionGetOnlineRowData();

        $li='';
        if(isset($getOnlineUsers) && !empty($getOnlineUsers)) 
		{ 
            foreach ($getOnlineUsers as $key => $user) 
			{ 
				$li .='<li class="chat-uid-'.$user['id'].'">
						<div class="chat-summery">
							<a href="javascript:void(0)">
								<span class="img-holder"><img src="'.$user['thumb'].'"/>
								</span>
								
								<span class="desc-holder">
									'.$user["name"].'
									<span class="info">Graphics Designer</span>                             
								</span>
								<span class="online-sing"></span>
							</a>
						</div>
					</li>';
            }
        }
		else
		{
            $li .= '<li><div class="no-listcontent"> No connections online. </div></li>';
        }
        return $li;
    }

	public function actionGetSecurityAnswer() 
	{
		$session = Yii::$app->session;
		$email = $session->get('email_id');
		$user_id = (string)$session->get('user_id');
		

		$model = new \frontend\models\SecuritySetting();
		$result_security = SecuritySetting::find()->where(['user_id' => $user_id])->one();
		$eml_ans = (isset($result_security['eml_ans']) && !empty($result_security['eml_ans'])) ? $result_security['eml_ans'] : '';
		$born_ans = (isset($result_security['born_ans']) && !empty($result_security['born_ans'])) ? $result_security['born_ans'] : '';
		$gf_ans = (isset($result_security['gf_ans']) && !empty($result_security['gf_ans'])) ? $result_security['gf_ans'] : '';
		$data = json_encode(array('email'=>$eml_ans,'born'=>$born_ans,'girl'=>$gf_ans));
		return $data;
    }

    /* ========== START strophe loaded ==================
	public function actionMessages() 
	{   
		$session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');

        Messages::getbasicinfoouser('57972b76cf926ff93c90909f', $user_id);
        $getRecentUsersWithDetails = array();

    	$thumb = $this->getimage($user_id,'thumb');
        if(isset($user_id) && $user_id != '') {
        	$getRecentUsers = Yii::$app->cache->redis->hgetall($user_id);	
        	//$getRecentUsers = json_encode($getRecentUsers, true);

        	//$getRecentUsers = array("from" => "579729e4cf926f773c90909f", "to" => "57972b76cf926ff93c90909f","message" => "Message from  adel yahoo to adel yahoo ?");  		
    		//$getRecentUsers = Yii::$app->cache->redis->hgetall($user_id);
    		//$getRecentUsers = {"from":"579729e4cf926f773c90909f","to":"57972b76cf926ff93c90909f","message":"Message from  adel yahoo to adel yahoo ?"}{"from":"579729e4cf926f773c90909f","to":"5796f812cf926fb4339090a0","message":"Message from  adel yahoo to Adel Hasanat ?"}
    		//$getRecentUsers = json_encode(array(array("from" => "579729e4cf926f773c90909f", "to" => "57972b76cf926ff93c90909f", "message" => "Message from  adel yahoo to adel yahoo ?"), array("from" => "579729e4cf926f773c90909f", "to" => "5796f812cf926fb4339090a0", "message" => "Message from  adel yahoo to Adel Hasanat ?")), true);


			if(!empty($getRecentUsers)) {
    			$getRecentUsersWithDetails = Messages::recentMessagesUserList($user_id, $getRecentUsers);
    		}
        }
 
        $usrfrd = Connect::getuserConnections($user_id);
        $usrfrdlist = array(); 
        foreach($usrfrd AS $ud)  
		{
            if(isset($ud['userdata']['fullname']) && $ud['userdata']['fullname'] != '') {
                $id = (string)$ud['userdata']['_id'];
                $fbid = isset($ud['userdata']['fb_id']) ? $ud['userdata']['fb_id'] : '';
                $dp = $this->getimage($ud['userdata']['_id'],'thumb');
                $nm = $ud['userdata']['fullname'];
                $usrfrdlist[] = array('id' => $id, 'fbid' => $fbid, 'name' => $nm, 'text' => $nm, 'thumb' => $dp);
            } 
        }
        return $this->render('messages',['usrfrdlist' => $usrfrdlist, 'thumb' => $thumb, 'getRecentUsersWithDetails' => $getRecentUsersWithDetails]);
    }
	============ END strophe loaded =====================*/

	/* ========== START node loaded ================== */
	public function actionMessages() 
	{  
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        $thumb = $this->getimage($user_id,'thumb');
        /*
        $post = array(
			"category" => "inbox",
			"from_id" => "579b340bcf926fbb2790909f",
			"limit" => 20,
			"start" => 0,
			"to_id" => "579729e4cf926f773c90909f"
		);

        Messages::getLoadHistoryMessage($post, $user_id);        
        die;*/

        $usrfrd = Connect::getuserConnections($user_id);
        $usrfrdlist = array(); 
        foreach($usrfrd AS $ud) 
		{
            if(isset($ud['userdata']['fullname']) && $ud['userdata']['fullname'] != '') {
                $id = (string)$ud['userdata']['_id'];
                $fbid = isset($ud['userdata']['fb_id']) ? $ud['userdata']['fb_id'] : '';
                $dp = $this->getimage($ud['userdata']['_id'],'thumb');
                $nm = $ud['userdata']['fullname'];
                $usrfrdlist[] = array('id' => $id, 'fbid' => $fbid, 'name' => $nm, 'text' => $nm, 'thumb' => $dp);
            }
        }

        $model = new \frontend\models\SecuritySetting();
        return $this->render('messages',['model' => $model, 'usrfrdlist' => $usrfrdlist, 'thumb' => $thumb]);
    }
    /* ========== END node loaded ================== */

    public function actionDeleteSocketConversation() 
	{
        $session = Yii::$app->session;
        $email = $session->get('email_id');
        $user_id = (string)$session->get('user_id');
        if(isset($_POST['from_id']) && $user_id != '') 
		{
            $from_id = $_POST['from_id'];
            $responce = Messages::deletesocketconversation($from_id);
        }
    }

    public function actionCheckloginstatus()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if(isset($user_id) && $user_id != '')
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

	/* Hotels */
	public function actionHotels() 
	{
		return $this->render('hotels');
	}

	public function actionBilling() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $email = $session->get('email');
        if($session->get('email'))
        {
			$model = new Order();
			$orderhistory = $model->orderhistory($uid);
			return $this->render('billing',['orderhistory' =>$orderhistory]);
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionBillinginfo() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $email = $session->get('email');
        if ($session->get('email'))
        {
			$post_date = $_POST['month'];
			
			$model = new Order();
			$orderhistory = $model->ordermonthlyhistory($uid,$post_date);
			
			$date = $post_date;
			$time=strtotime($date);
			$current_month=date("F Y",$time);
			
			$year  = date("Y",$time);
			$month = date("m",$time);
			
			$date2 = mktime(0, 0, 0, $month, 1, $year);
			$last_month =  date("F Y", strtotime('-1 month', $date2));
			$next_month =  date("F Y", strtotime('+1 month', $date2));
			
			$return = '<div class="history-table">
						<div class="table-navigation">	
							<a href="javascript:void(0)" onclick="billing_info(\''.$last_month.'\')" class="prev-month"><i class="mdi mdi-chevron-left"></i>'.$last_month.'</a>
							<span>'.$current_month.'</span>
							<a href="javascript:void(0)" onclick="billing_info(\''.$next_month.'\')" class="next-month">'.$next_month.'<i class="mdi mdi-chevron-right"></i></a>
						</div>
						<div class="table-responsive">
						  <table class="table">
							<thead>
								<tr>
									<th>Date</th>
									<th>Order#</th>
									<th>Item</th>
									<th>Paid Via</th>
									<th>Amount</th>
									<th>Details</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>';
							
							if(empty($orderhistory))
							{
								$return.= '<tr><td colspan="7"><div class="no-listcontent">
									No transaction in this month
								</div></td></tr>';
							}
							
							 foreach($orderhistory as $order){
								$time=strtotime($order['current_date']);
								$month=date("F",$time);
								$day=date("d",$time);
								
								$order_type = '';
								$order_detail = '';

								if($order['order_type'] == 'joinvip') 
								{
									$order_type = 'VIP Member'; 
								}
								else if($order['order_type'] == 'buycredits') 
								{
									$order_type = 'Purchase Credits'; 
								} 
								else if($order['order_type'] == 'verify') 
								{ 
									$order_type = 'Verify Member'; 
								}

								if($order['order_type'] == 'joinvip') 
								{
									$order_detail =  $order['detail'] . ' Month'; 
								}
								else if($order['order_type'] == 'buycredits')
								{
									$order_detail =  $order['detail'] . ' Credits'; 
								}
								else if($order['order_type'] == 'verify') 
								{
									$order_detail =  '1 Year'; 
								}
							
			$return.=			'<tr>
									<td>'.$month .' '.$day.'</td>
									<td>'.$order['transaction_id'].'</td>
									<td>'.$order_type.'</td>
									<td>'.$order['curancy'].'</td>
									<td>'.$order['amount'].'</td>
									<td>'.$order_detail.'</td>
									<td class="btext">'.$order['status'].'</td>
								</tr>';
								 } 
			$return.=	'</tbody>
						  </table>
						</div>
					</div>';
			
			return $return;
			
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionPublishpost() 
    {
        $pid = $_POST['pid'];
        $date = time();
        $record = PostForm::find()->where(['_id' => $pid,'is_deleted' => '2'])->one();
        $record->is_deleted = '0';
        $record->update();
        
        
        /* Insert Notification For The Owner of Post For Publishing his/her post*/
            
            $post_owner_id = $record['post_user_id'];
        
            $notification =  new Notification();
            $notification->post_id = "$pid";
            $notification->user_id = "$post_owner_id";
            $notification->notification_type = 'publishpost';
            $notification->is_deleted = '0';
            $notification->status = '1';
            $notification->created_date = "$date";
            $notification->updated_date = "$date";
            $notification->insert();
            return true;
    }   

    public function actionGetalluserwiththumb() 
	{
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $usrfrdlist = array(); 
        if(isset($user_id) && $user_id != '') {
            $usrfrd = Connect::getuserConnections($user_id);
            foreach($usrfrd AS $ud) 
            {
                if(isset($ud['userdata']['fullname']) && $ud['userdata']['fullname'] != '') {
                    $id = (string)$ud['userdata']['_id'];
                    $fbid = isset($ud['userdata']['fb_id']) ? $ud['userdata']['fb_id'] : '';
                    $dp = $this->getimage($ud['userdata']['_id'],'thumb');
                    $nm = $ud['userdata']['fullname'];
                    $usrfrdlist[] = array('id' => $id, 'fbid' => $fbid, 'name' => $nm, 'text' => $nm, 'thumb' => $dp);
                }
            }

            return json_encode($usrfrdlist, true);
            exit;
        }
	}
	
	public function actionCommunicationSettings() 
	{
		if (isset($_POST) & !empty($_POST)) {
		    return CommunicationSettings::communicationsettings();
        } else {
            return $this->render('notification_setting');
        }
    }
	
	public function actionCompleteProfile() 
	{

        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		$model = new \frontend\models\LoginForm();
		$genderArray = array('Male', 'Female');
		$s2 = LoginForm::find()->where(['_id' => $user_id])->one();		 	
		if(isset($_POST) && !empty($_POST))
		{

			if($_POST['city'] != '' && $_POST['country'] != '' && $_POST['gender'] != '' && $_POST['birth_date'] !='') {
				$gender = $_POST['gender'];
				if (in_array($gender, $genderArray)) { 
					$city = $_POST['city'];
					$country = $_POST['country'];
					$birth_date = $_POST['birth_date'];

					$s2 = LoginForm::find()->where(['_id' => $user_id])->one();
					$s2->city = $city;
					$s2->country = $country;
					$s2->gender = $gender;
					//$s2->isd_code = $_POST['isd_code'];
					$s2->birth_date = $birth_date;
					//$s2->country_code = $_POST['country_code'];

					$GApiKeyL = $GApiKeyP = Googlekey::getkey();

					$prepAddr = str_replace(' ','+',$city);
					$prepAddr = str_replace("'",'',$prepAddr);
					$geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?key='.$GApiKeyL.'&address='.$prepAddr.'&sensor=false');
					$output = json_decode($geocode);
					$latitude = '';
					$longitude = '';
					if(!empty($output)) {
						if(isset($output->results[0])) {
							if(isset($output->results[0]->geometry)) {
								if(isset($output->results[0]->geometry->location)) {
									if(isset($output->results[0]->geometry->location->lat)) {
										$latitude = $output->results[0]->geometry->location->lat;
									}

									if(isset($output->results[0]->geometry->location->lng)) {
										$longitude = $output->results[0]->geometry->location->lng;
									}
								}
							}
						}
					}
					$s2->citylat = "$latitude";
					$s2->citylong = "$longitude";
					$s2->update();
					return true;
				 	exit;
				
				}
			}
			return false;
			exit;
		}

		return $this->render('complete-profile',['data' => $s2]);
            
	}

	public function actionCompleteEmailprofile() 
	{
        $session = Yii::$app->session;
        $temporary_u_id = (string)$session->get('temporary_u_id');

        if($temporary_u_id != '' && $temporary_u_id != 'undefined') {
			return $this->render('complete-emailprofile');
        }
    }

	public function actionCheckUserStatus() 
	{
		return UserForm::checkuserauth();
	}
	
	public function actionMobilePost() 
	{
		$session = Yii::$app->session;
		$userid = (string)$session->get('user_id');
		$email = $session->get('email');
		
		if(isset($_POST['postid'])) {
			$PostId = $_POST['postid']; 
			?>	
				<div class="hidden_header">
					<div class="content_header">
						<button class="close_span cancel_poup">
							<i class="mdi mdi-close mdi-20px"></i>
						</button>
						<p class="modal_header_xs">Post detail</p>
					</div>
				</div>				
				<div class="modal-content"> 
					<div class="mobile-screen mobile-all-comments mobile-popup">
						<div class="main-pcontent spadding">
							<div class="post-column">
							<?php if(strstr($_SERVER['HTTP_REFERER'],'r=discussion') || strstr($_SERVER['HTTP_REFERER'],'r=tips') || strstr($_SERVER['HTTP_REFERER'],'r=questions') || strstr($_SERVER['HTTP_REFERER'],'r=reviews')) {
								?>
								<div id="post-status-list" class="post-list"> 
									<?php 
									if((string)$PostId != '') {
										$issearch = 'yes';
										$post = PostForm::find()->where([(string)'_id' => (string)$PostId])->one();
										if(!empty($post)) {
											$issearch = 'no';	
										}

										if($issearch == 'yes') {
											$post = PlaceReview::find()->where([(string)'_id' => (string)$PostId])->andWhere(['not','flagger', "yes"])->one();
											if(!empty($post)) {
												$issearch = 'no';	
											}
										}

										if($issearch == 'yes') {
											$post = PlaceDiscussion::find()->where([(string)'_id' => (string)$PostId])->andWhere(['not','flagger', "yes"])->one();
											if(!empty($post)) {
												$issearch = 'no';	
											}
										}

										if($issearch == 'yes') {
											$post = PlaceAsk::find()->where([(string)'_id' => (string)$PostId])->andWhere(['not','flagger', "yes"])->one();
											if(!empty($post)) {
												$issearch = 'no';	
											}
										}

										if($issearch == 'yes') {
											$post = PlaceTip::find()->where([(string)'_id' => (string)$PostId])->andWhere(['not','flagger', "yes"])->one();
											if(!empty($post)) {
												$issearch = 'no';	
											}
										}

										if(!empty($post)) {
											$postid = (string)$post['_id'];
											$postownerid = (string)$post['post_user_id'];
											$postprivacy = $post['post_privacy'];
											$isOk = $this->filterDisplayLastPost($postid, $postownerid, $postprivacy);
											if($isOk == 'ok2389Ko') {
												$this->display_last_post($postid,'from_save','','tippost-holder bborder ','','restingimagefixes');
											}
										}
									} 

									?> 
								</div>
							<?php } else { ?>	
								<div id="post-status-list" class="post-list"> 
									<?php 
									if((string)$PostId != '') {
										$post = PostForm::find()->where([(string)'_id' => (string)$PostId])->one();
										if(!empty($post)) {
											$postid = (string)$post['_id'];
											$postownerid = (string)$post['post_user_id'];
											$postprivacy = $post['post_privacy']; 
											$isOk = $this->filterDisplayLastPost($postid, $postownerid, $postprivacy);
											if($isOk == 'ok2389Ko') {
												$this->display_last_post($postid); 
											}
										}
									}
									?> 
								</div>
							<?php } ?>
							</div>
						</div>
					</div>
				</div>		
			<?php
		}
	}
	
	public function actionMobileComment() 
	{
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		$email = $session->get('email');
		$status = $session->get('status');
		$result = LoginForm::find()->where(['_id' => $user_id])->one();
		
		$Auth = '';
		if(isset($user_id) && $user_id != '') 
		{
		$authstatus = UserForm::isUserExistByUid($user_id);
		if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') 
		{
			$Auth = $authstatus;
		}
		}   
		else    
		{
			$Auth = 'checkuserauthclassg';
		}
		
		if(isset($_POST['postid'])) {
		if ($session->get('email'))
		{
			$PostId = $_POST['postid'];
			$post = PostForm::find()->where(['_id' => $PostId])->one();
			$comments = Comment::getAllPostLike($PostId);
			$init_comments = Comment::getFirstPostComments($PostId);
			?>	
			<div class="content_header">
				<button class="close_span cancel_poup">
					<i class="mdi mdi-close mdi-20px"></i>
				</button>
				<p class="modal_header_xs">Comments</p>
			</div>			
			
			<div class="post-data">			
				<div class="post-holder">						
					<div class="comments-section panel open">
								<?php if(count($init_comments)>0){ ?>
								<div class="post-more">
									<?php if(count($comments)>1){ ?>
									<a href="javascript:void(0)" class="view-morec view-morec-<?=$post['_id']?>" onclick="showPreviousComments('<?=$post['_id']?>')">View more comments</a>
									
									<span class="total-comments commment-ctr-<?=$post['_id']?>" id="commment-ctr-<?=$post['_id']?>"><font class="ctrdis_<?=$post['_id']?>"><?=count($init_comments)?></font> of <font class="countdisplay_<?=$post['_id']?>"><?=count($comments)?></font></span>
									<?php } ?>
								</div>
								<?php } ?>
								<input type="hidden" name="from_ctr" id="from_ctr_<?=$post['_id']?>" value="<?=count($init_comments)?>">      
								<input type="hidden" name="to_ctr" id="to_ctr_<?=$post['_id']?>" value="<?=count($comments)?>">

								<div class="post-comments post_comment_<?=$post['_id']?>">                     
									<div class="pcomments sub_post_comment_<?=$post['_id']?>">                                    
										<?php
										if(count($init_comments)>0) { 
											foreach($init_comments as $init_comment) {
												$comment_time = Yii::$app->EphocTime->comment_time(time(),$init_comment['updated_date']);$commentcount =Comment::getCommentReply($init_comment['_id']);
												$hidecomments = new HideComment();
												$hidecomments = HideComment::find()->where(['user_id' => (string)$user_id])->one();
												$hide_comments_ids = explode(',',$hidecomments['comment_ids']);
												if(!(in_array($init_comment['_id'],$hide_comments_ids)))
												{
													if(($user_id == $post['post_user_id']) || ($user_id == $init_comment['user']['_id']))
													{
														$afun_post = 'deleteComment';
														$atool_post = 'Delete';
													}
													else
													{
														$afun_post = 'hideComment';
														$atool_post = 'Hide';
													}
													$id = $init_comment['user']['_id'];
										?>
										<div class="pcomment-holder" id="comment_<?=$init_comment['_id']?>">
											<div class="pcomment main-comment">
												<div class="img-holder">
													<div id="commentptip-4" class="profiletipholder">
														<span class="profile-tooltip">
															<?php $init_comment_img = $this->getimage($init_comment['user']['_id'],'thumb'); ?>
															<img class="circle" src="<?= $init_comment_img?>"/>
														</span>
													</div>
												</div> 
												<div class="desc-holder">
													<div class="normal-mode">
														<div class="desc">
															<a href="<?=Url::to(['userwall/index', 'id' => "$id"])?>" class="userlink"><?=ucfirst($init_comment['user']['fname']).' '.ucfirst($init_comment['user']['lname'])?></a>
															<?php if(strlen($init_comment['comment'])>200){ ?>
																<p class="shorten" data-id="<?=$init_comment['_id']?>" id="text_<?= $init_comment['_id']?>"><?=$init_comment['comment']?>
																<a href="javascript:void(0)" class="overlay" onclick="explandReadMore(this)"><span class="readlink">Read More</span></a>
																</p>
															<?php }else{ ?>                                                     
																<p data-id="<?=$init_comment['_id']?>" id="text_<?= $init_comment['_id']?>"><?=$init_comment['comment']?></p>
															<?php } ?>
														</div>
														<div class="comment-stuff">
														    <div class="more-opt">
														        <a class='dropdown-button more_btn' href='javascript:void(0)' data-activates='subset_<?=$init_comment['_id']?>'>
														            <i class="zmdi zmdi-more"></i>
														        </a>
														                                                                        
														        <!-- Dropdown Structure -->
														        <ul id='subset_<?=$init_comment['_id']?>' class='dropdown-content custom_dropdown'>
														        <?php if($status == '10') { ?> 
														            <li><a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$init_comment['_id']?>')">Flag</a></li>
														        <?php } else { ?>                                                                    
														        <?php if(($userid != $post['post_user_id']) && ($userid != $init_comment['user']['_id'])) { ?>
														            <li><a href="javascript:void(0)" class="close-comment <?=$Auth?> directcheckuserauthclass" onclick="<?= $afun_post?>('<?=$init_comment['_id']?>')"><i class="mdi mdi-close mdi-20px "></i><?=$atool_post?></a></li>
														        <?php } else { ?>
														            <?php if($userid == $init_comment['user']['_id']){ ?>
														            <li>
														              <a class="edit-comment" href="javascript:void(0)">Edit</a>
														            </li>
														            <?php } ?>
														            <li>
														              <a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$init_comment['_id']?>','<?=$post['_id']?>')">Delete</a>
														            </li>
														        <?php } ?>
														        <?php } ?>
														        </ul>        
														    </div>
														</div>
													</div>
													<div style="clear:both"></div>
													<div class="normode-action">
													  <span class="likeholder">
													     <span class="like-tooltip">
													        <div class="fixed-action-btn horizontal direction-top direction-left" >
													          <?php
													          $like_active = Like::find()->where(['comment_id' => (string)$init_comment['_id'],'status' => '1','user_id' => (string) $user_id])->one();
													          $likeIcon = '';
													          if(!empty($like_active)) {
													              $likeIcon = 'bold';
													          }
													          ?>
													          <a href="javascript:void(0)" class="post-like commentcounttitle_<?=$init_comment['_id']?> <?=$likeIcon?>" data-title='<?=$newlike_buddies?>' onclick="likeComment('<?=$init_comment['_id']?>')" title="Like">Like</a>
													        </div>
													     </span>
													  </span>  
													  <a href="javascript:void(0)" class="pa-reply reply-comment post-reply" title="Reply">
													     Reply
													  </a>
													  <div class="post-time"><?=$comment_time?></div>
													</div>
													<div class="edit-mode">
														<div class="desc">
															<div class="cmntarea underlined fullwidth">
																<textarea data-adaptheight class="editcomment-tt materialize-textarea data-adaptheight" data-id="<?=$init_comment['_id']?>" id="edit_comment_<?=$init_comment['_id']?>"><?=$init_comment['comment']?></textarea>
															</div>
															<a class="editcomment-cancel" href="javascript:void(0)"><i class="mdi mdi-close mdi-20px"></i></a>
														</div>                                                                          
													</div>
												</div>                              
											</div>  
											<div class="clear"></div>
											
											<div class="comment-reply-holder reply_comments_<?=$init_comment['_id']?>">
												<?php $comment_replies =Comment::getCommentReply($init_comment['_id']);
													if(count($comment_replies)>0) {
													$lastcomment = Comment::find()->where(['parent_comment_id' => (string)$init_comment['_id']])->orderBy(['updated_date'=>SORT_DESC])->one();
													$last_comment_time = Yii::$app->EphocTime->comment_time(time(),$lastcomment['updated_date']);
												?>
												<div class="comments-reply-summery">
													<a href="javascript:void(0)" onclick="openReplies(this)">
														<i class="mdi mdi-share"></i>
														<?=count($comment_replies)?>
														<?php if(count($comment_replies)>1) { ?>
															Replies
														<?php } else { ?>
															Reply
														<?php } ?>
													</a>
												</div>
												<?php }
												if(!empty($comment_replies))
												{
													foreach($comment_replies AS $comment_reply) { 
													$hidecomment = new HideComment();
													$hidecomment = HideComment::find()->where(['user_id' => (string)$user_id])->one();
													$hide_comment_ids = explode(',',$hidecomment['comment_ids']); 
													if(!(in_array($comment_reply['_id'],$hide_comment_ids)))
													{
														if(($user_id == $post['post_user_id']) || ($user_id == $comment_reply['user']['_id']))
														{
															$bfun_post = 'deleteComment';
															$btool_post = 'Delete';
														}
														else
														{
															$bfun_post = 'hideComment';
															$btool_post = 'Hide';
														}
													$comment_time = Yii::$app->EphocTime->comment_time(time(),$comment_reply['updated_date']);

													$like_count = Like::find()->where(['comment_id' => (string) $comment_reply['_id']  ,'status' => '1'])->all();

													$user_ids = ArrayHelper::map(Like::find()->select(['user_id'])->where(['comment_id' => (string) $comment_reply['_id'], 'like_type' => 'comment', 'status' => '1'])->orderBy(['updated_date'=>SORT_DESC])->all(), 'user_id', 'user_id');
													$comlikeuseinfo = UserForm::find()->select(['_id','fname', 'lname'])->asArray()->where(['in','_id',$user_ids])->all();

													$usrbox = array();
													foreach ($comlikeuseinfo as $key => $single) {
													$fullnm = $single['fname'] . ' ' . $single['lname'];
													$usrbox[(string)$single['_id']] = $fullnm; 
													}

													$newlike_buddies = implode("<br/>", $usrbox);
													$id = $comment_reply['user']['_id'];

											?>
											<div class="comments-reply-details">
												<div class="pcomment comment-reply" id="comment_<?=$comment_reply['_id']?>">
													<div class="img-holder">
														<div class="profiletipholder" id="commentptip-6">
															<span class="profile-tooltip tooltipstered">
																<img class="circle" src="<?=$this->getimage($comment_reply['user']['_id'],'thumb')?>">
															</span>
														</div>
													</div>
													<div class="desc-holder">
														<div class="normal-mode">
															<div class="desc">
																<a class="userlink" href="<?=Url::to(['userwall/index', 'id' => "$id"])?>"><?=ucfirst($comment_reply['user']['fname']).' '.ucfirst($comment_reply['user']['lname'])?></a>
																
																<?php if(strlen($comment_reply['comment'])>200){ ?>
																	<p class="shorten" data-id="<?=$comment_reply['_id']?>" id="text_<?= $comment_reply['_id']?>"><?=$comment_reply['comment']?><a href="javascript:void(0)" class="overlay" onclick="explandReadMore(this)"><span class="readlink">Read More</span></a>
																	</p>
																<?php }else{ ?>                                                         
																	<p data-id="<?=$comment_reply['_id']?>" id="text_<?= $comment_reply['_id']?>"><?=$comment_reply['comment']?></p>
																<?php } ?>
															</div>
															<div class="comment-stuff">
															    <div class="more-opt">
															        <a class='dropdown-button more_btn' href='javascript:void(0)' data-activates='subset_<?=$comment_reply['_id']?>'>
															            <i class="zmdi zmdi-more"></i>
															        </a>
															                                                                        
															        <!-- Dropdown Structure -->
															        <ul id='subset_<?=$comment_reply['_id']?>' class='dropdown-content custom_dropdown'>
															        <?php if($status == '10') { ?> 
															            <li><a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$comment_reply['_id']?>')">Flag</a></li>
															        <?php } else { ?>                                                                    
															        <?php if(($userid != $post['post_user_id']) && ($userid != $comment_reply['user']['_id'])) { ?>
															            <li><a href="javascript:void(0)" class="close-comment <?=$Auth?> directcheckuserauthclass" onclick="<?= $afun_post?>('<?=$comment_reply['_id']?>')"><i class="mdi mdi-close mdi-20px "></i><?=$atool_post?></a></li>
															        <?php } else { ?>
															            <?php if($userid == $comment_reply['user']['_id']){ ?>
															            <li>
															              <a class="edit-comment" href="javascript:void(0)">Edit</a>
															            </li>
															            <?php } ?>
															            <li>
															              <a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$comment_reply['_id']?>','<?=$post['_id']?>')">Delete</a>
															            </li>
															        <?php } ?>
															        <?php } ?>
															        </ul>        
															    </div>
															</div> 
														</div>
														<div style="clear:both"></div>
														<div class="normode-action">
														  <span class="likeholder">
														     <span class="like-tooltip">
														        <div class="fixed-action-btn horizontal direction-top direction-left" >
														          <?php
														          $like_active = Like::find()->where(['comment_id' => (string)$comment_reply['_id'],'status' => '1','user_id' => (string) $user_id])->one();
														          $likeIcon = '';
														          if(!empty($like_active)) {
														              $likeIcon = 'bold';
														          }
														          ?>
														          <a href="javascript:void(0)" class="post-like commentcounttitle_<?=$comment_reply['_id']?> <?=$likeIcon?>" data-title='<?=$newlike_buddies?>' onclick="likeComment('<?=$comment_reply['_id']?>')" title="Like">Like</a>
														        </div>
														     </span>
														  </span>  
														  <a href="javascript:void(0)" class="pa-reply reply-comment post-reply" title="Reply">
														     Reply
														  </a>
														  <div class="post-time"><?=$comment_time?></div>
														</div>
														<div class="edit-mode">
															<div class="desc">
																<div class="cmntarea underlined fullwidth">
																	<textarea data-adaptheight class="editcomment-tt materialize-textarea data-adaptheight" data-id="<?=$comment_reply['_id']?>" id="edit_comment_<?=$comment_reply['_id']?>"><?=$comment_reply['comment']?></textarea>
																</div>
																<a class="editcomment-cancel" href="javascript:void(0)"><i class="mdi mdi-close mdi-20px"></i></a>
															</div>                                                                          
														</div>
													</div>
												</div>
											</div>
											<?php } } } ?>
											</div>
											
											<div class="comment-reply-holder comment-addreply">                                 
												<div class="addnew-comment valign-wrapper comment-reply">                          
													<div class="img-holder"><a href="javascript:void(0)"><img src="<?= $this->getimage($result['_id'],'thumb');?>"/></a></div>
													<div class="desc-holder">                                   
														<div class="cmntarea">
															<textarea data-adaptheight name="reply_txt" placeholder="Write a reply" data-postid="<?=$post['_id']?>" data-commentid="<?=$init_comment['_id']?>" id="reply_txt_<?=$init_comment['_id']?>" class="reply_class materialize-textarea data-adaptheight"></textarea>
														</div>  
													</div>  
												</div>
											</div>
										</div>
										<?php  } } } ?>
									</div>
									<?php if($post['comment_setting'] != 'Disable'){
									if(isset($post['trav_item']) && $post['trav_item']== '1')
									{
										$comment_placeholder = "Ask question or send query";
									}
									else{
										$comment_placeholder = "Write a comment";
									}   
									?>
									<div class="addnew-comment valign-wrapper">
										<?php $comment_image = $this->getimage($result['_id'],'thumb'); ?>
										<div class="img-holder"><a href="javascript:void(0)"><img src="<?= $comment_image?>"/></a></div>
										<form name="imageCommentForm" id="imageCommentForm" enctype="multipart/form-data">
											<div class="desc-holder">                                   
												<div class="cmntarea">
													<textarea data-adaptheight data-postid="<?=$post['_id']?>" id="comment_txt_<?=$post['_id']?>" placeholder="<?= $comment_placeholder;?>" class="comment_class  materialize-textarea data-adaptheight"></textarea>
												</div>
											</div>
										</form>
									</div>
									
									<?php } ?>
								</div>
							</div>
				</div>
			</div>	
			<?php
			}
			else
			{
				return $this->goHome();
			}
		}
	}
	
	public function actionGeneralLoginPopup()  
	{ 
		return $this->render('/layouts/loginpopup');
	}

	public function actionDeveloperGeneralLoginPopup()  
	{ 
		return $this->render('/layouts/developerloginpopup');
	}

	public function actionDirectlogin()  
	{ 
		return $this->render('directlogin');
	}
	
	public function actionCloseAccount()  
	{
		$session = Yii::$app->session;
		$user_id = (string) $session->get('user_id');
		
		if(isset($user_id) && $user_id != '') {
			if(isset($_POST) && !empty($_POST)) {
				$post = $_POST;
				CloseAccount::CloseUserAccount($post, $user_id);
			}
		}
	}
	
	public function actionPublishTagPost() 
    {
		$session = Yii::$app->session;
		$user_id = (string) $session->get('user_id');
		
		$post_id = (string) $_POST['post_id'];
		
		$notification = Notification::find()->where(['post_id' => $post_id,'user_id' => $user_id])->one();
        $notification->review_setting = 'Disabled';
        if($notification->update())
		{
			return "1";
		}
		else
		{
			return "0";
		}
	}	
	public function actionSetadduserfortag() 
    {
        if(isset($_POST['editpostid']) && $_POST['editpostid'] != '') {
            $session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
            $result = array('status' => false);
            if(isset($user_id) && $user_id != '') {
                $checkuserauthclass = UserForm::isUserExistByUid($user_id);
                if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                    $editpostid = $_POST['editpostid'];
                    $result = PostForm::getUserTagIds($user_id, $editpostid);
                    return $result;
                }
            }
        }
    }

    public function actionAddUserForTagSearch() 
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('status' => false);
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                $keygun = isset($_POST['$key']) ? $_POST['$key'] : '';
                $lazyhelpcount = isset($_POST['$lazyhelpcount']) ? $_POST['$lazyhelpcount'] : 0;
                $start = $lazyhelpcount * 12;

                if($keygun != '') {
                	$data = Connect::AddUserForTagSearch($user_id, $keygun, $start, true);
                } else {
                	$data = Connect::AddUserForTagSearch($user_id, $keygun, $start);
                }
                
                $data = json_decode($data, true);
                $ids = array();
                if(isset($_POST['addUserForTagTemp']) && !empty($_POST['addUserForTagTemp'])) {
                    $ids = $_POST['addUserForTagTemp'];
                }

                if(isset($_POST['addUserForAccountSettingsArrayTemp']) && !empty($_POST['addUserForAccountSettingsArrayTemp'])) {
                    $ids = $_POST['addUserForAccountSettingsArrayTemp'];
                }

		    	$html = '';
                foreach ($data as $key => $value) {
                    $name = $value['fullname'];
                    if($keygun != '') {
                        $fname = isset($value['fname']) ? $value['fname'] : '';
                        $lname = isset($value['lname']) ? $value['lname'] : '';
                        if (stripos($fname, $keygun) === 0 || stripos($lname, $keygun) === 0 || stripos($name, $keygun) === 0) {
                        } else {
                            continue;
                        }
                	} 
  
                	$cls = '';
                	$idArray = array_values($value['_id']);
					$id = $idArray[0];
                    if(in_array($id, $ids)) {
                        $cls = 'checked';
                    }
                    
                    $profile = $this->getimage($id,'thumb');
                    $html .= "<div class='person_detail_container person_detail_div'> <span class='person_profile'> <img src='".$profile."'> </span> <div class='person_name_container'> <p class='person_name' id='".$id."'>".$name."</p> <p class='user_checkbox' style='z-index:99999;'> <input type='checkbox' ".$cls." value='".$id."' class='chk_person' name='chk_person' data-name='".$name."'> <label for='filled_for_person_".$id."'></label> </p> </div> </div>";
                }
                $result = array('status' => true, 'html' => $html);
            }
        }
        return json_encode($result, true);
    }

    public function actionCustomUserSearch() 
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('status' => false);
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                $lazyhelpcount = isset($_POST['$lazyhelpcount']) ? $_POST['$lazyhelpcount'] : 0;
                $start = $lazyhelpcount * 12; 
                $keygun = isset($_POST['$key']) ? $_POST['$key'] : '';
                if($keygun != '') {
                	$data = Connect::AddUserForTagSearch($user_id, $keygun, $start, true);
                } else {
                	$data = Connect::AddUserForTagSearch($user_id, $keygun, $start);
                }
                
                $data = json_decode($data, true);
                $ids = array();
                if(isset($_POST['addUserForTagTemp']) && !empty($_POST['addUserForTagTemp'])) {
                    $ids = $_POST['addUserForTagTemp'];
                }

                if(isset($_POST['addUserForAccountSettingsArrayTemp']) && !empty($_POST['addUserForAccountSettingsArrayTemp'])) {
                    $ids = $_POST['addUserForAccountSettingsArrayTemp'];
                }

                if(isset($_POST['customArrayTemp']) && !empty($_POST['customArrayTemp'])) {
                    $ids = $_POST['customArrayTemp'];
                }

		    	$html = '';
                foreach ($data as $value) {
                    $name = $value['fullname'];
                    if($keygun != '') {
                        $fname = isset($value['fname']) ? $value['fname'] : '';
                        $lname = isset($value['lname']) ? $value['lname'] : '';
                        if (stripos($fname, $keygun) === 0 || stripos($lname, $keygun) === 0 || stripos($name, $keygun) === 0) {
                        } else {
                            continue;
                        }
                	}

                	$cls = '';
                	$idArray = array_values($value['_id']);
					$id = $idArray[0];
                    if(in_array($id, $ids)) {
                        $cls = 'checked';
                    }
                    
                    $profile = $this->getimage($id,'thumb');
                    $html .= "<div class='person_detail_container person_detail_div'> <span class='person_profile'> <img src='".$profile."'> </span> <div class='person_name_container'> <p class='person_name' id='".$id."'>".$name."</p> <p class='user_checkbox' style='z-index:99999;'> <input type='checkbox' ".$cls." value='".$id."' class='chk_person' name='chk_person' data-name='".$name."'> <label for='filled_for_person_".$id."'></label> </p> </div> </div>";
                }
                $result = array('status' => true, 'html' => $html);
            }
        }
        return json_encode($result, true);
    }
	
	public function actionAddUserForTag() 
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('status' => false);
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                $lazyhelpcount = isset($_POST['$lazyhelpcount']) ? $_POST['$lazyhelpcount'] : 0;
                $start = $lazyhelpcount * 12;
                $data = Connect::AddUserForTag($user_id, $start);
                $data = json_decode($data, true);

                $ids = array();
                if(isset($_POST['ids']) && !empty($_POST['ids'])) {
                    $ids = $_POST['ids'];
                }

                $html = '';
                foreach ($data as $key => $value) {
                    $idArray = array_values($value['_id']);
					$id = $idArray[0];
                    //$id = (string)$value['_id']['$id'];
                    $cls = '';
                    if(in_array($id, $ids)) {
                        $cls = 'checked';
                    }
                    $name = $value['fullname'];
                    $profile = $this->getimage($id,'thumb');
                    $html .= "<div class='person_detail_container person_detail_div'> <span class='person_profile'> <img src='".$profile."'> </span> <div class='person_name_container'> <p class='person_name' id='".$id."'>".$name."</p> <p class='user_checkbox' style='z-index:99999;'> <input type='checkbox' ".$cls." value='".$id."' class='chk_person' name='chk_person' data-name='".$name."'> <label for='filled_for_person_".$id."'></label> </p> </div> </div>";
                }
                $result = array('status' => true, 'html' => $html);
            }
        }
        return json_encode($result, true);
    }

    public function actionAddUserForAccountSettings() 
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('status' => false);
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                $lazyhelpcount = isset($_POST['$lazyhelpcount']) ? $_POST['$lazyhelpcount'] : 0;
                $start = $lazyhelpcount * 12;
                $data = Connect::AddUserForTag($user_id, $start);
                $data = json_decode($data, true);

                $ids = array();
                if(isset($_POST['ids']) && !empty($_POST['ids'])) {
                    $ids = $_POST['ids'];
                }

                $html = '';
                foreach ($data as $key => $value) {
                    $idArray = array_values($value['_id']);
					$id = $idArray[0];
                    //$id = (string)$value['_id']['$id'];
                    $cls = '';
                    if(in_array($id, $ids)) {
                        $cls = 'checked';
                    }
                    $name = $value['fullname'];
                    $profile = $this->getimage($id,'thumb');
                    $html .= "<div class='person_detail_container person_detail_div'> <span class='person_profile'> <img src='".$profile."'> </span> <div class='person_name_container'> <p class='person_name' id='".$id."'>".$name."</p> <p class='user_checkbox' style='z-index:99999;'> <input type='checkbox' ".$cls." value='".$id."' class='chk_person' name='chk_person' data-name='".$name."'> <label for='filled_for_person_".$id."'></label> </p> </div> </div>";
                }
                $result = array('status' => true, 'html' => $html);
            }
        }
        return json_encode($result, true);
    } 

    public function actionCustom() 
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('status' => false);
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                $lazyhelpcount = isset($_POST['$lazyhelpcount']) ? $_POST['$lazyhelpcount'] : 0;
                $start = $lazyhelpcount * 12;
                $data = Connect::AddUserForTag($user_id, $start);	
                $data = json_decode($data, true);

                $ids = array();
                if(isset($_POST['ids']) && !empty($_POST['ids'])) {
                    $ids = $_POST['ids'];
                }
 
                $html = '';
                foreach ($data as $key => $value) {
                    $idArray = array_values($value['_id']);
					$id = $idArray[0];
                    //$id = (string)$value['_id']['$id'];
                    $cls = '';
                    if(in_array($id, $ids)) {
                        $cls = 'checked';
                    }
                    $name = $value['fullname'];
                    $profile = $this->getimage($id,'thumb');
                    $html .= "<div class='person_detail_container person_detail_div'> <span class='person_profile'> <img src='".$profile."'> </span> <div class='person_name_container'> <p class='person_name' id='".$id."'>".$name."</p> <p class='user_checkbox' style='z-index:99999;'> <input type='checkbox' ".$cls." value='".$id."' class='chk_person' name='chk_person' data-name='".$name."'> <label for='filled_for_person_".$id."'></label> </p> </div> </div>";
                }
                $result = array('status' => true, 'html' => $html);
            }
        }
        return json_encode($result, true);
    }
	
	public function actionGetlocation() 
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('status' => false);
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                $result = UserForm::getlocation($user_id);
                return $result;
            }
        }
        return false;
    }

    public function actionCombileidwithname() 
    {
    	if(isset($_POST['ids']) && !empty($_POST['ids'])) {
           	$ids = $_POST['ids'];
           	$result = UserForm::getUserNames($ids);
           	$result = json_decode($result, true);
           	$html = '';
           	$namesString = '';

           	if(!empty($result)) {
	            $for = isset($_POST['for']) ? $_POST['for'] : '';
           		$i = 1;
           		
           		foreach ($result as $key => $singleresult) {
           		    if($i>1) {
		                $namesString .= $singleresult.'<br/>';
		            }
		            $i++;
		        }

           		if (count($result) > 1) {
	                $t = count($result) - 1;

	                if($t>1) {
	                    $t = $t . ' Others';
	                } else {
	                    $t = '1 Other';
	                }

	                if($for == 'share') {
	                	$html = "<a href='javascript:void(0)' class='compose_addpersonActionShareWith tagged_person_name compose_addpersonActionShareWith' id='compose_addpersonActionShareWith'>&nbsp;".$result[0]."</a><span> and </span><a href='javascript:void(0)' class='tagged_person_number liveliketooltip compose_addpersonActionShareWith' data-title='".$namesString."'>".$t."</a>";
	            	} else if($for == 'tagged_users') {
	            		$html = "<span class='tagged_person_name userwall_tagged_users'>&nbsp;".$result[0]."</span><span>&nbsp;and&nbsp;</span><span class='tagged_person_name userwall_tagged_users liveliketooltip' data-title='".$namesString."'>".$t."</span>";
	            	} else {
	            		$html = "<span>&nbsp;With&nbsp;</span><span class='tagged_person_name compose_addpersonAction' id='compose_addpersonAction'>&nbsp;".$result[0]."</span><span>&nbsp;and&nbsp;</span><span class='tagged_person_number liveliketooltip compose_addpersonAction' data-title='".$namesString."'>".$t."</span>";
	            	}
	            } else if (count($result) == 1) {
	            	if($for == 'share') {
	            		$html = "<a href='javascript:void(0)' class='compose_addpersonActionShareWith tagged_person_name compose_addpersonActionShareWith' id='compose_addpersonActionShareWith'>&nbsp;".$result[0]."</a>";
	            	} else if($for == 'tagged_users') {
	            		$html = "<span class='tagged_person_name userwall_tagged_users'>".$result[0]."</span>";
	            	} else {
	            		$html = "<span>With</span> <span class='tagged_person_name compose_addpersonAction' id='compose_addpersonAction'>".$result[0]."</span>";
	            	}
	            }
           	}

           	$return = array('status' => true, 'html' => $html);
           	return json_encode($return, true);
        }
    }
	
	public function actionNewPostHtml() 
    {
    	return $this->render('/layouts/newpostmodal');
    }

    public function actionLoadNewPost() 
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('status' => false);
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
            	$newpostbulk = array();
		    	$record = '';
		    	$howmuch = 0;
			   	$getlastdisplaypostid = isset($_POST['$selectorid']) ? $_POST['$selectorid'] : '';
			   	if(!empty($getlastdisplaypostid)) {
			   	 	$getlastdisplaypostrecord = PostForm::find()->select(['post_created_date'])->where([(string)'_id' => $getlastdisplaypostid])->asarray()->one();
			   		$lawTime = $getlastdisplaypostrecord['post_created_date'];
			    } else {
			    	$lawTime = 0;
			    }

				$posts =  PostForm::getUserConnectionsPosts();
				
				foreach($posts as $post)
				{ 
					$highTime = $post['post_created_date'];

					if($lawTime >= $highTime) {
						break;
					}

					// you are exist in post owner restartiction list....
					$post_user_id = $post['post_user_id'];         
					$SecuritySetting = SecuritySetting::find()->where(['user_id' => $post_user_id])->asarray()->one();
					if(!empty($SecuritySetting)) {
						$filterrestrict = isset($SecuritySetting['restricted_list']) ? $SecuritySetting['restricted_list'] : '';
						$filterrestrict = explode(",", trim($filterrestrict));
						if(in_array($user_id, $filterrestrict)) {
							continue;
						}
					}

					$howmuch++;
					$existing_posts = '1';
					$cls = '';

					if((string)$post['_id'] != '') {
						$post = PostForm::find()->where([(string)'_id' => (string)$post['_id']])->one();
						if(!empty($post)) {
							$postid = (string)$post['_id'];
							$postownerid = (string)$post['post_user_id'];
							$postprivacy = $post['post_privacy'];
							$isOk = $this->filterDisplayLastPost($postid, $postownerid, $postprivacy);
							if($isOk == 'ok2389Ko') {
								$this->display_last_post($postid, $existing_posts, '', $cls); 
							}
						}
					}
				} 
			}
        }
        return false;
    }

    public function actionGetHtmlContentForBlock() 
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('status' => false);
        $ids = array();
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
            	if(isset($_POST['$label']) && $_POST['$label'] != '') {
            		$label = $_POST['$label'];

            		if(isset($_POST['ids'])) {
	            		$ids = isset($_POST['ids']) ? $_POST['ids'] : array();
	            	} else {
	            		$data = SecuritySetting::find()->where(['user_id' => $user_id])->one();
			            if(!empty($data)) {
			                if($label == 'restricted_list_label') {
			                	$ids = (isset($data['restricted_list']) && !empty($data['restricted_list'])) ? $data['restricted_list'] : '';
			                	$ids = explode(',', $ids);
			                } else if($label == 'blocked_list_label') {
			                	$ids = (isset($data['blocked_list']) && !empty($data['blocked_list'])) ? $data['blocked_list'] : '';
			                	$ids = explode(',', $ids);
			                } else if($label == 'message_filter_label') {
			                	$ids = (isset($data['message_filtering']) && !empty($data['message_filtering'])) ? $data['message_filtering'] : '';
			                	$ids = explode(',', $ids);
			                } else if($label == 'request_filter_label') {
			                	$ids = (isset($data['request_filter']) && !empty($data['request_filter'])) ? $data['request_filter'] : '';
			                	$ids = explode(',', $ids);
			                }
	            		}
	            	}

	            	$ids = array_values(array_filter($ids));   
					$returnlabel = SecuritySetting::getFullnamesWithToolTip($ids, $label);
	                $result = array('status' => true);
	                $result = array('status' => true, 'returnlabel' => $returnlabel, 'ids' => $ids);
                	return json_encode($result, true);
           	 	}
        	}
        return false;
    	}
    }	

    public function actionCheckuseradminstuff() 
    {
    	$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('status' => false);
        
        $isRecordEmpty = true;
		$storedData = array();

		$myads = PostForm::getUserAds($user_id);
		if(!empty($myads)) {
			$isRecordEmpty = false;
			$storedData['myads'] = 'yes';
		}

		$pageRecord = ArrayHelper::map(Page::find()->select(['page_name'])->where(['created_by' => $user_id])->asarray()->all(), function($demo) { return (string)$demo['_id']; }, 'page_name');
		if(!empty($pageRecord)) {
			$isRecordEmpty = false;
			$storedData['page'] = $pageRecord;
		}

		if($isRecordEmpty) {
			return $this->render('close_account2');
		} else {
			return $this->render('close_account1', array('data' => $storedData));
		}
    }

    public function actionFetchlabelmessage() 
	{
		if(isset($_POST['$key']) && $_POST['$key'] != '') {
			$key = $_POST['$key'];
			$labelArray = array(
				'nostikersfound' => array(
					'selectore' => '.giftlist-popup .emostickers-box',
					'html' => 'No Stickers Found'
				),
				'norecordfoundaddperson' => array(
					'selectore' => '#compose_addperson .person_box',
					'html' => 'No user found.'
				),
				'norecordfoundaddpersonshare' => array(
					'selectore' => '#compose_addperson_sharewith .person_box',
					'html' => 'No user found for share.'
				),
				'norecordfoundcustomuser' => array(
					'selectore' => '#custom_user_modal .person_box',
					'html' => 'No user found.'
				),
				'norecordfoundtag' => array(
					'selectore' => '#compose_addperson .person_box',
					'html' => 'No user found for tag.'
				),
				'norecordfound' => array(
					'selectore' => '.post-list',
					'html' => 'No record found.'
				),
				'nonotificationfound' => array(
					'selectore' => '#notifications-content .noti-listing',
					'html' => 'No New Notifications'
				)
			);

			if(array_key_exists($key, $labelArray)) {
				$data  = $labelArray[$key];
				$data['status'] = true;

				return json_encode($data, TRUE);
			}
		}
		return false;
    }

    public function actionStropheloginchk() {	
     	$data = UserForm::stropheloginchk();
     	return $data;
    }

    public function actionGetchatblock() {	
     	return $this->render('/layouts/chat');
    }

    public function actionPayment() {	
    	if(isset($_POST['code']) && $_POST['code'] != '') {	
     		$code = $_POST['code'];
     		$callfrom = '';
     		if($code == 'VIPUBI003322') {
     			$callfrom = 'vip'; 
     		} else if($code == 'CREDITUBI003322') {
     			$callfrom = 'credits'; 
     		} else if($code == 'VERIFYUBI003322') {
     			$callfrom = 'verify';
     		} else {
     			$callfrom = 'ads';
     		}

     		return $this->render('/layouts/payment', array('callfrom' => $callfrom));
     	}
    }

    public function actionDropdownfilter() {	    
    	if(isset($_POST) && !empty($_POST)) {
    		$post = $_POST;
    		$action = isset($post['$action']) ? $post['$action'] : '';
    		$fill = isset($post['$fill']) ? (string)$post['$fill'] : 'n';
    		$selectore = isset($post['$selectore']) ? '.'.$post['$selectore'] : '';
    		$dummy = isset($post['$dummy']) ? $post['$dummy'] : '';
    		DropdownFilter::filter($action, $fill, $selectore, $dummy);
    	}
    }

    public function actionDirectprivacy() {	    
    	if(isset($_POST['$privacy']) && $_POST['$privacy'] != '') {
    		if(isset($_POST['$privacy']) && $_POST['$privacy'] != '') {
    			$session = Yii::$app->session;
        		$user_id = (string)$session->get('user_id');
        		$customids = '';
    			$privacy = $_POST['$privacy'];
    			$postid = $_POST['$id'];
    			$issearch = 'yes';

    			if($privacy == 'custom') {
    				if(isset($_POST['customids']) && !empty($_POST['customids'])) {
	    				$customids = $_POST['customids'];
	    				$customids = implode(',', $customids);
	    			}
    			}

    			$post = PostForm::find()->where([(string)'_id' => $postid])->one();
    			if(!empty($post)) {
    				$issearch = 'no';
    			}

    			if($issearch == 'yes') {
 					$post = PlaceDiscussion::find()->where([(string)'_id' => $postid])->andWhere(['not','flagger', "yes"])->one();
					if(!empty($post)) {
	    				$issearch = 'no';
	    			}    				
    			}

    			if($issearch == 'yes') {
 					$post = PlaceReview::find()->where([(string)'_id' => $postid])->andWhere(['not','flagger', "yes"])->one();
					if(!empty($post)) {
	    				$issearch = 'no';
	    			}    				
    			}

    			if($issearch == 'no') {
    				$post->post_privacy = ucfirst(strtolower($privacy));
    				$post->customids = $customids;
    				$post->update();

	    			$matcher = array('private' => 'lock', 'connections' => 'account', 'custom' => 'settings', 'public' => 'earth');
	    			if(array_key_exists($privacy, $matcher)) {
	    				$privacylogo = $matcher[$privacy];
	    				$content = '<i class="mdi mdi-'.$privacylogo.' mdi-13px"></i><i class="mdi mdi-menu-down mdi-22px"></i>';
	    				$result = array('status' => 'yes', 'content' => $content);
	    				return json_encode($result, true);
	    			}
	    		}
    		}
    	}
    	$result = array('status' => 'no');
    	return json_encode($result, true);
    }

    public function actionXhiso() {	    
    	if(isset($_POST['type']) && $_POST['type'] != '') {
    		$type = $_POST['type'];
    		$session = Yii::$app->session;
        	$user_id = (string)$session->get('user_id');

        	$security = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();

        	if(!empty($security)) {
        		if(isset($security[$type])) {
        			$ids = $security[$type];
        			$ids = explode(',', $ids);
        			$ids = array_filter($ids);

        			if(!empty($ids)) {
        				$ids = array_values($ids);
        			}
        			return json_encode($ids, true);
        		}  	
        	}
    	}
    }
  
    public function actionPrivacymodal() {	    
		$ids = array();
		$choosedBulk = array('Private', 'Connections', 'Custom', 'Public', 'Enabled', 'Disabled');
		$privateradio = $connectionsradio = $connectofconnectradio = $customradio = $publicradio = $enabledradio = $disabledradio = ''; 
		$data = array();
		$choosed = isset($_POST['choosed']) ? $_POST['choosed'] : '';
		if(in_array($choosed, $choosedBulk)) {
			$privacy = $choosed;
		} else {
			$privacy = 'Public';
		}   	

		if(isset($_POST['fetch']) && $_POST['fetch'] != '') {
		if(isset($_POST['modeltag']) && $_POST['modeltag'] != '') {
		if(isset($_POST['label']) && $_POST['label'] != '') {
			$fetch = $_POST['fetch'];
			$modeltag = $_POST['modeltag'];
			$label = $_POST['label'];

			$fetchArray = array('yes', 'no');
			
			$session = Yii::$app->session;
	    	$user_id = (string)$session->get('user_id');
			$customli_modal = 'customli_modal';

			$accountSettings = array('lookupsetting','connect_request','connect_list','photosecurity','postprivacy','postonwallprivacy','post_review','tag_review','activitypermissionprivacy');

	    	if($user_id != '') {
	    		if($fetch == 'yes') {
	    			if($label == 'photoalbums') {
	    				$pid = $_POST['selectedpostid'];
	    			} else if($label == 'editpost') {
	    				$pid = $_POST['selectedpostid'];
	    				$customli_modal = 'customli_modal_post itiseditpost';
	    			} else if(in_array($label, $accountSettings)) {
	    			} else {    
		        		$url = $_SERVER['HTTP_REFERER'];
						$urls = explode('&',$url);
						$url = explode('=',$urls[1]);
						$pid = $url[1];
					}

	        		if($label == 'normalpost' || $label == 'editpost') {
	        			$data = PostForm::find()->where([(string)'_id' => $pid])->one();
	        		} else if(in_array($label, $accountSettings)) {
	        			$data = SecuritySetting::find()->where(['user_id' => $user_id])->one();
	        		} else if($label == 'photoalbums') {
	        			$data = UserPhotos::find()->where([(string)'_id' => $pid])->one();
	        		}
		        	
		        	if(!empty($data)) {
		        		if($label == 'photosecurity' && isset($data['view_photos'])) {
		        			$privacy = $data['view_photos'];
		        		} else if($label == 'postprivacy' && isset($data['my_post_view_status'])) {
		        			$privacy = $data['my_post_view_status'];
		        		} else if($label == 'postonwallprivacy' && isset($data['add_public_wall'])) {
		        			$privacy = $data['add_public_wall'];
		        		} else if($label == 'activitypermissionprivacy' && isset($data['add_post_on_your_wall_view'])) {
		        			$privacy = $data['add_post_on_your_wall_view'];
		        		} else if($label == 'lookupsetting') {
		        			$privacy = $data['my_view_status'];
		        		} else if($label == 'connect_request') {
		        			$privacy = $data['connect_request'];
		        		} else if($label == 'connect_list') {
		        			$privacy = $data['connect_list'];
		        		} else if($label == 'post_review') {
		        			$privacy = $data['review_posts'];
		        		} else if($label == 'tag_review') {
		        			$privacy = $data['review_tags'];
		        		} else if(isset($data['privacy'])) {
		        			$privacy = $data['privacy'];
		        		} else if($label == 'photoalbums') {
		        			$privacy = $data['post_privacy'];
		        		}

		        		if($privacy == 'Custom') {
		        			if($label == 'photosecurity' && isset($data['view_photos_custom'])) {
			        			$ids = $data['view_photos_custom'];
			        			$ids = explode(',', $ids);
			        		} else if($label == 'postprivacy' && isset($data['my_post_view_status_custom'])) {
			        			$ids = $data['my_post_view_status_custom'];
			        			$ids = explode(',', $ids);
			        		} else if($label == 'postonwallprivacy' && isset($data['add_public_wall_custom'])) {
			        			$ids = $data['add_public_wall_custom'];
			        			$ids = explode(',', $ids);
			        		} else if($label == 'activitypermissionprivacy' && isset($data['add_post_on_your_wall_view_custom'])) {
			        			$ids = $data['add_post_on_your_wall_view_custom'];
			        			$ids = explode(',', $ids);
			        		} else {
			        			$ids = $data['customids'];
				        		$ids = explode(',', $ids);
				        	}
		        		}  	

		        	}
				}

				if(!empty($ids)) {
					$ids = array_values(array_filter($ids));
				}



				if($privacy == 'Custom') {
					$editLink = '<a href="javascript:void(0)"> <i class="zmdi zmdi-edit mdi-20px"></i> </a>';
				} else {
					$editLink = '';
				}
				if($privacy == 'Private') { $privateradio = 'checked'; }
				else if($privacy == 'Connections') { $connectionsradio = 'checked'; }
				else if($privacy == 'Custom') { $customradio = 'checked'; }
				else if($privacy == 'Public') { $publicradio = 'checked'; }
				else if($privacy == 'Enabled') { $enabledradio = 'checked'; }
				else if($privacy == 'Disabled') { $disabledradio = 'checked'; }


				$html = '<div class="content_header"> <button class="close_span waves-effect"> <i class="mdi mdi-close mdi-20px material_close"></i> </button> <p class="selected_person_text">Choose privacy</p> <a href="javascript:void(0)" id="customdoneprivacymodal" data-privacy="'.$modeltag.'" class="done_btn action_btn">Done</a> </div> <div class="person_box"> <div class="row"> <div class="head privatehead"> <div class="col s2 m2 l2 xl2 col1 valign-wrapper center-align"><i class="mdi mdi-lock mdi-20px center-align"></i></div> <div class="col s8 m8 l8 xl8 col2 valign-wrapper">Private</div> <div class="col s2 m2 l2 xl2 col3 valign-wrapper center-align"> <input name="privacyradiomodal" type="radio" id="privacymodalPrivate" value="Private" '.$privateradio.'> <label for="privacymodalPrivate" class="label"></label> </div> </div> <div class="clear"></div> <div class="head connectionshead"> <div class="col s2 m2 l2 xl2 col1 valign-wrapper center-align"><i class="mdi mdi-account mdi-20px center-align"></i></div> <div class="col s8 m8 l8 xl8 col2 valign-wrapper">Connections</div> <div class="col s2 m2 l2 xl2 col3 valign-wrapper center-align"> <input name="privacyradiomodal" type="radio" id="privacymodalConnections" value="Connections" '.$connectionsradio.'/> <label for="privacymodalConnections" class="label"></label> </div> </div> <div class="clear"></div> <div class="head '.$customli_modal.' customhead"> <div class="col s2 m2 l2 xl2 col1 valign-wrapper center-align"><i class="mdi mdi-settings mdi-20px center-align"></i></div> <div class="col s8 m8 l8 xl8 col2 valign-wrapper center-align"> <div class="col s10 m10 l10 xl10 left-align internal">Custom</div> <div class="col s2 m2 l2 xl2 left right-align internal">'.$editLink.'</div> </div> <div class="col s2 m2 l2 xl2 col3 valign-wrapper center-align"> <input name="privacyradiomodal" type="radio" id="privacymodalCustom" value="Custom" '.$customradio.'/> <label for="privacymodalCustom" class="label"></label> </div> </div> <div class="clear"></div> <div class="head publichead"> <div class="col s2 m2 l2 xl2 col1 valign-wrapper center-align"><i class="mdi mdi-earth mdi-20px center-align"></i></div> <div class="col s8 m8 l8 xl8 col2 valign-wrapper">Public</div> <div class="col s2 m2 l2 xl2 col3 valign-wrapper center-align"> <input name="privacyradiomodal" type="radio" id="privacymodalPublic" value="Public" '.$publicradio.'/> <label for="privacymodalPublic" class="label"></label> </div> </div> <div class="uniquehead"> <div class="col s2 m2 l2 xl2 col1 valign-wrapper center-align"><i class="mdi mdi-check-circle-outline mdi-20px center-align"></i></div> <div class="col s8 m8 l8 xl8 col2 valign-wrapper center-align">Enabled</div> <div class="col s2 m2 l2 xl2 col3 valign-wrapper center-align"> <input name="privacyradiomodal" type="radio" id="privacymodalEnabled" value="Enabled" '.$enabledradio.' /> <label for="privacymodalEnabled" class="label"></label> </div> </div> <div class="clear"></div> <div class="uniquehead"> <div class="col s2 m2 l2 xl2 col1 valign-wrapper center-align"><i class="mdi mdi-minus-circle-outline mdi-20px center-align"></i></div> <div class="col s8 m8 l8 xl8 col2 valign-wrapper center-align">Disabled</div> <div class="col s2 m2 l2 xl2 col3 valign-wrapper center-align"> <input name="privacyradiomodal" type="radio" id="privacymodalDisabled" value="Disabled" '.$disabledradio.' /> <label for="privacymodalDisabled" class="label"></label> </div> </div> </div> </div>';

				$result = array('ids' => $ids, 'html' => $html);
				return json_encode($result, true);


	        }
		}
		}
		}
	}

     public function actionGetsinglepostcustom() {	    
    	if(isset($_POST['id']) && $_POST['id'] != '') {
    		$id = $_POST['id'];
    		$session = Yii::$app->session;
        	$user_id = (string)$session->get('user_id');
        	$result = array();
        	$issearch = 'yes';

        	$data = PostForm::find()->where([(string)'_id' => $id])->asarray()->one();
        	if(!empty($data)) {
        		$issearch = 'no';
        		$post_privacy = $data['post_privacy'];
        		if($post_privacy == 'Custom') {
        			$ids = $data['customids'];
        			$ids = explode(',', $ids);
        			$result = array('ids' => $ids);
        		}
        	}

        	if($issearch == 'yes') {
	        	$data = PlaceDiscussion::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->asarray()->one();
	        	if(!empty($data)) {
	        		$issearch = 'no';
	        		$post_privacy = $data['post_privacy'];
	        		if($post_privacy == 'Custom') {
	        			$ids = $data['customids'];
	        			$ids = explode(',', $ids);
	        			$result = array('ids' => $ids);
	        		}
	        	}
	        }

	        if($issearch == 'yes') {
	        	$data = PlaceReview::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->asarray()->one();
	        	if(!empty($data)) {
	        		$issearch = 'no';
	        		$post_privacy = $data['post_privacy'];
	        		if($post_privacy == 'Custom') {
	        			$ids = $data['customids'];
	        			$ids = explode(',', $ids);
	        			$result = array('ids' => $ids);
	        		}
	        	}
	        }

        	return json_encode($result, true);
    	}
    }

     public function actionDirectprivacyalbumphoto() {	    
    	if(isset($_POST['$privacy']) && $_POST['$privacy'] != '') {
    		if(isset($_POST['$id']) && $_POST['$id'] != '') {
    			$session = Yii::$app->session;
        		$user_id = (string)$session->get('user_id');
        		$customids = '';
    			$privacy = $_POST['$privacy'];
    			$postid = $_POST['$id'];

    			if($privacy == 'custom') {
    				if(isset($_POST['customids']) && !empty($_POST['customids'])) {
	    				$customids = $_POST['customids'];
	    				$customids = implode(',', $customids);
	    			}
    			}

    			$post = UserPhotos::find()->where([(string)'_id' => $postid])->one();
    			if(!empty($post)) {
    				$post->post_privacy = ucfirst(strtolower($privacy));
    				$post->customids = $customids;
    				$post->update();

	    			$matcher = array('private' => 'lock', 'connections' => 'account', 'custom' => 'settings', 'public' => 'earth');
	    			if(array_key_exists($privacy, $matcher)) {
	    				$privacylogo = $matcher[$privacy];
	    				$content = '<i class="mdi mdi-'.$privacylogo.' mdi-16px"></i>';
	    				$result = array('status' => 'yes', 'content' => $content);
	    				return json_encode($result, true);
	    			}
	    		}
    		}
    	}
    	$result = array('status' => 'no');
    	return json_encode($result, true);
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

    public function actionHotellist()
    {
    	$session = Yii::$app->session;
		$uid = (string)$session->get('user_id');

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
  
		return $this->render('hotellist',array('place'=>$place,'placetitle'=>$placetitle,'placefirst'=>$placefirst,'lat' => $lat,'lng' => $lng, 'checkuserauthclass' => $checkuserauthclass));
    }

    public function actionRestaurantlist()
    {
    	$session = Yii::$app->session;
		$uid = (string)$session->get('user_id');

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

		return $this->render('restaurantlist',array('place'=>$place,'placetitle'=>$placetitle,'placefirst'=>$placefirst,'lat' => $lat,'lng' => $lng, 'checkuserauthclass' => $checkuserauthclass));
    }

    public function actionAttractionlist()
    {
    	$session = Yii::$app->session;
		$uid = (string)$session->get('user_id');

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

		return $this->render('attractionlist',array('place'=>$place,'placetitle'=>$placetitle,'placefirst'=>$placefirst,'lat' => $lat,'lng' => $lng, 'checkuserauthclass' => $checkuserauthclass));	
    }

    public function actionHotelmap()
    {
    	$session = Yii::$app->session;
		$uid = (string)$session->get('user_id');

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

		return $this->render('hotelmap',array('place'=>$place,'placetitle'=>$placetitle,'placefirst'=>$placefirst,'lat' => $lat,'lng' => $lng, 'checkuserauthclass' => $checkuserauthclass));
	}

	public function actionRestaurantmap()
    {
    	$session = Yii::$app->session;
		$uid = (string)$session->get('user_id');

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

		return $this->render('restaurantmap',array('place'=>$place,'placetitle'=>$placetitle,'placefirst'=>$placefirst,'lat' => $lat,'lng' => $lng, 'checkuserauthclass' => $checkuserauthclass));
	}

	public function actionPopluarphotoacrdscn()
    {
    	$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');

		if(isset($user_id) && $user_id != '') {
		    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
		} else {
		    $checkuserauthclass = 'checkuserauthclassg';
		}   

		$w = $_POST['w'];

		if($w <= 600) {
			$gallery = Gallery::find()->where(['user_id' => (string)$user_id, 'type' => 'places'])->andWhere(['not','flagger', "yes"])->limit(2)->asarray()->all();  
		} else {
			$gallery = Gallery::find()->where(['user_id' => (string)$user_id, 'type' => 'places'])->andWhere(['not','flagger', "yes"])->limit(3)->asarray()->all();  
		}

		$gallery__KS = 1;
		foreach($gallery as $gallery_item) {
            $hideids = isset($gallery_item['hideids']) ? $gallery_item['hideids'] : '';
            $hideids = explode(',', $hideids);
            if(in_array($user_id, $hideids)) {
                continue;
            }

            $galimname = $gallery_item['image'];
            if(file_exists($galimname)) {
                $gallery_item_id = $gallery_item['_id'];
                $eximg = $galimname;
                $inameclass = preg_replace('/\\.[^.\\s]{3,4}$/', '', $galimname);
                
                $picsize = $imgclass = '';
                $like_count = Like::getLikeCount((string)$gallery_item_id);
                $comments = Comment::getAllPostLikeCount((string)$gallery_item_id);
                $title = $gallery_item['title'];
                
                $like_active = Like::find()->where(['post_id' => (string) $gallery_item_id,'status' => '1','user_id' => (string) $user_id])->one();
                if(!empty($like_active)) {
                    $like_active = 'active';
                } else {
                    $like_active = '';
                }
                
                $time = Yii::$app->EphocTime->comment_time(time(),$gallery_item['created_at']);
                $puserid = (string)$gallery_item['user_id'];
                
                $puserdetails = LoginForm::find()->where(['_id' => $puserid])->one();
                if($puserid != $user_id) {
                    $galusername = ucfirst($puserdetails['fname']) . ' ' . ucfirst($puserdetails['lname']);
                    $isOwner = false;
                } else {
                    $galusername = 'You';
                    $isOwner = true;
                }
                
                $like_buddies = Like::getLikeUser($inameclass .'_'. $gallery_item['_id']);
                $newlike_buddies = array();
                foreach($like_buddies as $like_buddy) {
                    $newlike_buddies[] = ucwords(strtolower($like_buddy['fullname']));
                }
                $newlike_buddies = implode('<br/>', $newlike_buddies);  

                $val = getimagesize($eximg);
                $picsize .= $val[0] .'x'. $val[1] .', ';
                if($val[0] > $val[1]) {
                    $imgclass = 'himg';
                } else if($val[1] > $val[0]) {
                    $imgclass = 'vimg';
                } else {
                    $imgclass = 'himg';
                }
                
                $isEmpty = false;
                $KSOW = '';
				if($gallery__KS > 2) {
					$KSOW = 'hide-on-small-only';
				}

                ?> 
                <div data-src="<?=$eximg?>" class="allow-gallery <?=$KSOW?>" data-sizes="<?=$gallery_item_id?>|||Gallery">
                    <img class="himg" src="<?=$eximg?>"/>
                    <?php if($isOwner) { ?> 
                    <a href="javascript:void(0)" class="removeicon prevent-gallery" data-id="<?=$gallery_item_id?>" onclick="removepic(this)"><i class="mdi mdi-delete"></i></a>
                    <?php } ?>   
                    <div class="caption">
                        <div class="left">
                            <span class="title"><?=$title?> ( <?=$time?> )</span> <br>
                            <span class="attribution">By <?=$galusername?></span>
                        </div>
                        <div class="right icons">
                            <a href="javascript:void(0)" class="prevent-gallery like custom-tooltip pa-like liveliketooltip liketitle_<?=$gallery_item_id?> <?=$like_active?>" onclick="doLikeAlbumbImages('<?=$gallery_item_id?>');" data-title="<?=$newlike_buddies?>">
                                <i class="mdi mdi-thumb-up-outline mdi-15px"></i>
                            </a>
                            <?php if($like_count >0) { ?>
                                <span class="likecount_<?=$gallery_item_id?> lcount"><?=$like_count?></span>
                            <?php } else { ?>
                                <span class="likecount_<?=$gallery_item_id?> lcount"></span>
                            <?php } ?>
                            
                            <a href="javascript:void(0)">
                                <i class="mdi mdi-comment-outline mdi-15px cmnt"></i>
                                <?php if($comments > 0){ ?>
                                    <span class="lcount commentcountdisplay_<?=$gallery_item_id?>"><?=$comments?></span>
                                <?php } else { ?>
                                    <span class="lcount commentcountdisplay_<?=$gallery_item_id?>"></span>
                                <?php } ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php 
            $gallery__KS++;
            } 
        } 
	}

	public function actionDelupdpht()
    {

    	$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');

		$HTTP_REFERER = $_SERVER['HTTP_REFERER'];
		$urls = explode('&',$HTTP_REFERER);
		$url = explode('=',$urls[1]);
		$post_id = $url[1];
		$post = $_POST;
		$imgSrc = $post['imgSrc'];

    	if (strpos($HTTP_REFERER, 'camping') !== false) {
    		$data = Camping::find()->where([(string)'_id' => $post_id])->andWhere(['not','flagger', "yes"])->one();		
		} else if (strpos($HTTP_REFERER, 'homestay') !== false) {
			$data = Homestay::find()->where([(string)'_id' => $post_id])->andWhere(['not','flagger', "yes"])->one();
		} else if (strpos($HTTP_REFERER, 'localdine') !== false) {
			$data = Localdine::find()->where([(string)'_id' => $post_id])->andWhere(['not','flagger', "yes"])->one();
		} else if (strpos($HTTP_REFERER, 'localdriver') !== false) {
			$data = LocaldriverPost::find()->where([(string)'_id' => $post_id])->andWhere(['not','flagger', "yes"])->one();
		} else if (strpos($HTTP_REFERER, 'localguide') !== false) {
			$data = LocalguidePost::find()->where([(string)'_id' => $post_id])->andWhere(['not','flagger', "yes"])->one();
		}

		if(!empty($data)) {
			$images = isset($data['images']) ? $data['images'] : '';
			$images = explode(',', $images);
			$images = array_values(array_filter($images));

			$pos = array_search($imgSrc, $images);

			unset($images[$pos]);

			$images = array_values(array_filter($images));
			$images = implode(',', $images);				
			$data->images = $images;
			$data->update();

			$result = array('status' => true);
			return json_encode($result, true);
		}

		$result = array('status' => false);
		return json_encode($result, true);
	}

	public function actionGetmapinfo()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
        	$place = Yii::$app->params['place'];
			$placetitle = Yii::$app->params['placetitle'];
			$placefirst = Yii::$app->params['placefirst'];
			$lat = Yii::$app->params['lat'];
			$lng = Yii::$app->params['lng'];

			$placetitle = $_POST['placetitle']; 
			$type = $_POST['type'];
			if($type == 'lodge'){$title = 'hotels';$default = 'Hotels';}
			else if($type == 'dine'){$title = 'restaurants';$default = 'Restaurants';}
			else if($type == 'todo'){$title = 'park';$default = 'Attractions';}
			$placeapi = str_replace(' ','+',$placetitle);
			
			$GApiKeyL = $GApiKeyP = Googlekey::getkey();
			
			$urlhotel="https://maps.googleapis.com/maps/api/place/textsearch/json?query=$title+in+$placeapi&key=$GApiKeyP&type=$title";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL,$urlhotel); 
			$result=curl_exec($ch);
			curl_close($ch);
			$rs = json_decode($result, true);
			$ql = $rs['status'];
			return $this->render('mapinfo',array('user_id' => $user_id,'placetitle' => $placetitle,'gk'=>$GApiKeyP,'rs'=>$rs,'ql'=>$ql,'default'=>$default));
        }
        else
        {
            return $this->goHome();
        }
    }

    public function actionGetplacehotels()
    {
        if(isset($_POST) && !empty($_POST))
        {
			$baseUrl = $_POST['baseUrl'];
			$place = $_POST['place'];
			$placetitle = $_POST['placetitle'];
			$placefirst = $_POST['placefirst'];
			$count = $_POST['count'];
			$placeapi = str_replace(' ','+',$placetitle);
			$token = $_POST['token'];
			if($token != 'empty'){$tk = $token;}
			else{$tk = '';}

			$GApiKeyL = $GApiKeyP = Googlekey::getkey();
			$ql = '';
			$rs = '';

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://maps.googleapis.com/maps/api/place/textsearch/json?key='.$GApiKeyP.'&query=hotels+in+'.$placeapi.'&type=lodging&next_page_token='.$tk.'');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			$output = curl_exec($ch);   
			$rs = json_decode($output, true);

			if(isset($rs['results']) && !empty($rs['results'])) {
				$ql = $rs['status'];  
				$file = 'hotels';
				if($count == 'all'){$file = 'hotels_all';}

				return $this->render($file,array('baseUrl' => $baseUrl,'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'rs'=> $rs,'gk'=> $GApiKeyP,'ql'=> $ql,'count'=> $count,'tk'=>$tk));
			}
        }
        else
        {
            return $this->goHome();
        }
    }

	public function actionGetplacerest()
    {
        if(isset($_POST) && !empty($_POST))
        {
			$baseUrl = $_POST['baseUrl'];
			$place = $_POST['place'];
			$placetitle = $_POST['placetitle'];
			$placefirst = $_POST['placefirst'];
			$count = $_POST['count'];
			$placeapi = str_replace(' ','+',$placetitle);
			$token = $_POST['token'];
			if($token != 'empty'){$tk = $token;}
			else{$tk = '';}

			$GApiKeyL = $GApiKeyP = Googlekey::getkey();
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://maps.googleapis.com/maps/api/place/textsearch/json?key='.$GApiKeyP.'&query=restaurants+in+'.$placeapi.'&type=restaurant&next_page_token='.$tk.'');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			$output = curl_exec($ch);   
			$rs = json_decode($output, true);
			if(isset($rs['results']) && !empty($rs['results'])) {
				$ql = $rs['status']; 
				$file = 'restaurants';
				if($count == 'all'){$file = 'restaurants_all';}
				return $this->render($file,array('baseUrl' => $baseUrl,'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'rs'=> $rs,'gk'=> $GApiKeyP,'ql'=> $ql,'count'=> $count,'tk'=>$tk));
			}
        }
    }
	
	public function actionGetplaceattr()
    {
        if(isset($_POST) && !empty($_POST))
        {
			$lazyhelpcount = isset($_POST['$lazyhelpcount']) ? $_POST['$lazyhelpcount'] : 0;
			$start = $lazyhelpcount * 12;
			$baseUrl = $_POST['baseUrl'];
			$place = $_POST['place'];
			$placetitle = trim($_POST['placetitle']);
			$placefirst = trim($_POST['placefirst']);
			$count = $_POST['count'];
			$token = $_POST['token'];
			$placeapi = str_replace(' ','+',$placetitle);
			$file = 'things';
			if($count == 'all'){$file = 'things_all';}
			$pcount = substr_count($place,",");
			if($pcount > 0) {
				$placet = (explode(", ",$place));
				$placecountry = $placet[1]; 
				$type = 'City';
			} else {
				$placecountry = '';
				$type = 'Country';
			}

			if($token == 'empty'){$sort = 'PriceUSD';}
			else{$sort = 'AvgRating';} 
			$todos = Tours::getTodos($placefirst,$placecountry,$type,$sort,$start);
			if(!empty($todos)) {
				return $this->render($file,array('baseUrl' => $baseUrl,'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'todos'=> $todos,'count'=> $count,'token'=> $token, 'lazyhelpcount' => $lazyhelpcount));
			}
        }
        else
        {
            return $this->goHome();
        }
    }

    public function actionGetplaceattractions()
    {
    	$file = 'attractions_pagination';
		$attractions = Attractions::getAttractionsAll();
		if(!empty($attractions)) {
			return $this->render($file, array('attractions' => $attractions));
		}
    }

    public function actionGetplacetopplaces()
	{
		$file = 'top_places_pagination';
		$topplaces = TopPlaces::getTopPlacesAll();
		if(!empty($topplaces)) {
			return $this->render($file, array('topplaces' => $topplaces));
		}
	}
	
	public function actionGetplacedests()
    {
        if(isset($_POST) && !empty($_POST))
        {
			$baseUrl = $_POST['baseUrl'];
			$place = $_POST['place'];
			$placetitle = $_POST['placetitle'];
			$placefirst = $_POST['placefirst'];
			$count = $_POST['count'];
			$lat = $_POST['lat'];
			$lng = $_POST['lng'];
			$file = 'dests';
			$cities = array();
			$connected = @fsockopen("www.geobytes.com", 80); //website, port  (try 80 or 443)
			if($connected)
			{
				$geocode = file_get_contents('http://gd.geobytes.com/GetNearbyCities?radius=1000&Latitude='.$lat.'&Longitude='.$lng.'&limit=4');
				$cities = json_decode($geocode, true);
			}
			return $this->render($file,array('baseUrl' => $baseUrl,'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'cities'=> $cities,'count'=> $count));
        }
    }
	
	public function actionGetplacetravellers()
    {
        if(isset($_POST) && !empty($_POST))
        {
        	$session = Yii::$app->session;
        	$user_id = (string)$session->get('user_id');
        	if(isset($user_id) && $user_id != '') {
	            $checkuserauthclass = UserForm::isUserExistByUid($user_id); 
	        } else {
	            $checkuserauthclass = 'checkuserauthclassg';
	        }

			$baseUrl = $_POST['baseUrl'];
			$count = $_POST['count'];
			$place = $_POST['place'];
			$placetitle = $_POST['placetitle'];
			$placefirst = $_POST['placefirst'];
			$placeapi = str_replace(' ','+',$placetitle);
			$file = 'travellers';
			if($count == 'all'){$file = 'travellers_all';}
			$getdest = Destination::getDestUsers($placetitle,'future', $user_id); 
			$getUsers = array_values(array_filter($getdest));
			return $this->render($file,array('baseUrl' => $baseUrl,'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'getUsers'=> $getUsers, 'checkuserauthclass' => $checkuserauthclass));
		}
    }

    public function actionGetplacelocals()
    {
    	$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
        	$checkuserauthclass = UserForm::isUserExistByUid($user_id); 
        } else {
        	$checkuserauthclass = 'checkuserauthclassg';
        }

        if(isset($_POST) && !empty($_POST))
        {
			$baseUrl = $_POST['baseUrl'];
			$count = $_POST['count'];
			$place = $_POST['place'];
			$placetitle = str_replace("'","",$_POST['placetitle']);
			$placefirst = $_POST['placefirst'];
			$file = 'locals';
			if($count == 'all'){$file = 'locals_all';}
			$getUsers = LoginForm::find()->where(['like','city', $placetitle])->andwhere(['status'=>'1'])->asarray()->all();

			if(empty($getUsers))
			{
				$placetitle = str_replace(',',' -',$_POST['placetitle']);
				$getUsers = LoginForm::find()->where(['like','city',$placetitle])->andwhere(['status'=>'1'])->asarray()->all();
				if(empty($getUsers))
				{
					if(substr( $_POST['placetitle'], 0, 14 ) === "Japan")
					{
						$placetitle = "Japan";
					}
					$getUsers = LoginForm::find()->where(['like','city',$placetitle])->andwhere(['status'=>'1'])->asarray()->all();
				} 
			}
			return $this->render($file,array('baseUrl' => $baseUrl,'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'getUsers'=> $getUsers, 'checkuserauthclass' => $checkuserauthclass));
		}
    }
	
	public function actionGetplacephotos()
    {
    	$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if(isset($_POST) && !empty($_POST))
        {
			$baseUrl = $_POST['baseUrl'];
			$place = $_POST['place'];
			$placetitle = $_POST['placetitle'];
			$placefirst = $_POST['placefirst'];
			$placeapi = str_replace(' ','+',$placetitle);
			$file = 'photos_all';
			
			$gallery = Gallery::find()->where(['user_id' => (string)$user_id, 'type' => 'places', 'placetitle' => $placetitle])->andWhere(['not','flagger', "yes"])->asarray()->all(); 

			return $this->render($file,array('baseUrl' => $baseUrl,'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst, 'gallery' => $gallery));
		}
    }

    public function actionGetplacereviews()
    {
        if(isset($_POST) && !empty($_POST))
        {
        	$session = Yii::$app->session;
        	$user_id = (string)$session->get('user_id');
        	if(isset($user_id) && $user_id != '') {
	        	$checkuserauthclass = UserForm::isUserExistByUid($user_id); 
	        } else {
	        	$checkuserauthclass = 'checkuserauthclassg';   
	        }

			$baseUrl = $_POST['baseUrl'];
			$count = $_POST['count'];
			$place = $_POST['place'];
			$placetitle = $_POST['placetitle'];
			$placefirst = $_POST['placefirst'];
			$placeapi = str_replace(' ','+',$placetitle);
			$file = 'reviews';
			if($count == 'all'){$file = 'reviews_all';}
			$getpplacereviews = PlaceReview::getPlaceReviews($place,'reviews',$count);
			$getpplacereviewscount = PlaceReview::getPlaceReviewsCount($place,'reviews');
			if($getpplacereviewscount == 0){$getpplacereviewscountt = 1;}
			else{$getpplacereviewscountt = $getpplacereviewscount;}
			$totalcnt = PlaceReview::find()->where(['is_deleted'=>"0",'currentlocation'=>"$place",'placetype'=>'reviews'])->andWhere(['not','flagger', "yes"])->all();
			$sum = 0;
			foreach($totalcnt as $totalcnts)
			{
				$sum += $totalcnts['placereview'];
			}
			$avgcnt = ceil($sum / $getpplacereviewscountt);
			return $this->render($file,array('baseUrl' => $baseUrl,'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'getpplacereviews' => $getpplacereviews,'getpplacereviewscount' => $getpplacereviewscount,'avgcnt' => $avgcnt, 'checkuserauthclass' => $checkuserauthclass));
		}
    }
	
	public function actionGetplacetip()
    {
    	$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
        	$checkuserauthclass = UserForm::isUserExistByUid($user_id); 
        } else {
        	$checkuserauthclass = 'checkuserauthclassg';   
        }

        if(isset($_POST) && !empty($_POST))
        {
			$baseUrl = $_POST['baseUrl'];
			$count = $_POST['count'];
			$place = $_POST['place'];
			$placetitle = $_POST['placetitle'];
			$placefirst = $_POST['placefirst'];
			$placeapi = str_replace(' ','+',$placetitle);
			$file = 'tip';
			if($count == 'all'){$file = 'tip_all';} 
			$getpplacereviews = PlaceTip::getPlaceReviews($place,'tip',$count);
			$getpplacereviewscount = PlaceTip::getPlaceReviewsCount($place,'tip');

			return $this->render($file,array('checkuserauthclass' => $checkuserauthclass,'baseUrl' => $baseUrl,'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'getpplacereviews'=> $getpplacereviews,'getpplacereviewscount'=> $getpplacereviewscount));
		}
    }

	public function actionGetplaceask()
    {
    	$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
        	$checkuserauthclass = UserForm::isUserExistByUid($user_id); 
        } else {
        	$checkuserauthclass = 'checkuserauthclassg';
        }

        if(isset($_POST) && !empty($_POST))		
        {
			$baseUrl = $_POST['baseUrl'];
			$count = $_POST['count'];
			$place = $_POST['place'];
			$placetitle = $_POST['placetitle'];
			$placefirst = $_POST['placefirst'];
			$placeapi = str_replace(' ','+',$placetitle);
			$file = 'ask';
			if($count == 'all'){$file = 'ask_all';}
			$getpplacereviews = PlaceAsk::getPlaceReviews($place,'ask',$count);
			$getpplacereviewscount = PlaceAsk::getPlaceReviewsCount($place,'ask');
			return $this->render($file,array('checkuserauthclass' => $checkuserauthclass, 'baseUrl' => $baseUrl,'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'getpplacereviews'=> $getpplacereviews,'getpplacereviewscount'=> $getpplacereviewscount));
		}
    }
	
	public function actionGetplacediscussion()
    {
    	$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
        	$checkuserauthclass = UserForm::isUserExistByUid($user_id); 
        } else {
        	$checkuserauthclass = 'checkuserauthclassg';
        }
 
        if(isset($_POST) && !empty($_POST))
        {
			$baseUrl = $_POST['baseUrl']; 
			$count = $_POST['count'];
			$place = $_POST['place'];
			$placetitle = $_POST['placetitle'];
			$placefirst = $_POST['placefirst'];
			$placeapi = str_replace(' ','+',$placetitle);
			$file = 'discussion'; 
			if($count == 'all'){$file = 'discussion_all';}
			$getpplacereviews = PlaceDiscussion::getPlaceReviews($place,'discussion',$count); 
			$getpplacereviewscount = PlaceDiscussion::getPlaceReviewsCount($place,'discussion');
			return $this->render($file,array('checkuserauthclass' => $checkuserauthclass, 'baseUrl' => $baseUrl,'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'getpplacereviews'=> $getpplacereviews,'getpplacereviewscount'=> $getpplacereviewscount));
		}
    }

    	public function actionEditPostPreSetAsk()
	{


	    $session = Yii::$app->session;
	    $userid = $user_id = (string)$session->get('user_id');
		if(isset($userid) && $userid != '') {
			$authstatus = UserForm::isUserExistByUid($userid);
			if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
				$data['auth'] = $authstatus;
				return $authstatus;
			} else {
		        $postid = isset($_POST['editpostid']) ? $_POST['editpostid'] : '';
		        $post = PlaceAsk::find()->where(['_id' => $postid])->andWhere(['not','flagger', "yes"])->one();
				return $this->render('editaskpopup', array('post' => $post));
		        
			}
		} else {
			return 'checkuserauthclassg';
		}	
	}
	
    public function actionEditPostPreSetTip()  
    {
    	$session = Yii::$app->session;
	    $userid = $user_id = (string)$session->get('user_id');
		if(isset($userid) && $userid != '') {
			$authstatus = UserForm::isUserExistByUid($userid);
			if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
				$data['auth'] = $authstatus;
				return $authstatus;
			} else {
		        $postid = isset($_POST['editpostid']) ? $_POST['editpostid'] : '';
		        $post = PlaceTip::find()->where(['_id' => $postid])->andWhere(['not','flagger', "yes"])->one();
				return $this->render('edittippopup', array('post' => $post));
			}
		} else {
			return 'checkuserauthclassg';
		}	
    }
	
	public function actionEditPostPreSetPlaceReview()
    {
        $session = Yii::$app->session;
        $userid = $user_id = (string)$session->get('user_id');
		if(isset($userid) && $userid != '') {
			$authstatus = UserForm::isUserExistByUid($userid);
			if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
				$data['auth'] = $authstatus;
				return $authstatus;
			} else {
		        $postid = isset($_POST['editpostid']) ? $_POST['editpostid'] : '';
		        $post = PlaceReview::find()->where(['_id' => $postid])->andWhere(['not','flagger', "yes"])->one();
		        return $this->render('editreviewpopup', array('post' => $post));
			}
		} else {
			return 'checkuserauthclassg';	
		}
    }

    public function actionTakeDestAction()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		$data = array();
		if(isset($user_id) && $user_id != '') {
			$authstatus = UserForm::isUserExistByUid($user_id);
			if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
				return $authstatus;
			} else {       
				$type = $_POST['type'];
				$place = $_POST['place'];
				return Destination::addUserDest($user_id,$type,$place);
			}
        } else {
        	return 'checkuserauthclassg';
        }
		
    }
	
	public function actionDelDestAction()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$destid = $_POST['id'];
			return Destination::removeUserDest($destid);
		}
        else
        {
            return $this->goHome();
        }
    }

    public function actionPlacesbanner($baseUrl = '',$place = '',$placetitle = '',$placefirst = '')
    {
        if(isset($_POST) && !empty($_POST))
        {
			$place = $_POST['place'];
			$placetitle = $_POST['placetitle'];
			$placefirst = $_POST['placefirst'];
			$placetitle = str_replace(' ','+',$placetitle);
			$destimage = $this->getplaceimage($placetitle);
			return $destimage;
        }
    }

    public function actionGettourlistparticular()
    {	
        if(isset($_POST) && !empty($_POST))
        {
			$city = isset($_POST['city']) ? $_POST['city'] : '';
			$name = isset($_POST['name']) ? $_POST['name'] : '';

			$todos = Tours::getListParticular($city, $name);
			$placefirst = $name .', '.$city;
			$placetitle = $name .' '.$city;

			return $this->render('things_all', array('placetitle' => $placetitle,'placefirst' => $placefirst,'todos'=> $todos,'count'=> 'all','token'=> 'empty'));
        }
        else
        {
            return $this->goHome();
        }
    }

    public function actionGetHotelList()
    {
        if(isset($_POST['placetitle']) && $_POST['placetitle'] != '')
        {
        	$placetitle = $_POST['placetitle'];
        	$placetitle = urlencode($placetitle);
			$placetitle = htmlentities($placetitle);

			$GApiKeyL = $GApiKeyP = Googlekey::getkey();
 	
        	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://maps.googleapis.com/maps/api/geocode/json?key='.$GApiKeyP.'&query=hotels+in+'.$placetitle.'');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			$output = curl_exec($ch);   
			$rs = json_decode($output, true);
			if(isset($rs['results']) && !empty($rs['results'])) {
				$newapidata = array_slice($rs['results'], 0, 3);
				return $this->render('hotellist',array('data' => $newapidata, 'GApiKeyP' => $GApiKeyP));
			}
        }
    }

    public function actionFlagpost()
    {
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		$data = array();
		if(isset($_POST['id']) && $_POST['id'] != '') {
			if(isset($_POST['module']) && $_POST['module'] != '') {
				$id = $_POST['id'];
				$module = $_POST['module'];
				$moduleArray = array('discussion', 'review', 'tip', 'question', 'photostream', 'blog', 'collections', 'trip', 'localdine', 'homestay', 'camping', 'localguide', 'localdriver');
				if(in_array($module, $moduleArray)) {
					if($module == 'discussion') {
						$post = PlaceDiscussion::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
					} else if($module == 'review') {
						$post = PlaceReview::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
					} else if($module == 'question') {
						$post = PlaceAsk::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
					} else if($module == 'tip') {
						$post = PlaceTip::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
					} else if($module == 'photostream') {
						$post = Gallery::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
					} else if($module == 'blog') {
						$post = Blog::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
					} else if($module == 'collections') {
						$post = Collections::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
					} else if($module == 'trip') {
						$post = Trip::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
					} else if($module == 'localdine') {
						$post = Localdine::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
					} else if($module == 'homestay') {
						$post = Homestay::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
					} else if($module == 'camping') {
						$post = Camping::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
					} else if($module == 'localguide') {
						$post = LocalguidePost::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
					} else if($module == 'localdriver') {
						$post = LocaldriverPost::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
					}

					if(!empty($post)) {
						$post->flagger = 'yes';
						$post->flagger_by = $user_id;
						$post->flagger_date = time();
						$post->save();
						$result = array('success' => 'yes');
						return json_encode($result, true);
					}
				}
			}
		}
    }

    public function actionAcntemladded()
    {
		$session = Yii::$app->session;
		$temporary_u_id = (string)$session->get('temporary_u_id');
		if(isset($_POST['email']) && $_POST['email'] != '') {
			$user_info = LoginForm::find()->where([(string)'_id'=> $temporary_u_id])->one();
			if(!empty($user_info)) {
				$fullname = $user_info['fullname'];
			    $time = time();
			    $link_part = 'site/acntemlverify';
			    $email = isset($_POST['email']) ? $_POST['email'] : '';
			    $token = $time;
			    $t_token = strrev(base64_encode($token));
			    $date_on = $time;
			    $pwd = uniqid().time().uniqid();
			    $t_pwd = strrev(base64_encode($pwd));
			    $t_temporary_u_id = strrev(base64_encode($temporary_u_id));
			    
			    $link = "https://iaminjapan.com/frontend/web/index.php?r=$link_part&uid=$t_temporary_u_id&token=$t_token&pwd=$t_pwd";

			    try 
				{
					$test = Yii::$app->mailer->compose()
					->setFrom(array('csupport@iaminjapan.com' => 'iaminjapan Team'))
					->setTo($email)
					->setSubject('I am in Japan- Verify your account')
					->setHtmlBody('<html> <head> <meta charset="utf-8" /> <title>I am in Japan </title> </head> <body style="margin:0;padding:0;background:#dfdfdf;"> <div style="color: #353535; float:left; font-size: 13px;width:100%; font-family:Arial, Helvetica, sans-serif;text-align:center;padding:40px 0 0;"> <div style="width:600px;display:inline-block;"> <img src="https://iaminjapan.com/frontend/web/images/black-logo.png" style="margin:0 0 10px;width:130px;float:left;"/> <div style="clear:both"> </div> <div style="border:1px solid #ddd;margin:0 0 10px;"> <div style="background:#fff;padding:20px;border-top:10px solid #333;text-align:left;"> <div style="color: #333;font-size: 13px;margin: 0 0 20px;">Hi '.$fullname.'</div> <div style="color: #333;font-size: 13px;margin: 0 0 20px;">Please activate your account by clicking on verify button. </div> <div style="color: #333;font-size: 13px;margin: 0 0 20px;"> <a href="'.$link.'">VERIFY</a> </div> <div style="color: #333;font-size: 13px;">Thanks </div> <div style="color: #333;font-size: 13px;">The iaminjapan Team </div> </div> </div> <div style="clear:both"> </div> <div style="width:600px;display:inline-block;font-size:11px;"> <div style="color: #777;text-align: left;">&copy;  www.iaminjapan.com All rights reserved. </div> <div style="text-align: left;width: 100%;margin:5px  0 0;color:#777;">For support, you can reach us directly at <a href="csupport@iaminjapan.com" style="color:#4083BF">csupport@iaminjapan.com </a> </div> </div> </div> </div> </body> </html>')
					->send();	

					// delete old entry..
					Emailverifygarbage::deleteAll(['user_id' => $temporary_u_id]);

					$Emailverifygarbage = new Emailverifygarbage();
					$Emailverifygarbage->user_id = $temporary_u_id;
					$Emailverifygarbage->email = $email;
					$Emailverifygarbage->token = (string)$token;
					$Emailverifygarbage->pwd = $pwd;
					$Emailverifygarbage->date_on = time();
					$Emailverifygarbage->insert();

					return 'yes';
				}
				catch (ErrorException $e) 
				{
					return "no";
				}
			}

		}

    }

    public function actionAcntemlverify()
    {
    	$get = $_GET;

    	$token = isset($get['token']) ? $get['token'] : '';
		$token = base64_decode(strrev($token));

		$user_id = isset($get['uid']) ? $get['uid'] : '';
		$user_id = base64_decode(strrev($user_id));

		$pwd = isset($get['pwd']) ? $get['pwd'] : '';
		$pwd = base64_decode(strrev($pwd));

		//$time = time();
		//$exptime = $token + 60*60;

		//if($exptime < $time) {
			$Emailverifygarbage = Emailverifygarbage::find()->where(['pwd' => $pwd, 'token' => $token, 'user_id' => $user_id])->one();
			if(!empty($Emailverifygarbage)) {
				$email = $Emailverifygarbage['email'];
				$update = LoginForm::find()->where([(string)'_id' => $user_id])->one();
				$update->email = $email;
				$update->status = '1';
				$update->update();

				// delete old entry..
				Emailverifygarbage::deleteAll(['user_id' => $user_id]);

				$url = Yii::$app->urlManager->createUrl(['site/index']);
				Yii::$app->getResponse()->redirect($url);
			} else {
				$url = Yii::$app->urlManager->createUrl(['site/mainfeed']);
				Yii::$app->getResponse()->redirect($url);
			}
	    /*} else {
	    	// link expired...
	    	$url = Yii::$app->urlManager->createUrl(['site/mainfeed']);
			Yii::$app->getResponse()->redirect($url);
	    }*/
    }

    public function actionSearchleftbar() 
	{
        $session = Yii::$app->session;
        $suserid = (string)$session->get('user_id');
        $model = new \frontend\models\LoginForm();
        $isEmpty = 'yes';
        if (isset($_GET['key']) && !empty($_GET['key'])) 
		{
            $email = $_GET['key'];
            $eml_id = LoginForm::find()->select(['_id','fname','lname','fullname','email','city','photo','gender'])
                    ->orwhere(['like','fname',$email])
                    ->orwhere(['like','lname',$email])
                    ->orwhere(['like','fullname',$email])
                    ->andwhere(['status'=>'1'])
                    ->orderBy([$email => SORT_ASC])
                    ->limit(7)
                    ->all();

            $json = array();

            $i = 0;
            if (!empty($eml_id)) 
			{ 		
				foreach ($eml_id as $val) 
				{
					$data = array();
					$data[] = $val->fname;
					$data[] = $val->email;
					$data[] = $val->lname;
					$data[] = $val->photo;
					$data[] = (string) $val->_id;
					$data[] = $val->gender;
					$guserid = (string)$val->_id;

					$block = BlockConnect::find()->where(['user_id' => $guserid])->andwhere(['like','block_ids',$suserid])->one();
					if(!$block)
					{
						$result_security = SecuritySetting::find()->where(['user_id' => $guserid])->one();
						if($result_security)
						{
							$lookup_settings = $result_security['my_view_status'];
						}
						else
						{
							$lookup_settings = 'Public';
						}
						$is_connect = Connect::find()->where(['from_id' => $guserid,'to_id' => $suserid,'status' => '1'])->one();
						if(($lookup_settings == 'Public') || ($lookup_settings == 'Connects' && ($is_connect || $guserid == $suserid)) || ($lookup_settings == 'Private' && $guserid == $suserid)) 
						{
							$isEmpty = 'no';
							?>
							<a href="index.php?r=userwall%2Findex&id=<?= $val->_id ?>" onclick="openThread(this)" class="add_to_group_container">
							    <span class="add_to_group_personprofile">
							    	<?php
									$dp = $this->getimage($val['_id'],'photo');
									if(empty($val->city)){$val->city = '&nbsp;';}
									?>
									<img src="<?= $dp?>" alt="">
							    </span>
							    <div class="add_to_group__personlabel">
							        <p class="group_person_name" id="checkPerson0"><?=$val->fname?>&nbsp;<?=$val->lname?></p>
									<span><?=$val->city?></span>
							    </div>
							</a>
					<?php } 
					} 
				}
            }
        }

        if($isEmpty == 'yes') { ?>
			<div class="leftsearchbaremptybox">
			     <p class="center-align">Not Found...!</p>
			</div>
			<?php
        }
    }
}
