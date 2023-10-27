<?php
namespace frontend\controllers;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use frontend\models\Gallery;
class GalleryController extends Controller
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

    public function actionGetslidehtml()  
    { 
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($_POST['id']) && $_POST['id'] != '') { 
            $id = $_POST['id'];
            $imgsrc = $_POST['imgsrc'];
            if($id) {
                return $this->render('/userwall/getslidehtml',array('id' => $id, 'imgsrc' => $imgsrc));
            }
        }
    }

    public function actionFetcheditlayereduploadphotohtml()  
    { 
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST['$editid']) && $_POST['$editid'] != '') {
                $id = $_POST['$editid']; 
                return $this->render('/layouts/fetcheditlayereduploadphotohtml', array('id' => $id));
            }
        }
    }

    public function actionFetchlayereduploadphotohtml()  
    { 
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            return $this->render('/layouts/fetchlayereduploadphotohtml');
        }
    }

    public function actionGalleryhidephoto()  
    {  
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
     
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST['id']) && $_POST['id'] != '') {
                $id = (string)$_POST['id'];   
                $data = Gallery::galleryhidephoto($id, $user_id);
                return $data; 
            }
        }

        $result = array('success' => false);
        return json_encode($result, true);
    }

    public function actionFetchgallerycategoriestaggeduser()  
    { 
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
    
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST['$id']) && $_POST['$id'] != '') {
                $id = (string)$_POST['$id'];
                $data = Gallery::fetchgallerycategoriestaggeduser($id, $user_id);
                return $data;
            }
        }

        $result = array('success' => false);
        return json_encode($result, true);
    }

    public function actionAddgallery()  {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('success' => false);
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST['photoUpload']) && !empty($_POST['photoUpload'])) {
                $photoUpload = json_decode($_POST['photoUpload'], true);
                if(isset($_FILES['imageFile1']['name']) && !empty($_FILES['imageFile1']['name'])) {
                    $files = $_FILES['imageFile1'];
                    $filesCount = count($files['name']);

                    $place = isset($_POST['place']) ? $_POST['place'] : '';
                    $placetitle = isset($_POST['placetitle']) ? $_POST['placetitle'] : '';

                    $filterGalleryArray = array();

                    $defaultTITLE = '';
                    foreach ($photoUpload as $key => $tempphotoUpload) {
                        if($defaultTITLE == '') {
                            $currentTITLE = $tempphotoUpload['$uploadpopupJIDSphototitle']; 
                            if($currentTITLE != '') {
                                $defaultTITLE = $currentTITLE;
                            }
                        }
                    }

                    for ($i=0; $i < $filesCount; $i++) { 
                        $filterGalleryArray[$i] = array();

                        if(isset($photoUpload[$i])) {
                            $uploadpopupJIDSphototitle = isset($photoUpload[$i]['$uploadpopupJIDSphototitle']) ? $photoUpload[$i]['$uploadpopupJIDSphototitle'] : $defaultTITLE;
                            $uploadpopupJIDSdescription = isset($photoUpload[$i]['$uploadpopupJIDSdescription']) ? $photoUpload[$i]['$uploadpopupJIDSdescription'] : '';
                            $uploadpopupJIDSlocation = isset($photoUpload[$i]['$uploadpopupJIDSlocation']) ? $photoUpload[$i]['$uploadpopupJIDSlocation'] : '';
                            $uploadpopupJIDStaggedconnections = isset($photoUpload[$i]['$uploadpopupJIDStaggedconnections']) ? $photoUpload[$i]['$uploadpopupJIDStaggedconnections'] : array();
                            if(!empty($uploadpopupJIDStaggedconnections)) {
                                $uploadpopupJIDStaggedconnections = implode(',', $uploadpopupJIDStaggedconnections);
                            } else {
                                $uploadpopupJIDStaggedconnections = '';
                            }

                            $uploadpopupJIDSvisibleto = isset($photoUpload[$i]['$uploadpopupJIDSvisibleto']) ? $photoUpload[$i]['$uploadpopupJIDSvisibleto'] : '';

                            for ($k=0; $k <= $i; $k++) { 
                                if($uploadpopupJIDSphototitle != '') {
                                    if(isset($filterGalleryArray[$k]['$uploadpopupJIDSphototitle'])) {
                                        if($filterGalleryArray[$k]['$uploadpopupJIDSphototitle'] == '') {
                                            $filterGalleryArray[$k]['$uploadpopupJIDSphototitle'] = $uploadpopupJIDSphototitle;
                                        }
                                    } else {
                                        $filterGalleryArray[$k]['$uploadpopupJIDSphototitle'] = $uploadpopupJIDSphototitle;
                                    }
                                }

                                if($uploadpopupJIDSdescription != '') {
                                    if(isset($filterGalleryArray[$k]['$uploadpopupJIDSdescription'])) {
                                        if($filterGalleryArray[$k]['$uploadpopupJIDSdescription'] == '') {
                                            $filterGalleryArray[$k]['$uploadpopupJIDSdescription'] = $uploadpopupJIDSdescription;
                                        }
                                    } else {
                                        $filterGalleryArray[$k]['$uploadpopupJIDSdescription'] = $uploadpopupJIDSdescription;
                                    }
                                }

                                if($uploadpopupJIDSlocation != '') {
                                    if(isset($filterGalleryArray[$k]['$uploadpopupJIDSlocation'])) {
                                        if($filterGalleryArray[$k]['$uploadpopupJIDSlocation'] == '') {
                                            $filterGalleryArray[$k]['$uploadpopupJIDSlocation'] = $uploadpopupJIDSlocation;
                                        }
                                    } else {
                                        $filterGalleryArray[$k]['$uploadpopupJIDSlocation'] = $uploadpopupJIDSlocation;
                                    }
                                }

                                if($uploadpopupJIDStaggedconnections != '') {
                                    if(isset($filterGalleryArray[$k]['$uploadpopupJIDStaggedconnections'])) {
                                        if($filterGalleryArray[$k]['$uploadpopupJIDStaggedconnections'] == '') {
                                            $filterGalleryArray[$k]['$uploadpopupJIDStaggedconnections'] = $uploadpopupJIDStaggedconnections;
                                        }
                                    } else {
                                        $filterGalleryArray[$k]['$uploadpopupJIDStaggedconnections'] = $uploadpopupJIDStaggedconnections;
                                    }
                                }

                                if($uploadpopupJIDSvisibleto != '') {
                                    if(isset($filterGalleryArray[$k]['$uploadpopupJIDSvisibleto'])) {
                                        if($filterGalleryArray[$k]['$uploadpopupJIDSvisibleto'] == '') {
                                            $filterGalleryArray[$k]['$uploadpopupJIDSvisibleto'] = $uploadpopupJIDSvisibleto;
                                        }
                                    } else {
                                        $filterGalleryArray[$k]['$uploadpopupJIDSvisibleto'] = $uploadpopupJIDSvisibleto;
                                    }
                                }
                            }
                        }

                        $imageName = $files['name'][$i];
                        $imageType = $files['type'][$i];
                        $imageTmp_name = $files['tmp_name'][$i];
                        $imageError = $files['error'][$i];
                        $imageSize = $files['size'][$i];

                        $imageArray = array(
                            'name' => $imageName,
                            'type' => $imageType,
                            'tmp_name' => $imageTmp_name,
                            'error' => $imageError,
                            'size' => $imageSize
                        );

                        $filterGalleryArray[$i]['image'] = $imageArray;
                    }


                    if(!empty($filterGalleryArray)) {
                        for ($m=0; $m < count($filterGalleryArray) ; $m++) { 

                            $newuploadpopupJIDSphototitle = isset($filterGalleryArray[0]['$uploadpopupJIDSphototitle']) ? $filterGalleryArray[0]['$uploadpopupJIDSphototitle'] : '';
                            if(isset($filterGalleryArray[$m]['$uploadpopupJIDSphototitle'])) {
                                if($filterGalleryArray[$m]['$uploadpopupJIDSphototitle'] == '') {
                                    $filterGalleryArray[$m]['$uploadpopupJIDSphototitle'] = $newuploadpopupJIDSphototitle;
                                }
                            } else {
                                $filterGalleryArray[$m]['$uploadpopupJIDSphototitle'] = $newuploadpopupJIDSphototitle;
                            }

                            $newuploadpopupJIDSdescription = isset($filterGalleryArray[0]['$uploadpopupJIDSdescription']) ? $filterGalleryArray[0]['$uploadpopupJIDSdescription'] : '';
                            if(isset($filterGalleryArray[$m]['$uploadpopupJIDSdescription'])) {
                                if($filterGalleryArray[$m]['$uploadpopupJIDSdescription'] == '') {
                                    $filterGalleryArray[$m]['$uploadpopupJIDSdescription'] = $newuploadpopupJIDSdescription;
                                }
                            } else {
                                $filterGalleryArray[$m]['$uploadpopupJIDSdescription'] = $newuploadpopupJIDSdescription;
                            }
                        
                            $newuploadpopupJIDSlocation = isset($filterGalleryArray[0]['$uploadpopupJIDSlocation']) ? $filterGalleryArray[0]['$uploadpopupJIDSlocation'] : '';
                            if(isset($filterGalleryArray[$m]['$uploadpopupJIDSlocation'])) {
                                if($filterGalleryArray[$m]['$uploadpopupJIDSlocation'] == '') {
                                    $filterGalleryArray[$m]['$uploadpopupJIDSlocation'] = $newuploadpopupJIDSlocation;
                                }
                            } else {
                                $filterGalleryArray[$m]['$uploadpopupJIDSlocation'] = $newuploadpopupJIDSlocation;
                            }
                            
                            $newuploadpopupJIDStaggedconnections = isset($filterGalleryArray[0]['$uploadpopupJIDStaggedconnections']) ? $filterGalleryArray[0]['$uploadpopupJIDStaggedconnections'] : '';
                            if(isset($filterGalleryArray[$m]['$uploadpopupJIDStaggedconnections'])) {
                                if($filterGalleryArray[$m]['$uploadpopupJIDStaggedconnections'] == '') {
                                    $filterGalleryArray[$m]['$uploadpopupJIDStaggedconnections'] = $newuploadpopupJIDStaggedconnections;
                                }
                            } else {
                                $filterGalleryArray[$m]['$uploadpopupJIDStaggedconnections'] = $newuploadpopupJIDStaggedconnections;
                            }

                            $newuploadpopupJIDSvisibleto = isset($filterGalleryArray[0]['$uploadpopupJIDSvisibleto']) ? $filterGalleryArray[0]['$uploadpopupJIDSvisibleto'] : '';
                            if(isset($filterGalleryArray[$m]['$uploadpopupJIDSvisibleto'])) {
                                if($filterGalleryArray[$m]['$uploadpopupJIDSvisibleto'] == '') {
                                    $filterGalleryArray[$m]['$uploadpopupJIDSvisibleto'] = $newuploadpopupJIDSvisibleto;
                                }
                            } else {
                                $filterGalleryArray[$m]['$uploadpopupJIDSvisibleto'] = $newuploadpopupJIDSvisibleto;
                            }
                        }
                    }
                      
                    $add = Gallery::addGallery($filterGalleryArray, $place, $placetitle, $user_id);
                    $result = array('success' => true);
                    return json_encode($result, true);
                }
            }
        }

        $result = array('success' => false);
        return json_encode($result, true);
    }

    public function actionEditgallery()  {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        $result = array('success' => false);
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST) && !empty($_POST)) {
                $post = $_POST;

                $id = isset($post['id']) ? $post['id'] : '';
                if($id) {
                    $title = isset($post['$uploadpopupJIDSphototitleedit']) ? $post['$uploadpopupJIDSphototitleedit'] : '';
                    $description = isset($post['$uploadpopupJIDSdescriptionedit']) ? $post['$uploadpopupJIDSdescriptionedit'] : '';
                    $location = isset($post['$uploadpopupJIDSlocationedit']) ? $post['$uploadpopupJIDSlocationedit'] : '';
                    $taggedconnections = isset($post['$uploadpopupJIDStaggedconnectionsedit']) ? $post['$uploadpopupJIDStaggedconnectionsedit'] : array();
                    
                    if(!is_array($taggedconnections)) {
                        $taggedconnections = explode(',', $taggedconnections);
                        $taggedconnections = array_values(array_filter($taggedconnections));
                    }

                    if(!empty($taggedconnections)) {
                        $taggedconnections = implode(',', $taggedconnections);
                    } else {
                        $taggedconnections = '';
                    }

                    $visibleto = isset($post['$uploadpopupJIDSvisibletoedit']) ? $post['$uploadpopupJIDSvisibletoedit'] : '';
                    
                    $url = '../web/uploads/gallery/';
                    $date = uniqid().'_'.rand(9999, 99999).'_'.time();
                   
                    $Gallery = Gallery::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();

                    if(!empty($Gallery)) {
                        
                        if(isset($_FILES['image']) && !empty($_FILES['image'])) {
                            $image = $_FILES['image'];
                            if(!empty($image)) {
                                if(isset($image['name']) && $image['name'] != '') {
                                    $unlink = $Gallery->image;
                                    if(file_exists($unlink)) {
                                        unlink($unlink);
                                    }
                                    $name = $image["name"]; 
                                    $tmp_name = $image["tmp_name"];
                                    move_uploaded_file($tmp_name, $url . $date . $name);
                                    $img = $url . $date . $name;
                                    $Gallery->image = $img;
                                }
                            }
                        }
                        
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
                        $Gallery->tagged_connections = $taggedconnections;
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

    public function actionGetgallerycommentlikecount()  
    { 
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        $result = array('success' => false);

        
        if(isset($_POST['ids']) && $_POST['ids'] != '') {
            $ids = $_POST['ids'];
            $ids = explode('|||', $_POST['ids']); 
            if(count($ids) == 2) {
                $id = $ids[0];
                if($id) {
                    if($ids[1] == 'UserPhotos') {
                        $imgsrc = $_POST['imgsrc'];
                        $fileinfo = pathinfo($imgsrc);
                        $id = $fileinfo['filename'] .'_'. $id;
                    }    
                    $data = Gallery::getgallerycommentlikecount($id, $user_id);
                    return $data;
                }
            }
        }

        return json_encode($result, true);
    }

    public function actionLikehtml()  
    { 
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        $result = array('success' => false);
        if(isset($_POST['id']) && $_POST['id'] != '') {
            $id = $_POST['id'];
            if($id) {
                $data = Gallery::likehtml($id);
                return $data;
            }
        }
    }

    public function actionGetcurrentgalleryslideimg()  
    { 
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        $result = array('success' => false);
        
        if(isset($_POST['id']) && $_POST['id'] != '') {
            if(isset($_POST['imgsrc']) && $_POST['imgsrc'] != '') {
                $id = $_POST['id'];
                $imgsrc = $_POST['imgsrc'];
                if($id) {
                    $ISDSK = explode('|||', $id);
                    if(count($ISDSK) == 2) {
                        $type = $ISDSK[1];
                        if($type == 'Gallery') {
                            $id = $ISDSK[0];
                            $gallery = Gallery::find()->select(['image'])->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->asarray()->one();
                            if(!empty($gallery)) {
                                $storedimgsrc =  $gallery['image'];
                                if($imgsrc != $storedimgsrc) {
                                    $result = array('success' => true, 'imgsrc' => $storedimgsrc);
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return json_encode($result, true);
    }
}