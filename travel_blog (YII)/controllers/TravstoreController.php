<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter; 
use yii\filters\AccessControl;
use yii\mongodb\ActiveRecord;

use frontend\models\PostForm;
use frontend\models\LoginForm;
use backend\models\TravstoreCategory;
use frontend\components\ExSession;

class TravstoreController extends Controller {

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
		$email = $session->get('email');
		if ($session->get('email'))
		{
			$trav_cat = TravstoreCategory::getTravCat();
			//$featuredposts =  PostForm::getTravstorefeaturedPosts();
			//$yourposts =  PostForm::getTravstoreyourPosts();
			return $this->render('travstore',array('trav_cat'=>$trav_cat));
		}
		else
		{
			return $this->goHome();
		}
	}
	
	public function actionFeacturedtrav()
	{
		$cat_name = $_POST['cat_name'];
		$searchval = $_POST['searchval'];
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		if ($session->get('email'))
		{
			$featuredposts = PostForm::getTravstorefeaturedPosts("$cat_name","$searchval");
			return $this->render('featuretravstore',array('featuredposts'=>$featuredposts));
		}
		else
		{
			return $this->goHome();
		}
	}
	
	public function actionStafftrav()
	{
		$cat_name = $_POST['cat_name'];
		$searchval = $_POST['searchval'];
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		if ($session->get('email'))
		{
			$staffposts = PostForm::getTravstorestaffPosts("$cat_name","$searchval");
			return $this->render('stafftravstore',array('staffposts'=>$staffposts));
		}
		else
		{
			return $this->goHome();
		}
	}
	
	public function actionYourtrav()
	{
		$cat_name = $_POST['cat_name'];
		$searchval = $_POST['searchval'];
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		if ($user_id)
		{
			$yourposts =  PostForm::getTravstoreyourPosts($user_id,"$cat_name","$searchval");
			return $this->render('yourtravstore',array('yourposts'=>$yourposts));
		}
		else
		{
			return $this->goHome();
		}
	}
	
}	