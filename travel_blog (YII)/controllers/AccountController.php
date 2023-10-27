<?php
namespace frontend\controllers;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\mongodb\ActiveRecord;

class AccountController extends Controller
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
    
    public function actionGetpagedata()
    {
        $session = Yii::$app->session;
        $user_id = $userid =  (string)$session->get('user_id');
        $label = '';
        if($_POST['page'] == 'basicinfo')
        {
            $label = 'Basic Information';
        }
        if($_POST['page'] == 'profilepicture')
        {
            $label = 'Profile Picture';
        }
        if($_POST['page'] == 'security')
        {
            $label = 'Security Settings';
        }
        if($_POST['page'] == 'notifications')
        {
            $label = 'Notification Settings';
        }
        if($_POST['page'] == 'blocking')
        {
            $label = 'Blocking';
        }

        return $label;
    }

    public function actionGetbasicinformationnormal()
    {
        return $this->render('/site/getbasicinformationnormal');
    }     

    public function actionGetbasicinformationedit()
    {
        return $this->render('/site/getbasicinformationedit');
    }

    public function actionGetsecuritysettingsnormal()
    {
        return $this->render('/site/getsecuritysettingsnormal');
    }     

    public function actionGetsecuritysettingsedit()
    {
        return $this->render('/site/getsecuritysettingsedit');
    }

    public function actionGetblockingnormal()
    {
        return $this->render('/site/getblockingnormal');
    }

    public function actionGetblockingedit()
    {
        return $this->render('/site/getblockingedit');
    }
}
?>

