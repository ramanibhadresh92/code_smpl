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
use frontend\models\Credits;

class GoogleController extends Controller {

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
	
    public function oAuthSuccess($client) {
		$attributes = $client->getUserAttributes();
		$email = ArrayHelper::getValue($attributes, 'email');
		$google_id = ArrayHelper::getValue($attributes, 'id');
		$name = ArrayHelper::getValue($attributes, 'name');
		$image = ArrayHelper::getValue($attributes, 'picture');
		$thumbnail = ArrayHelper::getValue($attributes, 'picture');
		$session = Yii::$app->session;
		$result = UserForm::isUserExist($email);
		$model = new \frontend\models\LoginForm();  
		$rand = rand(0,9999999999);

		if ($result) 
		{
		    $session->set('user_id',$result['_id']);
		    $update = LoginForm::find()->where(['_id' => $result['_id']])->one();
		    
			$google_user_id = $update['_id'];
			$google_user_img = 'profile/'.$update['photo'];
			$google_user_thumb_img = 'profile/'.$update['photo'];
			$big_img_link = $image;
			$big_img_path1 = 'ORI_'.$google_user_id.'_'.$rand.'.jpeg';
			$thumb_img_path1 = $google_user_id.'_'.$rand.'.jpeg';
			$big_img_path = 'profile/ORI_'.$google_user_id.'_'.$rand.'.jpeg';
			$thumb_link = $thumbnail;
			$thumb_img_path = 'profile/'.$google_user_id.'_'.$rand.'.jpeg';
			
			if($update['photo'] != '')
			{
				if (file_exists($google_user_img)) {
					$md5image1 = md5(file_get_contents($google_user_img));
					$md5image2 = md5(file_get_contents($image));
					
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
		    $explode = explode(" ",$name);
		    $fname = $explode[0];
		    $lname = $explode[1];
		    $fullname = $fname . " " .$lname;

		    $photo = $update['photo'];
			if($update['photo'] != '')
			{
				if (file_exists($google_user_img)) {
					if ($md5image1 != $md5image2) {
						$update->photo = $big_img_path1;
						$update->thumbnail = $thumb_img_path1;
					}
				}
				else
				{
					$update->photo = $big_img_path1;
					$update->thumbnail = $thumb_img_path1;	
				}
				
			}
			else
			{
				$update->photo = $big_img_path1;
				$update->thumbnail = $thumb_img_path1;
			}	
			
		    
			$last_time = $result['last_login_time'];
			
			$update->google_id = $google_id;
		    $update->fname = $fname;
		    $update->lname = $lname;
		    $update->fullname = $fullname;
		    $update->email = $email;
			$update->last_time = "$last_time";
			$update->last_login_time = "$date";
				
		    $update->updated_date = "$date";
		    $update->status = '1';
		    $update->login_from_ip = $_SERVER['REMOTE_ADDR'];
		    $update->update();
			
			$session->set('user_id',$update['_id']);
			$session->set('email',$email);
		} 
		else 
		{
		    // insert user detail and redirect
		    $date = time();
		    $explode = explode(" ",$name);
		    $fname = $explode[0];
		    $lname = $explode[1];
		    $fullname = $fname . " " .$lname;
		    
		    $user = new UserForm();
			
			$user->google_id = $google_id;
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
		    $googleid = $user->_id;
			
			$record = LoginForm::find()->where(['google_id' => $google_id])->one();
			
			$google_user_id = $record['_id'];
			$big_img_link = $image;
			$big_img_path1 = 'ORI_'.$google_user_id.'_'.$rand.'.jpeg';
			$thumb_img_path1 = $google_user_id.'_'.$rand.'.jpeg';
			$big_img_path = 'profile/ORI_'.$google_user_id.'_'.$rand.'.jpeg';
			$thumb_link = $thumbnail;
			$thumb_img_path = 'profile/'.$google_user_id.'_'.$rand.'.jpeg';
			file_put_contents($big_img_path, file_get_contents($big_img_link));
			file_put_contents($thumb_img_path, file_get_contents($thumb_link));
			
			$record->photo = $big_img_path1;
		    $record->thumbnail = $thumb_img_path1;
			$record->update();

			$cre_amt = 25;
			$cre_desc = 'signup';
			$status = '1';
			$details = $googleid.'_signup';
			$credit = new Credits();
			$credit = $credit->addcredits("$googleid", $cre_amt, $cre_desc, $status, "$details");

			$cre_amt = 10;
			$cre_desc = 'profilephoto';
			$status = '1';
			$details = $googleid.'_profile';
			$credit = new Credits();
			$credit = $credit->addcredits("$googleid", $cre_amt, $cre_desc, $status, "$details");
			
			$notification = NotificationSetting::notification3($email);
			$security = SecuritySetting::security3($email);
			
			$session->set('user_id',$record['_id']);
			$session->set('email',$email);

		}

		$url = Yii::$app->urlManager->createUrl(['site/mainfeed']);
		$id = LoginForm::getLastInsertedRecord($email);
		$user_id = $id['_id'];
		\Yii::$app->session->setId($user_id);            
		\Yii::$app->session->readSession($user_id);

		$session->set('user_id',$user_id);
	}
}
