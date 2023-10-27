<?php
namespace frontend\controllers;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\mongodb\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use frontend\models\PostForm;
use frontend\models\PlaceDiscussion;
use frontend\models\PlaceReview;
use frontend\models\LoginForm;
use frontend\models\Comment;
use frontend\models\UserForm; 
use frontend\models\Like;
use frontend\models\Page;
use frontend\models\Connect;
use frontend\models\Personalinfo;
use frontend\models\ProfileVisitor;
use frontend\models\SecuritySetting;
use frontend\models\SavePost;
use frontend\models\Interests;
use frontend\models\Language;
use frontend\models\Occupation;
use frontend\models\Notification;
use frontend\models\MuteConnect;
use frontend\models\BlockConnect;
use frontend\models\UnfollowConnect;
use frontend\models\TurnoffNotification;
use frontend\models\PinImage;
use frontend\models\ReferForm;
use frontend\models\Referal;
use frontend\models\Vip;
use frontend\models\Verify;
use frontend\models\Credits;
use frontend\models\CountryCode;
use frontend\models\Education;
use frontend\models\Destination;
use frontend\models\SliderCover;
use frontend\models\UserPhotos;
use frontend\models\PlaceAsk;
use frontend\models\PlaceTip;
use frontend\models\PageReviewPhotos;
use frontend\models\Userwallgallery;
use frontend\models\Gallery;

class UserwallController extends Controller
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
    $request = Yii::$app->request;
    $user_id = $request->get('id'); 
    $uid = (string)$session->get('user_id');	

        if(isset($uid) && $uid != '') { 
        	$checkuserauthclass = UserForm::isUserExistByUid($uid); 
        } else {
        	$checkuserauthclass = 'checkuserauthclassg';
        } 
	    
        $place = Yii::$app->params['place'];
        $placetitle = Yii::$app->params['placetitle'];
        $placefirst = Yii::$app->params['placefirst'];

    	$emails = $session->get('email');
        $user_exist = LoginForm::find()->where(['email' => $emails])->andwhere(['not','status','44'])->asarray()->one();
        $visitor_id = (string)$user_exist['_id'];
		
		$block = BlockConnect::find()->where(['user_id' => "$user_id"])->andwhere(['like','block_ids',$visitor_id])->one();
		if(!$block)
		{
			if($user_id != $visitor_id)
			{
				$visitors = ProfileVisitor::find()->select(['_id'])->with('user')->where(['user_id' => "$user_id",'visitor_id' => $visitor_id,'year' => date("Y"),'month' => date("M")])->one();
				$date = time();

				if($visitors)
				{
					$visitors->visited_date = "$date";
					$visitors->update();
				}
				else
				{
					$visitor = new ProfileVisitor();
					$visitor->user_id = $user_id;
					$visitor->visitor_id = $visitor_id;
					$visitor->visited_date = "$date";
					$visitor->status = '1';
					$visitor->ip = $_SERVER['REMOTE_ADDR'];
					$visitor->month = date("M");
					$visitor->year = date("Y");
					$visitor->insert();
				}
			}

			$user_data =  Personalinfo::getPersonalInfo($user_id);

			$user_connections =  Connect::getuserConnections($user_id);
            $user_connections = array_slice($user_connections, 0, 6);

			$user_tags =  Connect::getuserConnections($request->get('id'));

			$user_basicinfo = LoginForm::find()->where(['_id' => $user_id])->one();

            $posts = PostForm::getUserPostUserwall($user_id); 
        
            $discussion = PlaceDiscussion::getPlaceReviews($place,'discussion','all'); 

            $questions = PlaceAsk::getPlaceReviews($place,'ask','all');

            $tips = PlaceTip::getPlaceReviews($place,'tip','all');

            $reviews = PlaceReview::getPlaceReviews($place,'reviews','all');

            $result = array_merge($posts, $discussion, $questions, $tips, $reviews);

			$photos =  UserPhotos::getUserPostPhotos($user_id);
            
			$likes = Like::getUserPostLike($user_id);

			$path = 'profile/';
			$usrfrdlist = array();
			foreach($user_tags AS $ud) {
				$id = (string)$ud['userdata']['_id'];
				$fbid = isset($ud['userdata']['fb_id']) ? $ud['userdata']['fb_id'] : '';
				$dp = $this->getimage($ud['userdata']['_id'],'thumb');
				
				$nm = (isset($ud['userdata']['fullname']) && !empty($ud['userdata']['fullname'])) ? $ud['userdata']['fullname'] : $ud['userdata']['fname'].' '.$ud['userdata']['lname'];
				$usrfrdlist[] = array('id' => $id, 'fbid' => $fbid, 'name' => $nm, 'text' => $nm, 'thumb' => $dp);
			}

			return $this->render('index',array('posts' => $result,'user_connections' => $user_connections,'photos' => $photos,'user_basicinfo' => $user_basicinfo,'user_data' => $user_data,'likes' => $likes, 'usrfrdlist' => $usrfrdlist, 'checkuserauthclass' => $checkuserauthclass));
		} else {
			$this->redirect(Url::toRoute(['site/block','blockid'=>$user_id]));
		}
}

public function actionPhotos()
{
   $session = Yii::$app->session;
   $request = Yii::$app->request;
   $user_id = $request->get('id');  
   $userid = (string)$session->get('user_id');
   $secureity_model = new SecuritySetting();
   $result_security = SecuritySetting::find()->select(['view_photos'])->where(['user_id' => (string)$userid])->asarray()->one();
   if($result_security)
    {
        $album_privacy = $result_security['view_photos'];
   }
   else
   {
       $album_privacy = 'Public';
   }

   $model = new \frontend\models\LoginForm();

    $post = new UserPhotos();
    $isNewAlbum =  false;
    $page_id = '';
    if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
    {
        $url = $_SERVER['HTTP_REFERER'];
        $url = explode('&',$url);
        $url = explode('=',$url[1]);
        $page_id = $url[1];

        $getPageData = Page::find()->where([(string)'_id' => $page_id])->asarray()->one();
        if(!empty($getPageData)) {
            $gen_photos = isset($getPageData['gen_photos']) ? $getPageData['gen_photos'] : '';
            $gen_photos_review = isset($getPageData['gen_photos_review']) ? $getPageData['gen_photos_review'] : '';
            if($gen_photos == 'allowPhotos' && $gen_photos_review == 'on') {
                $post = new PageReviewPhotos();                    
                $isNewAlbum =  true;
            }
        }
    }
    
    $date = time();
    if($session->get('email'))
    {
        if(isset($_POST) && !empty($_POST)){
        $album_title = $_POST['album_title'];
        if(empty($album_title)){$album_title = 'Untitled Album';}
        $album_description = $_POST['album_description'];
        $album_place = $_POST['album_place'];
        $album_img_date = '';  
        $post->post_status = '1'; 
        $post->album_title = ucfirst($album_title);
        $post->post_text = $album_description;
        $post->album_place = $album_place;
        $post->album_img_date = $album_img_date;
        $post->post_type = 'text and image'; 
        $post->is_album = '1';
        $post->post_privacy = $album_privacy;
        $post->post_created_date = "$date";
        $post->is_deleted = '0';
        if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
        {
            $page_details = Page::Pagedetails($page_id);
            $userid = $page_id;
            $post->post_privacy = 'Public';
            $puid = (string)$session->get('user_id');
            $post->shared_by = "$puid"; 
            $post->pagepost = '1';
            $post->is_deleted = "0";
            if($isNewAlbum) {
                $post->isnewalbum = true;
                $post->page_id = $page_id;
            }
        }
        else
        {
            $userid = $userid;
        }
        $post->post_user_id = (string)$userid;
        $post->post_ip = $_SERVER['REMOTE_ADDR'];
        
         if(isset($_FILES) && !empty($_FILES)){
            $imgcount = count($_FILES["imageFile1"]["name"]);
            $img = '';
            $im = '';
            for($i=0; $i<$imgcount; $i++)
            {
				if(isset($_FILES["imageFile1"]["name"][$i]) && $_FILES["imageFile1"]["name"][$i] != "") 
				{
				   $url = '../web/uploads/';
				   $urls = '/uploads/';
				   $image_extn = explode('.',$_FILES["imageFile1"]["name"][$i]);
$image_extn = end($image_extn);
				   $rand = rand(111,999);
				   $img = $urls.$date.$rand.'.'.$image_extn.',';
				   move_uploaded_file($_FILES["imageFile1"]["tmp_name"][$i], $url.$date.$rand.'.'.$image_extn);

				   $im = $im . $img;
				}
            }
          
            $post->image = $im;
        }
        $post->insert();
        $last_ablum_id = $post->_id;

        if($album_privacy != 'Public')
        {
            $notification = new Notification();
            $notification->post_id = "$last_ablum_id";
            $notification->user_id = "$userid";
            $notification->notification_type = 'post';
            $notification->is_deleted = '0';
            $notification->status = '1';
            $notification->created_date = "$date";
            $notification->updated_date = "$date";
            if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
            {
                $pagedata = Page::Pagedetails($page_id);
                $usrid = $pagedata['created_by'];
                $notification->user_id = "$usrid";
                $notification->page_id = "$page_id";
                $notification->entity = 'page';
            }
            if(!strstr($_SERVER['HTTP_REFERER'],'r=page'))
            {
                $notification->insert();
            }
        }

            if($isNewAlbum){
                return 'isreview';
            } else {
                return $last_ablum_id;
            }
        }
       else { }
    }
    else
    {
        return $this->render('index', ['model' => $model,]);
    }
}
   
public function actionViewalbumpics() 
{
    $data = array();
    if (isset($_POST['post_id']) && !empty($_POST['post_id']))
    {
        $getalbumdetails = UserPhotos::find()->select(['_id','post_user_id','image','album_title'])->where(['_id' => $_POST['post_id']])->asarray()->one();
        $result = UserForm::find()->where(['_id' => (string)$getalbumdetails['post_user_id']])->one();
        $fullname = $result['fname'].' '.$result['lname'];
        if ($getalbumdetails)
        {
            $imgcontent = '';
            if(isset($getalbumdetails['image']) && !empty($getalbumdetails['image']))
            {
                $session = Yii::$app->session;
                $user_id = (string)$session->get('user_id');
                $photosallow = 'denyPhotos';
                $page_name = '';
                $isWall = true;
                if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
                {
                    $url = $_SERVER['HTTP_REFERER'];
                    $urls = explode('&',$url);
                    $url = explode('=',$urls[1]);
                    $page_id = Page::Pagedetails($url[1]);
                    $page_owner_id = $page_id['created_by'];
                    $fullname = $page_id['page_name'];
                    if($user_id == $page_owner_id)
                    {
                        $user_id = $url[1];
                    }
                    $photosallow = $page_id['gen_photos'];
                    $page_name =$page_id['page_name'];
                    $isWall = false;
                }
                $eximgs = explode(',',$getalbumdetails['image'],-1);
                $total_eximgs = count($eximgs); 
            ?>
              <div class="section-title">
                <span><?= $fullname?></span>
                <i class="mdi mdi-chevron-right"></i>
                <span><?=$getalbumdetails['album_title']?></span>&nbsp;<span class="info sub-number">&nbsp;(<?=$total_eximgs?>)</span>
              </div>
              <div class="albums-grid gallery images-container images-container7">
                <div class="row">
                <?php if($user_id == $getalbumdetails['post_user_id'] || $photosallow == 'allowPhotos'){ ?>
                  <div class="grid-box">
					<div class="divrel">
						
                        <a href="#add-photo-popup" class="add-photo popup-modal" id="add-photo-albums">
                         <span class="icont">+</span>
                         Add New Photo
                        </a>
                    
						<input type="file" name="newphotoupld" class="hidden_uploader custom-upload-new" title="Choose a file to upload" required data-class="#add-photo-popup .post-photos .img-row" multiple/>
					</div>
                  </div>
                <?php } ?>
                <div class="lgt-gallery-photo images-container dis-none">
                <?php
                foreach ($eximgs as $eximg) {
                    $uniq_id = rand(999, 9999);
                  $imgpath = Yii::$app->getUrlManager()->getBaseUrl().$eximg;
                  $picsize = '';
                    $imgclass = '';
                    $iname = '';
                    if(file_exists('../web'.$eximg)) {
                    $val = getimagesize('../web'.$eximg);
                    $iname = $this->getimagename($eximg);
                    $inameclass = $this->getimagefilename($eximg);
                    $picsize .= $val[0] .'x'. $val[1] .', ';
                    $pinit = PinImage::find()->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
                    if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
                    if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}
                  }
				    if(!(isset($inameclass) && !empty($inameclass)))
					{
						$inameclass = '';
					}
                ?>
                  <div class="grid-box allow-gallery galleryphotobom<?=(string)$_POST['post_id']?>" id="albumimg_<?=$iname?>" data-src="<?=$imgpath?>" data-sizes="<?=$_POST['post_id']?>|||UserPhotos">
                    <div class="photo-box">
                      <div class="imgholder <?= $imgclass?>-box">
                    	<a href="<?=$imgpath?>" data-imgid="<?=$inameclass?>" data-pinit="<?=$pinval?>"  class="imgpin imgholder <?= $imgclass?>-box">
						  <img src="<?=$imgpath?>" class="<?=$imgclass?>"/>
						</a>
					  <?php if($user_id == $getalbumdetails['post_user_id']){ ?>
                        <div class="edit-link">
                          <div class="dropdown dropdown-custom ">
                            <a class="dropdown-button more_btn" href="javascript:void(0);" data-activates="asds<?=$uniq_id?>"> 
                                <i class="zmdi zmdi-edit"></i>
                            </a>
                            <ul id="asds<?=$uniq_id?>" class="dropdown-content custom_dropdown">
                            <li><a href="javascript:void(0)" onclick="moveImage('<?=$eximg?>','<?=$getalbumdetails['_id']?>')">Move to other album</a></li>
                            <li><a href="javascript:void(0)" onclick="albumCover('<?=$user_id?>','<?=$eximg?>','<?=$getalbumdetails['_id']?>')">Make album cover</a></li>
                            <li><a href="javascript:void(0)" onclick="deleteImage('<?=$iname?>','<?=$eximg?>','<?=$getalbumdetails['_id']?>')">Delete this photo</a></li>
							<?php 
							if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
							{
								$is_exist = SliderCover::find()->where(['image_name' => $iname, 'pageid' => $user_id])->asarray()->one();
							}
							else
							{
								$is_exist = SliderCover::find()->where(['image_name' => $iname, 'user_id' => $user_id])->asarray()->one();
							} ?>
                            </ul>
                          </div>
                        </div>
                      <?php } ?>
                        </div>
					   <?php
                            $fileinfo = pathinfo($eximg);
                            $uniq_id = $fileinfo['filename'] .'_'. $getalbumdetails['_id'];
                            $like_count = Like::getLikeCount((string)$uniq_id);
                            $like_names = Like::getLikeUserNames((string)$fileinfo['filename'] .'_'. $getalbumdetails['_id']);
                            $like_buddies = Like::getLikeUser((string)$fileinfo['filename'] .'_'. $getalbumdetails['_id']);
							$is_like = Like::find()->where(['user_id'=>$user_id,'post_id'=>$uniq_id,'status'=>'1'])->one();
							if($is_like){
                                $ls = 'mdi-thumb-up';
                            } else {
                                $ls = 'mdi-thumb-up-outline';
                            }
                            $newlike_buddies = array();
                            foreach($like_buddies as $like_buddy)
                            {
                                $newlike_buddies[] = ucfirst($like_buddy['user']['fname']). ' '.ucfirst($like_buddy['user']['lname']);
                            }
                            $newlike_buddies = implode('<br/>', $newlike_buddies);
                          ?>
                      <div class="descholder">
                        <a href="javascript:void(0)" class="namelink"><span><?=$getalbumdetails['album_title']?></span></a>
                        <div class="options prevent-gallery">
                            <a href="javascript:void(0)"  onclick="doLikeAlbumbPhotos('<?=$uniq_id?>');">
                                <i class="mdi mdi-16px <?=$ls?> ls_<?=$uniq_id?>"></i>
                            </a>
                         <div class="info">                                   
                            <a href="javascript:void(0)" data-id='photo-1' data-section='photos' class="custom-tooltip pa-like liveliketooltip liketitle_<?=$uniq_id?>" onclick="doLikeAlbumbPhotos('<?=$uniq_id?>');" data-title="<?=$newlike_buddies?>">
                            </a>
                            <span class="glyphicon glyphicon-thumbs-up likecount_<?=$uniq_id?>">
                            <?php if($like_count >0 ) { ?> <?=$like_count?><?php } ?>
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php } ?>                      
                </div>
				</div>
              </div>
           <?php } }
        else
        {
            $data['value'] = '2';
            return json_encode($data);
        }
    }
    else
    {
        $data['value'] = '0';
        return json_encode($data);
    }
}

public function actionViewprofilepics() 
{
    $data = array();
    if (isset($_POST['u_id']) && !empty($_POST['u_id']))
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
        {
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $user_id = $_POST['u_id'] = $url[1];
        }
        $profile_albums = UserPhotos::getProfilePics((string)$_POST['u_id']);
        $total_profile_albums = count($profile_albums);
        $result = UserForm::find()->where(['_id' => (string)$_POST['u_id']])->one();
        $fullname = $result['fname'].' '.$result['lname'];
        if ($profile_albums)
        {
            $imgcontent = '';
            ?>               
              <div class="section-title">
                <span><?= $fullname?></span>
                <i class="mdi mdi-chevron-right"></i>
                <span>Profile Photos</span><span class="info sub-number">&nbsp;<?=$total_profile_albums?> Photos</span>
              </div>
              <div class="albums-grid images-container images-container9">
                <div class="row gallery">
				<div class="images-container lgt-gallery-photo dis-none">
                <?php
                foreach ($profile_albums as $eximg) {
                    $uniq_id = rand(999, 9999);
                    $iname = $eximg['image'];
                    $inameclass = $this->getnameonly($eximg['image']);
                    $imgpath = Yii::$app->getUrlManager()->getBaseUrl().'/profile/'.$eximg['image'];
                    $picsize = '';
                    $val = getimagesize('../web/profile/'.$eximg['image']);
                    $picsize .= $val[0] .'x'. $val[1] .', ';
                    $pinit = PinImage::find()->select(['_id'])->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
                    if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
                    if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}
					  if(!(isset($inameclass) && !empty($inameclass)))
					  {
						  $inameclass = '';
					  }
                ?>
                  <div class="grid-box" id="albumimg_<?=$eximg['image']?>" data-src="<?=$imgpath?>">
                    <div class="photo-box">
                      <div class="imgholder <?= $imgclass?>-box">
                        <a href="<?=$imgpath?>" class="pinlink pin_<?=$inameclass?> <?php if($pinit){ ?>active<?php } ?> data-imgid="<?=$inameclass?>" data-sizes="<?=$_POST['post_id']?>|||UserPhotos" class="allow-gallery imgpin">
                          <img src="<?=$imgpath?>" class="<?=$imgclass?>"/>
                        </a>
					  <?php if($user_id == $eximg['post_user_id']){ ?>
                        <div class="edit-link">
                        <div class="dropdown dropdown-custom dropdown-auto">
                            <a href="javascript:void(0)" class="dropdown-button more_btn" data-activates="<?=$uniq_id?>">
                            <i class="mdi mdi-pencil"></i>
                            </a>
                            <ul id="<?=$uniq_id?>" class="dropdown-content custom_dropdown">
                            <li><a href="javascript:void(0)" onclick="deletePhoto('<?=$eximg['_id']?>')">Delete this photo</a></li>
                            </ul>
                          </div>
                        </div>
                        <?php } ?>
                        </div>
						<?php
                            $fileinfo = pathinfo($iname);
                            $uniq_id = $fileinfo['filename'] .'_'. $eximg['_id'];
                            $like_count = Like::getLikeCount((string)$uniq_id);
                            $like_names = Like::getLikeUserNames((string)$fileinfo['filename'] .'_'. $eximg['_id']);
                            $like_buddies = Like::getLikeUser((string)$fileinfo['filename'] .'_'. $eximg['_id']);
							$is_like = Like::find()->where(['user_id'=>$user_id,'post_id'=>$uniq_id,'status'=>'1'])->one();
							if($is_like){$ls = 'mdi-thumb-up';}else{$ls = 'mdi-thumb-up-outline';}
                            $newlike_buddies = array();
                            foreach($like_buddies as $like_buddy)
                            {
                                $newlike_buddies[] = ucfirst($like_buddy['user']['fname']). ' '.ucfirst($like_buddy['user']['lname']);
                            }
                            $newlike_buddies = implode('<br/>', $newlike_buddies);
                          ?>
                      <div class="descholder">
                        <a href="javascript:void(0)" class="namelink"><span>Profile Photo</span></a>
                        <div class="options prevent-gallery">
                          <a href="javascript:void(0)" onclick="doLikeAlbumbPhotos('<?=$uniq_id?>');">
                            <i class="mdi mdi-16px <?=$ls?> ls_<?=$uniq_id?>"></i
                          </a>
                          <div class="info">                                                                 
                            <a href="javascript:void(0)" data-id='photo-1' data-section='photos' class="custom-tooltip pa-like liveliketooltip liketitle_<?=$uniq_id?>" onclick="doLikeAlbumbPhotos('<?=$uniq_id?>');" data-title="<?=$newlike_buddies?>">
                            </a>
                            <span class="glyphicon glyphicon-thumbs-up likecount_<?=$uniq_id?>">
                            <?php if($like_count >0 ) { ?> <?=$like_count?> <?php } ?>
                            </span>
                        </div>
                      </div>
                    </div>
                  </div>
				  </div>
                <?php } ?>   
                </div>
              </div>
			  </div>
           <?php }
        else
        {
            $data['value'] = '2';
            return json_encode($data);
        }
    }
    else
    {
        $data['value'] = '0';
        return json_encode($data);
    }
}

public function actionViewcoverpics() 
{
    $data = array();
    if (isset($_POST['u_id']) && !empty($_POST['u_id']))
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $uid = (string)$session->get('user_id');
        if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
        {
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $user_id = $_POST['u_id'] = $url[1];
        }
        $profile_albums = UserPhotos::getCoverPics((string)$_POST['u_id']);
        $total_profile_albums = count($profile_albums);
        $result = UserForm::find()->where(['_id' => (string)$_POST['u_id']])->one();
        $fullname = $result['fname'].' '.$result['lname'];
        if ($profile_albums)
        {
            $imgcontent = '';
            ?>
              <div class="section-title">
                <span><?= $fullname?></span>
                <i class="mdi mdi-chevron-right"></i>
                <span>Cover Photos</span><span class="info sub-number">&nbsp;<?=$total_profile_albums?> Photos</span>
              </div>
              <div class="albums-grid images-container images-container10">
                <div class="row gallery">
				<div class="images-container lgt-gallery-photo dis-none">
                <?php
                foreach ($profile_albums as $eximg) {
                    $uniq_id = rand(999, 9999);
                    $imgpath = Yii::$app->getUrlManager()->getBaseUrl().'/uploads/cover/'.$eximg['image'];
                    $picsize = '';
                    $val = getimagesize('../web/uploads/cover/'.$eximg['image']);
                    $iname = $eximg['image'];
                    $inameclass = $this->getnameonly($eximg['image']);
                    $picsize .= $val[0] .'x'. $val[1] .', ';
                    $pinit = PinImage::find()->select(['_id'])->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
                    if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
                    if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}
					if(!(isset($inameclass) && !empty($inameclass)))
					{
						$inameclass = '';
					}
                ?>
                  <div class="grid-box" id="albumimg_<?=$eximg['image']?>" data-src="<?=$imgpath?>">
                    <div class="photo-box">
                      <div class="imgholder <?= $imgclass?>-box">
                        <a href="<?=$imgpath?>" class="allow-gallery imgpin <?= $imgclass?>-box pinlink pin_<?=$inameclass?> <?php if($pinit){ ?>active<?php } ?>" data-sizes="<?=$_POST['u_id']?>|||UserPhotos" data-imgid="<?=$inameclass?>">
                          <img src="<?=$imgpath?>" class="<?=$imgclass?>"/>
                        </a>
                        <?php if(strstr($_SERVER['HTTP_REFERER'],'r=page')){ ?>
					  <?php if($uid == $eximg['page_owner']){ ?>
                        <div class="edit-link">
                          <div class="dropdown dropdown-custom dropdown-auto">
                            <a class="dropdown-button more_btn" href="javascript:void(0);" data-activates="<?=$uniq_id?>cdvfcd">
                            <i class="zmdi zmdi-edit zmdi-hc-fw"></i>
                            </a>
                            <ul id="<?=$uniq_id?>cdvfcd" class="dropdown-content custom_dropdown">
                            <li><a href="javascript:void(0)" onclick="deletePhoto('<?=$eximg['_id']?>')">Delete this photo</a></li>
                            </ul>
                          </div>
                        </div>
                        <?php } ?>
                        <?php } else { ?>
						<?php if($user_id == $eximg['post_user_id']){ ?>
                        <div class="edit-link">
                            <div class="dropdown dropdown-custom dropdown-auto">
                            <a class="dropdown-button more_btn" href="javascript:void(0);" data-activates="<?=$uniq_id?>cdvfcd">
                            <i class="zmdi zmdi-edit zmdi-hc-fw"></i>
                            </a>
                            <ul id="<?=$uniq_id?>cdvfcd" class="dropdown-content custom_dropdown">
                            <li><a href="javascript:void(0)" onclick="deletePhoto('<?=$eximg['_id']?>')">Delete this photo</a></li>
                            </ul>
                          </div>
                        </div>
                        <?php } ?>
                        <?php } ?>
                        </div>
						<?php
                            $fileinfo = pathinfo($iname);
                            $uniq_id = $fileinfo['filename'] .'_'. $eximg['_id'];
                            $like_count = Like::getLikeCount((string)$uniq_id);
                            $like_names = Like::getLikeUserNames((string)$fileinfo['filename'] .'_'. $eximg['_id']);
                            $is_like = Like::find()->where(['user_id'=>$user_id,'post_id'=>$uniq_id,'status'=>'1'])->one();
							if($is_like){$ls = 'mdi-thumb-up';}else{$ls = 'mdi-thumb-up-outline';}
                            $like_buddies = Like::getLikeUser((string)$fileinfo['filename'] .'_'. $eximg['_id']);
                            $newlike_buddies = array();
                            foreach($like_buddies as $like_buddy)
                            {
                                $newlike_buddies[] = ucfirst($like_buddy['user']['fname']). ' '.ucfirst($like_buddy['user']['lname']);
                            }
                            $newlike_buddies = implode('<br/>', $newlike_buddies);
                          ?>
                      <div class="descholder">
                        <a href="javascript:void(0)" class="namelink"><span>Cover Photo</span></a>
                        <div class="options prevent-gallery">
                          <a href="javascript:void(0)"  onclick="doLikeAlbumbPhotos('<?=$uniq_id?>');">
                            <i class="mdi mdi-16px <?=$ls?> ls_<?=$uniq_id?>"></i
                            </a>
						  <div class="info">                                  
                            <a href="javascript:void(0)" data-id='photo-1' data-section='photos' class="custom-tooltip pa-like liveliketooltip liketitle_<?=$uniq_id?>" onclick="doLikeAlbumbPhotos('<?=$uniq_id?>');" data-title="<?=$newlike_buddies?>">
                            </a>
                            <span class="glyphicon glyphicon-thumbs-up likecount_<?=$uniq_id?>">
                            <?php if($like_count >0 ) { ?><?=$like_count?><?php } ?>
                            </span>
                          </div>
                      </div>
                      </div>
                    </div>
                  </div>
                <?php } ?>  
				</div>                    
                </div>
              </div>
           <?php }
        else
        {
            $data['value'] = '2';
            return json_encode($data);
        }
    }
    else
    {
        $data['value'] = '0';
        return json_encode($data);
    }
}

public function actionDeleteImage()
{
    $image_name = isset($_POST['image_name']) ? $_POST['image_name'] : '';
    $post_id =  isset($_POST['post_id']) ? $_POST['post_id'] : '';
    $data = array();
    $issearch = 'yes';
    if($image_name != '' && $post_id != '')
    {   
        $delimage = UserPhotos::find()->select(['_id','image'])->where(['_id' => $post_id])->one();
        if(!empty($delimage))
        {
            $issearch = 'no';
            $imagevalue = $delimage['image'];
            $imagepath = $image_name.',';
            $updatedimagevalue = str_replace($imagepath,"",$imagevalue);
            if(strlen($updatedimagevalue) < 3)
            {
                $delimage->post_type = 'text';
                if($delimage->is_album == '1') { $delimage->is_album = '0'; $delimage->is_deleted = '1';}
            }
            $delimage->image = $updatedimagevalue;
            if($delimage->update())
            {
                $data['value'] = '1';
            }
            else
            {
                $data['value'] = '0';
            }
        }

        if($issearch == 'yes'){
            $delimage = PostForm::find()->where(['_id' => $post_id])->one();
            if(!empty($delimage))
            {
                $issearch = 'no';
				$imagevalue = $delimage['image'];
				$imagepath = $image_name.',';
				$updatedimagevalue = str_replace($imagepath,"",$imagevalue);
				if(strlen($updatedimagevalue) < 3)
				{
					$delimage->post_type = 'text';
				}
				$delimage->image = $updatedimagevalue;
				if($delimage->update())
				{
					$data['value'] = '1';
				}
				else
				{
					$data['value'] = '0';
				}
			}
        }


        if($issearch == 'yes'){
            $delimage = PlaceDiscussion::find()->where(['_id' => $post_id])->andWhere(['not','flagger', "yes"])->one();
            if(!empty($delimage))
            {
                $issearch = 'no';
                $imagevalue = $delimage['image'];
                $imagepath = $image_name.',';
                $updatedimagevalue = str_replace($imagepath,"",$imagevalue);
                if(strlen($updatedimagevalue) < 3)
                {
                    $delimage->post_type = 'text';
                }
                $delimage->image = $updatedimagevalue;
                if($delimage->update())
                {
                    $data['value'] = '1';
                }
                else
                {
                    $data['value'] = '0';
                }
            }
        }

        if($issearch == 'yes'){
            $delimage = PlaceReview::find()->where(['_id' => $post_id])->andWhere(['not','flagger', "yes"])->one();
            if(!empty($delimage))
            {
                $issearch = 'no';
                $imagevalue = $delimage['image'];
                $imagepath = $image_name.',';
                $updatedimagevalue = str_replace($imagepath,"",$imagevalue);
                if(strlen($updatedimagevalue) < 3)
                {
                    $delimage->post_type = 'text';
                }
                $delimage->image = $updatedimagevalue;
                if($delimage->update())
                {
                    $data['value'] = '1';
                }
                else
                {
                    $data['value'] = '0';
                }
            }
        }

        if($issearch == 'yes'){
				$data['value'] = '0';
		}
    }
    else
    {
        $data['value'] = '0';
    }
    return json_encode($data, true);
}

public function actionDeleteAlbum()
{
	return UserPhotos::DeletePhotoCleanUp();
}

public function actionAlbumCover()
{
    $image_name = $_POST['image_name'];
    $post_id = $_POST['post_id'];
    $data = array();
    if(isset($image_name) && !empty($image_name) && isset($post_id) && !empty($post_id))
    {
        $updatealbumcover = UserPhotos::find()->select(['image'])->where(['_id' => $post_id])->one();
        if($updatealbumcover)
        {
            $imagevalue = $updatealbumcover['image'];
            $eximgs = explode(',',$imagevalue,-1);
            $totalimgs = count($eximgs);
            if($totalimgs == '1')
            {
                $data['value'] = '1';
                return json_encode($data);
            }
            else
            {
                $imagepath = $image_name.',';
                $updatedimagevalue = str_replace($imagepath,"",$imagevalue);
                $updatealbumcover->image = $image_name.','.$updatedimagevalue;
                if($updatealbumcover->update())
                {
                    $data['value'] = '1';
                    return json_encode($data);
                }
                else
                {
                    $data['value'] = '0';
                    return json_encode($data);
                }
            }
        }
        else
        {
            $data['value'] = '0';
            return json_encode($data);
        }
    }
    else
    {
        $data['value'] = '0';
        return json_encode($data);
    }
}

public function actionMoveAlbumImage()
{
    $image_name = $_POST['image_name'];
    $from_post_id = $_POST['from_post_id'];
    $to_post_id = $_POST['to_post_id'];
    $data = array();
    if(isset($image_name) && !empty($image_name) && isset($from_post_id) && !empty($from_post_id) && isset($to_post_id) && !empty($to_post_id))
    {
        $removealbumimage = UserPhotos::find()->select(['image'])->where(['_id' => $from_post_id])->one();
        $imagevalue = $removealbumimage['image'];
        $imagepath = $image_name.',';
        $removealbumimagee = str_replace($imagepath,"",$imagevalue);
        if(strlen($removealbumimagee) < 3)
        {
            $removealbumimage->post_type = 'text';
            if($removealbumimage->is_album == '1') { $removealbumimage->is_album = '0'; $removealbumimage->is_deleted = '1';}
        }
        $removealbumimage->image = $removealbumimagee;

        $addalbumimage = new UserPhotos();
        $addalbumimage = UserPhotos::find()->select(['image'])->where(['_id' => $to_post_id])->one();
        $addimagevalue = $addalbumimage['image'];
        $addalbumimage->image = $addimagevalue.$image_name.',';

        if($removealbumimage->update() && $addalbumimage->update())
        {
            $data['value'] = '1';
            return json_encode($data);
        }
        else
        {
            $data['value'] = '0';
            return json_encode($data);
        }
    }
    else
    {
        $data['value'] = '0';
        return json_encode($data);
    }
}

public function actionGalleryContent() 
{
    $session = Yii::$app->session;
    $request = Yii::$app->request;
    $email = $session->get('email'); 
    $user_id = (string) $session->get('user_id');  
    $wall_user_id = (string) $_POST['id'];
    $baseUrl = (string) $_POST['baseUrl'];
      
    $assetsPath = '../../vendor/bower/travel/images/';
    ?>
    <div class="combined-column">
	<?php
	$gallery = PinImage::getPinnedImages($wall_user_id);
	$gallery_img = $this->getimage($user_id,'photo');
	if(count($gallery) > 0){
	?>
	<div class="gloader"><img src="<?=$assetsPath?>loading.gif" class="g-loading"/></div>
		<div id="box" class="images-container" style="visibility:hidden;"> 
	<?php            
	foreach($gallery as $gallery_item)
	{
		$like_active = '';
		$eximg = '/uploads/'.$gallery_item['imagename'];
		$picsize = '';
		$imgclass = '';
		$fileinfo = pathinfo($eximg);
		$uniq_id = $fileinfo['filename'] .'_'. $gallery_item['_id'];
		$like_count = Like::getLikeCount((string)$uniq_id);
		$comments = Comment::getAllPostLike((string)$uniq_id);

		if(file_exists('../web'.$eximg))
		{
			$galimname = $gallery_item['imagename'];
			$postdetails = PostForm::find()->select(['post_user_id','currentlocation','post_created_date'])->where(['like','image',$galimname])->one();
			$like_active = Like::find()->where(['post_id' => (string) $uniq_id,'status' => '1','user_id' => (string) $user_id])->one();
			if(!empty($like_active))
			{
				$like_active = 'active';
			}
			else
			{
				$like_active = '';
			}
			$time = Yii::$app->EphocTime->comment_time(time(),$postdetails['post_created_date']);
			$puserid = (string)$postdetails['post_user_id'];
			$curloc = $postdetails['currentlocation'];
			$puserdetails = LoginForm::find()->where(['_id' => $puserid])->one();
			if($puserid != $user_id)
			{
				$galusername = ucfirst($puserdetails['fname']) . ' ' . ucfirst($puserdetails['lname']);
			}
			else
			{
				$galusername = 'You';
			}
			$iname = $this->getimagename($eximg);
			$inameclass = $this->getimagefilename($eximg);
			$pinit = PinImage::find()->select(['_id'])->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
			if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
			$val = getimagesize('../web'.$eximg);
			$picsize .= $val[0] .'x'. $val[1] .', ';
			if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}
			if(!(isset($inameclass) && !empty($inameclass)))
			{
			  $inameclass = '';
			}
		?>
		<div class="wcard">
			<div class="img-holder">
				<figure>
					<a href="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" class="listalbum-box imgpin" data-imgid="<?=$inameclass?>" data-size="1600x1600"  data-med="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-med-size="1024x1024" data-author="<?=$galusername?>" data-pinit="<?=$pinval?>">
							<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" alt="">
					</a>

					<div class="ititle">
						<div class="ititle-icon"><i class="mdi mdi-camera"></i></div>
						<div class="ititle-text">
							<p>Picture Added <?=$time?></p>
							<span>Photo by <?=$galusername?></span>
						</div>
						<a class="pinlink pin_<?=$inameclass?> <?php if($pinit){ ?>active<?php } ?>" href="javascript:void(0)"><i class="mdi mdi-nature"></i></a>
					</div>
			   </figure>
			</div>
			<?php
				$like_names = Like::getLikeUserNames((string)$fileinfo['filename'] .'_'. $gallery_item['_id']);
				$like_buddies = Like::getLikeUser((string)$fileinfo['filename'] .'_'. $gallery_item['_id']);
				$newlike_buddies = array();
				foreach($like_buddies as $like_buddy)
				{
					$newlike_buddies[] = ucfirst($like_buddy['user']['fname']). ' '.ucfirst($like_buddy['user']['lname']);
				}
				$newlike_buddies = implode('<br/>', $newlike_buddies);  
			?>
			<div class="desc-holder">
				<div class="post-actions">
					<a href="javascript:void(0)" class="custom-tooltip pa-like liveliketooltip liketitle_<?=$uniq_id?> <?=$like_active?>" onclick="doLikeAlbumbImages('<?=$uniq_id?>');" data-title="<?=$newlike_buddies?>">
						<i class="zmdi zmdi-thumb-up"></i>
                    </a>
					<?php if($like_count >0 ) { ?>
						<span class="likecount_<?=$uniq_id?> lcount"><?=$like_count?></span>
					<?php } else { ?>
                        <span class="likecount_<?=$uniq_id?> lcount"></span>
                    <?php } ?>

					<a href="javascript:void(0)" data-section="gallery" data-id="1" class="view-comments pa-comment" onclick="galary_comment('<?=$uniq_id?>')"><i class="zmdi zmdi-comment"></i>
					</a>
                    <?php if((count($comments)) > 0){ ?>
                        <span class="lcount commentcountdisplay_<?=$uniq_id?>"><?=count($comments)?></span>
                    <?php } else { ?>
                        <span class="lcount commentcountdisplay_<?=$uniq_id?>"></span>
                    <?php } ?>
				</div>
				<div class="comments-section panel">
					<div class="img-area"><img src="<?= $gallery_img;?>" class="img-responsive" alt="user-photo"></div>
					<div class="desc-area"><textarea data-adaptheight id="comment_txt_<?=$uniq_id?>" class="comment_class data-adaptheight" placeholder="Write a comment" data-postid="<?=$uniq_id?>"></textarea></div>
				</div>      
		   </div>
	   </div>
	<?php } } ?> 
	</div>
	<?php } else { ?>
	
	<div class="content-box bshadow">			
		<?php $this->getnolistfound('nopinnedphotos');?>
	</div>
	
	<?php } ?>
						
	</div>
	<?php 
}

public function actionSavedContent() {

$session = Yii::$app->session;
$request = Yii::$app->request;
$email = $session->get('email'); 
$user_id = (string) $session->get('user_id');  
$wall_user_id = (string) $_POST['id'];
$baseUrl = (string) $_POST['baseUrl'];
?>

<div class="wall-subcontent">
<div class="content-box bshadow">
  <div class="cbox-title">
	Saved Post
  </div>              
  <div class="saved-post">
  <ul>
  <?php 	
$savedposts = SavePost::find()->select(['_id','post_id','user_id','post_type','post_user_id','type'])->where(['user_id' => "$user_id",'is_saved' => '1'])->orderBy(['saved_date'=>SORT_DESC])->all();

if(!empty($savedposts)) {
  foreach($savedposts as $savedpost) {
    $postid = $savedpost['post_id'];
    $uniqId = rand(999, 9999999).$postid;
	$userid = $savedpost['user_id'];
	$posttype = $savedpost['post_type'];
	$userinfo = LoginForm::find()->where(['_id' => $savedpost['postData']['post_user_id']])->one();
	$post_user_id = $userinfo['_id'];
	$userfname = $userinfo['fname'];
	$userlname = $userinfo['lname'];
	if($post_user_id != $userid)
	{
	  $name = $userfname." ".$userlname."'s";
	}
	else 
	{
	  $name = 'your';
	}
	$save_value = $name.' post.';
	if($savedpost['type'] == 'ask')
	{
		$postinfo = PlaceAsk::find()->select(['post_type','image','link_title', 'post_title', 'post_user_id','post_text'])->where(['_id' => $savedpost['post_id']])->andWhere(['not','flagger', "yes"])->one();
	}
	else if($savedpost['type'] == 'tip')
	{
		$postinfo = PlaceTip::find()->select(['post_type','image','link_title', 'post_title', 'post_user_id','post_text'])->where(['_id' => $savedpost['post_id']])->andWhere(['not','flagger', "yes"])->one();
	}
	else if($savedpost['type'] == 'place_review')
	{
		$postinfo = PlaceReview::find()->select(['post_type','image','link_title', 'post_title', 'post_user_id','post_text'])->where(['_id' => $savedpost['post_id']])->andWhere(['not','flagger', "yes"])->one();
	}
	else
	{
		$postinfo = PostForm::find()->select(['post_type','image','link_title', 'post_title', 'post_user_id','post_text'])->where(['_id' => $savedpost['post_id']])->one();
	}


    if(isset($postinfo['post_title']) && trim($postinfo['post_title']) != '') {
        $postTitle = $postinfo['post_title'];
    } else {
        $postTitle = $this->getuserdata($postinfo['post_user_id'],'fullname') .' post';
    }
    
	$posttype = $postinfo['post_type'];
	if($posttype == 'image')
	{
	  $eximgs = explode(',',$postinfo['image'],-1);
	  $totalimgs = count($eximgs);
	  $saveimageval = $totalimgs > 1 ? 's' : '';
	  $savetitle = $totalimgs.' Photo'.$saveimageval;
	}
	else if($posttype == 'profilepic')
	{
	  $savetitle = '1 Photo';
	}
	else if($posttype == 'link')
	{
	  $savetitle = $postinfo['link_title'];
	}
	else if($posttype == 'text' || $posttype == 'text and image')
	{
	  $savetitle = $postinfo['post_text'];
	}
	else
	{
	  $savetitle = 'View Post';
	}


	if($posttype == 'image' || $posttype == 'text and image' )
	{
	  $eximgs = explode(',',$postinfo['image'],-1);
	  $display_image = substr($eximgs[0],1);
	}
	else if($posttype == 'link')
	{
	  $display_image = $postinfo['image'];
	}
	else
	{
	  $display_image = $this->getimage($post_user_id,'photo');
	}
	$picsize = '';
	$val = getimagesize($display_image);
	$picsize .= $val[0] .'x'. $val[1] .', ';
	$imgclass = "";
	$imgclassanc = "";
	if($val[0] > $val[1]){$imgclass = 'himg';$imgclassanc = $imgclass.'-box';}
	if($val[1] > $val[0]){$imgclass = 'vimg';$imgclassanc = $imgclass.'-box';}
					$dpforpopup = $this->getimage($post_user_id,'thumb');
  ?>
	<li  class="main-li" id="save_content_<?= $postid;?>">
		<div class="saved-box">
          <div class="imgholder <?= $imgclassanc?>">
			<?php if($posttype == 'link') { ?>
			<a href="javascript:void(0)" class="s_postlink">
			<?php } else { ?>
			<a href="javascript:void(0)" class="s_postlink">
			<?php } ?>
			<img src="<?= $display_image?>" class="img-responsive <?= $imgclass?>"/>
			<?php if($posttype == 'link') { ?>
			<span class="vplay"><i class="mdi mdi-google-play"></i></span>
			<?php }?>
			</a>
		  </div>
		  <div class="descholder post-desc">
            <h6><?=$postTitle?></h6>  
            <a href="javascript:void(0)" class="s_postlink nothemecolor">
            <?php if($posttype == 'link')
            {
                ?>
                Link · Video
                <?php
            }
            else if($posttype == 'text and image')
            {
              $eximgs = explode(',',$postinfo['image'],-1);
              $totalimgs = count($eximgs);
              $saveimageval = $totalimgs > 1 ? 's' : '';
              $saveother = $totalimgs.' Photo'.$saveimageval;
              ?>
              <?=$saveother?>
              <?php
              $savetitle = 'View Post';
            }
            ?>
            </a>
			<a href="javascript:void(0)" class="mainlink s_postlink nothemecolor">
            <p>
            <?=substr($savetitle,0,125)?>
			<?php if(strlen($savetitle) >= 125) { ?> 
                ...
            <?php } ?>
            </p>
			</a>
			<div class="dropdown dropdown-custom dropdown-xxsmall">
			  <a href="javascript:void(0)" class="dropdown-button nothemecolor" data-activates="<?=$uniqId?>">
			 <i class="zmdi zmdi-more"></i>
			  </a>
			  <ul id="<?=$uniqId?>" class="dropdown-content custom_dropdown">
              <li><a href="javascript:void(0)" data-sharepostid="<?=$postid;?>" class="customsharepopup-modal">Share</a></li>
			  <li><a href="javascript:void(0)" onClick="savePost('<?=$postid?>','<?=$posttype?>','Unsave')">Unsave</a></li>
			  </ul>
			</div>
		  </div>
		</div>
		<div class="saved-post-detail"> 
    		<?php 
    			$existing_posts = 'from_save';
    			
    			if($savedpost['type'] == 'ask')
    			{
    				$this->display_last_ask($postid,$existing_posts);
    			}
    			else if($savedpost['type'] == 'tip')
    			{
    				$this->display_last_tip($postid,$existing_posts);
    			}
    			else if($savedpost['type'] == 'place_review')
    			{
    				$this->display_last_place_review($postid,$existing_posts);
    			}
    			else
    			{ 
                    if((string)$postid != '') {
                        $post = PostForm::find()->where([(string)'_id' => (string)$postid])->one();
                        if(!empty($post)) {
                            $postid = (string)$post['_id'];
                            $postownerid = (string)$post['post_user_id'];
                            $postprivacy = $post['post_privacy'];
                            $isOk = $this->filterDisplayLastPost($postid, $postownerid, $postprivacy);
                            if($isOk == 'ok2389Ko') {
                                $this->display_last_post($postid, $existing_posts);
                            }
                        }
                    }
    			}
    		?>
		</div>
		<?php } ?>
	</li>
	</ul>
	<?php } else { 
		$this->getnolistfound('nophotosaved');
	} ?>
  </div>              

</div>
</div>

<div class="scontent-column">

<?php include('../views/layouts/people_you_may_know.php'); ?>
		
<?php include('../views/layouts/people_view_you.php'); ?>

<?php include('../views/layouts/recently_joined.php'); ?>

</div>

<?php
}

public function actionConnectContent() { 
$session = Yii::$app->session;
$request = Yii::$app->request;
$email = $session->get('email'); 
$suserid = $user_id = (string) $session->get('user_id');  
$guserid = $wall_user_id = (string) $_POST['id'];
$data = array();
  
$assetsPath = '../../vendor/bower/travel/images/';
$online_users = isset($_POST['userList']) ? $_POST['userList'] : array();


 if(isset($suserid) && $suserid != '') {
    $authstatus = UserForm::isUserExistByUid($suserid);
    if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
        $data['auth'] = $authstatus;
        return $authstatus;
    } 
    else {

$baseUrl = (string) $_POST['baseUrl'];
$result_security = SecuritySetting::find()->select(['_id','connect_list'])->where(['user_id' => $guserid])->asarray()->one();
$walluserdetail = LoginForm::find()->where(['_id' => $guserid])->one();
if (isset($result_security['connect_list']) && !empty($result_security['connect_list']))
{
  $my_connect_view_status = $result_security['connect_list'];
  if ($my_connect_view_status == 'Private')
    {
        $post_dropdown_class = 'lock';
    }
    else if ($my_connect_view_status == 'Connections')
    {
        $post_dropdown_class = 'user';
    }
    else
    {
        $post_dropdown_class = 'globe';
    }
}
else
{
  $my_connect_view_status = 'Public';
  $post_dropdown_class = 'globe';
} 
$is_connect = Connect::find()->select(['_id'])->where(['from_id' => "$guserid",'to_id' => "$suserid",'status' => '1'])->asarray()->one();

if($my_connect_view_status == 'Private' && ($is_connect || $guserid != $suserid))  {
    $user_connections = Connect::getMutualConnect($wall_user_id);
} else {
    $user_connections =  Connect::getuserConnections($wall_user_id);

}
?>

<div class="combined-column <?=$my_connect_view_status?> <?=$guserid?> <?=$suserid?>">
    <?php
    $connectcount = Connect::getuserConnectionscount($guserid);
    if(($my_connect_view_status == 'Public') || ($my_connect_view_status == 'Connections' && ($is_connect || $guserid == $suserid)) || ($my_connect_view_status == 'Private' && ($is_connect || $guserid == $suserid)))  {
    ?>
      <div class="cbox-desc">
        <div class="fake-title-area divided-nav">
          <ul class="tabs">                 
            <li class="active tab"><a href="#fc-all">All Connections <span class="frnd_count"></span></a></li>
            <li class="tab" onclick="tempconnectcallwithoutnoderecently('<?=$wall_user_id?>')"><a href="#fc-recent">Recently</a></li>
            <li class="tab" onclick="tempconnectcallwithoutnodebirthday('<?=$wall_user_id?>')"><a href="#fc-bday">Birthday</a></li>
          </ul>
        </div>
        <?php if($guserid == $suserid){ ?>
            <div class="connections-search">
              <div id="tabSearch" class="fsearch-form closable-search">
                <input type="text" placeholder="Search for your connections" id="connect_search"/>
                <a href="javascript:void(0)" class="gray-text-555"><i class="zmdi zmdi-search"></i></a>
              </div>
              <div class="dropdown dropdown-custom dropdown-connectlock no-sword setDropVal">
                <a href="javascript:void(0)" class="dropdown_text dropdown-button" data-activates="connect_lock" >
                    <span class="sword"><?=$my_connect_view_status?></span>
                    <i class="zmdi zmdi-caret-down"></i>
                </a>
               <ul id="connect_lock" class="dropdown-content custom_dropdown">
                <li class="dmenu-title">Who can see my connections list?</li>
                <li class="divider"></li>
                <li class="post-private" data-val='private'><a href="javascript:void(0)"><span class="sword">Private</span></a></li>
                <li class="post-connections" data-val='connections'><a href="javascript:void(0)"><span class="sword">Connections</span></a></li>
                <li class="post-public" data-val='public'><a href="javascript:void(0)"><span class="glyphicon glyphicon-globe"></span><span class="sword">Public</span></a></li>   
                <input id="post_privacy" type="hidden" value="<?= $my_connect_view_status ?>" name="post_privacy">
                </ul>
              </div>
            </div>      
        <?php } ?>
        <div class="tab-content">
            <div class="tab-pane fade in active  fc-all" id="fc-all">
                <div class="connections-grid person-list">
                    <div class="row connect_search">
                        <?php 
                            $this->getUserGridLayout($user_connections, 'wall', $wall_user_id);
                        ?>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade fc-recent" id="fc-recent">
                <div class="connections-grid person-list">
                    <div class="row">
                    </div>
                </div>
            </div>
              
           <div class="tab-pane fade" id="fc-bday">
            <div class="connections-grid person-list">
              <div class="row">
              </div>
              
            </div>
          
          </div>
         
            </div>
          
          </div>
        </div>
      </div>
      <?php } else {
        return '<span class="no-listcontent">'.$walluserdetail['fname'].' has set connect list as private</span>';
      } ?>
   
  </div>  

  <?php
    }
    }
    else {
        return 'checkuserauthclassg';
    }
}    

public function actionConnectContentRecently() { 
    $session = Yii::$app->session;
    $email = $session->get('email'); 
    $suserid = $user_id = (string) $session->get('user_id');  
    $guserid = $wall_user_id = (string) $_POST['id'];
    $result_security = SecuritySetting::find()->select(['_id','connect_list'])->where(['user_id' => $guserid])->asarray()->one();
    if (isset($result_security['connect_list']) && !empty($result_security['connect_list'])) {
        $my_connect_view_status = $result_security['connect_list'];
    } else {
        $my_connect_view_status = 'Public';
    } 
    $is_connect = Connect::find()->select(['_id'])->where(['from_id' => (string)$guserid,'to_id' => (string)$suserid,'status' => '1'])->asarray()->one();

    $add_connect_time =  strtotime("-1 month");
    if($my_connect_view_status == 'Private' && ($is_connect || $guserid != $suserid))  {
        $mutualIds = Connect::getMutualConnectIds($wall_user_id);
        $user_recent_connections =  Connect::find()->with('userdata')->Where(['status'=>'1'])->andwhere(['updated_date'=> ['$gte'=>$add_connect_time]])->andwhere(['in', 'from_id', $mutualIds])->andWhere(['to_id'=> "$wall_user_id"])->all();
    } else {
        $user_recent_connections =  Connect::find()->with('userdata')->Where(['status'=>'1'])->andwhere(['updated_date'=> ['$gte'=>$add_connect_time]])->andWhere(['to_id'=> "$wall_user_id"])->all();
    }
    
    $this->getUserGridLayout($user_recent_connections, 'wall', 'recently');
}

public function actionConnectContentBirthday() { 
    $session = Yii::$app->session;
    $email = $session->get('email'); 
    $user_id = (string) $session->get('user_id');  
    $wall_user_id = (string) $_POST['id'];
    $user_birth_connections =  Connect::find()->with('userdata')->Where(['status'=>'1'])->andWhere(['to_id'=> "$wall_user_id"])->asarray()->all();

    $this->getUserGridLayout($user_birth_connections, 'wall', 'birthdate');
}

public function actionPhotosContent() {
    $session = Yii::$app->session;
    $request = Yii::$app->request;
    $email = $session->get('email'); 
    $suserid = $user_id = (string) $session->get('user_id');  
    
    if(strstr($_SERVER['HTTP_REFERER'],'r=page')) {
        $type = 'page';
    } else { 
        $type = 'wall';
    }
    
    if(isset($suserid) && $suserid != '' ) {
        $authstatus = UserForm::isUserExistByUid($suserid);
        if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
            return $authstatus;
        } else {

            $guserid = $wall_user_id = (string) $_POST['id'];
            $photosallow = 'denyPhotos';
            $page_name = '';
            $isWall = true;
            if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
            {
                $url = $_SERVER['HTTP_REFERER'];
                $urls = explode('&',$url);
                $url = explode('=',$urls[1]);
                $page_id = Page::Pagedetails($url[1]);

                $wall_user_id = $page_id['created_by'];
                if($user_id == $wall_user_id) {
                    $user_id = $wall_user_id = $url[1];
                } else {
                    $wall_user_id = $url[1];
                }

                //$wall_user_id = $page_id['created_by'];
                $photosallow = $page_id['gen_photos'];
                $page_name =$page_id['page_name'];
                $isWall = false;
            }

            $baseUrl = (string) $_POST['baseUrl'];
            $albums = UserPhotos::getAlbums($wall_user_id); 
              
            $total_albums = count($albums);
            $profile_albums = UserPhotos::getProfilePics($wall_user_id);
            $total_profile_albums = count($profile_albums);
            if($total_profile_albums>0){$total_profile_album=1;}else{$total_profile_album=0;}
            $cover_albums = UserPhotos::getCoverPics($wall_user_id);
            $total_cover_albums = count($cover_albums);
            if($total_cover_albums>0){$total_cover_album=1;}else{$total_cover_album=0;}
            $totalpics = PostForm::getPics($wall_user_id);
            $totalpics2 = UserPhotos::getPics($wall_user_id);
            $totalpictures = $totalpics+$totalpics2+$total_profile_albums+$total_cover_albums;
            $totalcounts = $total_albums;
            $totalalbums = UserPhotos::getAlbumCounts($wall_user_id);
            $result = LoginForm::find()->where(['_id' => $wall_user_id])->one();
            $fullname = $result['fname'].' '.$result['lname'];
            if($isWall) {
                $page_name = $fullname;
            }
            $profile_picture_image = $this->getimage($result['_id'],'thumb');
            if(isset($result['cover_photo']) && !empty($result['cover_photo'])) {
              $cover_picture_image = "uploads/cover/".$result['cover_photo'];
            } else {
                $assetsPath = '../../vendor/bower/travel/images/';
                $cover_picture_image = $assetsPath.'cover.jpg';
            }
            $gallery = UserPhotos::getUserPhotos($wall_user_id);
            $gallery_img = $this->getimage($user_id,'photo'); 
            $my_connect_view_status = 'Public';
            $is_connect = Connect::find()->select(['_id'])->where(['from_id' => "$user_id",'to_id' => "$wall_user_id",'status' => '1'])->one();
            ?>
            <div class="combined-column">
                <div class="fake-title-area divided-nav callfiximageui" data-id="recount">
                  <ul class="tabs callfiximageui" id="photosareatab"> 
                    <li class="album-tab tab callfiximageui" onclick="albumscontentsplit()"><a href="#pc-albums">Albums <span class="getAgainAlbumscount"></span></a></li>
                    <li class="tab callfiximageui" onclick="photoscontentsplit()"><a href="#pc-photos">Photos <span class="photos_count getAgainPhotoscount">(<?=$totalpictures?>)</span></a></li>
                  </ul>               
                </div>
                <div class="content-box bshadow">
                  <div class="cbox-desc">
                    <div class="tab-content">
                        <div class="tab-pane in active" id="pc-albums">
                            <div class="albums-area">
                                <?php if($totalcounts > 0) { ?>
                                    <div class="section-title">
                                        <span><?=$page_name?></span>&nbsp;
                                        <i class="mdi mdi-chevron-right"></i>
                                        &nbsp;<span>Albums</span><span class="getAgainAlbumscount"></span>
                                    </div>
                                <?php } ?>
                                <div class="albums-grid images-container images-container4">
                                    <div class="row newalbumviewalbums">
                                        <div class="cus-gallery">
                                            <?php if(($user_id == $wall_user_id) || ($photosallow == 'allowPhotos')){ ?>
                                                <div class="grid-box">
                                                    <div class="">
                                                        <div class="album-box">
                                                            <a href="javascript:void(0)" class="add-album addAlbumContent">
                                                                <span class="icont">+</span>
                                                                Add New Album
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <div class="lgt-gallery-photo">
                                            <?php 
                                                if($total_albums>0) {
                                                    $isWriteAllow = 'no';      
                                                    $result_security = SecuritySetting::find()->select(['view_photos'])->where(['user_id' => (string)$guserid])->asarray()->one();
                                                    if(isset($result_security['view_photos']) && !empty($result_security['view_photos'])) {
                                                        $wall_owner_privacy = $result_security['view_photos'];
                                                    } else {
                                                        $wall_owner_privacy = 'Public';
                                                    }

                                                    if($user_id == $wall_user_id) { 
                                                        $isWriteAllow = 'yes';
                                                    } else if($wall_owner_privacy == 'Public' || ($wall_owner_privacy == 'Connections' && ($is_connect)) || ($wall_owner_privacy == 'Private')) {
                                                        $isWriteAllow = 'yes';
                                                    } else if($wall_owner_privacy == 'Custom') {
                                                        $customids = array();
                                                        $customids = $result_security['view_photos_custom'];
                                                        $customids = explode(',', $customids);

                                                        if(in_array($user_id, $customids)) {
                                                            $isWriteAllow = 'yes';          
                                                        }
                                                    }

                                                    if($isWriteAllow == 'yes') {
                                                        foreach($albums as $album) {
                                                            $againisWriteAllow = 'no';

                                                            $rand = rand(999, 99999);
                                                            if(isset($album['image']) && !empty($album['image'])) {
                                                                $eximgs = explode(',',$album['image'],-1);
                                                                $totalimages = count($eximgs);
                                                                $my_post_view_status = $album['post_privacy'];
                                                                $post_user_id = $album['post_user_id'];

                                                                $is_connectcurrent = Connect::find()->select(['_id'])->where(['from_id' => "$user_id",'to_id' => "$post_user_id",'status' => '1'])->one();

                                                                if($user_id == $post_user_id) { 
                                                                    $againisWriteAllow = 'yes';
                                                                } else if($my_post_view_status == 'Public' || ($my_post_view_status == 'Connections' && ($is_connectcurrent)) || ($my_post_view_status == 'Private')) {
                                                                    $againisWriteAllow = 'yes';
                                                                } else if($my_post_view_status == 'Custom') {
                                                                    $customids = array();
                                                                    $customids = $album['customids'];
                                                                    $customids = explode(',', $customids);

                                                                    if(in_array($user_id, $customids)) {
                                                                        $againisWriteAllow = 'yes';          
                                                                    }
                                                                }

                                                                if($againisWriteAllow == 'yes') {
                                                                if ($my_post_view_status == 'Private') {
                                                                    $post_dropdown_class = 'lock';
                                                                } else if ($my_post_view_status == 'Connections') {
                                                                    $post_dropdown_class = 'account';
                                                                } else if ($my_post_view_status == 'Custom') {
                                                                    $post_dropdown_class = 'settings';
                                                                } else {
                                                                    $my_post_view_status = 'Public';
                                                                    $post_dropdown_class = 'earth';
                                                                }

                                                                $picsize = '';
                                                                $imgclass = '';
                                                                $iname = '';
                                                                $inameclass = '';
                                                                $pinval = '';

                                                                if(file_exists('../web'.$eximgs[0])) {

                                                                    $val = getimagesize('../web'.$eximgs[0]);
                                                                    $iname = $this->getimagename($eximgs[0]);
                                                                    $inameclass = $this->getimagefilename($eximgs[0]);
                                                                    $picsize .= $val[0] .'x'. $val[1] .', ';
                                                                    $pinit = PinImage::find()->select(['_id'])->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
                                                                    if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}                        
                                                                    if($val[0] > $val[1]) { 
                                                                        $imgclass = 'himg'; 
                                                                    } else if($val[1] > $val[0]) { 
                                                                        $imgclass = 'vimg';
                                                                    } else if($val[1] == $val[0]) { 
                                                                        $imgclass = 'vimg';
                                                                    } else {
                                                                        $imgclass = 'himg';
                                                                    } 
                                                                    ?>
<div class="grid-box countgrid-box albumphotobox<?=(string)$album['_id'];?>" data-src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?=$eximgs[0]?>">
    <div class="">
        <div class="album-box">
            <div class="imgholder <?= $imgclass?>-box">
                <a href="javascript:void(0)" class="<?= $imgclass?>-box">
                    <img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?=$eximgs[0]?>" class="<?=$imgclass?>" onclick="openAlbumImagesTab(this,'<?=$album['_id']?>','<?=$album['album_title']?>')"/>
                </a>
                <?php if($user_id == $wall_user_id){ ?>
                <div class="edit-link">
                    <div class="dropdown dropdown-custom dropdown-xxsmall no-sword setDropVal">
                        <a href="javascript:void(0)" class="dropdown-button more_btn" data-activates="xsdca<?=$rand?>">
                            <i class="zmdi zmdi-edit zmdi-hc-fw"></i>
                        </a>
                        <ul id="xsdca<?=$rand?>" class="dropdown-content custom_dropdown">
                            <li><a href="#edit-album-popup" onclick="editAlbum('<?=$album['_id']?>')" class="popup-modal">Edit</a></li>
                            <li><a href="javascript:void(0)" onclick="deleteAlbum('<?=$album['_id']?>')">Delete</a></li>
                        </ul>
                    </div>
                </div>  
                <?php } ?>
            </div>
            <div class="descholder">
                <a href="javascript:void(0)" class="namelink" onclick="openAlbumImagesTab(this,'<?=$album['_id']?>','<?=$album['album_title']?>')"><span><?=$album['album_title']?></span></a>
                <span class="info" onclick="openAlbumImagesTab(this,'<?=$album['_id']?>','<?=$album['album_title']?>')"><?= $totalimages?> Photos</span>
                <?php if($user_id == $wall_user_id){ ?>
                <div class="dropdown dropdown-custom no-sword setDropVal">
                    
                    <a class="dropdown-button more_btn ksjdsikosadsa reaxsdca<?=$rand?>" href="javascript:void(0);" data-activates="reaxsdca<?=$rand?>" data-fetch="yes" data-modeltag="reaxsdca<?=$rand?>" data-label="photoalbums" data-boxid="<?=(string)$album['_id'];?>"><i class="mdi mdi-<?=$post_dropdown_class?> mdi-16px"></i></a>  

                    <ul id="reaxsdca<?=$rand?>" class="dropdown-content custom_dropdown">
                        <li class="album-private" data-album="<?=(string)$album['_id']?>"><a href="javascript:void(0)"><span class="glyphicon glyphicon-lock"></span><span class="sword">Private</span></a></li>
                        <li class="album-connections" data-album="<?=(string)$album['_id']?>"><a href="javascript:void(0)"><span class="glyphicon glyphicon-user"></span><span class="sword">Connections</span></a></li>
                        <li class="album-custom customli_modal" data-album="<?=(string)$album['_id']?>"><a href="javascript:void(0)"><span class="glyphicon glyphicon-globe"></span><span class="sword">Custom</span></a></li>
                        <li class="album-public" data-album="<?=(string)$album['_id']?>"><a href="javascript:void(0)"><span class="glyphicon glyphicon-globe"></span><span class="sword">Public</span></a></li>
                    </ul>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
                                                            <?php 
                                                            }
                                                        }
                                                    } 
                                                } 
                                            } 
                                        } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>    

                        <div class="photos-area" data-id="recount">
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pc-photos">        
                        <div class="photos-area">
                        </div>
                    </div>
                </div>
              </div>
            </div>
        <?php
        } 
    } else {
        return 'checkuserauthclassg';
    } 
}

public function actionAlbumsPhotosContent() {
    $session = Yii::$app->session;
    $suserid = $user_id = (string) $session->get('user_id');  
    
    if(isset($suserid) && $suserid != '' ) {
        $authstatus = UserForm::isUserExistByUid($suserid);
        if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
            return $authstatus;
        } else {

            $guserid = $wall_user_id = (string) $_POST['id'];
            $photosallow = 'denyPhotos';
            $page_name = '';
            $isWall = true;
            if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
            {
                $url = $_SERVER['HTTP_REFERER'];
                $urls = explode('&',$url);
                $url = explode('=',$urls[1]);
                $page_id = Page::Pagedetails($url[1]);

                $wall_user_id = $page_id['created_by'];
                if($user_id == $wall_user_id) {
                    $user_id = $wall_user_id = $url[1];
                } else {
                    $wall_user_id = $url[1];
                }

                //$wall_user_id = $page_id['created_by'];
                $photosallow = $page_id['gen_photos'];
                $page_name =$page_id['page_name'];
                $isWall = false;
            }

            $result = LoginForm::find()->where(['_id' => $wall_user_id])->one();
            $fullname = $result['fname'].' '.$result['lname'];
            if($isWall) {
                $page_name = $fullname;
            }


            $albums = UserPhotos::getAlbums($wall_user_id);
            
            $total_albums = count($albums);
            $totalcounts = $total_albums;
            
            $is_connect = Connect::find()->select(['_id'])->where(['from_id' => "$user_id",'to_id' => "$wall_user_id",'status' => '1'])->one();
            ?>
            <div class="albums-area">
                <?php if($totalcounts > 0) { ?>
                    <div class="section-title">
                        <span><?= $page_name?></span>
                        <i class="mdi mdi-chevron-right"></i>
                        <span>Albums</span><span class="getAgainAlbumscount"></span>
                    </div>
                <?php } ?>
                <div class="albums-grid images-container images-container4">
                    <div class="row newalbumviewalbums">
                        <div class="cus-gallery">
                            <?php if(($user_id == $wall_user_id) || ($photosallow == 'allowPhotos')){ ?>
                                <div class="grid-box">
                                    <div class="">
                                        <div class="album-box">
                                            <a href="javascript:void(0)" class="add-album addAlbumContent">
                                                <span class="icont">+</span>
                                                Add New Album
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php 
                            if($total_albums>0) {
                                $isWriteAllow = 'no';
                                $result_security = SecuritySetting::find()->select(['view_photos'])->where(['user_id' => (string)$guserid])->asarray()->one();
                                if(isset($result_security['view_photos']) && !empty($result_security['view_photos'])) {
                                    $wall_owner_privacy = $result_security['view_photos'];
                                } else {
                                   $wall_owner_privacy = 'Public';
                                }

                                    if($user_id == $wall_user_id) { 
                                        $isWriteAllow = 'yes';
                                    } else if($wall_owner_privacy == 'Public' || ($wall_owner_privacy == 'Connections' && ($is_connect)) || ($wall_owner_privacy == 'Private')) {
                                        $isWriteAllow = 'yes';
                                    } else if($wall_owner_privacy == 'Custom') {
                                        $customids = array();
                                        $customids = $result_security['view_photos_custom'];
                                        $customids = explode(',', $customids);

                                        if(in_array($user_id, $customids)) {
                                            $isWriteAllow = 'yes';          
                                        }
                                    }                                    

                                    if($isWriteAllow == 'yes') {
                                        foreach($albums as $album) {
                                            $rand = rand(999, 99999);
                                            if(isset($album['image']) && !empty($album['image'])) {
                                                $eximgs = explode(',',$album['image'],-1);
                                                $totalimages = count($eximgs);
                                                $my_post_view_status = $album['post_privacy'];
                                                $post_user_id = $album['post_user_id'];
                                                $isWriteAllowagain = 'no';

                                                $is_connectagain = Connect::find()->select(['_id'])->where(['from_id' => "$user_id",'to_id' => "$wall_user_id",'status' => '1'])->one();

                                                if($user_id == $post_user_id) { 
                                                    $isWriteAllowagain = 'yes';
                                                } else if($my_post_view_status == 'Public' || ($my_post_view_status == 'Connections' && ($is_connectagain)) || ($my_post_view_status == 'Private')) {
                                                    $isWriteAllowagain = 'yes';
                                                } else if($my_post_view_status == 'Custom') {
                                                    $customids = array();
                                                    $customids = $album['view_photos_custom'];
                                                    $customids = explode(',', $customids);

                                                    if(in_array($user_id, $customids)) {
                                                        $isWriteAllowagain = 'yes';          
                                                    }
                                                }

                                                if($isWriteAllowagain == 'yes') {

                                                if ($my_post_view_status == 'Private') {
                                                    $post_dropdown_class = 'lock';
                                                } else if ($my_post_view_status == 'Connections') {
                                                    $post_dropdown_class = 'account';
                                                } else if ($my_post_view_status == 'Custom') {
                                                    $post_dropdown_class = 'settings';
                                                } else {
                                                    $my_post_view_status = 'Public';
                                                    $post_dropdown_class = 'earth';
                                                }
                                                $picsize = '';
                                                $imgclass = '';
                                                $iname = '';
                                                $inameclass = '';
                                                $pinval = '';

                                                if(file_exists('../web'.$eximgs[0])) {

                                                    $val = getimagesize('../web'.$eximgs[0]);
                                                    $iname = $this->getimagename($eximgs[0]);
                                                    $inameclass = $this->getimagefilename($eximgs[0]);
                                                    $picsize .= $val[0] .'x'. $val[1] .', ';
                                                    $pinit = PinImage::find()->select(['_id'])->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
                                                    if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}                        
                                                    if($val[0] > $val[1]) { 
                                                        $imgclass = 'himg'; 
                                                    } else if($val[1] > $val[0]) { 
                                                        $imgclass = 'vimg';
                                                    } else if($val[1] == $val[0]) { 
                                                        $imgclass = 'vimg';
                                                    } else {
                                                        $imgclass = 'himg';
                                                    }
                                                    ?>
                                                    <div class="grid-box countgrid-box albumphotobox<?=(string)$album['_id'];?>">
                                                        <div class="">
                                                            <div class="album-box">
                                                                <div class="imgholder <?= $imgclass?>-box">
                                                                        <img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?=$eximgs[0]?>" class="<?=$imgclass?>" onclick="openAlbumImagesTab(this,'<?=$album['_id']?>','<?=$album['album_title']?>')"/>
                                                                        <?php if($user_id == $wall_user_id){ ?>
                                                                        <div class="edit-link">
                                                                            <div class="dropdown dropdown-custom dropdown-xxsmall no-sword setDropVal">
                                                                                <a href="javascript:void(0)" class="dropdown-button more_btn" data-activates="xsdca<?=$rand?>">
                                                                                    <i class="zmdi zmdi-edit zmdi-hc-fw"></i>
                                                                                </a>
                                                                                <ul id="xsdca<?=$rand?>" class="dropdown-content custom_dropdown">
                                                                                    <li><a href="#edit-album-popup" onclick="editAlbum('<?=$album['_id']?>')" class="popup-modal">Edit album</a></li>
                                                                                    <li><a href="javascript:void(0)" onclick="deleteAlbum('<?=$album['_id']?>')">Delete this album</a></li>
                                                                                </ul>
                                                                            </div>
                                                                        </div>  
                                                                        <?php } ?>
                                                                </div>
                                                                <div class="descholder">
                                                                    <a href="javascript:void(0)" class="namelink" onclick="openAlbumImagesTab(this,'<?=$album['_id']?>','<?=$album['album_title']?>')"><span><?=$album['album_title']?></span></a>
                                                                    <span class="info" onclick="openAlbumImagesTab(this,'<?=$album['_id']?>','<?=$album['album_title']?>')"><?= $totalimages?> Photos</span>
                                                                    <?php if($user_id == $wall_user_id){ ?>
                                                                    <div class="dropdown dropdown-custom no-sword setDropVal">
                                                                        <a class="dropdown-button more_btn ksjdsikosadsa reaxsdca<?=$rand?>" href="javascript:void(0);" data-activates="reaxsdca<?=$rand?>" data-fetch="yes" data-modeltag="reaxsdca<?=$rand?>" data-label="photoalbums" data-boxid="<?=(string)$album['_id'];?>"><i class="mdi mdi-<?=$post_dropdown_class?> mdi-16px"></i></a>
                                                                        <ul id="reaxsdca<?=$rand?>" class="dropdown-content custom_dropdown">
                                                                            <li class="album-private" data-album="<?=$album['_id']?>"><a href="javascript:void(0)"><span class="glyphicon glyphicon-lock"></span><span class="sword">Private</span></a></li>
                                                                            <li class="album-connections" data-album="<?=$album['_id']?>"><a href="javascript:void(0)"><span class="glyphicon glyphicon-user"></span><span class="sword">Connections</span></a></li>
                                                                            <li class="album-custom customli_modal" data-album="<?=(string)$album['_id']?>"><a href="javascript:void(0)"><span class="glyphicon glyphicon-globe"></span><span class="sword">Custom</span></a></li>
                                                                            <li class="album-public" data-album="<?=$album['_id']?>"><a href="javascript:void(0)"><span class="glyphicon glyphicon-globe"></span><span class="sword">Public</span></a></li>
                                                                        </ul>
                                                                    </div>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php 
                                                     
                                                    }
                                                }
                                            } 
                                        } 
                                    } 
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        } 
    } else {
        return 'checkuserauthclassg';
    } 
}

public function actionPhotosContentSplit() {
    $session = Yii::$app->session;
    $request = Yii::$app->request;
    $email = $session->get('email'); 
    $suserid = $user_id = (string) $session->get('user_id');  
    
    if(isset($suserid) && $suserid != '' ) {
        $authstatus = UserForm::isUserExistByUid($suserid);
        if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
            return $authstatus;
        } else {
            $guserid = $wall_user_id = (string) $_POST['id'];
            $photosallow = 'denyPhotos';
            $modulenm = 'wall';
            $isWall = true;
            if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
            {
                $url = $_SERVER['HTTP_REFERER'];
                $urls = explode('&',$url);
                $url = explode('=',$urls[1]);
                $page_id = Page::Pagedetails($url[1]);

                $wall_user_id = $page_id['created_by'];
                if($user_id == $wall_user_id) {
                    $user_id = $wall_user_id = $url[1];
                } else {
                    $wall_user_id = $url[1];
                }

                //$wall_user_id = $page_id['created_by'];
                $photosallow = $page_id['gen_photos'];
                $page_name =$page_id['page_name'];
                $isWall = false;
                $modulenm = 'page';
            }

            $result = LoginForm::find()->where(['_id' => $wall_user_id])->one();
            $fullname = $result['fname'].' '.$result['lname'];
            if($isWall) {
                $page_name = $fullname;
            }


            $albums = UserPhotos::getAlbums($wall_user_id);
            $total_albums = count($albums);
            $totalcounts = $total_albums;
            $gallery = UserPhotos::getUserPhotos($wall_user_id);
            $is_connect = Connect::find()->select(['_id'])->where(['from_id' => "$user_id",'to_id' => "$wall_user_id",'status' => '1'])->one();
            ?>

            <?php if($totalcounts > 0) { ?>
            <div class="section-title">
                <span><?= $page_name?></span>
                <i class="mdi mdi-chevron-right"></i>
                <span>Album Name <span class="sub-number photos_count getAgainPhotoscount"></span></span>
            </div>
            <?php } ?>
            <div class="albums-grid images-container images-container6">
                <div class="row">
                    <?php if(($user_id == $wall_user_id) || ($photosallow == 'allowPhotos')){ ?>
                    <div class="grid-box">
                        <div class="divrel"> 
                            <a href="#add-photo-popup" id="add-photo-photos" class="add-photo popup-modal">
                                <span class="icont">+</span>
                                Add New Photo
                            </a>
                            <input type="file" name="newphotoupld" class="<?=$modulenm?> hidden_uploader hidden_uploaderr custom-upload-new" title="Choose a file to upload" required data-class="#add-photo-popup .post-photos .img-row" multiple/>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="lgt-gallery lgt-gallery-photo dis-none">
                    <?php 
                        if($wall_user_id == $user_id) {
                            $album_privacy = 'Public';
                        } else {
                           $result_security = SecuritySetting::find()->select(['view_photos'])->where(['user_id' => (string)$guserid])->asarray()->one();
                           if(isset($result_security['view_photos']) && !empty($result_security['view_photos']))
                           {
                                $album_privacy = $result_security['view_photos'];
                           } else {
                               $album_privacy = 'Public';
                           }
                        }

                        if($album_privacy == 'Public' || ($album_privacy == 'Connections' && $is_connect)) {
                            foreach($gallery as $gallery_item)
                            {
                                $my_post_view_status = $gallery_item['post_privacy'];
                                if(($my_post_view_status == 'Public') || ($my_post_view_status == 'Connections' && ($is_connect || (string)$guserid == $suserid)) || ($my_post_view_status == 'Private' && (string)$guserid == $suserid)) {
                                $eximgs = explode(',',$gallery_item['image'],-1);
                                foreach ($eximgs as $eximg) {
                                $picsize = '';
                                $imgclass = '';
                                $iname = '';
                                if(file_exists('../web'.$eximg)) {
                                    $val = getimagesize('../web'.$eximg);
                                    $iname = $this->getimagename($eximg);
                                    $inameclass = $this->getimagefilename($eximg);
                                    $picsize .= $val[0] .'x'. $val[1] .', ';
                                    $pinit = PinImage::find()->select(['_id'])->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
                                    if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
                                    if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}
                                }
                                if(!(isset($inameclass) && !empty($inameclass)))
                                { 
                                    $inameclass = '';
                                }
                                $rand = rand(9999, 999999).time();
                            ?>
                            <div class="grid-box countgrid-box galleryphotobom<?=(string)$gallery_item['_id']?>" id="albumimg_<?=$iname?>" data-src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-sizes="<?=(string)$gallery_item['_id']?>|||UserPhotos">
                                <div class="photo-box">
                                    <div class="imgholder <?= $imgclass?>-box">
                                        <a href="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-imgid="<?=$inameclass?>" data-pinit="<?=$pinval?>" class="allow-gallery imgpin <?=$imgclass?>-box">
                                          <img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?=$eximg?>" class="<?=$imgclass?>"/>
                                        </a>
                                        <?php if(($user_id == $wall_user_id) && $gallery_item['is_album'] == '1'){ ?>
                                        <div class="edit-link">
                                            <div class="dropdown dropdown-custom dropdown-auto">
                                                <a class="dropdown-button more_btn" href="javascript:void(0);" data-activates="redsssds<?=$rand?>">
                                                    <i class="zmdi zmdi-edit zmdi-hc-fw"></i>
                                                </a>
                                                <ul id="redsssds<?=$rand?>" class="dropdown-content custom_dropdown">
                                                    <li><a href="javascript:void(0)" onclick="moveImage('<?=$eximg?>','<?=$gallery_item['_id']?>')">Move to other album</a></li>
                                                    <li><a href="javascript:void(0)" onclick="albumCover('<?=$user_id?>','<?=$eximg?>','<?=$gallery_item['_id']?>')">Make album cover</a></li>
                                                    <li><a href="javascript:void(0)" onclick="deleteImage('<?=$iname?>','<?=$eximg?>','<?=$gallery_item['_id']?>')">Delete this photo</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    </div>
                                    <?php
                                    $fileinfo = pathinfo($eximg);
                                    $uniq_id = $fileinfo['filename'] .'_'. $gallery_item['_id'];
                                    $like_count = Like::getLikeCount((string)$uniq_id);
                                    $like_names = Like::getLikeUserNames((string)$fileinfo['filename'] .'_'. $gallery_item['_id']);
                                    $like_buddies = Like::getLikeUser((string)$fileinfo['filename'] .'_'. $gallery_item['_id']);
                                    $is_like = Like::find()->select(['_id'])->where(['user_id'=>$user_id,'post_id'=>$uniq_id,'status'=>'1'])->one();
                                    if($is_like){$ls = 'mdi-thumb-up';}else{$ls = 'mdi-thumb-up-outline';}
                                    $newlike_buddies = array();
                                    foreach($like_buddies as $like_buddy)
                                    {
                                        $newlike_buddies[] = ucfirst($like_buddy['user']['fname']). ' '.ucfirst($like_buddy['user']['lname']);
                                    }
                                    $newlike_buddies = implode('<br/>', $newlike_buddies);
                                    ?>
                                    <div class="descholder">
                                        <?php if($gallery_item['is_album'] == '1'){ ?>
                                        <a href="javascript:void(0)" class="namelink"><span><?=$gallery_item['album_title']?></span></a>
                                        <?php } ?>
                                        <div class="options prevent-gallery">
                                            <a href="javascript:void(0)" onclick="doLikeAlbumbPhotos('<?=$uniq_id?>');">
                                                <i class="mdi mdi-16px <?=$ls?> ls_<?=$uniq_id?>"></i>
                                            </a>

                                            <div class="info">                                  
                                                <a href="javascript:void(0)" data-id='photo-1' data-section='photos' class="custom-tooltip pa-like liveliketooltip liketitle_<?=$uniq_id?>" onclick="doLikeAlbumbPhotos('<?=$uniq_id?>');" data-title="<?=$newlike_buddies?>">
                                                </a>
                                                <span class="glyphicon glyphicon-thumbs-up likecount_<?=$uniq_id?>">
                                                <?php if($like_count >0 ) { ?><?=$like_count?><?php } ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                }   
                            }
                        } 
                    } ?>
                </div>
            </div>
            </div>
        <?php
        } 
    } else {
        return 'checkuserauthclassg';
    } 
}
public function actionEditalbum() {
	if (isset($_POST['album_id']) && !empty($_POST['album_id'])) {
        $albumid = $_POST['album_id'];
        return $this->render('edit_album',array('album_id'=> $albumid));
    }
}

public function actionEditalbumdetails()
{
    $album_id = isset($_POST['album_id']) ? $_POST['album_id'] : '';
    $data = array();
    if($album_id != '')
    {
        $editalbum = UserPhotos::find()->select(['_id'])->where(['_id' => $album_id])->one();
        if($editalbum)
        {
            $editalbum->album_title = $_POST['edit_title'];
            $editalbum->post_text = $_POST['edit_desc'];
            $editalbum->album_place = $_POST['edit_place'];
            if($editalbum->update())
            {
                $data['value'] = '1';
                return json_encode($data);
            }
        }
        else
        {
            $data['value'] = '0';
            return json_encode($data);
        }
    }
    else
    {
        $data['value'] = '0';
        return json_encode($data);
    }
}

public function actionMoveimagedisplay() {
    $data = array();
    $iname = $_POST['iname'];
    $album_id = $_POST['album_id'];
    if (isset($iname) && !empty($iname) && isset($album_id) && !empty($album_id))
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
          
        $assetsPath = '../../vendor/bower/travel/images/';
        if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
        {
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $userid = $url[1];
        }
        $getalbumdetails = UserPhotos::find()->select(['_id'])->where(['_id' => $album_id,'post_user_id' => "$userid",'is_deleted' => '0','is_album' => '1'])->one();
        if ($getalbumdetails)
        {
            $getalbums = UserPhotos::find()->where(['post_user_id' => "$userid",'is_deleted' => '0','is_album' => '1'])->all();
        ?>
        <div class="modal_content_container">
            <div class="modal_content_child modal-content">
                <div class="popup-title ">
                    <button class="hidden_close_span close_span">
                        <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
                    </button>           
                    <h3>Move photo to other album</h3>
                    <a type="button" class="item_done crop_done waves-effect hidden_close_span custom_close" href="javascript:void(0)" >Done</a>
                </div>

                <div class="custom_modal_content modal_content" id="createpopup">
                    <div class="ablum-yours profile-tab">
                        <div class="ablum-box detail-box">                                                          
                            <div class="content-holder main-holder">
                                <div class="summery">                                                                   
                                    <div class="dsection bborder expandable-holder expanded">   
                                        <div class="form-area expandable-area">
                                            <form class="ablum-form">
                                                <div class="form-box">
                                                    <div class="fulldiv">
                                                        <div class="half">
                                                            <div class="frow">
                                                                <div class="caption-holder">
                                                                    <label>Select album to move this photo.</label>
                                                                </div>
                                                                <div class="detail-holder">
                                                                    <div class="input-field">
                                                                        <select id="toalbum">
                                                                            <?php foreach ($getalbums as $getalbum)
                                                                            { if($album_id != $getalbum['_id']){ ?>
                                                                                <option value="<?=$getalbum['_id']?>"><?=$getalbum['album_title']?></option>
                                                                            <?php } } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="valign-wrapper additem_modal_footer modal-footer">     
            <a href="javascript:void(0)" class="btngen-center-align close_modal open_discard_modal">Cancel</a>
            <a href="javascript:void(0)" class="btngen-center-align" onclick="moveAlbumImage('<?=$iname?>','<?=$album_id?>')">Move</a>
        </div>

<?php
    }
    }	
}

public function actionAddimages()
{

    
    $pid = $_POST['add_image_id'];
    $update = new UserPhotos();
    $update = UserPhotos::find()->where(['_id' => $pid,'is_deleted' => '0','is_album' => '1'])->one();
    $isNewAlbum =  true;
    $page_id = '';
    // check for pages
    if(!empty($update)) {
        if(isset($update['pagepost']) && $update['pagepost'] == '1') {
            $page_id = $update['post_user_id'];

            $getPageData = Page::find()->where([(string)'_id' => $page_id])->asarray()->one();
            if(!empty($getPageData)) {
                $gen_photos = isset($getPageData['gen_photos']) ? $getPageData['gen_photos'] : '';
                $gen_photos_review = isset($getPageData['gen_photos_review']) ? $getPageData['gen_photos_review'] : '';
                if($gen_photos == 'allowPhotos' && $gen_photos_review == 'on') {
                    $update = new PageReviewPhotos();                    
                    $isNewAlbum =  false;
                }
            }
        }

        $date = time();
        if (isset($_FILES) && !empty($_FILES))
        { 
            $imgcount = count($_FILES["imageFile1"]["name"]);
            $img = '';
            $im =  '';
            for ($i = 0;$i < $imgcount;$i++)
            {
                $name = $_FILES["imageFile1"]["name"][$i];
                $tmp_name = $_FILES["imageFile1"]["tmp_name"][$i];
                if (isset($name) && $name != "")
                {
                    $url = '../web/uploads/';
                    $urls = '/uploads/';
                    $image_extn = explode('.',$_FILES["imageFile1"]["name"][$i]);
$image_extn = end($image_extn);
                    $rand = rand(111,999);
                    $img = $urls.$date.$rand.'.'.$image_extn.',';
                    move_uploaded_file($_FILES["imageFile1"]["tmp_name"][$i], $url.$date.$rand.'.'.$image_extn);
                    $im = $im . $img;
                } 
            }

            if($isNewAlbum) {
                $image = $update['image'];
                $im = $image . $im;
                $update->image = $im;
                $update->update();
                return true;
            } else {
                $update->isnewalbum = false;
                $update->page_id = $page_id;
                $update->album_id = $pid;
                $update->image = $im;
                $update->insert();
                return 'isreview';
            }
        }
    }
}

public function actionLikesContent()
{
    $session = Yii::$app->session;
    $request = Yii::$app->request;
    $email = $session->get('email'); 
    $user_id = (string)$session->get('user_id');  
    $wall_user_id = (string)$_POST['id'];
    $baseUrl = (string) $_POST['baseUrl'];
    $allpageslikes = Page::getAllLikesPages($wall_user_id);
    ?>
    <div class="combined-column">
		<?php if(count($allpageslikes) > 0){?>
			<div class="grid-view">
				<div class="pages-list generalbox-list liked-list" id="dencity">
						<div class="row">
						<?php foreach($allpageslikes as $allpagelike){
						$pageid = (string)$allpagelike['post_id'];
						$pagelink = Url::to(['page/index', 'id' => "$pageid"]);
						$like_count = Like::getLikeCount($pageid);
						$like_names = Like::getLikeUserNames($pageid);
						$like_buddies = Like::getLikeUser($pageid);
						$newlike_buddies = array();
						$start = 0;
						foreach($like_buddies as $like_buddy) {
							if($start < 3) {
								$lid = $like_buddy['user']['_id'];
								$id = Url::to(['userwall/index', 'id' => "$lid"]);
								if($user_id == (string)$lid) {
									$name = 'You';
								} else {
									$name = ucfirst($like_buddy['user']['fname']). ' '.ucfirst($like_buddy['user']['lname']);
								}
								$newlike_buddies[] = "<a href='$id'>".$name."</a>";
							}
							$start++;
						}
						$newlike_buddies = implode(', ', $newlike_buddies);
						$likeexist = Like::getPageLike($pageid);
						if($likeexist){$likestatus = 'Liked';}
						else{$likestatus = 'Like';}
						$pagedetail = Page::find()->where([(string)'_id' => $pageid])->asarray()->one();
						$page_img = $this->getpageimage($pageid);
						$pagelikeids = Page::getpagenameLikes($pageid);
						?>
						<div class="col l6 m6 s12">
							<div href="javascript:void(0);" class="page-box general-box h-pagebox">
								<div class="photo-holder">
									<img src="<?=$page_img?>"/>
								</div>
								<div class="content-holder">
									<h4><a href="<?=$pagelink?>"><?=$pagedetail['page_name']?></a></h4>
									<div class="username">
										<span><?=$pagelikeids?> Likes</span>
									</div>	
									<div class="action-btns">	
									
									<a href="javascript:void(0)">
										<span class="noClick likestatus_<?=$pageid?>" onclick="pageLike(event,'<?=$pageid?>',this)"><?=$likestatus?></span>														
									</a>
									</div>
								</div>
							</div>
					
						</div>
						<?php } ?>
						</div>
				</div>
			</div>
			<?php } else { ?>
			<div class="cbox-desc">
				<div class="content-box bshadow">
					<?php $this->getnolistfound('nolikefound');?>
				</div>
			</div>
			<?php } ?>
    </div>		
    <?php 
}

public function actionReferContent()
{
    $session = Yii::$app->session;
    $request = Yii::$app->request;
    $email = $session->get('email'); 
    $user_id = (string)$session->get('user_id');  
    $wall_user_id = (string)$_POST['id'];
    $baseUrl = (string) $_POST['baseUrl'];
    $refers = ReferForm::getAllReferals($wall_user_id);
    $total_refers = ReferForm::getTotalReferals($wall_user_id);
    ?>
    <div class="combined-column">
            <div class="content-box">
				<div class="cbox-title nborder hidetitle-mbl">
					<i class="zmdi zmdi-thumb-up"></i>
					References<span class="count">98</span>										
					<div class="right-tabs noborder">											
						
						<select class="select2">														
							<option selected>Personal</option>
							<option>Travellers</option>
							<option>Hosts</option>
							<option>Positive</option>
							<option>Negative</option>
						</select>
					</div>	
				</div>
				<div class="cbox-desc">
					<?php if($total_refers > 0) { ?>
						<div class="fake-title-area">
								<div class="table-header">
										<div class="btn-holder"></div>
										<div class="title-holder">Feedback</div>
										<div class="from-holder">From</div>
										<div class="when-holder">When</div>
								</div>
						</div>
						<div class="table-content">
								<div class="trow-holder">
									<?php foreach($refers as $refer){
										$score = $refer['referal_point'];
										if($score == 'Positive'){$ricon = 'positive';$fa = 'plus-box';}
										if($score == 'Neutral'){$ricon = 'neutral';$fa = 'bullseye';}
										if($score == 'Negative'){$ricon = 'negative';$fa = 'close-circle-outline';}
										$now = time();
										$refer_date = strtotime($refer['date']);
										$daydiff = $now - $refer_date;
										$days = floor($daydiff/(60*60*24));
										if($days > '1'){$day = 'on '. $refer['date'];}
										if($days == '1'){$day = 'Yesterday';}
										if($days == '0'){$day = 'Today';}
									?>
										<div class="trow">					
												<div class="btn-holder"><span class="feedback feedback-<?=$ricon?>"><i class="mdi mdi-<?=$fa?>"></i></a></div>
												<div class="title-holder"><?=$refer['referal_text']?></div>
												<div class="from-holder"><?=$refer['user']['fullname']?></div>
												<div class="when-holder"><?=$day?></div>
										</div>
									<?php } ?>
								</div>	
								
						</div>
					<?php } else { ?>
					<?php $this->getnolistfound('norefersfound');?>
					<?php } ?>
				</div>
            </div>
    </div>	
	<?php 
}

public function actionAlbumprivacy()
{
    $albumid = isset($_POST['albumid']) ? $_POST['albumid'] : '';
    $album_value = isset($_POST['albumvalue']) ? $_POST['albumvalue'] : '';
    $data = array();
    if($albumid != '' && $album_value != '' )
    {
        $editalbumprivacy = UserPhotos::find()->select(['_id'])->where(['_id' => $albumid])->one();
        if($editalbumprivacy)
        {
            $editalbumprivacy->post_privacy = $album_value;
            if($editalbumprivacy->update())
            {
                $data['value'] = '1';
                return json_encode($data);
            }
        }
        else
        {
            $data['value'] = '0';
            return json_encode($data);
        }
    }
    else
    {
        $data['value'] = '0';
        return json_encode($data);
    }
}

public function actionAddSliderCover()
{
	$session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');  
	if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
    {
		$url = $_SERVER['HTTP_REFERER'];
		$urls = explode('&',$url);
		$url = explode('=',$urls[1]);
		$page_id = $url[1];
		$type = 'page';
	}
	else
	{
		$type = 'wall';
	}	
    $image_name = isset($_POST['image_name']) ? $_POST['image_name'] : '';
    $image_path = isset($_POST['image_path']) ? $_POST['image_path'] : '';
	$image_path = ltrim($image_path, '/');
    $image_id = isset($_POST['image_id']) ? $_POST['image_id'] : '';
    $data = array();
   
	$record = SliderCover::find()->select(['_id'])->where(['user_id' => $user_id,'image_name'=> $image_name])->one();
	if(!$record)
	{
		$record = new SliderCover();
		$record->user_id = $user_id;
		$record->image_name = $image_name;
		$record->image_path = $image_path;
		$record->image_id = $image_id;
		if(isset($page_id) && !empty($page_id))
		{
			$record->pageid = $page_id;
		}
		$record->type = $type;
		if($record->insert())
		{
			return '1';
		}
		else
		{
			return '0';
		}		
	}
	else
	{
		return 'exist';
	}
}

public function actionAddSliderCoverAlbum()
{
	$session = Yii::$app->session;
    $user_id = (string)$session->get('user_id'); 
	if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
	{
		$url = $_SERVER['HTTP_REFERER'];
		$urls = explode('&',$url);
		$url = explode('=',$urls[1]);
		$page_id = $url[1];
		$type = 'page';
	}
	else
	{
		$type = 'wall';
	}			
	if(isset($user_id) && $user_id !='') {
		if(isset($_POST['$AlbumId']) && $_POST['$AlbumId'] !='') {
			$AlbumId = $_POST['$AlbumId'];
			$AlbumIdImages = UserPhotos::find()->select(['image'])->where([(string)'_id'=> $AlbumId])->asarray()->one();
			if(!empty($AlbumIdImages)) {
				$AlbumIdImages = explode(",", $AlbumIdImages['image']);
				
				if(!empty($AlbumIdImages)) {
					foreach($AlbumIdImages as $SingleAlbumIdImage) {
						if($SingleAlbumIdImage != '') {
							
							$SingleAlbumIdImage = explode("/", $SingleAlbumIdImage);
							
							$SingleAlbumIdImage = end($SingleAlbumIdImage);
							$record = SliderCover::find()->where(['user_id' => $user_id,'image_name'=> $SingleAlbumIdImage])->one();
							if(!$record)
							{
								$record = new SliderCover();
								$record->user_id = $user_id;
								$record->image_name = $SingleAlbumIdImage;
								$record->image_path = 'uploads/'.$SingleAlbumIdImage;
								$record->image_id = $AlbumId;
								$record->type = $type;
								if(isset($page_id) && !empty($page_id))
								{
									$record->pageid = $page_id;
								}
								$record->insert();
							}
						}
					}
				}
			}			
		}        
	}
	return true;
	exit;
}

public function actionRemoveSliderCoverAlbum()
{
	$session = Yii::$app->session; 
    $user_id = (string)$session->get('user_id');
	if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
	{
		$type = 'page';
	}
	else
	{
		$type = 'wall';
	}	
	
	if(isset($user_id) && $user_id !='') {
		if(isset($_POST['$AlbumId']) && $_POST['$AlbumId'] !='') {
			$AlbumId = $_POST['$AlbumId'];
			if(isset($AlbumId) && $AlbumId != '') {
				SliderCover::deleteAll(['image_id' => $AlbumId, 'user_id' => $user_id, 'type' => $type]);
				return true;
				exit;
			}
		}			
	}        	
	return false;
	exit;
}

public function actionRemoveSliderCover()
{
	$session = Yii::$app->session; 
    $user_id = (string)$session->get('user_id');
	if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
	{
		$type = 'page';
	}
	else
	{
		$type = 'wall';
	}
	if(isset($user_id) && $user_id !='') {
		if(isset($_POST['image_name']) && $_POST['image_name'] !='') {
			$image_name = $_POST['image_name'];
			if(isset($image_name) && $image_name != '') {
				SliderCover::deleteAll(['image_name' => $image_name, 'user_id' => $user_id, 'type' => $type]);
				return true;
				exit;
			}
		}			
	}        	
	return false;
	exit;
}

public function actionLoadSlider()
{
	$session = Yii::$app->session;
    $user_id = (string)$session->get('user_id'); 
	if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
	{
		$type = 'page';
	}
	else
	{
		$type = 'wall';
	}
	
	if(isset($user_id) && $user_id !='') {
		$result = array('status' => false);
		$li = '';
		if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
		{
			$url = $_SERVER['HTTP_REFERER'];
			$urls = explode('&',$url);
			$url = explode('=',$urls[1]);
			$page_id = $url[1];
			$AlbumIdImages = SliderCover::find()->select(['image_path'])->where(['user_id' => $user_id, 'type' => 'page','page_id' => $page_id])->asarray()->all();
		}
		else
		{
			$AlbumIdImages = SliderCover::find()->select(['image_path'])->where(['user_id' => $user_id, 'type' => 'wall'])->asarray()->all();
		}	
		if(!empty($AlbumIdImages)) {
			foreach($AlbumIdImages as $SingleAlbumIdImage) {
				$SingleAlbumIdImage = $SingleAlbumIdImage['image_path'];
				if(file_exists($SingleAlbumIdImage)) {
					$picsize = '';
					$val = getimagesize($SingleAlbumIdImage);
					$picsize .= $val[0] .'x'. $val[1] .', ';
					if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}
					
					$li .='<li class="lslide"><figure><a href="'.$SingleAlbumIdImage.'" data-size="1600x1600" data-med="'.$SingleAlbumIdImage.'" data-med-size="1024x1024" data-author="Folkert Gorter" class="'.$imgclass.'-box"><img src="'.$SingleAlbumIdImage.'" alt="" class="'.$imgclass.'"/></a></figure></li>';
                }
			}
		}
		
		$result = array('status' => true, 'data' => $li);
	}
	return json_encode($result, true);
	exit;
}

public function actionFetchslidercoversettingmenu()
{
	$session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');  
	if(isset($_POST['$AlbumId']) && $_POST['$AlbumId'] !='') {
		$AlbumId = $_POST['$AlbumId'];
		$is_exist = SliderCover::find()->select(['_id'])->where(['image_id' => $AlbumId, 'user_id' => $user_id])->asarray()->one();
		
		$li ='<li><a href="#edit-album-popup" onclick="editAlbum(\''.$AlbumId.'\')" class="popup-modal">Edit album</a>

		</li><li><a href="javascript:void(0)" onclick="deleteAlbum(\''.$AlbumId.'\')">Delete this album</a></li>';
		
		$result = array('status' => true, 'li' => $li);
		return json_encode($result, true);
		exit;
	}
	
	$result = array('status' => false);
	return json_encode($result, true);
	exit;
}

public function actionGetreferalcommentbox()
{
    $session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');  
    $login_dp = $this->getimage($user_id,'photo');

    $url = $_SERVER['HTTP_REFERER'];
    $urls = explode('&',$url);
    $url = explode('=',$urls[1]);
    $wall_user_id = $url[1];

    if(isset($_POST['postid']) && $_POST['postid'] != '') {
        $postid = $_POST['postid'];
        if($wall_user_id == $user_id) {  ?>
            <div class="post-data post_comment_<?=$postid?>">
                <div class="post-comments">                                       
                    <div class="addnew-comment valign-wrapper">                            
                        <div class="img-holder"><a href="javascript:void(0)"><img src="<?= $login_dp;?>"></a></div>
                        <div class="desc-holder">                                   
                            <div class="cmntarea">
                                <textarea data-adaptheight data-adaptheight class="materialize-textarea data-adaptheight" na="<?=$postid?>" id="comment_txt_<?=$postid?>" placeholder="Write a reply..."></textarea>
                            </div>
                        </div>             
                    </div>       
                </div>                      
            </div>
        <?php } 
    }
}

/*Referal Code Start*/
public function actionReferal()
{
    $session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');  
    $wall_user_id = (string)$_POST['id'];
    $baseUrl = (string) $_POST['baseUrl'];
    $referals = Referal::getAllReferals($wall_user_id);

	$total_referal = Referal::getTotalReferals($wall_user_id);
	$lastmonth_referal = Referal::getLastMonthReferals($wall_user_id);
	$crntmonth_referals = Referal::getCrntMonthReferals($wall_user_id);
	$connections = Connect::find()->select(['to_id'])->where(['from_id' => "$user_id", 'status' => '1'])->orderBy(['updated_date'=>SORT_DESC,'rand()' => SORT_DESC,])->limit(6)->offset(0)->all();
	$isvip = Vip::isVIP($wall_user_id);
	$isVerify = Verify::isVerify($wall_user_id);
	
	return $this->render('referal',array('baseUrl'=> $baseUrl,'wall_user_id'=>$wall_user_id,'referals'=>$referals,'total_referal'=>$total_referal, 'lastmonth_referal'=>$lastmonth_referal, 'crntmonth_referals'=>$crntmonth_referals, 'connections'=>$connections, 'isvip'=>$isvip, 'isVerify'=>$isVerify, 'wall_user_id'=>$wall_user_id));
}

public function actionTotalreferal()
{
    $wall_user_id = $_POST['id'];
	return Referal::getTotalReferals($wall_user_id);
}

public function actionAllreferal(){
	$category = $_POST['category'];
	$wall_user_id = $_POST['wall_user_id'];
	if($category != 'All'){
		$referals = Referal::getAllCatReferals($wall_user_id,$category);
	}else{
		$referals = Referal::getAllReferals($wall_user_id);
	}	
	return $this->render('catreferal',array('referals'=>$referals));
}

public function actionReferalupload()
{
    $session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');
	 if(isset($user_id) && $user_id != '') {
	$authstatus = UserForm::isUserExistByUid($user_id);
	if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
		return $authstatus;
	} else {
		
	$url = $_SERVER['HTTP_REFERER'];
	$urls = explode('&',$url);
	$url = explode('=',$urls[1]);
	$wall_user_id = $url[1];
	
	$referal_text = $_POST['referal_text'];
	$recommend = $_POST['recommend'];
	$category = $_POST['category'];
	$date = time();
	$month_year = date("Y-n");
	
	$post = new Referal();
	$post->referal_text = $referal_text;
	$post->recommend = $recommend;
	$post->category = $category;
	$post->user_id = $wall_user_id;
	$post->sender_id = $user_id;
	$post->month_year = $month_year;
	$post->created_date = $date;
	$post->is_deleted = "1";
	if($post->insert())
	{
		$notification =  new Notification();
        $notification->from_connect_id = "$wall_user_id";
        $notification->user_id = "$user_id";
        $notification->notification_type = 'addreferal';
        $notification->is_deleted = '0';
        $notification->status = '1';
        $notification->created_date = "$date";
        $notification->updated_date = "$date";
		$notification->insert();
	}
	$last_insert_id =  $post->_id;
	$this->display_last_referal($last_insert_id);
	}
    } else {
    	return  'checkuserauthclassg';
    }
}

public function actionSendinvitereferal()
{
	$session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');
	$connect_id = (string)$_POST['user_id'];
	$date = time();
	if($connect_id && $user_id)
    {
        $notification =  new Notification();
        $notification->from_connect_id = "$connect_id";
        $notification->user_id = "$user_id";
        $notification->notification_type = 'invitereferal';
        $notification->is_deleted = '0';
        $notification->status = '1';
        $notification->created_date = "$date";
        $notification->updated_date = "$date";
		$notification->insert();
	}
	else
	{
		return false;
	}
	return true;	
}

public function actionSearchreferalfrd()
{
	$search_val = $_POST['search_val'];
	$session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');
	$connections = Connect::find()->select(['to_id'])->where(['from_id' => "$user_id", 'status' => '1'])->orderBy(['updated_date'=>SORT_DESC,'rand()' => SORT_DESC,])->all();
?>
	<?php if($connections)
	{ 
        ?>
        <ul>
        <?php
		$i=0;
		foreach($connections as $connect)
		{ 
			$connectidss = $connect['to_id'];
			$users = LoginForm::find()->select(['_id'])->where(['in','_id',$connectidss])->andwhere(['status'=>'1'])->andwhere(['like','fullname', $search_val])->asarray()->all();
			
			foreach($users as $user)
			{
				$i++;
				$connectid = (string) $user['_id']; 
				?>
				<li>
					<div class="invitelike-friend invitelike-connect">
						<div class="imgholder"><img src="<?= $this->getimage($connectid,'thumb');?>"/></div>
						<div class="descholder">
							<h6><?= $this->getuserdata($connectid,'fullname');?></h6>
							<?php $invitaionsent = Notification::find()->where(['status' => '1', 'from_connect_id' => "$connectid", 'user_id' => "$user_id", 'notification_type' => 'invitereferal'])->one();
							if($invitaionsent) {?>
								<div class="btn-holder referal_invited_<?= $connectid;?>">
								<label class="infolabel"><i class="zmdi zmdi-check"></i> Invited</label>
								</div>
							<?php 
							}else {
							?>
							<div class="btn-holder referal_invite_<?= $connectid;?>">
								<a href="javascript:void(0)" onclick="sendinvitereferal('<?=$connectid?>')" class="btn-custom">Invite</a>
							</div>
							<?php }?>
							<div class="btn-holder referal_invited_<?= $connectid;?> dis-none">
								<label class="infolabel"><i class="zmdi zmdi-check"></i> Invited</label>
							</div>
						</div>
					</div>
				</li>
		<?php		
			}
		}
        ?>
		</ul>
        <?php
		if($i<=0){ 
		 $this->getnolistfound('noconnectfound');
		}  
	}
}

public function actionAddreferalreply()
{
	$referal_id = $_POST['referal_id'];
	$referal_user = Referal::find()->select(['sender_id'])->where(['_id' => $referal_id])->one();
	$connect_user = $referal_user['sender_id'];
	$session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');
	$referal_text = $_POST['text'];;
	$date = time();
	$month_year = date("Y-n");
	
	$post = new Referal();
	$post->referal_text = $referal_text;
	$post->referal_id = $referal_id;
	$post->sender_id = $user_id;
	$post->month_year = $month_year;
	$post->created_date = $date;
	$post->is_deleted = "1";
	if($post->insert())
	{	
		$notification =  new Notification();
        $notification->from_connect_id = "$connect_user";
        $notification->user_id = "$user_id";
        $notification->notification_type = 'replyreferal';
        $notification->is_deleted = '0';
        $notification->status = '1';
        $notification->created_date = "$date";
        $notification->updated_date = "$date";
		$notification->insert();
	}	
	$last_insert_id =  $post->_id;
    $ids = $user_id;
?>
	<div class="feedback-reply">
		<div class="post-topbar">
			<div class="post-userinfo">
				<div class="img-holder">
					<div id="profiletip-1" class="profiletipholder">
						<span class="profile-tooltip tooltipstered">
							<img class="circle" src="<?= $this->getimage($user_id,'photo');?>">
						</span>           
					</div>
				</div>
				<div class="desc-holder">
					<a href="<?=Url::to(['userwall/index', 'id' => "$ids"])?>"><?= $this->getuserdata($user_id,'fullname');?></a>
					<span class="timestamp">Just Now</span>
				</div>
			</div>
			<div class="settings-icon">
				<a href="javascript:void(0)" onclick="replyDelete('<?= $last_insert_id;?>','<?= $referal_id;?>')"><i class="zmdi zmdi-delete mdi-16px"></i></a>
			</div>
		</div>
		
		<div class="post-content">
			<div class="post-details">				
				<div class="post-desc">
					<i class="mdi mdi-share"></i>
					<div class="normal-mode">
						<p><?= $referal_text;?></p>							
					</div>
					<div class="edit-mode">
						<div class="sliding-middle-out anim-area underlined fullwidth tt-holder">
							<textarea class="materialize-textarea"><?= $referal_text;?></textarea>
						</div>   
						<a href="javascript:void(0)" onclick="replyNormalMode(this)"><i class="mdi mdi-close	"></i></a>									
					</div>
				</div>
			</div>
		</div>
		
	</div>
<?php	
}

public function actionDeletereferal()
{
	$referal_id = $_POST['referal_id'];
	$referal = Referal::find()->select(['_id'])->where(['_id' => $referal_id])->one();
    $referal->is_deleted = "0";
    $referal->update();
	return true;
}
/*Referal Code End*/


/*Contribution Code Start*/
public function actionContribution()
{
	$wall_user_id = $_POST['wall_user_id'];
	$baseUrl = (string) $_POST['baseUrl'];
	$iaminjapanconnections  = Connect::getuserConnections($wall_user_id);
	$iaminjapanconnections  = count($iaminjapanconnections);
	$positivereferences = Referal::getTotalPositiveReferals($wall_user_id);
	$tripexperiencess = PostForm::getTripexpyourPosts($wall_user_id,'undefined','undefined');
	$tripexperiences = count($tripexperiencess);
	$pages = Page::getMyPages($wall_user_id);
	$posts = PostForm::getUserPostCount($wall_user_id);
	$wallphoto = $placephoto = $result = '';
	foreach($posts as $post)
	{
		$result .= $post['share_by'];
		$wallphoto .= $post['image'];
	}
	$post_share = substr_count($result , ',');
	$wallphotos = substr_count($wallphoto , ',');
	foreach($tripexperiencess as $tripexperience)
	{
		$placephoto .= 	$tripexperience['image'];
	}
	$placephotos = substr_count($placephoto , ',');
	return $this->render('contribution',
		array(
		'wall_user_id'=> $wall_user_id,
		'iaminjapanconnections'=>$iaminjapanconnections,
		'positivereferences'=>$positivereferences,
		'tripexperiences' => $tripexperiences,
		'pages'=> $pages,
		'posts'=> $posts,
		'post_share' => $post_share,
		'placephotos' => $placephotos,
		'wallphotos' =>$wallphotos,
	   )
    );
}

public function actionTotalpoint()
{
	$wall_user_id = $_POST['wall_user_id'];
	$point_total = $_POST['total'];
	$user_data = LoginForm::find()->select(['_id'])->where(['_id' => $wall_user_id])->one();
	$user_data->point_total = (int)$point_total;
    $user_data->update();
	$users = LoginForm::find()->where(['status'=>'1'])->orderBy(['point_total'=>SORT_DESC])->limit(5)->offset(0)->all();
	$percent = ($point_total*100)/($users[0]['point_total']);
	return $this->render('ranking',array('users'=> $users,'point_total'=>$point_total,'percent'=>$percent,'wall_user_id'=> $wall_user_id));
}

public function actionActivitylog()
{
	$user_id = $_POST['wall_user_id'];
	$baseUrl = $_POST['baseUrl'];
	$activities = Notification::find()->where(['notification_type' =>'connectrequestaccepted','is_deleted'=>'0','from_connect_id'=>$user_id])->orderBy(['updated_date'=>SORT_DESC])->all();
	return $this->render('activitylog',array('baseUrl' => $baseUrl,'activities'=> $activities));
}

public function actionSectionaboutgenderbirthdate() {
    $session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');
    $user_data = LoginForm::find()->where(['_id' => $user_id])->one();
    $birth_date_privacy_custom = array();
    $gender_privacy_custom = array();
    
    if(!empty($user_data)) {
        if(isset($user_data['birth_date_privacy']) && $user_data['birth_date_privacy'] == 'Custom') {
            $I = isset($user_data['birth_date_privacy_custom']) ? $user_data['birth_date_privacy_custom'] : '';
            $I = explode(',', $I);
            if(!empty($I)) {
                $I = array_filter($I);
            }
            $birth_date_privacy_custom = $I;

        }

        if(isset($user_data['gender_privacy']) && $user_data['gender_privacy'] == 'Custom') {
            $I = isset($user_data['gender_privacy_custom']) ? $user_data['gender_privacy_custom'] : '';
            $I = explode(',', $I);
            if(!empty($I)) {
                $I = array_filter($I);
            }
            $gender_privacy_custom = $I;
        }
    }

    $result = array('birth_date_privacy_custom' => $birth_date_privacy_custom, 'gender_privacy_custom' => $gender_privacy_custom);

    return json_encode($result, true);
}

public function actionSecuritysettingssomeparams() {
    $session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');
    $user_data = SecuritySetting::find()->where(['user_id' => $user_id])->one();
    $view_photos_custom = array();
    $my_post_view_status_custom = array();
    $add_public_wall_custom = array();
    $add_post_on_your_wall_view_custom = array();
    
    if(!empty($user_data)) {
        if(isset($user_data['view_photos']) && $user_data['view_photos'] == 'Custom') {
            $I = isset($user_data['view_photos_custom']) ? $user_data['view_photos_custom'] : '';
            $I = explode(',', $I);
            if(!empty($I)) {
                $I = array_filter($I);
            }
            $view_photos_custom = $I;

        }

        if(isset($user_data['my_post_view_status']) && $user_data['my_post_view_status'] == 'Custom') {
            $I = isset($user_data['my_post_view_status_custom']) ? $user_data['my_post_view_status_custom'] : '';
            $I = explode(',', $I);
            if(!empty($I)) {
                $I = array_filter($I);
            }
            $my_post_view_status_custom = $I;
        }

        if(isset($user_data['add_public_wall']) && $user_data['add_public_wall'] == 'Custom') {
            $I = isset($user_data['add_public_wall_custom']) ? $user_data['add_public_wall_custom'] : '';
            $I = explode(',', $I);
            if(!empty($I)) {
                $I = array_filter($I);
            }
            $add_public_wall_custom = $I;
        }

        if(isset($user_data['add_post_on_your_wall_view']) && $user_data['add_post_on_your_wall_view'] == 'Custom') {
            $I = isset($user_data['add_post_on_your_wall_view_custom']) ? $user_data['add_post_on_your_wall_view_custom'] : '';
            $I = explode(',', $I);
            if(!empty($I)) {
                $I = array_filter($I);
            }
            $add_post_on_your_wall_view_custom = $I;
        }
    }

    $result = array('view_photos_custom' => $view_photos_custom, 'my_post_view_status_custom' => $my_post_view_status_custom, 'add_public_wall_custom' => $add_public_wall_custom, 'add_post_on_your_wall_view_custom' => $add_post_on_your_wall_view_custom);

    return json_encode($result, true);
}

public function actionBlockingsomeparams() {
    $session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');
    $user_data = SecuritySetting::find()->where(['user_id' => $user_id])->one();

    $restricted_list_label = array();
    $blocked_list_label = array();
    $message_filter_label = array();
    $request_filter_label = array();
    
    if(!empty($user_data)) {
        if(isset($user_data['restricted_list']) && $user_data['restricted_list'] != '') {
            $I = isset($user_data['restricted_list']) ? $user_data['restricted_list'] : '';
            $I = explode(',', $I);
            if(!empty($I)) {
                $I = array_filter($I);
            }
            $restricted_list_label = $I;
        }

        if(isset($user_data['blocked_list']) && $user_data['blocked_list'] != '') {
            $I = isset($user_data['blocked_list']) ? $user_data['blocked_list'] : '';
            $I = explode(',', $I);
            if(!empty($I)) {
                $I = array_filter($I);
            }
            $blocked_list_label = $I;
        }

        if(isset($user_data['message_filtering']) && $user_data['message_filtering'] != '') {
            $I = isset($user_data['message_filtering']) ? $user_data['message_filtering'] : '';
            $I = explode(',', $I);
            if(!empty($I)) {
                $I = array_filter($I);
            }
            $message_filter_label = $I;
        }

        if(isset($user_data['request_filter']) && $user_data['request_filter'] != '') {
            $I = isset($user_data['request_filter']) ? $user_data['request_filter'] : '';
            $I = explode(',', $I);
            if(!empty($I)) {
                $I = array_filter($I);
            }
            $request_filter_label = $I;
        }
    }

    $result = array('restricted_list_label' => $restricted_list_label, 'blocked_list_label' => $blocked_list_label, 'message_filter_label' => $message_filter_label, 'request_filter_label' => $request_filter_label);
    return json_encode($result, true);
}

public function actionSectionAbout() 
{
    $session = Yii::$app->session;
    $uid = (string)$session->get('user_id');
    $user_id = (string) $_POST['wall_user_id']; 
    $email = $session->get('email');
    $wall_user_id = $_POST['wall_user_id'];
    $model = new \frontend\models\LoginForm();
    $model2 = new \frontend\models\Personalinfo();
    
    $totalcredits = Credits::travusertotalcredits($wall_user_id);
    $total = (isset($totalcredits[0])) ? $totalcredits[0]['totalcredits'] : '0';

    $isvip = Vip::isVIP($wall_user_id);
    $isVerify = Verify::isVerify($wall_user_id);
    
    $personal_info = Personalinfo::find()->select(['_id','occupation','language','education','interests','visited_countries','lived_countries'])->where(['user_id' => (string)$user_id])->asarray()->one();
    
    $user_data =  Personalinfo::getPersonalInfo($user_id);
    $user_basicinfo = LoginForm::find()->where(['_id' => $user_id])->one();
    $other_user_basicinfo = LoginForm::find()->select(['country'])->where(['_id' => $user_id])->asarray()->one();
    
    $other_occupation = $personal_info['occupation'];
    $other_language = $personal_info['language'];
    $other_education = $personal_info['education'];
    
    
    if(isset($other_user_basicinfo['country']) && !empty($other_user_basicinfo['country'])) {
        $other_country = $other_user_basicinfo['country'];
    } else {
        $other_country = '';
    }
    
    $profile_competed = $this->getprofilepercentage($user_id);
    
    $occupation = (isset($user_data['occupation']) && trim($user_data['occupation']) != '') ? $user_data['occupation'] : 'No occupation set';
    $occupationarray = explode(',', $occupation);
    $occupationarray = array_filter($occupationarray);
    
    $interests = (isset($user_data['interests']) && trim($user_data['interests']) != '') ? $user_data['interests'] : 'No interest set';
    $interestsarray = explode(',', $interests);
    $interestsarray = array_filter($interestsarray);
    $inter_str_chuk_str = '';
    if(!empty($interestsarray)) {
        foreach($interestsarray as $inter_str_chuks) {
            $inter_str_chuk_str .= '<span class="inline-obj tagSpan">'.$inter_str_chuks.'</span>';
        }
    }

    $language = (isset($user_data['language']) && trim($user_data['language']) != '') ? $user_data['language'] : 'No language set';
    $languagearray = explode(',', $language);
    $languagearray = array_filter($languagearray);

    $education = (isset($user_data['education']) && trim($user_data['education']) != '') ? $user_data['education'] : 'No education set';
    $educationarray = explode(',', $education);
    $educationarray = array_filter($educationarray);
    
    $visited_countries = $user_data['visited_countries'];
    if($user_data['visited_countries']=='null'){$user_data['visited_countries']='';}
    $visited_str = '';
    if(isset($visited_countries) && $visited_countries != '') {
        $visited_str .= '"';
        $visited_str .= str_replace(",", '","', $visited_countries);
        $visited_str .= '"';    
    }

    $lived_countries = $user_data['lived_countries'];
    if($user_data['lived_countries']=='null'){$user_data['lived_countries']='';}
    $lived_str = '';
    if(isset($lived_countries) && $lived_countries != '') {
        $lived_str .= '"';
        $lived_str .= str_replace(",", '","', $lived_countries);
        $lived_str .= '"';  
    }
    

    if(isset($user_basicinfo['birth_date']) && $user_basicinfo['birth_date'] != 'undefined' && $user_basicinfo['birth_date'] != '')
    {
        $birth = strtotime($user_basicinfo['birth_date']);
        $birth = date('d/m/Y', $birth);
        $birth = explode('/', $birth);

        if(count($birth) == 3) {
            $age =  date('Y') - $birth[2] . " /";
        } else {
            $age = "Birth date Not Valid";       
        }
    }
    else
    {
        $age = "Birth date Not Added";
    }
    $user_created_date1 = date("d M Y", $user_basicinfo['created_date']);
    $user_created_date2 = date("h:i a", $user_basicinfo['created_date']);
    $user_created_date = $user_created_date1.' at '.$user_created_date2;
    if(empty($user_basicinfo['last_time'])){$user_basicinfo['last_time'] = $user_basicinfo['created_date'];}
    $last_login1 = date("M d, Y", $user_basicinfo['last_time']);
    $last_login2 = date("h:i a", $user_basicinfo['last_time']);
    $last_login = $last_login1.' at '.$last_login2;
    ?>
    <div class="about-summary">                                 
        <div class="row">
            <div class="col m8 l8 summeryinfo">
                <div class="section-title">Summery</div>
                <div class="row">
                    <div class="col l6 summerybox">
                        <div class="info-row"><div class="icon-holder"><i class="mdi mdi-format-quote-open"></i></div><div class="desc-holder">Lastly loged in <span class="darktext"><?=$last_login?></span></div></div>
                        <div class="info-row"><div class="icon-holder"><i class="mdi mdi-comment"></i></div><div class="desc-holder">Speaks <span class="darktext"><?=$other_language?></span></div></div>
                        <div class="info-row"><div class="icon-holder"><i class="mdi mdi mdi-gender-male-female"></i></div><div class="desc-holder"><span class="darktext"><?=$age?> Male</span></div></div>
                        <div class="info-row"><div class="icon-holder"><i class="mdi mdi-account"></i></div><div class="desc-holder">Member since <span class="darktext"><?=$user_created_date ?></span></div></div>
                    </div>
                    <div class="col l6 summerybox">
                        <div class="info-row"><div class="icon-holder"><i class="mdi mdi-briefcase"></i></div><div class="desc-holder">Works as <span class="darktext">
                        <?=$other_occupation?>
                        </span></div></div>
                        <div class="info-row"><div class="icon-holder"><i class="mdi mdi-library-books"></i></div><div class="desc-holder">Studies <span class="darktext"><?=$other_education?></span></div></div>
                        <div class="info-row"><div class="icon-holder"><i class="zmdi zmdi-pin"></i></div><div class="desc-holder">From <span class="darktext"><?= $other_country?></span></div></div>
                        <div class="info-row"><div class="icon-holder"><i class="mdi mdi-percent"></i></div><div class="desc-holder">Profile<span class="darktext"> <?=$profile_competed?>% Completed</span></div></div>
                    </div>
                </div>
            </div>
            <div class="col m4 l4 summerystatus">
                <div class="highlightInfo">
                    <ul>
                        <li class="<?php if($isVerify){ ?>success<?php } else{ ?>pendding<?php } ?>">
                            <span class="left">Verified</span> 
                            <span class="right"><i class="mdi mdi-<?php if($isVerify){ ?>check-circle<?php } else{ ?>close<?php } ?>" ></i></span>
                            <div class="clear"></div>
                        </li>
                        <li class="<?php if($isvip){ ?>success<?php } else{ ?>pendding<?php } ?>">
                            <span class="left">VIP</span>  
                            <span class="right"><i class="mdi mdi-<?php if($isvip){ ?>check-circle<?php } else{ ?>close<?php } ?>" ></i></span>
                            <div class="clear"></div>
                        </li>
                        <li class="success">
                            <span class="left">Credit Balance</span>   
                            <span class="right"><?=$total?></span>
                            <div class="clear"></div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="clear"></div>
    <div class="editable-summery mode-holder">
        <div class="normal-mode">
            <div class="about-personal">
                <div class="section-title">Personal
                <?php if($uid == $wall_user_id){ ?>
                <a href="javascript:void(0)" onclick="open_detail(this)" class="right editicon waves-effect waves-theme"><i class="zmdi zmdi-edit md-20"></i></a>
                <?php } ?>
                </div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3"><span class="darktext">About Me</span></div>
                        <div class="col l10 m9"><span class="detail about_me"><?php if($user_data['about']!=""){ ?><?=$user_data['about']?><?php } ?></span></div>                                                    
                    </div>
                </div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3"><span class="darktext">Education</span></div>
                        <div class="col l10 m9"><span class="detail edu_me"><?=$education?></span></div>                                    
                    </div>
                </div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3"><span class="darktext">Occupation</span></div>
                        <div class="col l10 m9"><span class="detail ocu_me"><?=$occupation?></span></div>
                    </div>
                </div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3"><span class="darktext">City</span></div>
                        <div class="col l10 m9"><span class="detail city_me"><?=$user_basicinfo['city']?></span></div>
                    </div>
                </div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3"><span class="darktext">Country</span></div>
                        <div class="col l10 m9"><span class="detail country_me"><?=$user_basicinfo['country']?></span></div>
                    </div>
                </div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3"><span class="darktext">Birth Date</span></div>
                        <!-- <div class="col l10 m9"><span class="detail birth_date_me"><?=$user_basicinfo['birth_date']?></span></div> -->
                        <div class="col l6 m5"><span class="detail">21-08-1964</span></div>
                        <div class="col l4 m4">
                            <span class="detail birth_date_privacylabell">
                            <?php
                            if(isset($user_basicinfo['birth_date_privacy']) && $user_basicinfo['birth_date_privacy']) { 
                                ?>
                                <?=$user_basicinfo['birth_date_privacy']?>
                                <?php
                            } else {
                                ?>
                                Private
                                <?php
                            }
                            ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3"><span class="darktext">Gender</span></div>
                        <!-- <div class="col l10 m9"><span class="detail gender_me"><?=$user_basicinfo['gender']?></span></div> -->
                        <div class="col l6 m5"><span class="detail">Male</span></div>
                        <div class="col l4 m4">
                            <span class="detail gender_privacylabel">
                            <?php
                            if(isset($user_basicinfo['gender_privacy']) && $user_basicinfo['gender_privacy']) {
                                ?>
                                <?=$user_basicinfo['gender_privacy']?>
                                <?php
                            } else {
                                ?>
                                Private
                                <?php
                            }
                            ?>           
                            </span>
                        </div>
                    </div>
                </div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3"><span class="darktext">Language</span></div>
                        <div class="col l10 m9"><span class="detail lang_me"><?=$language?></span></div>
                    </div>
                </div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3"><span class="darktext">Interest</span></div>
                        <div class="col l10 m9">
                            <span class="detail inte_me"><?=$interests?></span><div class="clear"></div>
                            <span class="inline-obj"><i class="mdi mdi-tag" ></i></span>
                            <span class="inte_me_box"><?=$inter_str_chuk_str?></span>
                        </div>
                    </div>
                </div>          
                <div class="personal-info fullwidth">
                    <span class="darktext">One amzing thing I&rsquo;ve done</span>
                    <div class="clear"></div>
                    <span class="detail amaz_me"><?=$user_data['amazing_things']?></span>
                </div>                                          
                <div class="personal-info fullwidth">
                    <span class="darktext">Conutries I&rsquo;ve visited</span>
                    <div class="clear"></div>
                    <span class="detail visited_me chips-initial">
                        <?php
                            $visited_countries = $user_data['visited_countries'];
                            $visited_countries = explode('@@@', $visited_countries);
                            $visited_countries = array_filter($visited_countries);
                            foreach ($visited_countries as $visited_countriess) { ?>
                                <div class="chip"><?=ucfirst(strtolower($visited_countriess))?></div>
                           <?php }
                        ?>
                            
                    </span>
                </div>
                <div class="personal-info fullwidth">
                    <span class="darktext">Conutries I&rsquo;ve lived in</span>
                    <div class="clear"></div>
                    <span class="detail lived_me chips-initial">
                        <?php
                            $lived_countries = $user_data['lived_countries'];
                            $lived_countries = explode('@@@', $lived_countries);
                            $lived_countries = array_filter($lived_countries);
                            foreach ($lived_countries as $lived_countriess) { ?>
                                <div class="chip"><?=ucfirst(strtolower($lived_countriess))?></div>
                           <?php }
                        ?>  
                    </span>
                </div>
                </div>
            </div>
        <?php $form = ActiveForm::begin(['id' => 'personal-info2','options'=>['onsubmit'=>'return false;',],]); $aboutMe = isset($user_data['about']) ? trim($user_data['about']) : '';
        ?>
        <div class="detail-mode">
            <div class="about-personal" id="menu-security1">
                <div class="section-title">Personal</div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3 s12"><label>About Me</label></div>
                        <div class="col l8 m9 s12">
                            <div class="sliding-middle-custom anim-area underlined fullwidth">
                                <textarea class="materialize-textarea mb0 md_textarea descinput au_about" placeholder="Something about you..."><?=$aboutMe?></textarea>
                            </div>
                        </div>                                                                                                  
                    </div>
                </div> 
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3 s12"><label>Education</label></div>
                        <div class="col l8 m9 s12 dropdown782" id="educationdropdown">
                            <div class="sliding-middle-out anim-area underlined fullwidth">
                                <select id="personalinfo-education" data-selectore="education123" class="education123 education-cls au_edu" data-fill="y" data-action="education" name="Personalinfo[education][]" multiple="multiple" style="width: 100%">
                                    <option value="" disabled>Choose education</option>
                                    <?php
                                    $Education = ArrayHelper::map(Education::find()->all(), 'name', 'name');

                                    foreach ($Education as $sEducation) {
                                        $cls = '';
                                        if(in_array($sEducation, $educationarray)) {
                                            $cls = 'selected';
                                        }
                                        ?>
                                        <option value='<?=$sEducation?>' <?=$cls?>><?=$sEducation?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3 s12"><label>Occupation</label></div>
                        <div class="col l8 m9 s12 dropdown782" id="occupationdropdown">
                            <div class="sliding-middle-out anim-area underlined fullwidth">
                                <select id="personalinfo-occupation" class="occupation123 occupations-cls au_occu" name="Personalinfo[occupation][]" data-selectore="occupation123" data-fill="y" data-action="occupation" multiple="multiple" style="width: 100%">
                                    <option value="" disabled>Choose occupation</option>
                                    <?php
                                    $Occupation = ArrayHelper::map(Occupation::find()->all(), 'name', 'name');
                                    foreach ($Occupation as $sOccupation) {
                                        $cls = '';
                                        if(in_array(trim($sOccupation), $occupationarray)) {
                                            $cls = 'selected';
                                        }
                                        ?>
                                        <option value='<?=$sOccupation?>' <?=$cls?>><?=$sOccupation?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3 s12"><label>City</label></div>
                        <div class="col l8 m9 s12">
                            <div class="sliding-middle-custom anim-area underlined width350 ">
                                <!-- <input type="text" placeholder="Location" class="materialize-textarea md_textarea item_address" id="placelocationsearch" data-query="M" onfocus="filderMapLocationModal(this)" autocomplete="off" style="height: 20px;"> -->

                                <input type="text" class="getplacelocation au_city" id="au_city" data-query="M" onfocus="filderMapLocationModal(this)" autocomplete="off" value="<?=$user_basicinfo['city']?>" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3 s12"><label>Country</label></div>
                        <div class="col l8 m9 s12">
                            <div class="sliding-middle-custom anim-area underlined  width350">
                                <input type="text" class="title au_country" placeholder="Country" data-query="M" onfocus="filderMapLocationModal(this)" value="<?=$user_basicinfo['country']?>"  id="au_country" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3 s12"><label>Birth Date</label></div>
                        <div class="col l7 m7 s9">
                            <div class="sliding-middle-custom anim-area underlined  width350 about-dob">
                                <input type="text" onkeydown="return false;" placeholder="Birthdate" class="datetime-picker datepickerinput" data-query="M" data-toggle="datepicker" id="datepicker" value="<?= $user_basicinfo['birth_date']?>" readonly>
                            </div>
                        </div>
                        <div class="col l3 m2 s3 right">
                            <div class="left">
                                <a class="dropdown-button birth_date_privacy" href="javascript:void(0);" data-modeltag="birth_date_privacy" data-fetch="yes" data-label="wallbirthdate" data-activates="wabout_btn1">
                                    <span class="getvalue">
                                    <?php
                                    if(isset($user_basicinfo['birth_date_privacy']) && $user_basicinfo['birth_date_privacy']) {
                                        ?>
                                        <?=$user_basicinfo['birth_date_privacy']?>
                                        <?php
                                    } else { 
                                        ?>
                                        Private
                                        <?php
                                    }
                                    ?>
                                    </span>
                                    <span class="caret"></span>
                                    <i class="zmdi zmdi-caret-down"></i>
                                </a>
                                <ul id="wabout_btn1" class="dropdown-content">
                                    <li class="selectore privacydropdownli"><a href="javascript:void(0)">Private</a></li>
                                    <li class="selectore privacydropdownli"><a href="javascript:void(0)">Connections</a></li>
                                    <li class="selectore privacydropdownli customli_modal"><a href="javascript:void(0)">Custom</a></li>
                                    <li class="selectore privacydropdownli"><a href="javascript:void(0)">Public</a></li>
                                </ul>
                            </div>                                
                        </div>
                    </div>
                </div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3 s12"><label>Gender</label></div>
                        <div class="col l7 m7 s9">
                            <div class="sliding-middle-custom anim-area underlined width350 about-gender">
                                <select id="genderDrop" class="genderDrop"> 
                                    <?php
                                        if($user_basicinfo['gender']=='Male')
                                        {
                                            ?>
                                            <option value="Male" selected>Male</option>
                                            <option value="Female">Female</option>
                                            <?php
                                        }
                                        else
                                        {
                                            ?>
                                            <option value="Female" selected>Female</option>
                                            <option value="Male">Male</option>
                                            <?php
                                        }
                                    ?>
                                </select>
                            </div>         
                        </div>
                        <div class="col l3 m2 s3 right">
                            <div class="left">
                                <a class="dropdown-button birthdat gender_privacy" href="javascript:void(0);" data-activates="wabout_btn1gender" data-modeltag="gender_privacy" data-fetch="yes" data-label="wallgender">
                                    <span class="getvalue">
                                    <?php
                                    if(isset($user_basicinfo['gender_privacy']) && $user_basicinfo['gender_privacy']) {
                                        ?>
                                        <?=$user_basicinfo['gender_privacy']?>
                                        <?php
                                    } else {
                                        ?>
                                        Private
                                        <?php
                                    }
                                    ?>
                                </span>
                                    <span class="caret"></span>
                                    <i class="zmdi zmdi-caret-down"></i>
                                </a>
                                <ul id="wabout_btn1gender" class="dropdown-content">
                                    <li class="selectore privacydropdownli"><a href="javascript:void(0)">Private</a></li>
                                    <li class="selectore privacydropdownli"><a href="javascript:void(0)">Connections</a></li>
                                    <li class="selectore privacydropdownli customli_modal"><a href="javascript:void(0)">Custom</a></li>
                                    <li class="selectore privacydropdownli"><a href="javascript:void(0)">Public</a></li>
                                </ul> 
                            </div>      
                        </div>
                    </div>
                </div>
                <div class="personal-info">
                    <div class="row">
                        <div class="col l2 m3 s12"><label>Language</label></div>
                        <div class="col l8 m9 s12 dropdown782" id="languagedropdown">
                            <div class="sliding-middle-custom anim-area underlined fullwidth">
                                <select id="personalinfo-language" class="language123 language-cls1 au_lang" name="Personalinfo[language][]" data-selectore="language123" data-fill="y" data-action="language" multiple="multiple"  style="width: 100%">
                                    <option value="" disabled>Choose language</option>
                                    <?php
                                    $Language = ArrayHelper::map(Language::find()->all(), 'name', 'name');
                                    foreach ($Language as $sLanguage) {
                                        $cls = '';
                                        if(in_array(trim($sLanguage), $languagearray)) {
                                            $cls = 'selected';
                                        }
                                        ?>
                                        <option value='<?=$sLanguage?>' <?=$cls?>><?=$sLanguage?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>         
                    </div>
                </div>
                <div class="personal-info">   
                    <div class="row">
                        <div class="col l2 m3 s12"><label>Interest</label></div>
                        <div class="col l8 m9 s12 dropdown782" id="interestsdropdown">
                            <div class="sliding-middle-custom anim-area underlined fullwidth">
                                <select id="personalinfo-interests" class="interest123 interests-cls au_inte" name="Personalinfo[interests][]" data-selectore="interest123" data-fill="y" data-action="interest" multiple="multiple" style="width: 100%">
                                    <option value="" disabled>Choose interest</option>
                                    <?php
                                    $Interests = ArrayHelper::map(Interests::find()->all(), 'name', 'name');
                                    foreach ($Interests as $sInterests) {
                                        $cls = '';
                                        if(in_array(trim($sInterests), $interestsarray)) {
                                            $cls = 'selected';
                                        }
                                        ?>
                                        <option value='<?=$sInterests?>' <?=$cls?>><?=$sInterests?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>   
                    </div>
                </div>
                <div class="personal-info">                 
                    <div class="row">
                        <div class="col l10 m12 s12">
                        <label class="margin-b6">One amzing thing I&rsquo;ve done</label>
                        <div class="clear"></div>
                        <div class="sliding-middle-custom anim-area underlined fullwidth">
                        <textarea class="materialize-textarea mb0 md_textarea descinput au_amazing" placeholder="Amazing thing done by you..."><?=$user_data['amazing_things']?></textarea>
                    </div>
                    </div>
                    </div>
                </div>                                          
                <div class="personal-info fullwidth">
                    <div class="row">
                    <div class="col l10 m12 s12"> 
                    <label class="margin-b6">Conutries I&rsquo;ve visited</label>
                    <div class="clear"></div>
                    <div class="chips chips-initial1 chips-autocomplete" id="tempoConutriesI">
                        <?php
                            $visited_countries = $user_data['visited_countries'];
                            $visited_countries = explode('@@@', $visited_countries);
                            $visited_countries = array_filter($visited_countries);
                            foreach ($visited_countries as $visited_countriess) { ?>
                                <div class="chip"><?=ucfirst(strtolower($visited_countriess))?><i class="close mdi mdi-close mdi-20px material-icons"></i></div>
                           <?php }
                        ?>
                    </div>              
                    </div>              
                    </div>              
                </div>
                <div class="personal-info fullwidth">
                    <div class="row">
                    <div class="col l10 m12 s12">
                    <label class="margin-b6">Conutries I&rsquo;ve lived in</label>
                    <div class="clear"></div>
                    <div class="chips chips-initial2 chips-autocomplete" id="tempoConutriesII">
                        <?php
                            $lived_countries = $user_data['lived_countries'];
                            $lived_countries = explode('@@@', $lived_countries);
                            $lived_countries = array_filter($lived_countries);
                            foreach ($lived_countries as $lived_countriess) { ?>
                                <div class="chip"><?=ucfirst(strtolower($lived_countriess))?><i class="close mdi mdi-close mdi-20px material-icons"></i></div>
                           <?php 
                            }
                        ?>  
                    </div>
                    </div>
                    </div>
                </div>
                <div class="personal-info fullwidth">
                    <div class="right settings-btn">
                        <a class="btngen-center-align waves-effect" tabindex="1" onclick="close_detail(this)">Cancel</a> 
                        <a class="btngen-center-align waves-effect" tabindex="1" onclick="close_detail(this),update_basicinfo2()">Save</a>
                    </div>
                </div>                                          
            </div>
        </div>
        <?php ActiveForm::end() ?>  
    </div>      
    <?php       
}

public function actionDestWishList()
{
    $wall_user_id = (string) $_POST['id'];
    $destfuture = Destination::getAllDestinationTypeNew($wall_user_id,'future');
    return $this->render('dest_wish_list',array('wall_user_id' => $wall_user_id,'destfuture' => $destfuture));
}

public function actionDestVisitList()
{
    $wall_user_id = (string) $_POST['id'];
    $getusercity = LoginForm::find()->where(['_id' => "$wall_user_id"])->one();
    $place = $getusercity['city'];
    if(!isset($place) && empty($place))
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $getplaceapi = 'http://freegeoip.net/json/'.$ip;
        $location = file_get_contents($getplaceapi);
        $location = json_decode($location);
        $place = $location->city;
    }
    else
    {
        $place = $place;
    }
    $destpast = Destination::getAllDestinationType($wall_user_id,'past');
    array_push($destpast,$place);
    return $this->render('dest_visit_list',array('wall_user_id' => $wall_user_id,'destpast' => $destpast));
}

public function actionDestMap()
{ 
    $session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');
    $wall_user_id = (string) $_POST['id'];
    $mode = (string) $_POST['mode'];
    $destpast = Destination::getAllDestinationType($wall_user_id,'past');
    $destfuture = Destination::getAllDestinationType($wall_user_id,'future');
    $place = '';
    $getusercity = LoginForm::find()->where(['_id' => "$wall_user_id"])->one();
    $place = $getusercity['city'];
    if(!isset($place) && empty($place))
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $getplaceapi = 'http://freegeoip.net/json/'.$ip;
        $location = file_get_contents($getplaceapi);
        $location = json_decode($location);
        $place = $location->city;
    }
    else
    {
        $place = $place;
    }
    return $this->render('dest_map',array('user_id' => $user_id,'wall_user_id' => $wall_user_id,'place' => $place,'destpast' => $destpast,'destfuture' => $destfuture));
}

public function actionDestList()
{ 
    $session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');
	$wall_user_id = (string) $_POST['id'];
	$mode = (string) $_POST['mode'];
	$dest = Destination::getAllDestination($wall_user_id);
	$place = '';
	$getusercity = LoginForm::find()->where(['_id' => "$wall_user_id"])->one();
	$place = $getusercity['city'];
	if(!isset($place) && empty($place))
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$getplaceapi = 'http://freegeoip.net/json/'.$ip;
		$location = file_get_contents($getplaceapi);
		$location = json_decode($location);
		$place = $location->city;
	}
	else
	{
		$place = $place;
	}
	return $this->render('dest_list',array('user_id' => $user_id,'wall_user_id' => $wall_user_id,'dest' => $dest,'place' => $place));
}

public function actionAddalbumcontent()
{
    return $this->render('add_album_content');
}

public function actionReferencepostcontent()
{
    return $this->render('reference_post_content');
}

public function actionFetchlocations()  
{
    $data = ArrayHelper::map(CountryCode::find()->all(), 'country_name', 'country_name');
    return json_encode($data, true);
}

public function actionFetchoccupation()  
{
    $data = ArrayHelper::map(Occupation::find()->all(), 'name', 'name');
    return json_encode($data, true);
}

public function actionFetchinterests()  
{
    $data = ArrayHelper::map(Interests::find()->all(), 'name', 'name');
    return json_encode($data, true);
}

public function actionConnectistprivacyupdate()  
{
    $session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');
    if(isset($user_id) && $user_id != '') {
        if(isset($_POST['$value']) && !empty($_POST['$value'])) {
            $value = $_POST['$value']; 
            $privacyBulk = array('Private', 'Public', 'Connections');
            if(in_array($value, $privacyBulk)) {
                $data = SecuritySetting::connectistprivacyupdate($value, $user_id);
                return $data;
            }
        }
    }
}

public function actionGetsliders()  
{
     /*
    $cover = Cover::find()->asarray()->all();
    if(isset($cover) && !empty($cover)) 
    {   
        $i = 1;  
        foreach($cover as $coversingle) 
        {   
            $name = $coversingle['cover_image'];
            if(!file_exists("uploads/cover/thumbs/thumb_".$name."")) {
                continue;
            }

        ?>
        <a class="carousel-item" href="#image<?=$i?>"><img src="uploads/cover/thumbs/thumb_<?=$name?>"></a>
        <?php 
        $i++;
    }
    <?php } else { 
    }
    */?>
    <a class="carousel-item" href="#one!"><img src="uploads/cover/thumbs/thumb_cover-1.jpg"></a>
    <a class="carousel-item" href="#two!"><img src="uploads/cover/thumbs/thumb_cover-2.jpg"></a>
    <a class="carousel-item" href="#three!"><img src="uploads/cover/thumbs/thumb_cover-3.jpg"></a>
    <a class="carousel-item" href="#four!"><img src="uploads/cover/thumbs/thumb_cover-4.jpg"></a>
    <a class="carousel-item" href="#five!"><img src="uploads/cover/thumbs/thumb_cover-5.jpg"></a>
    <a class="carousel-item" href="#six!"><img src="uploads/cover/thumbs/thumb_cover-6.jpg"></a>
    <a class="carousel-item" href="#seven!"><img src="uploads/cover/thumbs/thumb_cover-7.jpg"></a>
    <a class="carousel-item" href="#eight!"><img src="uploads/cover/thumbs/thumb_cover-8.jpg"></a>
    <a class="carousel-item" href="#nine!"><img src="uploads/cover/thumbs/thumb_cover-9.jpg"></a>
    <a class="carousel-item" href="#ten!"><img src="uploads/cover/thumbs/thumb_cover-10.jpg"></a>
    <?php
}

public function actionFetchgallerycontent()
{
    return $this->render('gallery');
}

public function actionAddPhotoPopup()  
{
    $pgid = '';
    if(strstr($_SERVER['HTTP_REFERER'],'r=page') || strstr($_SERVER['HTTP_REFERER'],'r=userwall')) 
    {
        $url = $_SERVER['HTTP_REFERER'];
        $urls = explode('&',$url);
        $url = explode('=',$urls[1]);
        $pgid = $url[1];
    }

    $postBulk = array('page', 'wall', 'other');
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    if(in_array($type, $postBulk)) {
        return $this->render('/layouts/add-photo-popup', array('type' => $type, 'id' => $pgid));
    }
}

public function actionGetcountries()  
{ 
    $session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');
    $user_data =  Personalinfo::getPersonalInfo($user_id);
    $result = array();

    $lived_countries = $user_data['lived_countries'];
    if($user_data['lived_countries']=='null') { 
        $user_data['lived_countries']='';
    }


    $lived_countries = $user_data['lived_countries'];
    $lived_countries = explode('@@@', $lived_countries);
    $lived_countries = array_filter($lived_countries);
    $ll1 = array();
    foreach ($lived_countries as $lived_country) {
        $temp = array();
        $temp['tag'] = $lived_country;
        $ll1[] = $temp;
    }
    $result['lived_countries'] = $ll1;

    $visited_countries = $user_data['visited_countries'];
    if($user_data['visited_countries']=='null') { 
        $user_data['visited_countries']='';
    }

    $visited_countries = $user_data['visited_countries'];
    $visited_countries = explode('@@@', $visited_countries);
    $visited_countries = array_filter($visited_countries);
    $ll2 = array();
    foreach ($visited_countries as $visited_country) {
        $temp = array();
        $temp['tag'] = $visited_country;
        $ll2[] = $temp;
    }
    $result['visited_countries'] = $ll2;

    $country = ArrayHelper::map(CountryCode::find()->asarray()->all(), 'country_name', 'country_name');
    
    if(!empty($country)) {
        $result['country']  = $country;
    }

    return json_encode($result, true);
}

public function actionFetchalbumids()  
{ 
    $session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');
    $user_data =  Personalinfo::getPersonalInfo($user_id);
    $result = array();

    if(isset($_POST['$boxid']) && $_POST['$boxid'] != '') {
        $id = $_POST['$boxid'];
        $data = UserPhotos::find()->where([(string)'_id' => $id])->asarray()->one();


        if(!empty($data)) {
            $privacy = isset($data['post_privacy']) ? $data['post_privacy'] : '';
            if($privacy == 'Custom') {
                $customids  = isset($data['customids']) ? $data['customids'] : '';
                $customids  = explode(",", $customids);
                if(!empty($customids)) {
                    $customids = array_filter($customids);
                }

                return json_encode($customids, true);
            }
        }
    }

    return json_encode($result, true);
}

public function actionGetuploadgalleryimage()  
{ 
    $session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');
    $user_data =  Personalinfo::getPersonalInfo($user_id);
    $result = array();

    if(isset($_POST['id']) && $_POST['id'] != '') {
        $id = $_POST['id'];

        $gallery = Gallery::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
        if(!empty($gallery)) {
            $image = isset($gallery['image']) ? $gallery['image'] : '';
            ?>
            <div class="img-box">
                <div class="custom-file addimg-box add-photo ablum-add" style="background-image: url('<?=$image?>'), linear-gradient( rgba(0, 0, 0, 0.5)  , rgba(0, 0, 0, 0.5) )">
                    <span class="icont">+</span>
                    <br><span class="">Update photo</span>
                    <div class="addimg-icon">
                    </div>
                    <input class="upload edit-gallery-file-upload" id="edit-gallery-file-upload" title="Choose a file to upload" type="file">
                </div>
            </div>
            <?php
        }
    }
}

public function actionRemovepic()  
{ 
    $session = Yii::$app->session;
    $user_id = (string)$session->get('user_id');
    $result = array();

    if(isset($_POST['$id']) && $_POST['$id'] != '') {
        $id = $_POST['$id'];
        $data = Gallery::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();

        if(!empty($data)) {
            if(isset($data->post_id) && $data->post_id != '') {
                $post_id = $data->post_id;
                $post = PostForm::find()->where([(string)'_id' => $post_id])->one();
                if(!empty($post)) {
                    $post->pinned = '';
                    $post->update();
                }
            }

            $data->delete();
            $result = array('success' => true);
        }
    }

    return json_encode($result, true);
}
    
    public function actionTakeDestAction()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $data = array();
        if(isset($user_id) && $user_id != '') {
            $authstatus = UserForm::isUserExistByUid($user_id);
            if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
                return $authstatus;
            } else {       
                $type = $_POST['type'];
                $place = $_POST['place'];
                return Destination::addUserDest($user_id,$type,$place);
            }
        } else {
            return 'checkuserauthclassg';
        }
        
    }
    
    public function actionDelDestAction()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
            $destid = $_POST['id'];
            return Destination::removeUserDest($destid);
        }
        else
        {
            return $this->goHome();
        }
    }
}
?>