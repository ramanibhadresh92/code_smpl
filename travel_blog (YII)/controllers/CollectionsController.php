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
use frontend\models\Collections;

class CollectionsController extends Controller
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
        return $this->render('index',array('checkuserauthclass' => $checkuserauthclass));
    } 
 
    public function actionDetails()  
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('success' => false);

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
        } else {
            $checkuserauthclass = 'checkuserauthclassg';
        }

        if(isset($_GET['id']) && $_GET['id']) {
            $id = $_GET['id'];
            return $this->render('details', array('id' => $id, 'checkuserauthclass' => $checkuserauthclass));
        }
    }

    public function actionFetchlayereduploadphotohtml()  
    { 
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            return $this->render('fetchlayereduploadphotohtml');
        }
    }

    public function actionFetcheditlayereduploadphotohtml()  
    { 
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST['$editid']) && $_POST['$editid'] != '') {
                $id = $_POST['$editid']; 
                return $this->render('fetcheditlayereduploadphotohtml', array('id' => $id));
            }
        }
    }

    public function actionFetcheditlayereduploadphotohtml_s()  
    { 
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST['$editid']) && $_POST['$editid'] != '') {
                $id = $_POST['$editid']; 
                return $this->render('fetcheditlayereduploadphotohtml_s', array('id' => $id));
            }
        }
    }

    public function actionAddcollections() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('success' => false);

        if(isset($user_id) && $user_id != '') {
            if(isset($_POST) && !empty($_POST)) {
                $postdata = $_POST;
                $images = $_FILES['images'];
                return Collections::addCollections($postdata, $images, $user_id);
            }
        }

        $result = array('success' => false);
        return json_encode($result, true);
    }

    public function actionEditcollections()  {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        $result = array('success' => false);
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST) && !empty($_POST)) {
                $post = $_POST;

                $id = isset($post['id']) ? $post['id'] : '';
                if($id) {
                    $title = isset($post['title']) ? $post['title'] : '';
                    $description = isset($post['description']) ? $post['description'] : '';
                    $location = isset($post['location']) ? $post['location'] : '';
                    $tagged = isset($post['tagged']) ? $post['tagged'] : array();
                    
                    if(!is_array($tagged)) {
                        $tagged = explode(',', $tagged);
                        $tagged = array_values(array_filter($tagged));
                    }

                    if(!empty($tagged)) {
                        $tagged = implode(',', $tagged);
                    } else {
                        $tagged = '';
                    }

                    $visibleto = isset($post['visibleto']) ? $post['visibleto'] : '';
                    
                    $url = '../web/uploads/collections/';
                    $date = uniqid().'_'.rand(9999, 99999).'_'.time();
                   
                    $Gallery = Collections::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();

                    if(!empty($Gallery)) {

                        $imgArray = $unlink = $Gallery->image;
                        $imgArray = explode(',', $imgArray);
                        $imgArray = array_values(array_filter($imgArray));

                        if(isset($_FILES['images']) && !empty($_FILES['images'])) {
                            $images = $_FILES['images'];
                            for ($i=0; $i < count($images); $i++) { 
                                if(isset($images['name'][$i]) && $images['name'][$i] != '') {
                                    $name = $images["name"][$i]; 
                                    $tmp_name = $images["tmp_name"][$i];
                                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                                    $time = time();
                                    $uniqid = uniqid();
                                    $gen_name = $time.$uniqid.'.'.$ext;
                                    move_uploaded_file($tmp_name, $url . $date . $gen_name);
                                    $img = $url . $date . $gen_name;
                                    $imgArray[] = $img;
                                }
                            }
                        }

                        if(empty($imgArray)) {
                            $result = array('success' => false, 'message' => 'Upload photo.');
                            return json_encode($result, true);    
                        }

                        $imgArray = implode(',', $imgArray);
                        $Gallery->image = $imgArray;

                        if(trim($visibleto) == 'Custom') {
                            if(isset($post['customids']) && !empty($post['customids'])) {
                                $ids = $post['customids'];
                                if(is_array($ids)) {
                                    $ids = implode(',', $ids);
                                }
                                $Gallery->customids = $ids;
                            }
                        } else {
                            $Gallery->customids = '';
                        }
                                 
                        $Gallery->title = $title;
                        $Gallery->description = $description;
                        $Gallery->location = $location;
                        $Gallery->tagged_connections = $tagged;
                        $Gallery->visible_to = $visibleto;
                        $Gallery->modified_at = time();
                        $Gallery->update(); 
                        $result = array('success' => true);
                        return json_encode($result, true);
                    }
                }
            }
        }

        $result = array('success' => false);
        return json_encode($result, true);
    }

    public function actionRemovepic()  
    { 
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($_POST['$id']) && $_POST['$id'] != '') {
            if(isset($_POST['$src']) && $_POST['$src'] != '') {
                $id = $_POST['$id'];
                $src = $_POST['$src'];
                $data = Collections::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();

                if(!empty($data)) {
                    $images = $data->image;
                    $images = explode(',', $images);
                    $pos = array_search($src, $images);
                    unset($images[$pos]);

                    $images = implode(',', $images);
                    $data->image = $images;
                    $data->update();
                    $result = array('success' => true);
                    return json_encode($result, true);
                }
            }
        }

        $result = array('success' => false);
        return json_encode($result, true);
    }

    public function actionDeletecollection()  
    { 
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($_POST['$id']) && $_POST['$id'] != '') {
            $id = $_POST['$id'];
            $Collections = Collections::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();
            if(!empty($Collections)) {
                $Collections->delete();
                $result = array('success' => true);
                return json_encode($result, true);
            }
        }

        $result = array('success' => false);
        return json_encode($result, true);
    }
}
?>