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
use frontend\models\UserForm;
use frontend\models\Vip;
use frontend\models\TravAdsVisitors;
use frontend\models\Order;
use frontend\models\UserMoney;
use frontend\models\PlaceDiscussion;

class LocalsController extends Controller
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

        $place = Yii::$app->params['place'];
        $placetitle = Yii::$app->params['placetitle'];
        $placefirst = Yii::$app->params['placefirst'];
        $lat = Yii::$app->params['lat'];
        $lng = Yii::$app->params['lng'];
        
        $getUsers = LoginForm::find()->where(['like','city', $placetitle])->andwhere(['status'=>'1'])->asarray()->all();

        if(empty($getUsers))
        {
            $placetitle = str_replace(',',' -',$placetitle);
            $getUsers = LoginForm::find()->where(['like','city',$placetitle])->andwhere(['status'=>'1'])->asarray()->all();
            if(empty($getUsers))
            { 
                if(substr( $placetitle, 0, 14 ) === "Japan District")
                {
                    $placetitle = "Japan";
                }
                $getUsers = LoginForm::find()->where(['like','city',$placetitle])->andwhere(['status'=>'1'])->asarray()->all();
            } 
        }

        return $this->render('index',array('place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'getUsers'=> $getUsers,'lat' => $lat,'lng' => $lng));
    }
}
?> 