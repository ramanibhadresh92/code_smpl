<?php
namespace frontend\controllers;
use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\helpers\HtmlPurifier;
use yii\base\InvalidParamException; 
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\mongodb\ActiveRecord;
use frontend\models\LoginForm;
use frontend\models\PageVisitor;
use frontend\models\Page;  
use frontend\models\PageRoles;
use frontend\models\Like;
use frontend\models\UserForm;
use frontend\models\Connect;
use frontend\models\PostForm;
use frontend\models\PinImage;
use frontend\models\PageEndorse;
use frontend\models\HideNotification;
use frontend\models\Notification;
use frontend\models\BusinessCategory;
use frontend\models\SecuritySetting;
use frontend\models\SliderCover;
use frontend\models\Credits;
use frontend\models\UserPhotos;
use frontend\models\Cover;
use frontend\models\PageReviewPhotos;
use frontend\models\Referal;
use frontend\models\Comment;

class PageController extends Controller
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

    public function actionIndex() {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        
        if(isset($uid) && $uid != '') { 
            $authstatus = UserForm::isUserExistByUid($uid); 
            $checkuserauthclass = $authstatus;
        } else {
            $checkuserauthclass = 'checkuserauthclassg';
        }
    
        return $this->render('index',array('checkuserauthclass' => $checkuserauthclass));
    }

    public function actionDetail() {
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        $page_id = $request->get('id');
        $visitor_id = (string)$session->get('user_id');
        
        if($visitor_id == '') {
            $visitor_id = LoginForm::find()->select(['_id'])->limit(1)->offset(0)->all();
            $visitor_id = $visitor_id[0]['_id'];
        }
         
        $page_exist = Page::Pagedetails($page_id);
        
        if(!$page_exist) {
            return $this->goHome();
        } else if($page_exist['is_deleted']=='3') {
            return $this->goHome();
        } else if(($visitor_id!=$page_exist['created_by']) && $page_exist['is_deleted']=='0') {
            return $this->goHome();
        } else if(strstr($page_exist['blk_block_list'],(string)$visitor_id)) {
            return $this->goHome();
        } else {
            $visitors = PageVisitor::find()->select(['_id'])->with('user')->where(['page_id' => "$page_id",'visitor_id' => $visitor_id,'year' => date("Y"),'month' => date("M")])->one();
            $date = time();
            if($visitors) {
                $visitors->visited_date = "$date";
                $visitors->month = date("M");
                $visitors->year = date("Y");
                $visitors->day = date("d");
                $visitors->date = date("Y-m-d h:i:s");
                $visitors->update();
            } else {
                $visitor = new PageVisitor();
                $visitor->page_id = $page_id;
                $visitor->visitor_id = $visitor_id;
                $visitor->visited_date = "$date";
                $visitor->viewed_date = "$date";
                $visitor->status = '1';
                $visitor->ip = $_SERVER['REMOTE_ADDR'];
                $visitor->month = date("M");
                $visitor->year = date("Y");
                $visitor->day = date("d");
                $visitor->date = date("Y-m-d h:i:s");
                $visitor->v_date = date("Y-m-d h:i:s");
                $visitor->insert(); 
            }
            $cover_photo = isset($page_exist['cover_photo']) ? $page_exist['cover_photo'] : '';
            $user_tags =  Connect::getuserConnections($visitor_id); 
            $user_basicinfo = Page::find()->where(['_id' => $page_id])->one();
            $user_basicinfo['cover_photo'] = $cover_photo;
            $posts = PostForm::getUserPost($page_id);
            $likes = Like::getUserPostLike($page_id); 
            $path = 'profile/';
            $usrfrdlist = array();

            foreach($user_tags AS $ud) {
                $id = (string)$ud['userdata']['_id'];
                $fbid = isset($ud['userdata']['fb_id']) ? $ud['userdata']['fb_id'] : '';
                $dp = $this->getimage($ud['userdata']['_id'],'thumb');

                $nm = (isset($ud['userdata']['fullname']) && !empty($ud['userdata']['fullname'])) ? $ud['userdata']['fullname'] : $ud['userdata']['fname'].' '.$ud['userdata']['lname'];
                $usrfrdlist[] = array('id' => $id, 'fbid' => $fbid, 'name' => $nm, 'text' => $nm, 'thumb' => $dp);
            }
            
            return $this->render('details',array('user_basicinfo' => $user_basicinfo, 'posts' => $posts, 'likes' => $likes, 'usrfrdlist' => $usrfrdlist));
        }
    }

    public function actionIndexnew()
    { 
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        $page_id = $request->get('id');
        $visitor_id = (string)$session->get('user_id');
		
		if($visitor_id == '') {
			$visitor_id = LoginForm::find()->select(['_id'])->limit(1)->offset(0)->all();
			$visitor_id = $visitor_id[0]['_id'];
		}
		 
        $page_exist = Page::Pagedetails($page_id);
        
		if(!$page_exist) {
			return $this->goHome();
		} else if($page_exist['is_deleted']=='3') {
			return $this->goHome();
		} else if(($visitor_id!=$page_exist['created_by']) && $page_exist['is_deleted']=='0') {
			return $this->goHome();
		} else if(strstr($page_exist['blk_block_list'],(string)$visitor_id)) {
			return $this->goHome();
		} else {
			$visitors = PageVisitor::find()->select(['_id'])->with('user')->where(['page_id' => "$page_id",'visitor_id' => $visitor_id,'year' => date("Y"),'month' => date("M")])->one();
			$date = time();
			if($visitors) {
				$visitors->visited_date = "$date";
				$visitors->month = date("M");
				$visitors->year = date("Y");
				$visitors->day = date("d");
				$visitors->date = date("Y-m-d h:i:s");
				$visitors->update();
			} else {
				$visitor = new PageVisitor();
				$visitor->page_id = $page_id;
				$visitor->visitor_id = $visitor_id;
				$visitor->visited_date = "$date";
				$visitor->viewed_date = "$date";
				$visitor->status = '1';
				$visitor->ip = $_SERVER['REMOTE_ADDR'];
				$visitor->month = date("M");
				$visitor->year = date("Y");
				$visitor->day = date("d");
				$visitor->date = date("Y-m-d h:i:s");
				$visitor->v_date = date("Y-m-d h:i:s");
				$visitor->insert(); 
			}
            $cover_photo = isset($page_exist['cover_photo']) ? $page_exist['cover_photo'] : '';
            $user_tags =  Connect::getuserConnections($visitor_id); 
            $user_basicinfo = Page::find()->where(['_id' => $page_id])->one();
            $user_basicinfo['cover_photo'] = $cover_photo;
            $posts = PostForm::getUserPost($page_id);
            $likes = Like::getUserPostLike($page_id); 
            $path = 'profile/';
            $usrfrdlist = array();

            foreach($user_tags AS $ud) {
                $id = (string)$ud['userdata']['_id'];
                $fbid = isset($ud['userdata']['fb_id']) ? $ud['userdata']['fb_id'] : '';
                $dp = $this->getimage($ud['userdata']['_id'],'thumb');

                $nm = (isset($ud['userdata']['fullname']) && !empty($ud['userdata']['fullname'])) ? $ud['userdata']['fullname'] : $ud['userdata']['fname'].' '.$ud['userdata']['lname'];
                $usrfrdlist[] = array('id' => $id, 'fbid' => $fbid, 'name' => $nm, 'text' => $nm, 'thumb' => $dp);
            }
            
            return $this->render('index',array('user_basicinfo' => $user_basicinfo, 'posts' => $posts, 'likes' => $likes, 'usrfrdlist' => $usrfrdlist));
        }      
    }
    
    public function getUser()
    {
        return $this->hasOne(LoginForm::className(), ['_id' => 'post_user_id']);
    }

    public function actionCreatepage()
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
		$data = array();
        $pageexist = false;
		if(isset($userid) && $userid != '') {
    		$authstatus = UserForm::isUserExistByUid($userid);
    		if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
    			return $authstatus;
    		} else {
    			$date = time();
                $page_cat = $_POST['pageCatDrop'];
                $page_name = $_POST['pagename'];
                $short_desc = $_POST['pageshort'];
                $desc = $_POST['pagedesc'];
                $site = $_POST['pagesite'];
                $bustown = $_POST['bustown'];
                //$busaddress = $_POST['busaddress'];
                $busemail = $_POST['busemail'];
                $pageid = $_POST['pageid'];

                if($pageid != '') {
                    $page = Page::find()->select(['_id'])->where(['page_id' => "$pageid"])->one();    
                    if(!empty($page)) {
                        $pageexist = true;
                    } else {
                        $page = new Page();
                    }
                } else {
                    $page = new Page();
                }

                $page->category = $page_cat; 
                $page->page_name = ucfirst(strtolower($page_name));
                $page->short_desc = ucfirst(strtolower($short_desc));
                $page->desc = ucfirst($desc);
                $page->site = $site;
                $page->fullname = ucfirst(strtolower($page_name));
                $page->city = $bustown;
                $page->email = $busemail;
                $page->is_deleted = '2';
                $page->created_date = "$date";
                $page->updated_date = "$date";
                $page->created_by = $userid;
                $page->updated_by = $userid;
                $page->pgendorse = '0';
                $page->pgmail = '0';
                $page->gen_post_review = 'off';
                $page->gen_photos_review = 'off';
                $page->gen_post = 'denyPost';
                $page->gen_photos = 'denyPhotos';
                $page->gen_reviews = 'on';
                $page->not_add_post = 'off';
                $page->not_add_comment = 'on';
                $page->not_like_page = 'on';
                $page->not_like_post = 'on';
                $page->not_post_edited = 'off';
                $page->not_get_review = 'on';
                $page->not_msg_rcv = 'on';
                $page->msg_use_key = 'off';
                $page->send_instant = 'off';
                $page->send_instant_msg = 'Hi, thank you for contacting us.We\'ll reply back to you as soon as possible.';
                $page->show_greeting = 'off';
                $page->show_greeting_msg = 'Hi, thank you for contacting us on messanger,Please send us any queries you may have.We\'ll be glad to help you with your query.';
                $page->created_from_ip = $_SERVER['REMOTE_ADDR'];
                $page->updated_from_ip = $_SERVER['REMOTE_ADDR'];
                $page->page_id = "$pageid";
                
                if($pageexist) {
                    $page->update();
                    $last_insert_id = (string)$pageid;
                } else {
                    $page->insert();
                    $last_insert_id = (array)$page->_id;
                    $last_insert_id = array_values($last_insert_id);
                    $last_insert_id = $last_insert_id[0];
                    $add_page_role = new PageRoles();
                    $add_page_role->user_id = "$userid";
                    $add_page_role->page_id = "$pageid";
                    $add_page_role->created_date = "$date";
                    $add_page_role->added_by = "$userid";
                    $add_page_role->pagerole = "Admin";
                    $add_page_role->insert();
                }

                $data['msg'] = 'success';
                $data['page_id'] = $last_insert_id;

                return json_encode($data, true);
           
    		}
        } else {
        	return  'checkuserauthclassg';
        }
    }
    
    public function actionUpdatebusinessdetails()
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');

        if($session->get('user_id'))
        {
            $data = array();
			$date = time();
            $busaddress = $_POST['busaddress'];
            $bustown = $_POST['bustown'];
            $buscode = $_POST['buscode'];
            $busemail = $_POST['busemail'];
            $busphone = $_POST['busphone'];
            $busstart = $_POST['busstart'];
            $bustype = $_POST['bustype'];
            $busconcode = $_POST['country_code'];

            $pageid = $_POST['pageid'];
            $page = Page::find()->select(['_id'])->where(['page_id' => "$pageid"])->one();
 
            $page->address = ucfirst($busaddress);
            $page->city = $bustown;
            $page->postal_code = $buscode;
            $page->email = $busemail;
            $page->phone = $busphone;
            $page->start_date = $busstart;
            $page->start_type = $bustype;
            $page->is_deleted = '0';
            $page->country_code = $busconcode;
            if($page->update())
            {
                $data['msg'] = 'success';
                $data['page_id'] = "$pageid";
            }
            else
            {
                $data['msg'] = 'fail';
            }
            return json_encode($data);
        }
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionAllpages()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		$page = new Page();
		$allpages = Page :: getAllPages();
     	$baseUrl = (string) $_POST['baseUrl'];
		return $this->render('allpages',array('user_id' => $user_id,'allpages' => $allpages,'baseUrl' => $baseUrl));		
    }
    
    public function actionLikedpages()
    {
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		
		$page = new Page();
		$allpageslikes = Page::getMyLikesPages($user_id);

		$baseUrl = (string) $_POST['baseUrl'];
		return $this->render('likedpages',array('user_id' => $user_id,'allpageslikes' => $allpageslikes,'baseUrl' => $baseUrl));		
    }
    
    public function actionMypages()
    {
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		
		$page = new Page();
		$mypages = Page::getMyPages($user_id);
		$baseUrl = (string) $_POST['baseUrl'];
		return $this->render('mypages',array('user_id' => $user_id,'mypages' => $mypages,'baseUrl' => $baseUrl));		
	}

    public function actionAddpage()
    {
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		if($user_id)
		{
			$baseUrl = (string) $_POST['baseUrl'];
			$model = new BusinessCategory();
			return $this->render('addpage',array('user_id' => $user_id,'model' => $model,'baseUrl' => $baseUrl));
		}
        else {
            return $this->goHome();
        }
    }

    public function actionPromotepages()
    {
        $session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		if($user_id)
		{
			$page = new Page();
			$promopages = Page::getPromoPages();
			$baseUrl = (string) $_POST['baseUrl'];
			return $this->render('promotedpages',array('user_id' => $user_id,'promopages' => $promopages,'baseUrl' => $baseUrl));
		}
        else {
            return $this->goHome();
        }
    }
    
    public function actionLikePage()
    { 
        $session = Yii::$app->session;
        $post_id = (string)$_POST['page_id'];
        $uid = (string)$session->get('user_id');
        $data = array();
		
		if(isset($uid) && $uid != '') {
		$authstatus = UserForm::isUserExistByUid($uid);
		if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
			return $authstatus;
		} else {

        $like = new Like(); 
        $date = time();
        $ldate = date('Y-m-d h:i:s');
        $already_like = Like::find()->select(['_id','status'])->where(['post_id' => $post_id , 'user_id' => $uid])->one();

        $status = $already_like['status'];
        if($status == '1'){$status = '0';$nstatus = '1';}else{$status = '1';$nstatus = '0';}
        
        if(!empty($already_like))
        {
            $already_like->status = $status;
            $already_like->updated_date = "$date";
            $already_like->liked_modified = "$ldate";
            $already_like->update();
            $data['status'] = $status;
            $data['msg'] = 'updated';
            $last_insert_id = "$already_like->_id";
        }
        else
        {
            $like->post_id = $post_id;
            $like->user_id = $uid;
            $like->like_type = 'page';
            $like->status = '1';
            $like->created_date = "$date";
            $like->updated_date = "$date";
            $like->liked_on = "$ldate";
            $like->liked_modified = "$ldate";
            $like->insert();
            $data['status'] = $status;
            $data['msg'] = 'inserted';
            $last_insert_id = "$like->_id";
            $page_details = Page::Pagedetails($post_id);
            $pg_owner = (string)$page_details['created_by'];
            if($pg_owner != $uid)
            {
                $cre_amt = 1;
                $cre_desc = 'pagelike';
                $status = $status;
                $details = (string)$post_id;
                $credit = new Credits();
                $credit = $credit->addcredits($uid,$cre_amt,$cre_desc,$status,$details);
            }
        }
        if(isset($last_insert_id))
        {
            $page_details = Page::Pagedetails($post_id);
            $likenotexist = Notification::find()->select(['_id'])->where(['user_id' => $uid ,'post_owner_id' => $page_details['created_by'] ,'post_id' => $post_id ,'notification_type' => 'likepage'])->one();
            if($likenotexist)
            {
                $notification = Notification::find()->where(['user_id' => $uid ,'post_owner_id' => $page_details['created_by'] ,'post_id' => $post_id ,'notification_type' => 'likepage'])->one();
            }
            else
            {
                $notification =  new Notification();
            }
            $notification->like_id = $last_insert_id;
            $notification->user_id = $uid;
            $notification->post_id = $post_id;
            $notification->notification_type = 'likepage';
            $notification->is_deleted = "$nstatus";
            $notification->status = '1'; 
            $notification->created_date = "$date";
            $notification->updated_date = "$date";
            $notification->post_owner_id = $page_details['created_by'];
            if($page_details['created_by'] != $uid && $page_details['not_like_page'] == 'on')
            {
                if($likenotexist)
                {
                    $notification->update();
                }
                else
                {
                    $notification->insert();
                }
            }
        }

        $likes = Like::find()->select(['_id'])->where(['post_id' => $post_id ,'status' => '1'])->all();

        $like_names = Like::getLikePageUserNames((string)$post_id);
        $like_buddies = Like::getLikeUser((string)$post_id);
        $data['display_ctr'] = $like_names['count'];
		$data['names'] =  $like_names['names'];
        $data['fname'] = ucfirst($like_names['login_user_details']['user']['fname']).' '.ucfirst($like_names['login_user_details']['user']['lname']);
        $buddies = '';
        foreach($like_buddies AS $like_buddy)
        {
           $buddies .= ucfirst($like_buddy['user']['fname']). ' '.ucfirst($like_buddy['user']['lname']).'<br>' ;
        }
        $data['buddies'] = $buddies;
        $data['like_count'] = count($likes);
        return json_encode($data);
		}
        } else {
        	return 'checkuserauthclassg';
        }
    }
    
    public function actionDeletePage()
    { 
        $session = Yii::$app->session;
        $page_id = (string)$_POST['page_id'];
        $uid = (string)$session->get('user_id');
        $delete = Page::find()->select(['_id'])->where(['page_id' => $page_id])->one();
        $delete->is_deleted = '3';
        if($delete->update())
        {
            Like::deleteAll(['like_type' => 'page','post_id' => $page_id]);

            Notification::deleteAll(['notification_type' => 'likepage','post_id' => $page_id]);
            Notification::deleteAll(['notification_type' => 'pageinvite','post_id' => $page_id]);
            Notification::deleteAll(['notification_type' => 'pageinvitereview','post_id' => $page_id]);
            Notification::deleteAll(['notification_type' => 'pagereview','page_id' => $page_id]);

            PageVisitor::deleteAll(['page_id' => $page_id]);

            PostForm::updateAll(['is_deleted' => '1'], ['is_page_review' => '1','post_user_id' => $page_id]);
            PostForm::updateAll(['is_deleted' => '1'], ['is_page_review' => '1','page_id' => $page_id]);

            PageEndorse::deleteAll(['page_id' => $page_id]);

            PageRoles::deleteAll(['page_id' => $page_id]);

            $date = $delete['created_date'];
            $page_owner_id = $delete['created_by'];
            $add_page_role = new PageRoles();
            $add_page_role->user_id = "$page_owner_id";
            $add_page_role->page_id = "$page_id";
            $add_page_role->created_date = "$date";
            $add_page_role->added_by = "$page_owner_id";
            $add_page_role->pagerole = "Admin";
            $add_page_role->insert();

            return true;
        }
        else
        {
            return false;
        }
    }
	
	public function actionFlagPage()
    { 
        $session = Yii::$app->session;
        $page_id = (string)$_POST['page_id'];
        $page_owner_id = (string)$_POST['p_uid'];
        $user_id = (string)$session->get('uid');
        $desc = $_POST['desc'];
        $delete = new Page();
        $delete = Page::find()->select(['_id'])->where(['page_id' => "$page_id"])->one();
        $delete->is_deleted = '2';
        $delete->update();
		
		/* Insert Notification For The Owner of Collection For Flagging*/
			
		$date = time(); 
		$notification =  new Notification();
		$notification->post_id = "$page_id";
		$notification->user_id = "$page_owner_id";
		$notification->notification_type = 'deletepageadmin';
		$notification->is_deleted = '0';
		$notification->status = '0';
		$notification->created_date = "$date";
		$notification->updated_date = "$date";
		$notification->flag_reason = "$desc";
		$notification->insert();
        
		return true;
    }
	
	
    public function actionDirectsetcover()
    { 
        $model = new \frontend\models\Page();
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');

        if(strstr($_SERVER['HTTP_REFERER'],'r=page')) {
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $pageid = $url[1];
            if($pageid != '') {
                $update = Page::find()->where([(string)'_id' => $pageid, 'created_by' => $uid])->one();
                if(!empty($update)) {
                    if(isset($_POST['$imgSrc']) && $_POST['$imgSrc'] != '') {
                        $rawImageString = $_POST['$imgSrc'];
                        $rawImageString = basename($rawImageString);
                        $rawImageString = str_replace("thumb_","", $rawImageString);
                        $update->cover_photo = $rawImageString;

                        $update->update();        
                        return true;
                    }
                }
            }
        }
    }  

    public function actionPageImageCrop()
    { 
        $model = new \frontend\models\Page();
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $date = time();
        
        $lastid = Page::find()->select(['_id','page_id'])->where(['is_deleted' => '2', 'created_by' => $uid])->orderBy(['created_date'=>SORT_DESC])->one();
        if(!empty($lastid)) {
            $pageid = (string)$lastid['_id'];
    		$update = Page::find()->where([(string)'_id' => $pageid])->one();

            $dt = time();
            $fnm = $update->_id.'_'.$dt;

            if(isset($_POST['images']) && $_POST['images'] != '') {
                $rawImageString = $_POST['images'];
                $filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $rawImageString));
                $imageName = $fnm.'.png';
                
 
                if(file_put_contents("uploads/cover/".$imageName, $filterString)) {
                    if(file_exists('uploads/cover/'.$update['cover_photo'])) {
                        unlink('uploads/cover/'.$update['cover_photo']);
                    }
                    $update->cover_photo = $imageName;
                }
                $update->update();        
                
                $cover = new Cover();
                $cover->cover_image = $imageName;
                $cover->created_at = $date;
                $cover->save();
                
                return true;
            }
        }
    }  

    public function actionPageImageCrop2()
    { 
        $model = new \frontend\models\Page();
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        
        $lastid = Page::find()->select(['_id','page_id'])->where(['is_deleted' => '2', 'created_by' => $uid])->orderBy(['created_date'=>SORT_DESC])->one();
        if(!empty($lastid)) {
            $pageid = (string)$lastid['_id'];
            $update = Page::find()->where([(string)'_id' => (string)$pageid])->one();

            $dt = time();
            $fnm = $update->_id.'_'.$dt;

            if(isset($_POST['file']) && $_POST['file'] != '') {
                $rawImageString = $_POST['file'];
                $filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $rawImageString));
                $imageName = $fnm.'.png';
 
                if(file_put_contents("profile/".$imageName, $filterString)) {
                    if(isset($update['thumbnail'])) {
                        if(file_exists('profile/'.$update['thumbnail'])) {
                            unlink('profile/'.$update['thumbnail']);
                        }
                        if(file_exists('profile/ORI_'.$update['thumbnail'])) {
                            unlink('profile/ORI_'.$update['thumbnail']);
                        }
                    }
                    $update->page_photo = 'ORI_'.$imageName;
                    $update->page_thumb = $imageName;
                }
                $update->update();        
                $response = Array(
                    "status" => 'success',
                    "url" => 'profile/'.$imageName
                );                            
                return json_encode($response);
            }
        }
    }

    public function actionPagecoverupload()
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');

        if($userid) {
            if(strstr($_SERVER['HTTP_REFERER'],'r=page')) {
                $url = $_SERVER['HTTP_REFERER'];
                $urls = explode('&',$url);
                $url = explode('=',$urls[1]);
                $page_id = $url[1];
                if($page_id) {
                    $rand = $userid .'_'.time();
                    $date =time();
                    $update = Page::find()->where([(string)'_id' => (string)$page_id])->one();
                    if(!empty($update)) {    
                        if(isset($_POST['images']) && $_POST['images'] != '' &&  $_POST['images'] != 'undefined') {
                            $rawImageString = $_POST['images'];
                            $filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $rawImageString));
                            $imageName = $rand.'.png';


                            $filepath = "uploads/cover/";
                            if(file_put_contents($filepath.$imageName, $filterString)) {
                                if(file_put_contents($filepath.'thumbs/thumb_'.$imageName, $filterString)) {
                                    $update->cover_photo = $imageName;
                                    $update->update();

                                    $cover = new Cover();
                                    $cover->cover_image = $imageName;
                                    $cover->created_at = $date;
                                    $cover->save();
                                }
                            }
                            $date =time();
                            $post =new PostForm();
                            $post->post_status ='1';
                            $post->post_type ='image';
                            $post->is_deleted ='0';
                            $post->post_privacy ='Public';
                            $post->image = $imageName;
                            $post->post_created_date ="$date";
                            $post->post_user_id =(string) $userid;
                            if(isset($page_owner) && !empty($page_owner)) {
                                $post->page_owner = $page_owner;
                            }
                            $post->is_coverpic = '1';
                            $post->insert();
                            return true;
                        }
                    }
                }
            }
        }
    }

    public function actionPageprofileupload()
    { 
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');

        if($uid) {
            if(strstr($_SERVER['HTTP_REFERER'],'r=page')) {
                $url = $_SERVER['HTTP_REFERER'];
                $urls = explode('&',$url);
                $url = explode('=',$urls[1]);
                $page_id = $url[1];
                if($page_id) {
                    $update = Page::find()->where([(string)'_id' => (string)$page_id])->one();
                    $dt = time();
                    $fnm = $update->_id.'_'.$dt;
                    if(isset($_POST['file']) && $_POST['file'] != '') {
                        $rawImageString = $_POST['file'];
                        $filterString = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $rawImageString));
                        $imageName = $fnm.'.png';
         
                        if(file_put_contents("profile/".$imageName, $filterString)) {
                            if(isset($update['thumbnail'])) {
                                if(file_exists('profile/'.$update['thumbnail'])) {
                                    unlink('profile/'.$update['thumbnail']);
                                }
                                if(file_exists('profile/ORI_'.$update['thumbnail'])) {
                                    unlink('profile/ORI_'.$update['thumbnail']);
                                }
                            }
                            $update->page_photo = 'ORI_'.$imageName;
                            $update->page_thumb = $imageName;
                        }
                        $update->update();        
                        $response = Array(
                            "status" => 'success',
                            "url" => 'profile/'.$imageName
                        );                            
                        return json_encode($response, true);
                    }    
                }
            }
        }
    }     
    
    public function actionVerifyemail() 
    {
        if (isset($_POST['verifyemailid']) && !empty($_POST['verifyemailid']))
        {
            $verifyemail = $_POST['verifyemailid'];
            $pageid = $_POST['pageid'];

            $page = Page::find()->select(['_id'])->where([(string)'_id' => (string)$pageid])->one();
            $bpage = $page['page_name'];

            $page->company_email = $verifyemail;
            $page->update();
            
            $data = array();
            $encrypt = strrev(base64_encode($pageid));
            $resetlink = "http://iaminjapan.com/frontend/web/index.php?r=site/verifypage&encpage=$encrypt";
            try {
    			$test = Yii::$app->mailer->compose()
    				->setFrom(array('csupport@iaminjapan.com' => 'Iaminjapan Page'))
    				->setTo($verifyemail)
    				->setSubject('Verify your page')
    				->setHtmlBody('<html><head><meta charset="utf-8" /><title>I am in Japan</title></head><body style="margin:0;padding:0;background:#dfdfdf;"><div style="color: #353535; float:left; font-size: 13px;width:100%; font-family:Arial, Helvetica, sans-serif;text-align:center;padding:40px 0 0;"><div style="width:600px;display:inline-block;"><img src="http://iaminjapan.com/frontend/web/images/black-logo.png" style="margin:0 0 10px;width:130px;float:left;"/><div style="clear:both"></div><div style="border:1px solid #ddd;margin:0 0 10px;"><div style="background:#fff;padding:20px;border-top:10px solid #333;text-align:left;"><div style="color: #333;font-size: 13px;margin: 0 0 20px;">Hi</div><div style="color: #333;font-size: 13px;">You recently created a page.</div> <div style="color: #333;font-size: 13px;margin: 0 0 20px;">To activate ' . $bpage . ' page, click <a href="' . $resetlink . '" target="_blank">here</a> or paste the following link into your browser: <br/><br/><a href="' . $resetlink . '" target="_blank">' . $resetlink . '</a></div><div style="color: #333;font-size: 13px;">Thank you for using Iaminjapan!</div><div style="color: #333;font-size: 13px;">The Iaminjapan Team</div></div></div><div style="clear:both"></div> <div style="width:600px;display:inline-block;font-size:11px;"><div style="color: #777;text-align: left;">&copy;  www.iaminjapan.com All rights reserved.</div><div style="text-align: left;width: 100%;margin:5px  0 0;color:#777;">For support, you can reach us directly at <a href="csupport@iaminjapan.com" style="color:#4083BF">csupport@iaminjapan.com</a></div></div></div></div></body></html>')
    			->send();
            } catch (ErrorException $e) {
                return $e->getMessage();
            }

            return true;
        }
        else{
            return false;
        }
    }
    
    public function actionSearchPages()
    {
        $baseUrl = $_GET['baseurl'];
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $page = new Page();
        $key = $_GET['key'];
        if (isset($_GET['key']) && !empty($_GET['key']))
        {
            $allpages = Page::find()->select(['_id','page_id','page_name','category'])->where(['like','category',$key])->orwhere(['like','page_name',$key])->andwhere(['is_deleted'=>'1'])->orwhere(['is_deleted' => '0','created_by' => $user_id])->orderBy(['created_date'=>SORT_DESC])->all();
        }
        else {
            $allpages = Page::getAllPages();
        }

        if(count($allpages) > 0){ ?>
			<ul>	
			<?php foreach($allpages as $allpage)
			{
				$pageid = (string)$allpage['page_id'];
				$pagelink = Url::to(['page/index', 'id' => "$pageid"]);
				$like_count = Like::getLikeCount($pageid);
				$like_names = Like::getLikeUserNames($pageid);
				$like_buddies = Like::getLikeUser($pageid);
				$newlike_buddies = array();
				$start = 0;
				foreach($like_buddies as $like_buddy) {
					if($start < 3){
					$lid = $like_buddy['user']['_id'];
					$id = Url::to(['userwall/index', 'id' => "$lid"]);
					if($user_id == (string)$lid)
					{
						$name = 'You';
					}
					else
					{
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
				$page_img = $this->getpageimage($pageid);
				$pagelikeids = Page::getpagenameLikes($pageid);
				?>
				<li>
					<div class="lcontent-holder">
						<div class="photo-holder">
							<a href="<?=$pagelink?>"><img src="<?=$page_img?>"/></a>
						</div>
						<div class="content-holder">
							<h4><a href="<?=$pagelink?>"><?=$allpage['page_name']?></a><span> | <?=$allpage['category']?></span></h4>
							<div class="icon-line">
								<i class="zmdi zmdi-thumb-up"></i>
								<span class="liketitle_<?=$pageid?>">
									<?php if($like_count > 0){
										if($like_count > 3 )
										{
											$val = $like_count - 3; 
											$counter = $val.' others'; 
											$counter = ' and <a href="javascript:void(0)">'.$counter.'</a>';
										}
										else {$counter = '';}
									?>
									<?=$newlike_buddies?><?=$counter?> liked this page
									<?php } else { ?>Become a first to like this page<?php } ?>
								</span>
							</div>
							<div class="icon-line">
								<i class="mdi mdi-accounts"></i>
								<?=$pagelikeids?>
							</div>
							<div class="icon-line">
								<i class="zmdi zmdi-thumb-up"></i>
								<span class="likecount_<?=$pageid?>">
									<?php if($like_count > 0){ ?><?=$like_count?> liked this page
									<?php } else { ?>Become a first to like this page<?php } ?>
								</span>
							</div>

							<div class="action-btns">														
								<a class="btn btn-primary waves-effect" href="javascript:void(0)" onclick="pageLike('<?=$pageid?>');"><i class="zmdi zmdi-thumb-up"></i> <span class="likestatus_<?=$pageid?>"><?=$likestatus?></span></a>
							</div>
						</div>
					</div>
				</li>
			<?php } ?>
			</ul>
    <?php } else { ?>
        <?php $this->getnolistfound('nopagefound');?>
    <?php }
    }
    
    public function actionSendinvite()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $connect_id = (string)$_POST['fid'];
        $page_id = (string)$_POST['pid'];
        $date = time();
		
        $notification =  new Notification();
        $notification->from_connect_id = "$connect_id";
        $notification->user_id = "$user_id";
        $notification->post_id = "$page_id";
        $notification->notification_type = 'pageinvite';
        $notification->is_deleted = '0';
        $notification->status = '1';
        $notification->created_date = "$date";
        $notification->updated_date = "$date";
        if($notification->insert()){
            return true;
        }
        else {
            return false;
        }
    }
    
    public function actionSendinvitereview()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		
		if(isset($user_id) && $user_id != '') {
			$authstatus = UserForm::isUserExistByUid($user_id);
			if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
				return $authstatus;
			} else {
		
			$connect_id = (string)$_POST['fid'];
			$page_id = (string)$_POST['pid'];
			$date = time();

			$notification =  new Notification();
			$notification->from_connect_id = "$connect_id";
			$notification->user_id = "$user_id";
			$notification->post_id = "$page_id";
			$notification->notification_type = 'pageinvitereview';
			$notification->is_deleted = '0';
			$notification->status = '1';
			$notification->created_date = "$date";
			$notification->updated_date = "$date";
			if($notification->insert())
			{
				return true;
			}
			else {
				return false;
			}
		}
        } else {
        	return 'checkuserauthclassg';
        }
    }
    
    public function actionInviteConnectionsReview()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		if($user_id)
		{
			$key = $_GET['key'];
			if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
			{
				$url = $_SERVER['HTTP_REFERER'];
				$urls = explode('&',$url);
				$url = explode('=',$urls[1]);
				$page_id = $url[1];
			}
			if (isset($_GET['key']) && !empty($_GET['key']))
			{
				$eml_id = LoginForm::find()->where(['like','fname',$key])->orwhere(['like','lname',$key])->orwhere(['like','fullname',$key])->andwhere(['status'=>'1'])->all();
			}
			else
			{
				$eml_id = Page::getConnectList($page_id);
			}
			return $this->render('connections_reviews',array('user_id' => $user_id,'page_id' => $page_id,'eml_id' => $eml_id));
		}
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionInviteConnections()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		if($user_id)
		{
			$key = $_GET['key'];
			if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
			{
				$url = $_SERVER['HTTP_REFERER'];
				$urls = explode('&',$url);
				$url = explode('=',$urls[1]);
				$page_id = $url[1];
			}
			if (isset($_GET['key']) && !empty($_GET['key']))
			{
				$eml_id = LoginForm::find()->where(['like','fname',$key])->orwhere(['like','lname',$key])->orwhere(['like','fullname',$key])->andwhere(['status'=>'1'])->all();
			}
			else
			{
				$eml_id = Page::getConnectList($page_id);
			}
			return $this->render('invite_reviews',array('user_id' => $user_id,'page_id' => $page_id,'eml_id' => $eml_id));
		}
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionPageupdate()
    {
        $buscat = $_POST['buscat'];
        $about = ucfirst($_POST['about']);
        $email = $_POST['email'];
        $website = $_POST['website'];
        $pageid = $_POST['pageid'];

        $page_details = new Page();
        $page_details = Page::Pagedetails($pageid);
        if($page_details)
        {
            $data = array();
			$date = time();            
            $page_details->category = $buscat;
            $page_details->short_desc = $about;
            $page_details->email = $email;
            $page_details->site = $website;
            $page_details->updated_date = "$date";
            if($page_details->update())
            {
                $page_details = Page::Pagedetails($pageid);
                $buscat = $page_details['category'];

                if(isset($page_details['short_desc']) && !empty($page_details['short_desc']))
                {
                    $busdes = $page_details['short_desc'];
                }
                else
                {
                    $busdes = 'Not added';
                }

                if(isset($page_details['email']) && !empty($page_details['email']))
                {
                    $busemail = $page_details['email'];
                    $linktext = 'mailto:'.$busemail;
                }
                else
                {
                    $busemail = 'Not added';
                    $linktext = 'javascript:void(0)';
                }

                if(isset($page_details['site']) && !empty($page_details['site']))
                {
                    $bussite = $link = $page_details['site'];
                    if(substr( $link, 0, 4 ) === "http")
                    {
                        $link = $link;
                    }
                    else
                    {
                        $link = 'http://'.$link;
                    }
                }
                else
                {
                    $bussite = 'Not added';
                    $link = 'javascript:void(0)';
                }

                $data['buscat'] = $buscat;
                $data['shrtdesc'] = $busdes;
                $data['email'] = $busemail;
                $data['emaillink'] = $linktext;
                $data['site'] = $bussite;
                $data['sitelink'] = $link;
                $data['updatestatus'] = 'Success';
                return json_encode($data);
            }
            else
            {
                $data['updatestatus'] = 'Fail';
                return json_encode($data);
            }

        }
    }
    
    public function actionPageupdateabout()
    {
        $pagename = $_POST['pagename'];
        $buscat = $_POST['buscat'];
        $about = ucfirst($_POST['pageshort']);
        $busaddress = $_POST['busaddress'];
        $bustown = $_POST['bustown'];
        $buscode = $_POST['buscode'];
        $country_code = $_POST['country_code'];
        $email = $_POST['email'];
        $website = $_POST['website'];
        $busphone = $_POST['busphone'];
        $pageid = $_POST['pageid'];

        $page_details = Page::Pagedetails($pageid);
        if($page_details)
        {
			$data = array();
			$date = time();
            
            $page_details->fullname = $pagename;
            $page_details->page_name = $pagename;
            $page_details->category = $buscat;
            $page_details->short_desc = $about;
            $page_details->address = $busaddress;
            $page_details->city = $bustown;
            $page_details->postal_code = $buscode;
            $page_details->country_code = $country_code;
            $page_details->email = $email;
            $page_details->site = $website;
            $page_details->phone = $busphone;
            $page_details->updated_date = "$date";
            if($page_details->update() )
            {
                $page_details = Page::Pagedetails($pageid);
                $busname = $page_details['page_name'];
                $buscat = $page_details['category'];
                $buscity = $page_details['city'];

                if(isset($page_details['short_desc']) && !empty($page_details['short_desc']))
                {
                    $busdes = $page_details['short_desc'];
                }
                else
                {
                    $busdes = 'Not added';
                }

                if(isset($page_details['email']) && !empty($page_details['email']))
                {
                    $busemail = $page_details['email'];
                    $linktext = 'mailto:'.$busemail;
                }
                else
                {
                    $busemail = 'Not added';
                    $linktext = 'javascript:void(0)';
                }

                if(isset($page_details['site']) && !empty($page_details['site']))
                {
                    $bussite = $link = $page_details['site'];
                    if(substr( $link, 0, 4 ) === "http")
                    {
                        $link = $link;
                    }
                    else
                    {
                        $link = 'http://'.$link;
                    }
                }
                else
                {
                    $bussite = 'Not added';
                    $link = 'javascript:void(0)';
                }
                if(isset($page_details['address']) && !empty($page_details['address']))
                {
                    $page_address = $page_details['address'];
                }
                else
                {
                    $page_address = 'Not added';
                }
                if(isset($page_details['postal_code']) && !empty($page_details['postal_code']))
                {
                    $postalcode = $page_details['postal_code'];
                }
                else
                {
                    $postalcode = 'Not added';
                }
                if(isset($page_details['phone']) && !empty($page_details['phone']))
                {
                    $busphone = $page_details['phone'];
                }
                else
                {
                    $busphone = 'Not added';
                }

                $data['busname'] = $busname;
                $data['buscat'] = $buscat;
                $data['shrtdesc'] = $busdes;
                $data['email'] = $busemail;
                $data['emaillink'] = $linktext;
                $data['address'] = $page_address;
                $data['bustown'] = $buscity;
                $data['buscode'] = $postalcode;
                $data['busphone'] = $busphone;
                $data['site'] = $bussite;
                $data['sitelink'] = $link;
                $data['updatestatus'] = 'Success';
                return json_encode($data);
            }
            else
            {
                $data['updatestatus'] = 'Fail';
                return json_encode($data);
            }

        }
    }
    
    public function actionLikesContent()
    {
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        $user_id = (string) $session->get('user_id');
		
			$page_id = (string) $_POST['id'];
			$baseUrl = (string) $_POST['baseUrl'];
			$pageposts = PostForm::find()->select(['_id'])->where(['post_id' => $page_id,'status' => '1'])->all();
			$like_count = $likes_count = Like::getLikeCount($page_id);
			$talks_count = count($pageposts) + $likes_count;
			$invitedconnect = Page::getConnectList($page_id);
			$pageuserdetails = Page::getPageLikeDetails($page_id);
			$page_details = Page::Pagedetails($page_id);
			$lastweekcount = Page::getLastWeekLikeCount($page_id);
			$currentweekcount = Page::getCurrentWeekLikeCount($page_id);
			return $this->render('likes',array('user_id' => $user_id,'baseUrl' => $baseUrl,'page_id' => $page_id,'like_count' => $like_count,'likes_count' => $likes_count,'talks_count' => $talks_count,'invitedconnect' => $invitedconnect,'pageuserdetails' => $pageuserdetails,'page_details' => $page_details,'lastweekcount' => $lastweekcount,'currentweekcount' => $currentweekcount));
		
    }
    
    public function actionMessagesContent()
    {
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        $user_id = (string) $session->get('user_id');
		if($user_id)
		{
			$page_id = (string) $_POST['id'];
			$baseUrl = (string) $_POST['baseUrl'];
			$page_details = Page::Pagedetails($page_id);
			return $this->render('messages',array('user_id' => $user_id,'baseUrl' => $baseUrl,'page_id' => $page_id,'page_details' => $page_details));
        }
		else
		{
			return $this->goHome();
		}
    }
    
    public function actionNotificationsContent()
    {
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        $user_id = (string) $session->get('user_id');
        $HideNotificationArray = array();

		if($user_id)
		{

            $page_id = (string) $_POST['id'];
            $baseUrl = (string) $_POST['baseUrl'];


            $HideNotification = HideNotification::find()->where(['user_id' => $user_id])->asarray()->one();
            if (!empty($HideNotification)) {
                $HideNotificationArray = $HideNotification['notification_ids'];
                $HideNotificationArray = explode(',', $HideNotificationArray);
                $HideNotificationArray = array_filter($HideNotificationArray);
            }

			$notificationsharepost = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'sharepost','post_owner_id'=>(string)$page_id])->andwhere(['is_deleted'=>"0"])->andwhere(['not in', (string)'_id', $HideNotificationArray])->orderBy(['created_date'=>SORT_DESC])->all();
			$notificationcomment = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'comment','page_id'=>(string)$page_id])->andwhere(['is_deleted'=>"0"])->andwhere(['not in', (string)'_id', $HideNotificationArray])->orderBy(['created_date'=>SORT_DESC])->all();
			$notificationlikepost = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'likepost','page_id'=>(string)$page_id])->andwhere(['is_deleted'=>"0"])->andwhere(['not in', (string)'_id', $HideNotificationArray])->orderBy(['created_date'=>SORT_DESC])->all();
			$notificationpagelikes = Notification::find()->with('user')->with('like')->where(['notification_type'=>'likepage','post_id'=>(string)$page_id])->andwhere(['is_deleted'=>"0"])->andwhere(['not in', (string)'_id', $HideNotificationArray])->orderBy(['created_date'=>SORT_DESC])->all();
			$notificationpagereviews = Notification::find()->with('user')->where(['notification_type'=>'pagereview','page_id'=>(string)$page_id])->andwhere(['is_deleted'=>"0"])->andwhere(['not in', (string)'_id', $HideNotificationArray])->orderBy(['created_date'=>SORT_DESC])->all();
			$notificationpagewall = Notification::find()->with('user')->with('like')->where(['notification_type'=>'onpagewall','page_id'=>(string)$page_id])->andwhere(['is_deleted'=>"0"])->andwhere(['not in', (string)'_id', $HideNotificationArray])->orderBy(['created_date'=>SORT_DESC])->all();

			$notification = array_merge_recursive($notificationsharepost,$notificationcomment,$notificationlikepost,$notificationpagelikes,$notificationpagereviews,$notificationpagewall);

			$notcount = count($notification);
			foreach ($notification as $key)
			{
				$sortkeys[] = $key["created_date"];
			}
			if($notcount)
			{
				array_multisort($sortkeys, SORT_DESC, SORT_STRING, $notification);
			}
			return $this->render('notifications',array('user_id' => $user_id,'baseUrl' => $baseUrl,'page_id' => $page_id,'notcount' => $notcount,'notification' => $notification));
		}
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionActivityContentNew() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $pageid = (string)$_POST['id'];
        
        $notificationconnectaccepted = Notification::find()->where(['notification_type'=>'connectrequestaccepted','from_connect_id'=> $pageid])->andwhere(['is_deleted'=>"0"])->asarray()->all();
        
        $notificationconnectacceptedfrd = Notification::find()->where(['notification_type'=>'connectrequestaccepted','user_id'=> $pageid])->andwhere(['is_deleted'=>"0"])->asarray()->all();
        
        $createpost = PostForm::find()->with('user')->where(['post_user_id'=> $pageid,'is_deleted'=>'0','rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'is_coverpic'=>null,'is_profilepic'=>null])->asarray()->all();

        $postIds = ArrayHelper::map(PostForm::find()->with('user')->where(['post_user_id'=> $pageid,'is_deleted'=>'0','rating'=>null,'collection_id'=>null,'trav_item'=>null,'is_trip'=>null,'is_ad'=>null,'is_coverpic'=>null,'is_profilepic'=>null])->asarray()->all(), function($data) { return (string)$data['_id']; }, 'yes');
        if(!empty($postIds)) {
            $postIds = array_keys($postIds);
        }

        $referal = Referal::find()->where(['sender_id'=> $pageid,'is_deleted'=>"1"])->asarray()->all();
            
        $travstore = array();
        
        $profilepic = PostForm::find()->where(['is_profilepic'=>"1",'is_deleted'=>"0",'post_user_id'=> $pageid])->asarray()->all();
        $likes = Like::find()->where(['user_id' => $user_id,'status' => '1'])->andwhere(['in', 'post_id', $postIds])->asarray()->all();
        $comment = Comment::find()->where(['user_id' => $user_id,'status' => '1'])->andwhere(['in', 'post_id', $postIds])->asarray()->all();

        $album = UserPhotos::find()->where(['is_deleted'=>'0','post_user_id'=> $pageid])->asarray()->all();
        
        $coverpic = PostForm::find()->where(['is_coverpic'=>"1",'is_deleted'=>"0",'post_user_id'=>$pageid])->asarray()->all();
        
        
        $activities = array_merge_recursive($notificationconnectaccepted, $notificationconnectacceptedfrd, $createpost, $likes, $referal, $album ,$comment, $profilepic, $coverpic, $travstore);
        
        $postIds = array();
        foreach ($activities as $key)
        {
            $postid = (string)$key['_id'];
            $postIds[] = $postid;

            if(isset($key["post_created_date"]))
            {
                $created_time = $key['post_created_date'];
            }
            else if(isset($key["created_at"]))
            {
                $created_time = $key['created_at'];
            }
            else
            {
                $created_time = $key["created_date"];
            }
             
            $sortkeys[] = $created_time; 
        }


        
        if(count($activities))
        {
            array_multisort($sortkeys, SORT_DESC, SORT_STRING, $activities);
        }

        return $this->render('/activity/indexnew',array('activities'=> $activities));
    }

    public function actionActivityContent() {
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        $user_id = (string) $session->get('user_id');
		if($user_id) {
			$page_id = (string) $_POST['id'];
			$baseUrl = (string) $_POST['baseUrl'];

			$notificationsharepost = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'sharepost','post_owner_id'=>(string)$page_id])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
			$notificationcomment = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'comment','page_id'=>(string)$page_id])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
			$notificationlikepost = Notification::find()->with('user')->with('post')->with('comment')->with('like')->where(['notification_type'=>'likepost','page_id'=>(string)$page_id])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
			$notificationpagelikes = Notification::find()->with('user')->with('like')->where(['notification_type'=>'likepage','post_id'=>(string)$page_id])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
			$notificationpagereviews = Notification::find()->with('user')->where(['notification_type'=>'pagereview','page_id'=>(string)$page_id])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();
			$notificationpagewall = Notification::find()->with('user')->with('like')->where(['notification_type'=>'onpagewall','page_id'=>(string)$page_id])->andwhere(['is_deleted'=>"0"])->orderBy(['created_date'=>SORT_DESC])->all();

			$notification = array_merge_recursive($notificationsharepost,$notificationcomment,$notificationlikepost,$notificationpagelikes,$notificationpagereviews,$notificationpagewall);
			$notcount = count($notification);
			foreach ($notification as $key) {
				$sortkeys[] = $key["created_date"];
			}
			if($notcount) {
				array_multisort($sortkeys, SORT_DESC, SORT_STRING, $notification);
			}
            
			return $this->render('activity',array('user_id' => $user_id,'baseUrl' => $baseUrl,'page_id' => $page_id,'notcount' => $notcount,'notification' => $notification));
		} else {
            return $this->goHome();
        }
    }
    
    public function actionReviewpostsContent()
    {
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        $user_id = (string) $session->get('user_id');  
        $page_id = (string) $_POST['id'];
        $baseUrl = (string) $_POST['baseUrl'];
        $type = (string) $_POST['type'];
        if($user_id) {
            if($type == 'posts') { 
                $reviewdposts = PostForm::find()->where(['is_deleted'=>"5",'post_user_id'=>"$page_id",'is_album'=>null,'rating'=>null,'collection_id'=>null])->orderBy(['post_created_date'=>SORT_DESC])->all();
                return $this->render('review_post',array('user_id' => $user_id,'baseUrl' => $baseUrl,'page_id' => $page_id,'type' => $type,'reviewdposts' => $reviewdposts));
            } else {
                $reviewdposts = PageReviewPhotos::find()->where(['page_id' => $page_id])->asarray()->all();
                /*$reviewdposts = PostForm::find()->where(['is_deleted'=>"5",'post_user_id'=>"$page_id",'is_album'=>'1','rating'=>null,'collection_id'=>null])->orderBy(['post_created_date'=>SORT_DESC])->all();*/

			     return $this->render('review_photos',array('user_id' => $user_id,'baseUrl' => $baseUrl,'page_id' => $page_id,'type' => $type,'reviewdposts' => $reviewdposts));
            }
        } else { 
            return $this->goHome();
        }
    }
    
    public function actionAprvreviewphoto()
    {
        
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        $user_id = (string) $session->get('user_id');

        if(isset($_POST) && !empty($_POST)) {
            $id = $_POST['aprvid'];
            $imgNm = $_POST['imgNm'];
            $imgNm = base64_decode($imgNm);
            $PageReviewPhotos = PageReviewPhotos::find()->where([(string)'_id' => $id])->one();

            if(!empty($PageReviewPhotos)) {
                $page_id = $PageReviewPhotos['page_id'];
                
                $PageDetails = Page::find()->where([(string)'_id' => $page_id])->asarray()->one();

                if(!empty($PageDetails)) {
                    $isAlbum = $PageReviewPhotos['isnewalbum'];
                    if($isAlbum) {
                        $movedId = isset($PageReviewPhotos['movedId']) ? $PageReviewPhotos['movedId'] : false;
                        $isMovedId = '';
                        if($movedId != '') {
                            $UserPhotosData = UserPhotos::find()->where([(string)'_id' => $movedId])->one();
                            $UserPhotosDataImage = $UserPhotosData['image'];
                            $UserPhotosDataImage = array_filter(explode(',', $UserPhotosDataImage));
                            $UserPhotosDataImage[] = $imgNm;
                            $UserPhotosDataImage = implode(',', $UserPhotosDataImage);
                            $UserPhotosDataImage = $UserPhotosDataImage.',';
                            $UserPhotosData->image = $UserPhotosDataImage;
                            $UserPhotosData->update();
                        } else {
                            $post_status = $PageReviewPhotos['post_status'];  
                            $album_title = $PageReviewPhotos['album_title'];  
                            $post_text = $PageReviewPhotos['post_text'];  
                            $album_place = $PageReviewPhotos['album_place'];  
                            $album_img_date = $PageReviewPhotos['album_img_date'];  
                            $post_type = $PageReviewPhotos['post_type'];  
                            $is_album = $PageReviewPhotos['is_album'];  
                            $post_privacy = $PageReviewPhotos['post_privacy'];  
                            $post_created_date = $PageReviewPhotos['post_created_date'];  
                            $is_deleted = $PageReviewPhotos['is_deleted'];  
                            $shared_by = $PageReviewPhotos['shared_by'];  
                            $pagepost = $PageReviewPhotos['pagepost'];  
                            $post_user_id = $PageReviewPhotos['post_user_id'];  
                            $post_ip = $PageReviewPhotos['post_ip'];  

                            $post = new UserPhotos();
                            $post->post_status = $post_status; 
                            $post->album_title = $album_title;
                            $post->post_text = $post_text;
                            $post->album_place = $album_place;
                            $post->album_img_date = $album_img_date;
                            $post->post_type = $post_type; 
                            $post->is_album = $is_album;
                            $post->post_privacy = $post_privacy;
                            $post->post_created_date = $post_created_date;
                            $post->is_deleted = $is_deleted;
                            $post->post_privacy = $post_privacy;
                            $post->shared_by = $shared_by; 
                            $post->pagepost = $pagepost;
                            $post->is_deleted = $is_deleted;
                            $post->post_user_id = $post_user_id;
                            $post->post_ip = $post_ip;
                            $post->image = $imgNm.',';
                            $post->insert();

                            $isMovedId = (string)$post->_id;
                        }
                    } else {
                        $album_id = $PageReviewPhotos['album_id'];
                        $UserPhotosData = UserPhotos::find()->where([(string)'_id' => $album_id])->one();
                        $UserPhotosDataImage = $UserPhotosData['image'];
                        $UserPhotosDataImage = array_filter(explode(',', $UserPhotosDataImage));
                        $UserPhotosDataImage[] = $imgNm;
                        $UserPhotosDataImage = implode(',', $UserPhotosDataImage);
                        $UserPhotosDataImage = $UserPhotosDataImage.',';
                        $UserPhotosData->image = $UserPhotosDataImage;
                        $UserPhotosData->update();
                    }

                    $images = $PageReviewPhotos['image'];
                    $images = array_filter(explode(',', $images));

                    if(!empty($images)) {
                        if(in_array($imgNm, $images)) {
                            $pos = array_search($imgNm, $images);
                            unset($images[$pos]);
                        }

                        if(empty($images)) {
                            $PageReviewPhotos->delete();
                            return true;
                        } else {
                            $images = implode(',', $images);
                            $PageReviewPhotos->image = $images;
                            if($isAlbum) {
                                if($isMovedId != '') {
                                    $PageReviewPhotos->movedId = $isMovedId;    
                                }
                            }
                            $PageReviewPhotos->update();
                            return true;
                        }
                    }
                }
            }
        }
        else
        {
            return $this->goHome();
        }
    }

    public function actionRjctreviewphoto()
    {
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        $user_id = (string) $session->get('user_id');

        if(isset($_POST) && !empty($_POST)) {
            $id = $_POST['rjctid'];
            $imgNm = $_POST['imgNm'];
            $imgNm = base64_decode($imgNm);
            $PageReviewPhotos = PageReviewPhotos::find()->where([(string)'_id' => $id])->one();
            $url = '../web';

            if(!empty($PageReviewPhotos)) {
                $page_id = $PageReviewPhotos['page_id'];
                $PageDetails = Page::find()->where([(string)'_id' => $page_id])->asarray()->one();

                if(!empty($PageDetails)) {
                    $images = $PageReviewPhotos['image'];
                    $images = array_filter(explode(',', $images));

                    if(!empty($images)) {
                        if(in_array($imgNm, $images)) {
                            $pos = array_search($imgNm, $images);
                            unset($images[$pos]);
                        }

                        if(empty($images)) {
                            $PageReviewPhotos->delete();
                            unlink($url.$imgNm);
                            return true;
                        } else {
                            $images = implode(',', $images);
                            $PageReviewPhotos->image = $images;
                            $PageReviewPhotos->update();
                            unlink($url.$imgNm);
                            return true;
                        }
                    }
                }
            }
        }
        else
        {
            return $this->goHome();
        }
    }

    public function actionReviewdPhotos()
    {
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        $user_id = (string) $session->get('user_id');
        $pid = (string) $_POST['pid'];
        $status = (string) $_POST['status'];
        if($user_id)
        {
            $postexist = PostForm::find()->select(['_id'])->where(['is_deleted'=>"5",'_id'=>"$pid"])->one();
            if($postexist)
            {
                if($status == 'approved'){$pst = '0';}
                else{$pst = '4';}
                $postexist->is_deleted = "$pst";
                if($postexist->update())
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionPromoteContent()
    {
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        $user_id = (string) $session->get('user_id');
		if($user_id)
		{
			$page_id = (string) $_POST['id'];
			$baseUrl = (string) $_POST['baseUrl'];
			$page_details = Page::Pagedetails($page_id);
			$owner_name = $this->getuserdata($page_details['created_by'],'fname');
			$cd = date('M Y');
			$ld = date('M Y', strtotime(" -11 months"));
			$dd = $ld.'-'.$cd;
			$mn = $pro = $view = $post = '';
			$d = date('Y-m-d h:i:s');
			$totalviewscount = PageVisitor::getLastYearPageVisitors($page_id,date('Y-m-d h:i:s',strtotime($d . " -1 year")));
			$totallikesscount = Page::getLastYearPageLikes($page_id,date('Y-m-d h:i:s',strtotime($d . " -1 year")));
			$totalpostcount = PostForm::find()->where(['is_deleted'=>"0",'post_user_id'=>"$page_id"])->count();
			$totallikecount = Like::find()->where(['like_type'=>"page",'status'=>"1",'post_id'=>"$page_id"])->count();
			return $this->render('promote',array('user_id' => $user_id,'baseUrl' => $baseUrl,'page_id' => $page_id,'page_details' => $page_details,'owner_name' => $owner_name,'totalviewscount' => $totalviewscount,'totallikesscount' => $totallikesscount,'totalpostcount' => $totalpostcount,'totallikecount' => $totallikecount,'cd' => $cd,'ld' => $ld,'dd' => $dd,'mn' => $mn,'pro' => $pro,'view' => $view,'post' => $post,'d' => $d));
        }
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionReviewsContent()
    {
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        $user_id = (string) $session->get('user_id');  
        $page_id = (string) $_POST['id'];
        $baseUrl = (string) $_POST['baseUrl'];
        
            $model = new Page();
            $pagereviews = $model->getPageReviews($page_id);
            $getPageReviewPerson = $model->getPageReviewPerson($page_id);
            $getPageReviewsCountPerson = $model->getPageReviewsCountPerson($page_id);
            $totalpagereviewscount = $pagereviewscount = $model->getPageReviewsCount($page_id);
            $sumpagereviewscount = $model->getPageReviewsSum($page_id);
            $fivepagereviewscount = $model->getSpecicficPageReviewsCount($page_id,'5');
            $fourpagereviewscount = $model->getSpecicficPageReviewsCount($page_id,'4');
            $threepagereviewscount = $model->getSpecicficPageReviewsCount($page_id,'3');
            $twopagereviewscount = $model->getSpecicficPageReviewsCount($page_id,'2');
            $onepagereviewscount = $model->getSpecicficPageReviewsCount($page_id,'1');
            $pagereviewslastmonthcount = $model->getPageReviewsLastMonthCount($page_id);
            $reviewdconnect = $model->getConnectList($page_id);
            if($totalpagereviewscount == 0) { $totalpagereviewscount = 1; }
            $avgreview = $sumpagereviewscount / $totalpagereviewscount;
            $pagedetails = $model->Pagedetails($page_id);
            $result_security = SecuritySetting::find()->select(['my_post_view_status'])->where(['user_id' => $user_id])->one();
            if ($result_security)
            {
                $my_post_view_status = $result_security['my_post_view_status'];
                if ($my_post_view_status == 'Private')
                {
                    $post_dropdown_class = 'lock';
                }
                else if ($my_post_view_status == 'Friends')
                {
                    $post_dropdown_class = 'user';
                }
                else
                {
                    $my_post_view_status = 'Public';
                    $post_dropdown_class = 'globe';
                }
            }
            else
            {
                $my_post_view_status = 'Public';
                $post_dropdown_class = 'globe';
            } 
			return $this->render('reviews',array('user_id'=>$user_id,'page_id'=>$page_id,'baseUrl'=>$baseUrl,'pagereviews'=>$pagereviews,'totalpagereviewscount'=>$totalpagereviewscount,'pagereviewscount'=>$pagereviewscount,'sumpagereviewscount'=>$sumpagereviewscount,'fivepagereviewscount'=>$fivepagereviewscount,'fourpagereviewscount'=>$fourpagereviewscount,'threepagereviewscount'=>$threepagereviewscount,'twopagereviewscount'=>$twopagereviewscount,'onepagereviewscount'=>$onepagereviewscount,'pagereviewslastmonthcount'=>$pagereviewslastmonthcount,'reviewdconnect'=>$reviewdconnect,'avgreview'=>$avgreview,'pagedetails'=>$pagedetails,'my_post_view_status'=>$my_post_view_status,'post_dropdown_class'=>$post_dropdown_class,'getPageReviewPerson'=>$getPageReviewPerson,'getPageReviewsCountPerson'=>$getPageReviewsCountPerson));
        
    }
    
    public function actionEndorseContent()
    {
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        $user_id = (string) $session->get('user_id'); 
		$pageid = (string) $_POST['id'];
        $baseUrl = (string) $_POST['baseUrl'];
        $date = time();
		
		$pageinfo = Page::Pagedetails($pageid);
		$pageendorselidt = PageEndorse::getExceptEndorse($pageid);
		$userimg = $this->getimage($user_id,'thumb');
		
		$defaultendorsement=array("0"=>"Customer Support");
		foreach($defaultendorsement as $x=>$x_value)
		{
			$pageendorse = $x_value;
			$page = new PageEndorse();
			$page->page_id = $pageid;
			$page->user_id = $user_id;
			$page->endorse_name = $pageendorse;
			$page->created_date = "$date";
			$page->updated_date = "$date";
			$page->is_deleted = '0';
			$page->insert();   
		}
		return $this->render('endorse',array('user_id'=>$user_id,'pageid'=>$pageid,'baseUrl'=>$baseUrl,'pageinfo'=>$pageinfo,'pageendorselidt'=>$pageendorselidt,'userimg'=>$userimg));	
    }
    
    public function actionSetendorse()
    {
        $session = Yii::$app->session;
		$userid = (string)$session->get('user_id');
        if ($session->get('email')) 
        {
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $pageid = $url[1];
            $result = Page::find()->select(['_id'])->where([(string)'_id' => (string)$pageid,'created_by' => $userid])->one();
            if($result)
            {
                $result->pgendorse = $_POST['pgendorse'];
                $result->pgmail = $_POST['pgmail'];
                $result->update();
            }
        }
        else
        {
            return $this->goHome();
        }
    }
     
    public function actionGivereview()
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        
        if(isset($userid) && $userid != '') {
        $authstatus = UserForm::isUserExistByUid($userid);
        if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
            return $authstatus;
        } 
        else {
            $post = new PostForm();
            $date = time();
            if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
            {
                $url = $_SERVER['HTTP_REFERER'];
                $urls = explode('&',$url);
                $url = explode('=',$urls[1]);
                $pageid = $url[1];
            }
            if (empty($_POST['title'])) { $_POST['title'] = ''; }

            $post->post_status = '1';
            $page_exist = Page::Pagedetails($pageid);
            $replace = explode(",",$page_exist['gen_page_filter']);
            $post->post_text = ucfirst(str_ireplace($replace, '', $_POST['test']));
            $post->post_type = 'text';
            $post->post_created_date = "$date";
            $post->post_user_id = "$userid";
            $purifier = new HtmlPurifier();
            $text = HtmlPurifier::process($_POST['test']);

            if(isset($_POST['counter'])) {
                $_POST['counter'] = $_POST['counter'];
            } else {
                $_POST['counter']=0;
            }

            if(isset($_POST['current_location']) && !empty($_POST['current_location']) && $_POST['current_location']!='undefined')
            {
                $post->currentlocation = $_POST['current_location'];
            }
            $post->post_tags = (isset($_POST['posttags']) && !empty($_POST['posttags'])) ? $_POST['posttags'] : '';
            $post->post_title = ucfirst($_POST['title']);
            $post->post_privacy = $_POST['post_privacy'];
            $post->customids = $_POST['custom'];
            $post->is_deleted = '0';
            $post->is_page_review = '1';
            if($_POST['pagereviewrate'] > 5){$_POST['pagereviewrate'] = '5';}
            else if($_POST['pagereviewrate'] < 1){$_POST['pagereviewrate'] = '1';}
            else{$_POST['pagereviewrate'] = $_POST['pagereviewrate'];}
            $post->rating = $_POST['pagereviewrate'];
            $post->page_id = $pageid;
            $post->post_ip = $_SERVER['REMOTE_ADDR'];
            $post->insert();

            $last_insert_id =  $post->_id;
            $page_details = Page::Pagedetails($pageid);

            if($_POST['post_privacy'] != 'Private' && $userid != $page_details['created_by'] && $page_details['not_get_review'] == 'on')
            {
                $notification = new Notification();
                $notification->post_id = "$last_insert_id";
                $notification->user_id = "$userid";
                $notification->notification_type = 'pagereview';
                $notification->is_deleted = '0';
                $notification->status = '1';
                $notification->created_date = "$date";
                $notification->updated_date = "$date";
                $notification->page_id = "$pageid";
                $notification->from_connect_id = $page_details['created_by'];
                $notification->entity = 'page';
                $notification->insert();
            }

            if($_POST['posttags'] != 'null')
            {
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
                    $notification = new Notification();
                    $notification->post_id = "$last_insert_id";
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


            if((string)$last_insert_id != '') {
                $post = PostForm::find()->where([(string)'_id' => (string)$last_insert_id])->one();
                if(!empty($post)) {
                    $postid = (string)$post['_id'];
                    $postownerid = (string)$post['post_user_id'];
                    $postprivacy = $post['post_privacy'];
                    $isOk = $this->filterDisplayLastPost($postid, $postownerid, $postprivacy);
                    if($isOk == 'ok2389Ko') {
                        $this->display_last_post($postid);
                    }
                }
            }
        }
        }
        else {
            return 'checkuserauthclassg';
        }
    }

    public function actionAddendorse() 
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
		
		if(isset($userid) && $userid != '') {
		$authstatus = UserForm::isUserExistByUid($userid);
		if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
			return $authstatus;
		} 
		else {
			$date = time();
            if (isset($_POST['endorse']) && !empty($_POST['endorse']))
            {
                $endorse = ucwords(strtolower($_POST['endorse']));
                $url = $_SERVER['HTTP_REFERER'];
                $urls = explode('&',$url);
                $url = explode('=',$urls[1]);
                $pageid = $url[1];

                $getpagepage = Page::find()->where(['page_id' => "$pageid"])->one();
                $pageowner = $getpagepage['created_by'];

                $endorseexist = PageEndorse::find()->where(['page_id' => "$pageid",'user_id' => "$userid",'endorse_name' => "$endorse"])->one();
                if(!$endorseexist)
                {
                    $page = new PageEndorse();
                    $page->page_id = $pageid;
                    $page->user_id = $userid;
                    $page->endorse_name = $endorse;
                    $page->created_date = "$date";
                    $page->updated_date = "$date";
                    $page->is_deleted = '1';
                    $page->insert();
                    if($getpagepage['pgmail'] == '1')
                    {
                        $this->actionSendMailEndorse($pageid,$userid,$endorse,'add');
                    }
                    return true;
                }
                else
                {
                    return false;
                }
            }
		}
		}
		else {
			return 'checkuserauthclassg';
		}
    }
    
    public function actionManageendorse() 
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
		
		if(isset($userid) && $userid != '') {
		$authstatus = UserForm::isUserExistByUid($userid);
		if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
			return $authstatus;
		} 
		else {
        
            $date = time();
            if (isset($_POST['endorse']) && !empty($_POST['endorse']))
            {
                $endorse = ucwords(strtolower($_POST['endorse']));
                $url = $_SERVER['HTTP_REFERER'];
                $urls = explode('&',$url);
                $url = explode('=',$urls[1]);
                $pageid = $url[1];

                $getpagepage = Page::find()->select(['pgmail'])->where(['page_id' => "$pageid"])->one();

                $endorseexist = PageEndorse::find()->select(['_id','is_deleted'])->where(['page_id' => "$pageid",'user_id' => "$userid",'endorse_name' => "$endorse"])->one();
                if(!$endorseexist)
                {
                    $page = new PageEndorse();
                    $page->page_id = $pageid;
                    $page->user_id = $userid;
                    $page->endorse_name = $endorse;
                    $page->created_date = "$date";
                    $page->updated_date = "$date";
                    $page->is_deleted = '1';
                    $page->insert();
                    if($getpagepage['pgmail'] == '1')
                    {
                        $this->actionSendMailEndorse($pageid,$userid,$endorse,'add');
                    }
                }
                else
                {
                    $endstatus = $endorseexist['is_deleted'];
                    if($endstatus == '1'){$endstatus = '0';}
                    else{$endstatus = '1';}
                    $endorseexist->updated_date = "$date";
                    $endorseexist->is_deleted = $endstatus;
                    $endorseexist->update();
                    if($getpagepage['pgmail'] == '1' && $endstatus == '1')
                    {
                        $this->actionSendMailEndorse($pageid,$userid,$endorse,'add');
                    }
                }
                $encount = PageEndorse::getPEcount($pageid,$endorse);
                return $encount;
            }
		}
		}
		else {
			return 'checkuserauthclassg';
		}	   
    }

    public function actionDeleteendorse() 
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        if($userid)
        {
            $date = time();
            if (isset($_POST['endorse']) && !empty($_POST['endorse']))
            {
                $endorse = ucwords(strtolower($_POST['endorse']));
                $url = $_SERVER['HTTP_REFERER'];
                $urls = explode('&',$url);
                $url = explode('=',$urls[1]);
                $pageid = $url[1];

                $getpagepage = Page::find()->select(['created_by'])->where(['page_id' => "$pageid"])->one();
                $pageowner = $getpagepage['created_by'];

                if($pageowner == $userid)
                {
                    PageEndorse::deleteAll(['page_id' => "$pageid", 'endorse_name' => "$endorse"]);
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionListuserendorse() 
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        if($userid)
        {
            $pageendorse = ucwords(strtolower($_POST['endorse']));
            $baseUrl = $_POST['baseurl'];
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $pageid = $url[1];
            $page_details = Page::Pagedetails($pageid);
            $enusers = PageEndorse::getPEallusers($pageid,$pageendorse);
              
            $assetsPath = '../../vendor/bower/travel/images/';

    ?>
            <div class="popup-title">
                <h3>People endorsed <span><?=$page_details['page_name']?></span> for <span><?=$pageendorse?></span></h3>
                <a class="popup-modal-dismiss close-popup" href="javascript:void(0)">
                    <i class="mdi mdi-close"></i>
                </a>
            </div>
            <div class="popup-content">
                <div class="content-holder nsidepad nbpad">
                    <div class="suggest-connections">
                        <div class="cbox-desc" style="overflow: hidden;" tabindex="11">	
                        <div class="connections-grid suggest-connect-list">
                        <div class="row">
                        <?php if(count($enusers) > 0){ foreach($enusers as $enuser){
                            $link = Url::to(['userwall/index', 'id' => $enuser['user_id']]);
                        ?>
                            <div class="grid-box">
                                <div class="connect-box">
                                    <div class="imgholder">
                                        <img src="<?=$this->getimage($enuser['user_id'],'thumb')?>">
                                    </div>
                                    <div class="descholder">															
                                        <a href="<?=$link?>" class="userlink"><span><?=$this->getuserdata($enuser['user_id'],'fullname')?></span></a>
                                    </div>																						
                                </div>						
                            </div>
                        <?php } } else { ?>
                        <?php $this->getnolistfound('noendorsefound');?>
                        <?php } ?>
                        </div>
                        </div>
                        </div>
                    </div>
                    <div class="btn-holder ">
                        <a href="javascript:void(0)" class="btn btn-primary waves-effect btn-sm close-popup pull-right"><i class="mdi mdi-close	"></i> Close</a>
                    </div>
                </div>
            </div>
        <?php }
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionGetnotset() 
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        if($userid)
        {
            $baseUrl = $_POST['baseUrl'];
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $pageid = $url[1];
            $page_details = Page::Pagedetails($pageid);
			return $this->render('notset',array('userid' => $userid, 'baseUrl' => $baseUrl, 'pageid' => $pageid, 'page_details' => $page_details));
		}
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionGetmsgset() 
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        if($userid)
        {
            $baseUrl = $_POST['baseUrl'];
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $pageid = $url[1];
            $page_details = Page::Pagedetails($pageid);
			return $this->render('msgset',array('userid' => $userid, 'baseUrl' => $baseUrl, 'pageid' => $pageid, 'page_details' => $page_details));			
		}
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionPageroles() 
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        if($userid)
        {
            $baseUrl = $_POST['baseUrl'];
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $pageid = $url[1];
            $page_details = Page::Pagedetails($pageid);
            $pageadmins = PageRoles::pageAdmins($pageid);
            $pageeditors = PageRoles::pageEditors($pageid);
            $pagesupporters = PageRoles::pageSupporters($pageid);
			return $this->render('pageroles',array('userid' => $userid, 'baseUrl' => $baseUrl, 'pageid' => $pageid, 'page_details' => $page_details,'pageadmins' => $pageadmins,'pageeditors' => $pageeditors,'pagesupporters' => $pagesupporters));
		}
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionSendMailEndorse($pageid,$userid,$endorse,$status)
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($userid)
        {
            try
            {
                $pagedetails = Page::Pagedetails($pageid);
                $pagename = $pagedetails['page_name'];
                $sendto = $pagedetails['company_email'];
                $pageowner = $this->getuserdata($pagedetails['created_by'],'fullname');
                $fullname = $this->getuserdata($userid,'fullname');
                $pagelink = "http://iaminjapan.com/frontend/web/index.php?r=page/index&id=$pageid";
                $userlink = "http://iaminjapan.com/frontend/web/index.php?r=userwall/index&id=$userid";
                $test = Yii::$app->mailer->compose()
                        ->setFrom(array('csupport@iaminjapan.com' => 'Iaminjapan'))
                        ->setTo($sendto)
                        ->setSubject($fullname .' has endorsed '.$endorse.' for page '.$pagename)
                        ->setHtmlBody('<html><head><meta charset="utf-8" /><title>I am in Japan</title></head><body style="margin:0;padding:0;background:#dfdfdf;"><div style="color: #353535; float:left; font-size: 13px;width:100%; font-family:Arial, Helvetica, sans-serif;text-align:center;padding:40px 0 0;"><div style="width:600px;display:inline-block;"><img src="http://iaminjapan.com/frontend/web/images/black-logo.png" style="margin:0 0 10px;width:130px;float:left;"/> <div style="clear:both"></div> <div style="border:1px solid #ddd;margin:0 0 10px;"><div style="background:#fff;padding:20px;border-top:10px solid #333;text-align:left;"><div style="color: #333;font-size: 13px;margin: 0 0 20px;">Hi '.$pageowner.'</div><div style="color: #333;font-size: 13px;margin: 0 0 20px;"><a href="' . $userlink . '" target="_blank">'.$fullname.'</a> has endorsed '.$endorse.' for your business page <a href="' . $pagelink . '" target="_blank">'.$pagename.'</a><br/><br/></div><div style="color: #333;font-size: 13px;">Thank you for using Iaminjapan!</div><div style="color: #333;font-size: 13px;">The Iaminjapan Team</div></div></div><div style="clear:both"></div><div style="width:600px;display:inline-block;font-size:11px;"><div style="color: #777;text-align: left;">&copy;  www.iaminjapan.com All rights reserved.</div><div style="text-align: left;width: 100%;margin:5px  0 0;color:#777;">For support, you can reach us directly at <a href="csupport@iaminjapan.com" style="color:#4083BF">csupport@iaminjapan.com</a></div></div></div></div></body></html>')
                        ->send();
            }
            catch (ErrorException $e)
            {
                return $e->getMessage();
            }
        }
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionPagesettings() 
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        $data = array();
        if($userid)
        {
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $pageid = $url[1];
            $page_exist = Page::Pagedetails($pageid);
            $date = time();
            if($page_exist)
            {
                if (isset($_POST['page_pub_set_val']) && !empty($_POST['page_pub_set_val']))
                {
                    $page_pub_set_val = ucwords(strtolower($_POST['page_pub_set_val']));

                    if($page_pub_set_val == 'Unpublish'){
                        $status = 0;
                    } else {
                        $status = 1;
                    }
                    $page_exist->is_deleted = "$status";
                    $data['msg'] = 'success';
                    $data['status'] = $status;
                }
                if (isset($_POST['pagepost']) && !empty($_POST['pagepost']))
                {
                    $page_exist->gen_post = $_POST['pagepost'];
                    $page_exist->gen_post_review = $_POST['pagepostreview'];
                    if($_POST['pagepost'] == 'denyPost')
                    {
                        $page_exist->gen_post_review = 'off';
                    }
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['gen_post'];
                }
                if (isset($_POST['pagephotos']) && !empty($_POST['pagephotos']))
                {
                    $page_exist->gen_photos = $_POST['pagephotos'];
                    $page_exist->gen_photos_review = $_POST['pagephotoreview'];
                    if($_POST['pagephotos'] == 'denyPhotos')
                    {
                        $page_exist->gen_photos_review = 'off';
                    }
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['gen_photos'];
                }
                if (isset($_POST['review_switch_value']) && !empty($_POST['review_switch_value']))
                {
                    if($_POST['review_switch_value']=='off'){$_POST['review_switch_value']='on';}
                    else{$_POST['review_switch_value']='off';}
                    $page_exist->gen_reviews = $_POST['review_switch_value'];
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['gen_reviews'];
                }
                if (isset($_POST['msgfltr']) && !empty($_POST['msgfltr']))
                {
                    $page_exist->gen_msg_filter = $_POST['msgfltr'];
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['gen_msg_filter'];
                }
                if (isset($_POST['pgfltr']))
                {
                    if (!strstr($_POST['pgfltr'],',') && (strlen($_POST['pgfltr'])>=1)){$_POST['pgfltr'] = $_POST['pgfltr'].',';}
                    $page_exist->gen_page_filter = $_POST['pgfltr'];
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['gen_page_filter'];
                }
                if (isset($_POST['restrictDrop']) && !empty($_POST['restrictDrop']))
                {
                    $restrictDrop = implode(',', $_POST['restrictDrop']);
                    $page_exist->blk_restrct_list = $restrictDrop;
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['blk_restrct_list'];
                }
                if (isset($_POST['blockedDrop']) && !empty($_POST['blockedDrop']))
                {
                    $blockedDrop = implode(',', $_POST['blockedDrop']);
                    $page_exist->blk_block_list = $blockedDrop;
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['blk_block_list'];
                }
                if (isset($_POST['blk_msg_filtering']) && !empty($_POST['blk_msg_filtering']))
                {
                    $blk_msg_filtering = implode(',', $_POST['blk_msg_filtering']);
                    $page_exist->blk_msg_filtering = $blk_msg_filtering;
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['blk_restrct_evnt'];
                }
                if (isset($_POST['not_add_post']) && !empty($_POST['not_add_post']))
                {
                    if($_POST['not_add_post']=='off'){$_POST['not_add_post']='on';}
                    else{$_POST['not_add_post']='off';}
                    $page_exist->not_add_post = $_POST['not_add_post'];
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['not_add_post'];
                }
                if (isset($_POST['not_add_comment']) && !empty($_POST['not_add_comment']))
                {
                    if($_POST['not_add_comment']=='off'){$_POST['not_add_comment']='on';}
                    else{$_POST['not_add_comment']='off';}
                    $page_exist->not_add_comment = $_POST['not_add_comment'];
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['not_add_comment'];
                }
                if (isset($_POST['not_like_page']) && !empty($_POST['not_like_page']))
                {
                    if($_POST['not_like_page']=='off'){$_POST['not_like_page']='on';}
                    else{$_POST['not_like_page']='off';}
                    $page_exist->not_like_page = $_POST['not_like_page'];
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['not_like_page'];
                }
                if (isset($_POST['not_like_post']) && !empty($_POST['not_like_post']))
                {
                    if($_POST['not_like_post']=='off'){$_POST['not_like_post']='on';}
                    else{$_POST['not_like_post']='off';}
                    $page_exist->not_like_post = $_POST['not_like_post'];
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['not_like_post'];
                }
                if (isset($_POST['not_post_edited']) && !empty($_POST['not_post_edited']))
                {
                    if($_POST['not_post_edited']=='off'){$_POST['not_post_edited']='on';}
                    else{$_POST['not_post_edited']='off';}
                    $page_exist->not_post_edited = $_POST['not_post_edited'];
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['not_post_edited'];
                }
                if (isset($_POST['get_new_share']) && !empty($_POST['get_new_share']))
                {
                    if($_POST['get_new_share']=='off'){$_POST['get_new_share']='on';}
                    else{$_POST['get_new_share']='off';}
                    $page_exist->get_new_share = $_POST['get_new_share'];
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['get_new_share'];
                }
                if (isset($_POST['not_get_review']) && !empty($_POST['not_get_review']))
                {
                    if($_POST['not_get_review']=='off'){$_POST['not_get_review']='on';}
                    else{$_POST['not_get_review']='off';}
                    $page_exist->not_get_review = $_POST['not_get_review'];
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['not_get_review'];
                }
                if (isset($_POST['not_msg_rcv']) && !empty($_POST['not_msg_rcv']))
                {
                    if($_POST['not_msg_rcv']=='off'){$_POST['not_msg_rcv']='on';}
                    else{$_POST['not_msg_rcv']='off';}
                    $page_exist->not_msg_rcv = $_POST['not_msg_rcv'];
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['not_msg_rcv'];
                }
                if (isset($_POST['msg_use_key']) && !empty($_POST['msg_use_key']))
                {
                    if($_POST['msg_use_key']=='off'){$_POST['msg_use_key']='on';}
                    else{$_POST['msg_use_key']='off';}
                    $page_exist->msg_use_key = $_POST['msg_use_key'];
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['msg_use_key'];
                }
                if (isset($_POST['send_instant']) && !empty($_POST['send_instant']))
                {
                    if($_POST['send_instant']=='off'){$_POST['send_instant']='on';}
                    else{$_POST['send_instant']='off';}
                    $page_exist->send_instant = $_POST['send_instant'];
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['send_instant'];
                }
                if (isset($_POST['send_instant_msg']))
                {
                    $page_exist->send_instant_msg = $_POST['send_instant_msg'];
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['send_instant_msg'];
                }
                if (isset($_POST['show_greeting']) && !empty($_POST['show_greeting']))
                {
                    if($_POST['show_greeting']=='off'){$_POST['show_greeting']='on';}
                    else{$_POST['show_greeting']='off';}
                    $page_exist->show_greeting = $_POST['show_greeting'];
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['show_greeting'];
                }
                if (isset($_POST['show_greeting_msg']))
                {
                    $page_exist->show_greeting_msg = $_POST['show_greeting_msg'];
                    $page_exist->update();
                    $data['msg'] = 'success';
                    $data['status'] = $page_exist['show_greeting_msg'];
                }
            }
            else
            {
                $data['msg'] = 'fail';
            }
            return json_encode($data);
        }
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionAddpagerole() 
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        if($userid)
        {
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $pageid = $url[1];
            $page_exist = Page::Pagedetails($pageid);
            $pageroles = array("Admin", "Editor", "Supporter");
            $date = time();
            $role = $_POST['whichRole'];
            $roleid = $_POST['roleId'];
            if($page_exist && in_array($role, $pageroles))
            {
                $fullname = $this->getuserdata($roleid,'fullname');
                $pagerole = PageRoles::getRole($roleid,$pageid);
                if(!$pagerole)
                {
                    $add_page_role = new PageRoles();
                    $add_page_role->user_id = "$roleid";
                    $add_page_role->page_id = "$pageid";
                    $add_page_role->created_date = "$date";
                    $add_page_role->added_by = "$userid";
                    $add_page_role->pagerole = "$role";
                    $add_page_role->insert();
                    $lastid = $add_page_role->_id;
                    if($lastid)
                    {
                        $img = $this->getimage($roleid,'photo');
                        $content = '<div class="pagerole-box">
                            <div class="imgholder"><img src="'.$img.'"/></div>
                            <div class="descholder">
                                <h5>'.$fullname.'</h5>
                                <div class="frow">
                                    <p>'.$role.'</p>
                                </div>
                                <a href="javascript:void(0)" class="closebtn" onclick="removePageRole(this,\''.$lastid.'\')"><i class="mdi mdi-close	"></i></a>
                            </div>
                        </div>';
                        $data['msg'] = 'success';
                        $data['status'] = $fullname.' is now '.$role;
                        $data['content'] = $content;
                        $roleexist = Notification::find()->select(['_id'])->where(['post_owner_id' => "$roleid" ,'post_id' => "$pageid" ,'notification_type' => 'page_role_type'])->asarray()->one();
                        if($roleexist)
                        {
                            $notification = $roleexist;
                        }
                        else
                        {
                            $notification =  new Notification();
                        }
                        $notification->user_id = "$userid";
                        $notification->post_id = "$pageid";
                        $notification->notification_type = 'page_role_type';
                        $notification->is_deleted = '0';
                        $notification->page_role_type = "$role";
                        $notification->status = '1';
                        $notification->created_date = "$date";
                        $notification->updated_date = "$date";
                        $notification->post_owner_id = "$roleid";
                        if($roleexist)
                        {
                            $notification->update();
                        }
                        else
                        {
                            $notification->insert();
                        }
                    }
                    else
                    {
                        $data['msg'] = 'fail';
                        $data['status'] = 'Something went wrong. Please try later.';
                    }
                }
                else
                {
                    $roletype = PageRoles::pageRole($roleid,$pageid);
                    $data['msg'] = 'exist';
                    $data['status'] = $fullname.' is already '.$roletype;
                }
            }
            else
            {
                $data['msg'] = 'fail';
                $data['status'] = 'Something went wrong. Please try later.';
            }
            return json_encode($data);
        }
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionDeletepagerole() 
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        if($userid)
        {
            $roleid = $_POST['id'];
            if($roleid)
            {
                $delete_page_role = PageRoles::find()->select(['_id','user_id','page_id'])->where(['_id' => "$roleid"])->one();
                $role_id = $delete_page_role['user_id'];
                $page_id = $delete_page_role['page_id'];
                if($delete_page_role->delete())
                {
                    $roleexist = Notification::find()->select(['_id'])->where(['post_owner_id' => "$role_id" ,'post_id' => "$page_id" ,'notification_type' => 'page_role_type'])->one();
                    if($roleexist)
                    {
                        $date = time();
                        $roleexist->created_date = "$date";
                        $roleexist->updated_date = "$date";
                        $roleexist->status = '0';
                        $roleexist->update();
                    }
                    $data['msg'] = 'success';
                    $data['status'] = 'Deleted successfully';
                }
                else
                {
                    $data['msg'] = 'fail';
                    $data['status'] = 'Something went wrong. Please try later.';
                }
            }
            else
            {
                $data['msg'] = 'fail';
                $data['status'] = 'Something went wrong. Please try later.';
            }
            return json_encode($data);
        }
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionDeletebsnesbtn()
    {
        $session = Yii::$app->session;
		$userid = (string)$session->get('user_id');
        if($userid)
        {
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $pageid = $url[1];
            $result = Page::Pagedetails($pageid);
            if($result)
            {
                $result->bsnesbtn = null;
                $result->bsnesbtnvalue = null;
                if($result->update())
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionAddbsnesbtn()
    {
        $session = Yii::$app->session;
		$userid = (string)$session->get('user_id');
        if($userid)
        {
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $pageid = $url[1]; 
            $result = Page::Pagedetails($pageid);
            if(!empty($result))
            {
                $result->bsnesbtn = $_POST['getType'];
                $result->bsnesbtnvalue = $_POST['getValue'];
                $result->update();
                if($result['bsnesbtn']!='Call Now' && $result['bsnesbtn']!='Contact Us' && $result['bsnesbtn']!='Send Email' && $result['bsnesbtn']!='Send Message')
                {
                    $link = $result['bsnesbtnvalue'];
                    if(substr( $link, 0, 4 ) === "http")
                    {
                        $link = $link;
                    }
                    else
                    {
                        $link = 'http://'.$link;
                    }
                    $result['bsnesbtnvalue'] = $link;
                }
                $data['msg'] = 'success';
                $data['status'] = $result['bsnesbtnvalue'];
                return json_encode($data);
            }
        }
        else
        {
            return $this->goHome();
        }
    }

    public function actionWallabouteditdirect()
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        if($userid)
        {
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $pageid = $url[1]; 
            $result = Page::Pagedetails($pageid);
            if($result)
            {
                $result->category = $_POST['getType'];
                $result->short_desc = $_POST['getAbout'];
                $result->pgmail = $_POST['getEmail'];
                $result->site = $_POST['getUrl'];
                if($result->update())
                {
                   return true;
                }
            }
        }
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionGetpageinfo()
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        if($userid)
        {
            $pageid = $_POST['pageid'];
            $result = Page::Pagedetails($pageid);
            if($result)
            {
                if($_SERVER['HTTP_HOST'] == 'localhost')
                {
                    $baseUrl = '/iaminjapan-code/frontend/web';
                }
                else
                {
                    $baseUrl = '/frontend/web/assets/baf1a2d0';
                }
                if($_POST['pageobj'] == 'pe')
                {
                    $like_count = PageEndorse::getAllEndorseCount($pageid);
                }
                else
                {
                    $like_count = Like::getLikeCount($pageid);
                }
                $page_img = $this->getpageimage($pageid);
                $page_desc = $result['short_desc'];
                if(empty($page_desc)){$page_desc = 'Not added';}
                $data['msg'] = 'success';
                $data['name'] = $result['page_name'];
                $data['desc'] = $page_desc;
                $data['img'] = $page_img;
                $data['count'] = $like_count;
            }
            else
            {
                $data['msg'] = 'fail';
            }
            return json_encode($data);
        }
        else
        {
            return $this->goHome();
        }
    }

    public function actionGetPageInfoForChat()
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        if(isset($_POST['$id']) && isset($_POST['$id']) != '') {
            $id = $_POST['$id'];
            $pageInfo = Page::getpageinfochat($id);
            return $pageInfo;
            exit;
        }
    }

    public function actionCheckPageId()
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        if(isset($_POST['pageId']) && isset($_POST['pageId']) != '') {
            $pageId = $_POST['pageId'];
            $pageInfo = Page::checkpageid($pageId, $userid);
            return $pageInfo;
            exit;
        }
    }  

    public function actionFetchbusinessbutton()
    {
        $session = Yii::$app->session;
		$userid = (string)$session->get('user_id');
        if(isset($userid) && $userid != '') {
            if(isset($_POST['pageid']) && $_POST['pageid'] != '') {
                $pageid = $_POST['pageid'];
                return $this->render('business_button', array('pageid' => $pageid));
            }
        }
    }
	
	public function createpage() {
        $session = Yii::$app->session;  
        $request = Yii::$app->request;
        $email = $session->get('email'); 
        $suserid = $user_id = (string) $session->get('user_id');  
		$page_owner = $_POST['page_owner'];

        $guserid = $wall_user_id = (string) $_POST['id'];
        $photosallow = 'denyPhotos';
        if(strstr($_SERVER['HTTP_REFERER'],'r=page')) {
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
            $photosallow = $page_id['gen_photos'];
        }
        
        $baseUrl = (string) $_POST['baseUrl'];
        $albums = UserPhotos::getAlbums($wall_user_id);
        $total_albums = count($albums);
        $profile_albums = UserPhotos::getProfilePics($wall_user_id);
        $total_profile_albums = count($profile_albums);
        if($total_profile_albums>0) { $total_profile_album=1; } else { $total_profile_album=0; }
        $cover_albums = UserPhotos::getCoverPics($wall_user_id);
        $total_cover_albums = count($cover_albums);
        if($total_cover_albums>0) { $total_cover_album=1; } else { $total_cover_album=0; }
        $totalpics = UserPhotos::getPics($wall_user_id);
        $totalpictures = $totalpics+$total_profile_albums+$total_cover_albums;
        $totalcounts = $total_albums;
        $totalalbums = UserPhotos::getAlbumCounts($wall_user_id);

        $result = LoginForm::find()->select(['_id','fname','lname','cover_photo'])->where(['_id' => $wall_user_id])->one();
        $fullname = $result['fname'].' '.$result['lname'];
        $profile_picture_image = $this->getimage($result['_id'],'thumb');
        if(isset($page_id['cover_photo']) && !empty($page_id['cover_photo'])) {
            $cover_picture_image = "uploads/cover/".$page_id['cover_photo'];
        } else {
              
            $assetsPath = '../../vendor/bower/travel/images/';
            $cover_picture_image = $assetsPath.'cover.jpg';
        }

        $gallery = UserPhotos::getUserPhotos($wall_user_id);
        $galleryuser = LoginForm::find()->where(['_id' => $wall_user_id])->one();
        $loginuser = LoginForm::find()->where(['_id' => "$user_id"])->one();
        $gallery_img = $this->getimage($loginuser['_id'],'photo');
        $my_connect_view_status = 'Public';
        $is_connect = Connect::find()->where(['from_id' => "$user_id",'to_id' => "$wall_user_id",'status' => '1'])->one();
        ?> 

        <div class="user-photos">
            <div>                                                                               
                <div class="inner images-container userwallslider">
                    <ul id="content-slider" class="gallery list-unstyled clearfix">
                        <?php             
                		$url = $_SERVER['HTTP_REFERER'];
                		$urls = explode('&',$url);
                		$url = explode('=',$urls[1]); 
                		$page_id = $url[1];
                        $AlbumIdImages = SliderCover::find()->select(['image_path'])->where(['user_id' => $page_owner, 'type' => "page",'pageid' => "$page_id"])->asarray()->all();
                        if(!empty($AlbumIdImages)) {
                            foreach($AlbumIdImages as $SingleAlbumIdImage) {
                                $SingleAlbumIdImage = $SingleAlbumIdImage['image_path']; 
                                if (file_exists($SingleAlbumIdImage)) {  
                                	$picsize = '';
                                	$val = getimagesize($SingleAlbumIdImage);
                                	$picsize .= $val[0] .'x'. $val[1] .', ';
                                	if($val[0] > $val[1]) { 
                                        $imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';
                                    } else {
                                        $imgclass = 'himg';
                                    }
                                    ?>
                                    <li class="lslide"><figure><a href="<?=$SingleAlbumIdImage?>" data-size="1600x1600" data-med="<?=$SingleAlbumIdImage?>" data-med-size="1024x1024" data-author="Folkert Gorter" class="<?=$imgclass?>-box"><img src="<?=$SingleAlbumIdImage?>" class="<?=$imgclass?>" alt="" /></a></figure></li>
                                    <?php
                                } 
                            } 
                        } ?>
                    </ul>
                </div>
            </div>
        </div>  

		<div class="fake-title-area divided-nav" data-id="recount">
		    <ul class="tabs"> 
    			<li class="active tab"><a href="#pc-all" data-toggle="tab" aria-expanded="true">View All</a></li>
    			<li class="album-tab tab"><a href="#pc-albums" data-toggle="tab" aria-expanded="false">All Albums <span>(<?=$totalalbums?>)</span></a></li>
    			<li class="tab"><a href="#pc-photos" data-toggle="tab" aria-expanded="false">All Photos <span class="photos_count">(<?=$totalpictures?>)</span></a></li>
		    </ul>               
		</div>

		<div class="content-box bshadow">
            <div class="cbox-desc">
                <div class="tab-content">
                  <div class="tab-pane fade in active" id="pc-all">
                    <div class="albums-area">
                      <div class="section-title">
                        <span><?= $fullname?></span>
                        <i class="mdi mdi-chevron-right"></i>
                        <span>Albums(<?=$totalcounts?>)</span>
                      </div>
                      <div class="albums-grid">
                        <div class="row">
                        <?php if(($user_id == $wall_user_id) || ($photosallow == 'allowPhotos')){ ?>
                          <div class="col-md-3 col-sm-3 col-xs-3">
                            <a href="javascript:void(0)" class="add-album popup-modal">
                              <span class="icont">+</span>
                              Create New Album
                            </a>
                          </div>
                        <?php } ?>
                          <div class="col-md-9 col-sm-9 col-xs-9">
                            <div class="row">
                              <div class="carousel carousel-albums slide" id="albumCarousel">
                                <div class="carousel-inner">
                                <?php if($total_profile_albums > 0){
                                    $picsize = '';
                                    $imgclass = '';

                                    if(file_exists($profile_picture_image)) {
                                    $val = getimagesize($profile_picture_image);
                                    $picsize .= $val[0] .'x'. $val[1] .', ';
                                    if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}
                                ?>
                                  <div class="item">
                                  <div class="col-xs-4 col-sm-4 col-md-4">
                                    <div class="album-box">
                                      <div class="imgholder <?=$imgclass?>-box">
                                        <a href="javascript:void(0)"><img style="cursor:pointer" onclick="viewprofilepics('<?=$guserid?>')" src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/'.$profile_picture_image ?>" class="<?=$imgclass?>"/></a>
                                      </div>
                                      <div class="descholder">
                                        <a href="javascript:void(0)" class="namelink" onclick="viewprofilepics('<?=$guserid?>')"><span>Profile Photos</span></a>
                                        <span class="info"><?= $total_profile_albums?> Photos</span>
                                      </div>
                                    </div>
                                  </div>
                                  </div>
                                <?php } } ?>
                                <?php if($total_cover_albums > 0){ ?>
                                  <div class="item">
                                  <div class="col-xs-4 col-sm-4 col-md-4">
                                    <div class="album-box">
                                      <div class="imgholder himg-box">
                                        <a href="javascript:void(0)"><img style="cursor:pointer" onclick="viewcoverpics('<?=$guserid?>')" src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/'.$cover_picture_image ?>" class="himg"/></a>
                                      </div>
                                      <div class="descholder">
                                        <a href="javascript:void(0)" class="namelink" onclick="viewcoverpics('<?=$guserid?>')"><span>Cover Photos</span></a>
                                        <span class="info"><?= $total_cover_albums?> Photos</span>
                                      </div>
                                    </div>
                                  </div>
                                  </div>
                                <?php } ?>
                                <?php if($total_albums>0){
                                  $slider_cnt = 0;
                                  foreach($albums as $album){
                                  if(isset($album['image']) && !empty($album['image'])){
                                  $eximgs = explode(',',$album['image'],-1);
                                  $totalimages = count($eximgs);
                                  $my_post_view_status = $album['post_privacy'];
                                  if ($my_post_view_status == 'Private') {
                                      $post_dropdown_class = 'lock';
                                  } else if ($my_post_view_status == 'Friends') {
                                      $post_dropdown_class = 'user';
                                  } else {
                                      $my_post_view_status = 'Public';
                                      $post_dropdown_class = 'globe';
                                  }
                                  $picsize = '';
                                  $imgclass = '';

                                  if(file_exists('../web'.$eximgs[0])) {
                                  $val = getimagesize('../web'.$eximgs[0]);
                                  $picsize .= $val[0] .'x'. $val[1] .', ';
                                  if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}
                                  }
                                if(($my_post_view_status == 'Public') || ($my_post_view_status == 'Friends' && ($is_connect || (string)$guserid == $suserid)) || ($my_post_view_status == 'Private' && (string)$guserid == $suserid)) {
                                ?>
                                <div class="item <?php if($slider_cnt == 0){ ?>active<?php } ?>">
                                  <div class="col-xs-4 col-sm-4 col-md-4">
                                    <div class="album-box">
                                      <div class="imgholder <?= $imgclass?>-box">
                                          <a href="javascript:void(0)"><img style="cursor:pointer" onclick="openAlbumImages(this,'<?=$album['_id']?>','<?=$album['album_title']?>')" src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximgs[0] ?>" class="<?= $imgclass?>"/></a>
                                        <?php if($user_id == $wall_user_id){ ?>
                                        <div class="edit-link fetchslidercoversettingmenu" onclick="fetchslidercoversettingmenu('<?=$album['_id']?>')">
                                          <div class="dropdown dropdown-custom dropdown-auto dropdown">
                                            <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                            <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <ul class="dropdown-menu">
                                            <li><a href="#edit-album-popup" onclick="editAlbum('<?=$album['_id']?>')" class="popup-modal">Edit</a></li>
                                            <li><a href="javascript:void(0)" onclick="deleteAlbum('<?=$album['_id']?>')">Delete</a></li>
                                            </ul>
                                          </div>
                                        </div>  
                                        <?php } ?>
                                      </div>
                                      <div class="descholder">
                                        <a href="javascript:void(0)" class="namelink" onclick="openAlbumImages(this,'<?=$album['_id']?>','<?=$album['album_title']?>')"><span><?=$album['album_title']?></span></a>
                                        <span class="info"><?= $totalimages?> Photos</span>
                                        <?php if($user_id == $wall_user_id){ ?>
                                        <div class="dropdown dropdown-custom no-sword setDropVal">
                                          <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                          <span class="glyphicon glyphicon-<?=$post_dropdown_class?>"></span> <span class="sword"><?=$my_connect_view_status?></span><span class="caret"></span>
                                          </a>
                                          <ul class="dropdown-menu no-sword setDropVal">
                                          <li class="album-private" data-album="<?=$album['_id']?>"><a href="javascript:void(0)"><span class="glyphicon glyphicon-lock"></span><span class="sword">Private</span></a></li>
                                          <li class="album-connections" data-album="<?=$album['_id']?>"><a href="javascript:void(0)"><span class="glyphicon glyphicon-user"></span><span class="sword">Friends</span></a></li>
                                          <li class="album-public" data-album="<?=$album['_id']?>"><a href="javascript:void(0)"><span class="glyphicon glyphicon-globe"></span><span class="sword">Public</span></a></li>
                                          </ul>
                                        </div>
                                        <?php } ?>
                                      </div>
                                    </div>
                                  </div>
                                  </div>
                                <?php $slider_cnt++; } } } } ?>
                                </div>
                                <a class="pull-left carousel-control" href="#albumCarousel" data-slide="prev"><i class="glyphicon glyphicon-chevron-left"></i></a>
                                <a class="pull-right carousel-control" href="#albumCarousel" data-slide="next"><i class="glyphicon glyphicon-chevron-right"></i></a>
                              </div>
                               
                            </div>
                          </div>
                        </div>
                      </div>                      
                    </div>
                    <div class="photos-area">
                    <?php if($total_albums>0){
                      $last_cnt = 0;
                      foreach($albums as $album){
                        if(($my_post_view_status == 'Public') || ($my_post_view_status == 'Friends' && ($is_connect || (string)$guserid == $suserid)) || ($my_post_view_status == 'Private' && (string)$guserid == $suserid)) {
                        if($last_cnt < 1){
                          if(isset($album['image']) && !empty($album['image'])){
                            $eximgs = explode(',',$album['image'],-1);
                            $total_eximgs = count($eximgs);
                    ?>
                      <div class="section-title">
                        <span><?= $fullname?></span>
                        <i class="mdi mdi-chevron-right"></i>
                        <span><?=$album['album_title']?></span><span class="info">&nbsp;<?=$total_eximgs?> Photos</span>
                      </div>
                      <div class="albums-grid gallery">
                        <div class="row">
                        <?php if($user_id == $wall_user_id){ ?>
                          <div class="grid-box">
                            <div class="divrel">
    						<a href="#add-photo-popup" class="add-photo popup-modal" id="add-photo-all">
                             <span class="icont">+</span>
                             Add New Photo
                            </a>
                           <input type="file" name="newphotoupld" class="hidden_uploader custom-upload-new" title="Choose a file to upload" required data-class="#add-photo-popup .post-photos .img-row" multiple/>
                            </div>
                          </div>
                        <?php } ?>
                        <div class="images-container">
                        <?php
                        foreach ($eximgs as $eximg) {
                          $imgpath = Yii::$app->getUrlManager()->getBaseUrl().$eximg;
                          $picsize = '';
                          $imgclass = '';
                          $iname = '';
                          if(file_exists('../web'.$eximg)) {
                            $val = getimagesize('../web'.$eximg);
                            $iname = $this->getimagename($eximg);
                            $inameclass = $this->getimagefilename($eximg);
                            $pinit = PinImage::find()->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
                            if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
                            $picsize .= $val[0] .'x'. $val[1] .', ';
                            if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}
                          }
                        ?>
                          <div class="grid-box" id="albumimg_<?=$iname?>">
                            <div class="photo-box">
                              <div class="imgholder <?= $imgclass?>-box">		
                                <figure>								
                                <a href="<?=$imgpath?>" data-imgid="<?=$inameclass?>" data-size="1600x1600" data-med="<?=$imgpath?>" data-med-size="1024x1024" data-author="Folkert Gorter" data-pinit="<?=$pinval?>" class="imgpin">
                                  <img src="<?=$imgpath?>" class="<?=$imgclass?>"/>
                                </a>
    							
                                </figure>
                                
                              </div>
    						  <?php if($user_id == $wall_user_id){ ?>
                                <div class="edit-link">
                                  <div class="dropdown dropdown-custom  dropdown-auto">
                                    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                    <i class="mdi mdi-pencil"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0)" onclick="moveImage('<?=$eximg?>','<?=$album['_id']?>')">Move to other album</a></li>
                                    <li><a href="javascript:void(0)" onclick="albumCover('<?=$user_id?>','<?=$eximg?>','<?=$album['_id']?>')">Make album cover</a></li>
                                    <li><a href="javascript:void(0)" onclick="deleteImage('<?=$iname?>','<?=$eximg?>','<?=$album['_id']?>')">Delete this photo</a></li>
    								</ul>
                                  </div>
                                </div>
                            <?php } ?>
    						<?php
                                    $fileinfo = pathinfo($eximg);
                                    $uniq_id = $fileinfo['filename'] .'_'. $album['_id'];
                                    $like_count = Like::getLikeCount((string)$uniq_id);
                                    $like_names = Like::getLikeUserNames((string)$fileinfo['filename'] .'_'. $album['_id']);
                                    $like_buddies = Like::getLikeUser((string)$fileinfo['filename'] .'_'. $album['_id']);
    								$is_like = Like::find()->where(['user_id'=>$user_id,'post_id'=>$uniq_id,'status'=>'1'])->one();
    								if($is_like){$ls = 'Liked';}else{$ls = 'Like';}
                                    $newlike_buddies = array();
                                    foreach($like_buddies as $like_buddy)
                                    {
                                        $newlike_buddies[] = ucfirst($like_buddy['user']['fname']). ' '.ucfirst($like_buddy['user']['lname']);
                                    }
                                    $newlike_buddies = implode('<br/>', $newlike_buddies);
                                  ?>
                              <div class="descholder">
                                <a href="javascript:void(0)" class="namelink"><span><?=$album['album_title']?></span></a>
                                <div class="options">
                                  <a href="javascript:void(0)" onclick="doLikeAlbumbPhotos('<?=$uniq_id?>');"><span class="ls_<?=$uniq_id?>"><?=$ls?></span></a>
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
                    <?php $last_cnt++; } } } } } ?>
                    </div>

                  </div>
                  <div class="tab-pane fade" id="pc-albums">
                    <div class="albums-area">
                      <div class="section-title">
                        <span><?=$fullname?></span>
                        <i class="mdi mdi-chevron-right"></i>
                        <span>Albums(<?=$totalcounts?>)</span>
                      </div>
                      <div class="albums-grid">
                        <div class="row newalbumviewalbums">
                        <?php if($user_id == $wall_user_id){ ?>
                          <div class="grid-box">
                            <a href="javascript:void(0)" class="add-album popup-modal">
                              <span class="icont">+</span>
                              Create New Album
                            </a>
                          </div>
                            <?php } ?>
                          <?php if($total_albums>0){
                            foreach($albums as $album){
                            if(isset($album['image']) && !empty($album['image'])){
                            $eximgs = explode(',',$album['image'],-1);
                            $totalimages = count($eximgs);
                            $my_post_view_status = $album['post_privacy'];
                            if ($my_post_view_status == 'Private') {
                                $post_dropdown_class = 'lock';
                            } else if ($my_post_view_status == 'Friends') {
                                $post_dropdown_class = 'user';
                            } else {
                                $my_post_view_status = 'Public';
                                $post_dropdown_class = 'globe';
                            }
                            $picsize = '';
                            $imgclass = '';
                            if(file_exists('../web'.$eximgs[0])) {
                            $val = getimagesize('../web'.$eximgs[0]);
                            $picsize .= $val[0] .'x'. $val[1] .', ';
                            if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}
                            }
                            if(($my_post_view_status == 'Public') || ($my_post_view_status == 'Friends' && ($is_connect || (string)$guserid == $suserid)) || ($my_post_view_status == 'Private' && (string)$guserid == $suserid)) {
                          ?>
                          <div class="grid-box">
                              <div class="album-box">
                                  <div class="imgholder <?= $imgclass?>-box">
                                      <a href="javascript:void(0)"><img style="cursor:pointer" onclick="openAlbumImages(this,'<?=$album['_id']?>','<?=$album['album_title']?>')" src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximgs[0] ?>" class="<?= $imgclass?>"/></a>
                                      <?php if($user_id == $wall_user_id){ ?>
                                        <div class="edit-link">
                                          <div class="dropdown dropdown-custom dropdown-xxsmall">
                                            <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                            <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <ul class="dropdown-menu">
                                            <li><a href="#edit-album-popup" onclick="editAlbum('<?=$album['_id']?>')" class="popup-modal">Edit</a></li>
                                            <li><a href="javascript:void(0)" onclick="deleteAlbum('<?=$album['_id']?>')">Delete</a></li>
                                            </ul>
                                          </div>
                                        </div>  
                                      <?php } ?>
                                  </div>
                                  <div class="descholder">
                                      <a href="javascript:void(0)" class="namelink" onclick="openAlbumImages(this,'<?=$album['_id']?>','<?=$album['album_title']?>')"><span><?=$album['album_title']?></span></a>
                                      <span class="info"><?= $totalimages?> Photos</span>
                                      <?php if($user_id == $wall_user_id){ ?>
                                      <div class="dropdown dropdown-custom no-sword setDropVal">
                                        <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                          <span class="glyphicon glyphicon-<?=$post_dropdown_class?>"></span><span class="sword"><?=$my_connect_view_status?></span> <span class="caret"></span>
                                        </a>
                                        <ul class="dropdown-menu no-sword setDropVal">
                                          <li class="album-private" data-album="<?=$album['_id']?>"><a href="javascript:void(0)"><span class="glyphicon glyphicon-lock"></span><span class="sword">Private</span></a></li>
                                          <li class="album-connections" data-album="<?=$album['_id']?>"><a href="javascript:void(0)"><span class="glyphicon glyphicon-user"></span><span class="sword">Friends</span></a></li>
                                          <li class="album-public" data-album="<?=$album['_id']?>"><a href="javascript:void(0)"><span class="glyphicon glyphicon-globe"></span><span class="sword">Public</span></a></li>
                                        </ul>
                                      </div>
                                      <?php } ?>
                                  </div>
                              </div>
                            </div>
                            <?php } } } } ?>
                        </div>
                      </div>
                    </div>
                    
                    <div class="photos-area" data-id="recount">
                    </div>
                  </div>                
                  <div class="tab-pane fade" id="pc-photos">
                    <div class="photos-area">
                      <div class="section-title">
                        <span><?= $fullname?></span>
                        <i class="mdi mdi-chevron-right"></i>
                        <span class="photos_count">Photos(<?=$totalpictures?>)</span>
                      </div>
                      <div class="albums-grid gallery">
                        <div class="row">
                            <?php if($user_id == $wall_user_id){ ?>
                          <div class="grid-box">
                            <div class="divrel">
    							
                                <a href="#add-photo-popup" class="add-photo popup-modal" id="add-photo-photos">
                                 <span class="icont">+</span>
                                 Add New Photo
                                </a>
                               <input type="file" name="newphotoupld" class="hidden_uploader hidden_uploaderr custom-upload-new" title="Choose a file to upload" required data-class="#add-photo-popup .post-photos .img-row" multiple/>
                            </div>
                          </div>
                            <?php } ?>
                            <div class="images-container">
                            <?php 
                            foreach($gallery as $gallery_item)
                            {
                                $my_post_view_status = $gallery_item['post_privacy'];
                                if(($my_post_view_status == 'Public') || ($my_post_view_status == 'Friends' && ($is_connect || (string)$guserid == $suserid)) || ($my_post_view_status == 'Private' && (string)$guserid == $suserid)) {
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
                                    $pinit = PinImage::find()->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
                                    if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
                                    if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}
                                }
                        ?>
                          <div class="grid-box" id="albumimg_<?=$iname?>">
                            <div class="photo-box">
                              <div class="imgholder <?= $imgclass?>-box">
                                    <figure>
                                        <a href="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-imgid="<?=$inameclass?>" data-size="1600x1600" data-med="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-med-size="1024x1024" data-author="Folkert Gorter" data-pinit="<?=$pinval?>" class="imgpin">
                                          <img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?=$eximg?>" class="<?=$imgclass?>"/>
                                        </a>
                                    
                                    </figure>
                              </div>
    						  <?php if(($user_id == $wall_user_id) && $gallery_item['is_album'] == '1'){ ?>
                                    <div class="edit-link">
                                        <div class="dropdown dropdown-custom dropdown-auto">
                                          <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                            <i class="mdi mdi-pencil"></i>
                                          </a>
                                          <ul class="dropdown-menu">
                                            <li><a href="javascript:void(0)" onclick="moveImage('<?=$eximg?>','<?=$gallery_item['_id']?>')">Move to other album</a></li>
                                            <li><a href="javascript:void(0)" onclick="albumCover('<?=$user_id?>','<?=$eximg?>','<?=$gallery_item['_id']?>')">Make album cover</a></li>
                                            <li><a href="javascript:void(0)" onclick="deleteImage('<?=$iname?>','<?=$eximg?>','<?=$gallery_item['_id']?>')">Delete this photo</a></li>
                                          </ul>
                                        </div>
                                    </div>
                                    <?php } ?>
    								<?php
                                    $fileinfo = pathinfo($eximg);
                                    $uniq_id = $fileinfo['filename'] .'_'. $gallery_item['_id'];
                                    $like_count = Like::getLikeCount((string)$uniq_id);
                                    $like_names = Like::getLikeUserNames((string)$fileinfo['filename'] .'_'. $gallery_item['_id']);
                                    $like_buddies = Like::getLikeUser((string)$fileinfo['filename'] .'_'. $gallery_item['_id']);
    								$is_like = Like::find()->where(['user_id'=>$user_id,'post_id'=>$uniq_id,'status'=>'1'])->one();
    								if($is_like){$ls = 'Liked';}else{$ls = 'Like';}
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
                                <div class="options">
                                  <a href="javascript:void(0)" onclick="doLikeAlbumbPhotos('<?=$uniq_id?>');"><span class="ls_<?=$uniq_id?>"><?=$ls?></span></a>
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
                        <?php } } } ?>
                        </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
        </div>

    <?php
    }

    public function actionGetHtmlContentForPage() 
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('status' => false);
        $idsArray = array();
        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {
                if(isset($_POST['$label']) && $_POST['$label'] != '') {
                    $label = $_POST['$label'];

                    if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
                    {
                        $url = $_SERVER['HTTP_REFERER'];
                        $urls = explode('&',$url);
                        $url = explode('=',$urls[1]);
                        $page_id = $url[1];
                        
                        $data = Page::find()->where([(string)'_id' => (string)$page_id])->asarray()->one();
                        if(!empty($data)) {
                            if($label == 'page_restricted_list') {
                                $ids = (isset($data['blk_restrct_list']) && !empty($data['blk_restrct_list'])) ? $data['blk_restrct_list'] : '';
                                if($ids != 'undefined' && $ids != 'null') {
                                    $idsArray = explode(',', $ids);
                                }
                            } else if($label == 'page_block_list') {
                                $ids = (isset($data['blk_block_list']) && !empty($data['blk_block_list'])) ? $data['blk_block_list'] : '';
                                if($ids != 'undefined' && $ids != 'null') {
                                    $idsArray = explode(',', $ids);
                                }
                            } else if($label == 'page_block_message_list') {
                                $ids = (isset($data['blk_msg_filtering']) && !empty($data['blk_msg_filtering'])) ? $data['blk_msg_filtering'] : '';
                                if($ids != 'undefined' && $ids != 'null') {
                                    $idsArray = explode(',', $ids);
                                }
                            }
                        } else {
                            $idsArray = isset($_POST['ids']) ? $_POST['ids'] : array();
                        } 
                    }
                    $idsArray = array_values(array_filter($idsArray)); 
                    $returnlabel = SecuritySetting::getFullnamesWithToolTip($idsArray, $label);
                    $result = array('status' => true, 'returnlabel' => $returnlabel, 'ids' => $idsArray);
                    return json_encode($result, true);
                }
            }
        }
        return false;
    }

    public function actionComposenewreviewpopup() 
    { 
        return $this->render('/layouts/pagereviewpost');
    }

    public function actionBussinesspagereview() 
    { 
        return $this->render('/layouts/bussinesspagereview');
    }

    public function actionGetpagegeneralnormal()
    {
        return $this->render('/page/getpagegeneralnormal');
    }     

    public function actionGetpagegeneraledit()
    { 
        return $this->render('/page/getpagegeneraledit');
    }

    public function actionGetpageblockingnormal()
    {
        return $this->render('/page/getpageblockingnormal');
    }     

    public function actionGetpageblockingedit()
    {
        return $this->render('/page/getpageblockingedit');
    }

    public function actionPagesettingsgeneralsave() 
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        $data = array();
        if($userid)
        {
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $pageid = $url[1];
            $page_exist = Page::Pagedetails($pageid);
            $date = time(); 
            if($page_exist)
            {
                if (isset($_POST['page_pub_set_val']) && !empty($_POST['page_pub_set_val']))
                { 
                    $page_pub_set_val = ucwords(strtolower($_POST['page_pub_set_val']));

                    if($page_pub_set_val == 'Unpublish'){
                        $status = 0; 
                    } else {
                        $status = 1;
                    }
                    $page_exist->is_deleted = "$status";
                }

                if (isset($_POST['pagepost']) && !empty($_POST['pagepost']))
                {
                    $page_exist->gen_post = $_POST['pagepost'];
                    $page_exist->gen_post_review = $_POST['pagepostreview'];
                    if($_POST['pagepost'] == 'denyPost')
                    {
                        $page_exist->gen_post_review = 'off';
                    }
                }

                if (isset($_POST['pagephotos']) && !empty($_POST['pagephotos']))
                {
                    $page_exist->gen_photos = $_POST['pagephotos'];
                    $page_exist->gen_photos_review = $_POST['pagephotoreview'];
                    if($_POST['pagephotos'] == 'denyPhotos')
                    {
                        $page_exist->gen_photos_review = 'off';
                    }
                }

                if (isset($_POST['pgfltr']))
                {
                    if (!strstr($_POST['pgfltr'],',') && (strlen($_POST['pgfltr'])>=1)){$_POST['pgfltr'] = $_POST['pgfltr'].',';}
                    $page_exist->gen_page_filter = $_POST['pgfltr'];
                }

                if (isset($_POST['review_switch_value']) && !empty($_POST['review_switch_value']))
                {
                    if($_POST['review_switch_value']=='off'){$_POST['review_switch_value']='on';}
                    else{$_POST['review_switch_value']='off';}
                    $page_exist->gen_reviews = $_POST['review_switch_value'];
                }

                $page_exist->update();
                $result = array('success' => true);
                return json_encode($result, true);
            }
        }
        $result = array('success' => false);
        return json_encode($result, true);
    }

    public function actionPagesettingsblockingsave() 
    {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        $data = array();
        if($userid)
        {
            $url = $_SERVER['HTTP_REFERER'];
            $urls = explode('&',$url);
            $url = explode('=',$urls[1]);
            $pageid = $url[1];
            $page_exist = Page::Pagedetails($pageid);
            $date = time();
            if($page_exist)
            {
                if (isset($_POST['page_restricted_list']) && !empty($_POST['page_restricted_list']))
                {
                    $page_restricted_list = implode(',', $_POST['page_restricted_list']);
                    $page_exist->blk_restrct_list = $page_restricted_list;
                }

                if (isset($_POST['page_block_list']) && !empty($_POST['page_block_list']))
                {
                    $page_block_list = implode(',', $_POST['page_block_list']);
                    $page_exist->blk_block_list = $page_block_list;
                }
                
                if (isset($_POST['page_block_message_list']) && !empty($_POST['page_block_message_list']))
                {
                    $page_block_message_list = implode(',', $_POST['page_block_message_list']);
                    $page_exist->blk_msg_filtering = $page_block_message_list;
                }

                $page_exist->update();
                $result = array('success' => true);
                return json_encode($result, true);
            }
        }
        $result = array('success' => false);
        return json_encode($result, true);
    }

    public function actionPageblockingsomeparams() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        $page_restricted_list = array();
        $page_block_list = array();
        $page_block_message_list = array();

        $url = $_SERVER['HTTP_REFERER'];
        $urls = explode('&',$url);
        $url = explode('=',$urls[1]);
        $pageid = $url[1];
        $page_exist = Page::Pagedetails($pageid);
        $date = time();
        if($page_exist)
        {
            if (isset($page_exist['blk_restrct_list']) && $page_exist['blk_restrct_list'] != '')
            {
                $I = $page_exist['blk_restrct_list'];
                $I = explode(',', $I);
                if(!empty($I)) {
                    $I = array_filter($I);
                }
                $page_restricted_list = $I;
            }

            if (isset($page_exist['blk_block_list']) && $page_exist['blk_block_list'] != '')
            {
                $I = $page_exist['blk_block_list'];
                $I = explode(',', $I);
                if(!empty($I)) {
                    $I = array_filter($I);
                }
                $page_block_list = $I;
            }

            if (isset($page_exist['blk_msg_filtering']) && $page_exist['blk_msg_filtering'] != '')
            {
                $I = $page_exist['blk_msg_filtering'];
                $I = explode(',', $I);
                if(!empty($I)) {
                    $I = array_filter($I);
                }
                $page_block_message_list = $I;
            }
        }
        
        $result = array('page_restricted_list' => $page_restricted_list, 'page_block_list' => $page_block_list, 'page_block_message_list' => $page_block_message_list);
        return json_encode($result, true);
    }

    public function actionDownload() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

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

        $gallery = UserPhotos::getUserPhotos($wall_user_id);

        $url = Yii::$app->getUrlManager()->getBaseUrl();
        $path = '../web/uploads/';
        $folderName = 'BP_'.$user_id.'.zip';
        $destination = $path.$folderName;
        $ImagesArray = array();

        foreach($gallery as $gallery_item)
        {
            $eximgs = explode(',', $gallery_item['image'],-1);
            foreach ($eximgs as $eximg) {
                if(file_exists('../web'.$eximg)) {
                    $ImagesArray[] = $url.$eximg;
                }
            }
        }

        if(!empty($ImagesArray)) {
            $zip = new ZipArchive();
            if($zip->open($destination, true ? $zip::OVERWRITE : $zip::CREATE) !== true) {
                $result = array('success' => false);
                return json_encode($result, true);
            }
        
            foreach($ImagesArray as $file) {
                $zip->addFile($file,$file);
            }
        
            if($zip->close()) {
                $result = array('success' => true, 'file' => $destination);
                return json_encode($result, true);
            }
        }

        $result = array('success' => false);
        return json_encode($result, true);
    }

}
?>