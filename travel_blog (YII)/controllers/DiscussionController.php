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
use frontend\models\LoginForm;
use frontend\models\Personalinfo;
use frontend\models\UserForm;
use frontend\models\Vip; 
use frontend\models\TravAdsVisitors;
use frontend\models\Order;
use frontend\models\UserMoney;
use frontend\models\PlaceDiscussion;
use frontend\models\Notification;
use frontend\models\Destination;
use frontend\models\PlaceVisitor;
use frontend\models\SecuritySetting;
use frontend\models\UserPhotos;
use backend\models\DefaultPosts;
use frontend\models\PinImage;
use frontend\models\General;
use frontend\models\Credits;
class DiscussionController extends Controller
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
        
        $getpplacereviews = PlaceDiscussion::getPlaceReviews($place,'discussion','all'); 
        
        return $this->render('index',array('checkuserauthclass' => $checkuserauthclass,'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'getpplacereviews'=> $getpplacereviews,'lat' => $lat,'lng' => $lng));
    }

    public function actionComposenewdiscussionpopup() 
    {
        return $this->render('adddiscussion');
    }

    public function actionAddDiscussionPlaces()
    {
        $this->layout = 'ajax_layout';
        $session = Yii::$app->session;
        $email = $session->get('email');
        $userid = $user_id = (string)$session->get('user_id');
        
        if(isset($userid) && $userid != '') {
            $authstatus = UserForm::isUserExistByUid($userid);
            if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
                $data['auth'] = $authstatus;
                return $authstatus;
            } else {
                $result = LoginForm::find()->where(['_id' => $userid])->one();
                $date = time();
                $post_status = '0';
                $text = isset($_POST['test']) ? $_POST['test'] : '';
                
                $purifier = new HtmlPurifier();
                $text = HtmlPurifier::process($text);
                $post = new PlaceDiscussion();
                $post->post_status = '1';
                $post->post_text = ucfirst($text);
                $post->post_type = 'text';
                $post->post_created_date = "$date";
                $post->post_user_id = "$user_id";

                if(isset($_POST['current_location']) && !empty($_POST['current_location']) && $_POST['current_location']!='undefined')
                {
                    $post->currentlocation = $_POST['current_location'];
                }

                $post->custom_share = (isset($_POST['sharewith']) && !empty($_POST['sharewith'])) ? $_POST['sharewith'] : '';
                $post->custom_notshare = (isset($_POST['sharenot']) && !empty($_POST['sharenot'])) ? $_POST['sharenot'] : '';
                $post->anyone_tag = (isset($_POST['customchk']) && !empty($_POST['customchk'])) ? $_POST['customchk'] : '';
                $post->post_tags = (isset($_POST['posttags']) && !empty($_POST['posttags'])) ? $_POST['posttags'] : '';    
                $post->post_title = isset($_POST['title']) ? ucfirst($_POST['title']) : '';

                $post->post_title = ucfirst($_POST['title']);
                $post->comment_setting = $_POST['comment_setting'];
                $post->post_privacy = $_POST['post_privacy'];
                $post->customids = $_POST['custom'];
                $post->is_deleted = "$post_status";
                $post->post_ip = $_SERVER['REMOTE_ADDR'];
                $post->placetype = 'discussion';

            
                $img = '';
                $im = '';
                $url = '../web/uploads/';
                $urls = '/uploads/';

                $imageBulkArray = array();


                if (isset($_FILES['imageFile1']) && count($_FILES["imageFile1"]["name"]) >0) 
                {
                    $imgcount = count($_FILES["imageFile1"]["name"]);
                    for ($i =0; $i < $imgcount; $i++)
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

                $post->insert();

                $last_insert_id =  $post->_id;
                
                // Insert record in notification table also
                $notification = new Notification();
                $notification->post_id = "$last_insert_id";
                $notification->user_id = "$user_id";
                $notification->notification_type = 'post';
                $notification->is_deleted = '0';
                $notification->created_date = "$date";
                $notification->updated_date = "$date";
                $notification->insert();
                
                if(isset($_POST['posttags']) && $_POST['posttags'] != 'null')
                {
                    // Insert record in notification table also
                    $tag_connections = explode(',',$_POST['posttags']);
                    $tag_count = count($tag_connections);
                    for ($i = 0; $i < $tag_count; $i++)
                    {
                        $result_security = SecuritySetting::find()->where(['user_id' => "$tag_connections[$i]"])->one();
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
                        $notification->is_deleted = '0';
                        $notification->status = '1';
                        $notification->created_date = "$date";
                        $notification->updated_date = "$date";
                        $notification->insert();
                    }
                }
                if($post_status == '0')
                {
                    $this->display_last_discussion($last_insert_id);
                }
                else
                {
                    if($page_details['gen_post_review'] == 'on' && $page_details['created_by'] != $user_id)
                    {
                        $this->display_review_message();
                    }
                    else
                    {
                        $this->display_last_discussion($last_insert_id);
                    }
                }
            }
        } else {
            return 'checkuserauthclassg';
        }
    }

    public function actionDeleteDiscussion() 
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            return PlaceDiscussion::DeleteDiscussionCleanUp($_POST['pid'],$_POST['post_user_id']);
        }
    }

    public function actionEditDiscussion() 
    {
        $pid = isset($_POST['pid']) ? $_POST['pid'] : '';
        if ($pid != '') {
            $date = time(); 
            $update = PlaceDiscussion::find()->where(['_id' => $pid])->andWhere(['not','flagger', "yes"])->one();
            $text = $update['post_text'];
            $image = $update['image'];

            if(isset($_POST['current_location']) && !empty($_POST['current_location']) && $_POST['current_location']!='undefined')
            {
                $update->currentlocation = $_POST['current_location'];
            }
            $update->post_created_date = "$date";
            
            $update->custom_share = (isset($_POST['sharewith']) && !empty($_POST['sharewith'])) ? $_POST['sharewith'] : '';
            $update->custom_notshare = (isset($_POST['sharenot']) && !empty($_POST['sharenot'])) ? $_POST['sharenot'] : '';
            $update->anyone_tag = (isset($_POST['customchk']) && !empty($_POST['customchk'])) ? $_POST['customchk'] : '';
            $update->post_tags = (isset($_POST['posttags']) && !empty($_POST['posttags'])) ? $_POST['posttags'] : '';

            $update->post_title = ucfirst($_POST['title']);
            $update->comment_setting = $_POST['comment_setting'];
            $update->post_privacy = $_POST['post_privacy'];
            $update->customids = $_POST['custom'];
            
            $img = '';
            $im = '';
            $url = '../web/uploads/';
            $urls = '/uploads/';

            $imageBulkArray = array();

            if (isset($_FILES['imageFile1']) && count($_FILES["imageFile1"]["name"]) >0) 
            {
                $imgcount = count($_FILES["imageFile1"]["name"]);
                for ($i = 0; $i < $imgcount; $i++)
                {
                    if (isset($_FILES["imageFile1"]["name"][$i]) && $_FILES["imageFile1"]["name"][$i] != "") 
                    {
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
                $existFiles = explode(',', $image);
                $files = array_merge($existFiles, $imageBulkArray);
                $files = array_values(array_filter($files));
                $files = implode(',', $files);
                if(!empty($files)) {
                    $files = $files.',';
                    $update->image = $files;
                } else {
                    $update->image = '';
                }
            } else {
                $files = explode(',', $image);
                $files = array_values(array_filter($files));
                $files = implode(',', $files);
                if(!empty($files)) {
                    $files = $files.',';
                    $update->image = $files;
                } else {
                    $update->image = '';
                }
            }

            if (trim($_POST['link_description']) != '' && trim($_POST['link_description']) != 'undefined')
            {
                $title = $_POST['link_title'];
                $description = $_POST['link_description'];
                $image = $_POST['link_image'];
                $url = $_POST['link_url'];

                $update->post_type = 'link';
                $update->link_title = ucfirst($title);
                $update->image = $image;
                $update->post_text = ucfirst($url);
                $update->link_description = $description;
            } else {
                if (isset($_POST['test']) && !empty($_POST['test'])) {
                    $update->post_text = ucfirst($_POST['test']);
                    
                    if ((isset($image) && $image != '') || !empty($imageBulkArray)) {
                        $update->post_type = 'text and image';
                    } else {   
                        $update->post_type = 'text';
                    } 
                }
            }

            $update->update();

            $last_insert_id = $pid;
            $this->display_last_discussion($last_insert_id);
            
            if($update['is_deleted'] == '2') {
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
        } else {
            return "0";
        }
    }

    public function actionEditPostPreSetDiscussion()
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
                $post = PlaceDiscussion::find()->where(['_id' => $postid])->andWhere(['not','flagger', "yes"])->one();
                return $this->render('editdiscussion', array('post' => $post));
            }
        } else {
            return 'checkuserauthclassg'; 
        }
    }

    public function getdiscussiondefault()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $eximgs = array();
        $totalimgs = '';

        $UserPhotos = General::find()->where(["label" => "photostreamcount"])->asarray()->one();
        if(!empty($UserPhotos)) {
            if(isset($UserPhotos['images'])) {
                $eximgs = $UserPhotos['images'];
                $totalimgs = $UserPhotos['count'];
            }            
        }

        $DefaultPosts = DefaultPosts::find()->where(['module' => 'discussion'])->asarray()->one();
        if(!empty($DefaultPosts)) {
            if(!empty($eximgs)) {
                $title = isset($DefaultPosts['title']) ? $DefaultPosts['title'] : '';
                $description = isset($DefaultPosts['title']) ? $DefaultPosts['description'] : '';
                $imgcountcls="";
                if($totalimgs == 1){$imgcountcls = 'one-img';}
                if($totalimgs == 2){$imgcountcls = 'two-img';}
                if($totalimgs == 3){$imgcountcls = 'three-img';}
                if($totalimgs == 4){$imgcountcls = 'four-img';}
                if($totalimgs == 5){$imgcountcls = 'five-img';}
                if($totalimgs > 6){$imgcountcls = 'more-img';}
                ?>
                <div class="post-holder bborder tippost-holder defaultpost">
                   <div class="post-content">
                      <div class="post-details">
                         <div class="post-title"><h5><b><?=$title?></b></h5></div>
                         <div class="post-desc">
                            <?php if(strlen($description)>187){ ?>
                                <div class="para-section">
                                    <div class="para">
                                        <p><?=$description?></p>
                                    </div>
                                    <a href="javascript:void(0)" onclick="showAllContent(this)">Read More</a>
                                </div>
                            <?php }else{ ?>                                     
                                <p><?= $description?></p>
                            <?php } ?>
                         </div>
                      </div>
                      <div class="post-img-holder">
                        <div class="post-img two-img defaultpost-gallery <?= $imgcountcls?> gallery swipe-gallery">
                            <?php
                            $cnt = 1;
                            foreach ($eximgs as $eximg) {

                            if (file_exists('../web/'.$eximg)) {
                            $picsize = '';
                            $val = getimagesize('../web'.$eximg);
                            $picsize .= $val[0] .'x'. $val[1] .', ';
                            $iname = $this->getimagename($eximg);
                            $inameclass = $this->getimagefilename($eximg);
                            $pinit = PinImage::find()->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
                            
                            if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
                            if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';} ?>
                                <a href="javascript:void(0)" class="imgpin pimg-holder <?= $imgclass?>-box <?php if($cnt > 5){?>extraimg<?php } ?> <?php if($cnt ==5 && $totalimgs > 5){?>more-box<?php } ?>" onclick="defaultimagesgallery()">
                                    <img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" class="<?= $imgclass?>"/>
                                    <?php if($cnt == 5 && $totalimgs > 5){?>
                                        <span class="moreimg-count"><i class="mdi mdi-plus"></i><?= $totalimgs - $cnt +1;?></span>
                                    <?php } ?>
                                </a>
                            <?php } $cnt++; } ?>
                        </div>
                        <?php if($imgcountcls == "one-img" && $post['trav_item'] != '1') {
                            if($Auth == 'checkuserauthclassg' || $Auth == 'checkuserauthclassnv') { ?>
                                <a href="javascript:void(0)" class="pinlink <?=$Auth?> directcheckuserauthclass"><i class="mdi mdi-nature"></i></a>
                            <?php } else { 
                                $iname = $this->getimagename($eximg);
                                $inameclass = $this->getimagefilename($eximg);
                                $image = '../web/uploads/gallery/'.$iname;
                                $JIDSsdsa = '';           
                                $pinit = Gallery::find()->where(['post_id' => (string)$post['_id'], 'image' => $image])->andWhere(['not','flagger', "yes"])->one();
                                if(!empty($pinit)) {
                                    $JIDSsdsa = 'active';                    
                                }
                                ?>
                                <a href="javascript:void(0)" class="pinlink pin_<?=$inameclass?> <?=$JIDSsdsa?>" onclick="pinImage('<?=$iname?>','<?=$post['_id']?>', '<?=$tableLabel?>')"><i class="mdi mdi-nature"></i></a>
                            <?php 
                            } 
                        } 
                        ?>
                    </div>
                   </div>
                   <div class="clear"></div>
                </div>
                <?php
            }
        }
    }

    public function actionDefaultpostslides()
    {
        $UserPhotos = ArrayHelper::map(UserPhotos::find()->select(['image'])->asarray()->all(), function($data){return (string)$data['_id'];}, 'image');
        $eximgs = array();
        if(!empty($UserPhotos)) {
            foreach ($UserPhotos as $S_UserPhotos) {
                $c_images = explode(',', $S_UserPhotos);
                $eximgs = array_merge($eximgs, $c_images);
            }
        }
        $eximgs = array_values(array_filter($eximgs));
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        $cnt = 1;

        $result = array();
        foreach ($eximgs as $eximg) {
            if (file_exists('../web/'.$eximg)) {
                $getBaseUrl = Yii::$app->getUrlManager()->getBaseUrl();
                $src = $getBaseUrl.$eximg;
                $cur = array("src" => $src, "thumb" => $src);
                $result[] = $cur;
            }
        } 

        return json_encode($result, TRUE);
    }

    public function actionSharePostPreSetDiscussion()
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

        $type = '';
        $post = PlaceDiscussion::find()->where(['_id' => $pid])->one();
        if(isset($post['parent_post_id']) && !empty($post['parent_post_id']))
        {
            $pid = $post['parent_post_id'];
        }
        else
        {
            $pid = $_POST['pid'];
        }
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
        $sharefunction = 'sharePostDiscussion()';
        
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

    public function actionSharenowwithconnectionsdiscussion() 
    {

        /*$_POST = Array
        (
            'sharewall' => 'own_wall',
            'spid' => '5e5d3e72d47f791384001328',
            'desc' => 'sa',
            'post_privacy' => 'Public',
            'current_location' => '',
            'share_setting' => 'Enable',
            'comment_setting' => 'Enable',
        );*/

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

            $getpostinfo = PlaceDiscussion::find()->where(['_id' => $_POST['spid'],])->one();
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
                $sharepost = new PlaceDiscussion();
               
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
                $sharepost->placetype = 'discussion';
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
                $post_details = PlaceDiscussion::find()->where(['_id' => $_POST['spid']])->one();
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


                $pardetails = PlaceDiscussion::find()->where(['_id' => "$parid"])->one();
                if ($last_insert_id) 
                {
                    $sharepost = PlaceDiscussion::find()->where(['_id' => $parid])->one();
                   
                    $sharepost->share_by = $sharepost['share_by'] . $user_id . ',';
                    if ($sharepost->update()) 
                    {
                        if((string)$last_insert_id != '') {
                            $post = PlaceDiscussion::find()->where([(string)'_id' => (string)$last_insert_id])->one();
                            if(!empty($post)) {
                                $postid = (string)$post['_id'];
                                $postownerid = (string)$post['post_user_id'];
                                $postprivacy = $post['post_privacy'];
                                $isOk = $this->filterDisplayLastPost($postid, $postownerid, $postprivacy);
                                if($isOk == 'ok2389Ko') {
                                    //echo $postid; die;
                                    //$this->display_last_discussion($postid);

                                    $this->display_last_discussion($postid,'from_save','','tippost-holder bborder ','','restingimagefixes');

                                    //$this->context->display_last_discussion($ad_id, $existing_posts, '', $cls,'','restingimagefixes','',$lpDHSU);
                                }
                            }
                        }
                    }
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

            $getpostinfo = PlaceDiscussion::find()->where(['_id' => $_POST['shareid'],])->one();
            if ($getpostinfo) 
            {
                $date = time();
                $sharepost = new PlaceDiscussion();
               
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
                $sharepost->placetype = 'discussion';
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
                    $sharepost = PlaceDiscussion::find()->where(['_id' => $_POST['shareid'],])->one();
                    $sharepost->share_by = $getpostinfo['share_by'] . $user_id . ',';
                    if ($sharepost->update()) 
                    {
                        if((string)$last_insert_id != '') {
                            $post = PlaceDiscussion::find()->where([(string)'_id' => (string)$last_insert_id])->one();
                            if(!empty($post)) {
                                $postid = (string)$post['_id'];
                                $postownerid = (string)$post['post_user_id'];
                                $postprivacy = $post['post_privacy'];
                                $isOk = $this->filterDisplayLastPost($postid, $postownerid, $postprivacy);
                                if($isOk == 'ok2389Ko') {
                                    $this->display_last_discussion($postid);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
?>