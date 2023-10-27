<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter; 
use yii\filters\AccessControl;

use yii\mongodb\ActiveRecord;
use frontend\components\ExSession;
use frontend\models\PostForm;
use frontend\models\UserForm;

class TripexperienceController extends Controller {

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
				'successCallback' => [$this,
					'oAuthSuccess'],
			],
			'captcha' => [
				//'class' => 'yii\captcha\CaptchaAction',
				'class' => 'mdm\captcha\CaptchaAction',
				'level' => 1,
			],
			'error' => [
				'class' => 'yii\web\ErrorAction',
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
		 
		return $this->render('tripexp', array('checkuserauthclass' => $checkuserauthclass)); 
	}
	
	public function actionContinent()
	{
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $continents = array();

        if(isset($_POST) && !empty($_POST)) {
	        $trip = $_POST['trip'];
	        if(isset($user_id) && $user_id != '') {
	        	if($trip == 'yourtrip'){
					$continents = PostForm::getContinents($user_id);
				} else {
					$continents = PostForm::getContinents();
				}
			} else {
				$continents = PostForm::getContinents(); 
			}
			return $this->render('continent', array('continents'=>$continents));
		} else {
			return $this->goHome();
		}		
	}

	public function actionYourtrip()
	{
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $yourtripexp = array();
        $checkuserauthclass = 'checkuserauthclassg';
        
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
       			if(isset($_POST) && !empty($_POST)) {      	
					$country = $_POST['country'];
					$searchval = $_POST['searchval'];
					$yourtripexp = PostForm::getTripexpyourPosts($user_id,"$country","$searchval");
					return $this->render('yourtrip',array('yourtripexps'=>$yourtripexp));
				}
			}
		}
		
		return $this->render('yourtrip',array('yourtripexps'=>$yourtripexp));
	}
	
	public function actionAlltrip() 
	{

        if(isset($_POST) && !empty($_POST)) {
			$session = Yii::$app->session;
	        $user_id = (string)$session->get('user_id');
	        
	        if(isset($user_id) && $user_id != '') {
	            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
	        } else {
	            $checkuserauthclass = 'checkuserauthclassg';
	        }

			$country = $_POST['country'];
			$searchval = $_POST['searchval'];
			$alltripexps = PostForm::getTripexpallPosts($country, $searchval);
			return $this->render('alltrip',array('alltripexps'=>$alltripexps, 'checkuserauthclass' => $checkuserauthclass));
		}
		
		return $this->goHome();
	}
}	