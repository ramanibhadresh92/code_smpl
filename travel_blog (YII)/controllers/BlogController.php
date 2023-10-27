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
use frontend\models\Destination;
use frontend\models\PlaceVisitor;
use frontend\models\Blog;
use frontend\models\BlogComments;

class BlogController extends Controller
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
        $uid = (string)$session->get('user_id');
        /*if(isset($uid) && !empty($uid))
        {
            $result = LoginForm::find()->select(['city'])->where(['_id' => $uid])->asarray()->one();
            
            if(!(isset($result['city']) && !empty($result['city'])))
            {
                $url = Yii::$app->urlManager->createUrl(['site/complete-profile']);
                Yii::$app->getResponse()->redirect($url);
            }
        }*/

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

        return $this->render('index',array('checkuserauthclass' => $checkuserauthclass,'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'lat' => $lat,'lng' => $lng));
    }

    public function actionDetail()  
    {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');

        if(isset($uid) && $uid != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($uid);
        } else {
            $checkuserauthclass = 'checkuserauthclassg';
        } 

        if(isset($_GET['id']) && !empty($_GET['id'])) {
            $id = $_GET['id'];
            return $this->render('detail', array('id' => $id,'checkuserauthclass' => $checkuserauthclass));
        }
    }

    public function actionCreateblogui()  
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            return $this->render('createblogui');
        }
    }

    public function actionEditblogui()  
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST['$editid']) && $_POST['$editid'] != '') {
                $id = $_POST['$editid']; 
                return $this->render('editblogui', array('id' => $id));
            }
        }
    }

    public function actionCreateblog()  
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('success' => false);

        if(isset($user_id) && $user_id != '') {
            if(isset($_POST) && !empty($_POST)) {
                $postdata = $_POST;
                $images = isset($_FILES['images']) ? $_FILES['images'] : array();
                return Blog::createblog($postdata, $images, $user_id);
            }
        }

        return false;
    }

    public function actionEditblog()  
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('success' => false);

        if(isset($user_id) && $user_id != '') {
            if(isset($_POST) && !empty($_POST)) {
                $postdata = $_POST;
                $images = isset($_FILES['images']) ? $_FILES['images'] : array();
                return Blog::editblog($postdata, $images, $user_id);
            }
        }

        return false;
    }

    public function actionDeleteblog()  
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('success' => false);
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST['$deleteid']) && $_POST['$deleteid']) {
                $deleteid = $_POST['$deleteid'];

                $blog = Blog::find()->where([(string)'_id' => $deleteid])->andWhere(['not','flagger', "yes"])->one();
                if(!empty($blog)) {
                    $blog->delete();
                    return true;
                }
            }
        }

        return false;
    }

    public function actionDocomment()  
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('success' => false);

        if(isset($user_id) && $user_id != '') {
            $comment = $_POST['comment'];
            return BlogComments::docomment($user_id, $comment);
        }

        return json_encode($result, true);
    }
}
?>