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
class WatchController extends Controller {
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
	
    public function actionIndex() 
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id); 
        } else {
            $checkuserauthclass = 'checkuserauthclassg';
        }
        $place = Yii::$app->params['place'];
        $placetitle = Yii::$app->params['placetitle'];
        $placefirst = Yii::$app->params['placefirst'];
        $lat = Yii::$app->params['lat'];
        $lng = Yii::$app->params['lng'];      
        return $this->render('index',array('checkuserauthclass' => $checkuserauthclass,'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'lat' => $lat,'lng' => $lng));
    }
 	
}
