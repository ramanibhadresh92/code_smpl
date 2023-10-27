<?php
namespace frontend\controllers;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\mongodb\ActiveRecord;
use frontend\models\UserForm;
use frontend\models\LoginForm;
use frontend\models\Personalinfo;
use frontend\models\PostForm;
use frontend\models\Like;
use frontend\models\Page;
use frontend\models\Comment;
use frontend\models\HideComment;
use frontend\models\Notification;
use frontend\models\PinImage;

class CommentController extends Controller
{
   public function behaviors(){
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
    
    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
	
      public function actions() {
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
  
    public function actionCommentPost()
    { 
		$session = Yii::$app->session;
        $post_id = $_POST['post_id'];
        $uid = $userid= (string)$session->get('user_id');

		if(isset($uid) && $uid != '') {
    		$authstatus = UserForm::isUserExistByUid($uid);
    		if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
    			$data['auth'] = $authstatus;
    			return $authstatus;
    		} 
    		else {
        		$comment = new Comment();
                $date = time();
                $data = array();
                $post = PostForm::find()->where(['_id' => $post_id])->one();
        		$result = LoginForm::find()->where(['_id' => $uid])->one();
                $comment_exists = Comment::find()->where(['user_id' => "$uid",'comment'=>$_POST['comment']])->andWhere(['parent_comment_id'=> "0"])->all();
        		
        		if(empty($comment_exists) || count($comment_exists<=0)) 
                {
                    if (isset($_FILES) && !empty($_FILES)) 
                    {
                        $imgcount = count($_FILES["imageCommentpost"]["name"]);
                        $img = '';
                        $im = '';
                        if (isset($_FILES["imageCommentpost"]["name"]) && $_FILES["imageCommentpost"]["name"] != "") {
                            $url =
                                    '../web/uploads/comment/';
                            $urls =
                                    '/uploads/comment/';
                            move_uploaded_file($_FILES["imageCommentpost"]["tmp_name"],$url . $date . $_FILES["imageCommentpost"]["name"]);
                            
                            $img =  $urls . $date . $_FILES["imageCommentpost"]["name"];
                            $im =  $im . $img;
                        }
                        $comment->image = $im;
                    }
                    
                    $comment->post_id = $_POST['post_id'];
                    $comment->user_id = (string)$uid;
        			$post_comment = str_replace(array("\n","\r\n","\r"), '', $_POST['comment']);
                    $comment->comment = $post_comment;
                    $comment->comment_type = 'post';
                    $comment->status = '1';
                    $comment->parent_comment_id = '0';
                    $comment->created_date = $date;
                    $comment->updated_date = $date;
                    if($post['pagepost']=='1') 
                    {
                        $pageid = $post['post_user_id'];
                        $page_exist = Page::Pagedetails($pageid);
                        $replace = explode(",",$page_exist['gen_page_filter']);
                        $comment->comment = ucfirst(str_ireplace($replace, '', $post_comment));
                    }
                    $comment->insert();
                    $last_insert_id = $comment->_id;

                    // Insert record in notification table also
                    $notification =  new Notification();
                    $notification->comment_id =   "$last_insert_id";
                    $notification->user_id = "$userid";
                    $notification->post_id = $_POST['post_id'];
                    $notification->notification_type = 'comment';
                    $notification->comment_content = $post_comment;
                    $notification->is_deleted = '0';
                    $notification->status = '1';
                    $notification->created_date = "$date";
                    $notification->updated_date = "$date";
                    $post_details = PostForm::find()->where(['_id' => $_POST['post_id']])->one();
                    $notification->post_owner_id = $post_details['post_user_id'];
        			$notification->tag_id = $post_details['post_tags'];
                    if(isset($post_details['pagepost']) && !empty($post_details['pagepost']) && $post_details['pagepost'] == '1')
                        {
                            $page_id = Page::Pagedetails($post_details['post_user_id']);
                            $usrid = $page_id['created_by'];
                            $pageid = $post_details['post_user_id'];
                            $notification->page_id = "$pageid";
                            $notification->post_owner_id = "$usrid";
                            if($usrid != $uid && $post_details['post_privacy'] != "Private" && $page_id['not_add_comment'] == 'on')
                            {
                                $notification->insert();
                            }
                        }
                    else
                    {
                        if($post_details['post_user_id'] != "$userid" && $post_details['post_privacy'] != "Private"){
                            $notification->insert();
                        }
                    }

                   $last_comment_data =  Comment::find()->with('user')->with('post')->where(['_id' => "$last_insert_id",'status' => '1'])->one();

                   $data['photo'] = $last_comment_data['user']['photo'];
                   $data['username'] = $last_comment_data['user']['username'];
                   $data['fb_id'] = $last_comment_data['user']['fb_id'];
                   $data['gender'] = $last_comment_data['user']['gender'];
                   $data['comment'] = $last_comment_data['comment'];
                   $data['status'] = '1';
                   $data['msg'] = 'inserted';
                   if($last_comment_data['user']['fb_id'] == '' && $last_comment_data['user']['photo'] == '')
                   {
                       $photo = 'profile/'.$last_comment_data['user']['gender'].'.jpg';
                   }
                   else if($last_comment_data['user']['photo'] != '' && $last_comment_data['user']['fb_id'] == '')
                   {
                        $photo = 'profile/'.$last_comment_data['user']['photo'];
                   }
                   else
                   {
                       if(substr($last_comment_data['user']['photo'],0,4) == 'http')
                            $photo = $last_comment_data['user']['photo'];
                      else
                          $photo = 'profile/'.$last_comment_data['user']['photo'];
                   }
                   $id = $last_comment_data['user']['_id'];
                   $cid = "`".$last_comment_data['_id']."`";
                   $pid = "`".$last_comment_data['post_id']."`";
                   $status_comment = Like::getUserCommentLike($id,$last_comment_data['_id']);

                    if($status_comment == '1' ) {  
                      $flag = 'Unlike';
                    } else { 
                      $flag = 'Like'; 
                    }
                    $comment_time = Yii::$app->EphocTime->comment_time(time(),$last_comment_data['created_date']);    
                    $url = Url::to(["userwall/index", "id" => "$uid"]);

                        $init_comment['_id'] = $last_comment_data['_id'];
                        $init_comment =  $last_comment_data;
                        $this->comment_html($post_id,$last_comment_data['_id']); 
                }
        	}
    	}
    	else {
    		return 'checkuserauthclassg';
    	}
        
    }

    public function actionSliderComment()
    { 
        $session = Yii::$app->session;
        $post_id = $_POST['post_id'];
        $uid = $userid= (string)$session->get('user_id');

        if(isset($uid) && $uid != '') {
            $authstatus = UserForm::isUserExistByUid($uid);
            if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
                $data['auth'] = $authstatus;
                return $authstatus;
            } 
            else {
                $loggedName = $this->getuserdata($uid,'fullname');
                $loggedThumb = $this->getimage($uid,'thumb');

                $comment = new Comment();
                $date = time();
                $data = array();
                $post = PostForm::find()->where(['_id' => $post_id])->one();
                $result = LoginForm::find()->where(['_id' => $uid])->one();
                $comment_exists = Comment::find()->where(['user_id' => "$uid",'comment'=>$_POST['comment']])->andWhere(['parent_comment_id'=> "0"])->all();
                
                if(empty($comment_exists) || count($comment_exists<=0)) 
                {
                    if(isset($_POST['type']) && trim($_POST['type']) == 'UserPhotos') {
                        if(isset($_POST['imgsrc']) && $_POST['imgsrc'] != '') {
                            $imgsrc = $_POST['imgsrc'];
                            
                            $fileinfo = pathinfo($imgsrc);
                            $post_id = $fileinfo['filename'] .'_'. $post_id;
                        }
                    }

                    $comment->post_id = $post_id;
                    $comment->user_id = (string)$uid;
                    $post_comment = str_replace(array("\n","\r\n","\r"), '', $_POST['comment']);
                    $comment->comment = $post_comment;
                    $comment->comment_type = 'post';
                    $comment->status = '1';
                    $comment->parent_comment_id = '0';
                    $comment->created_date = $date;
                    $comment->updated_date = $date;
                    if($post['pagepost']=='1') 
                    {
                        $pageid = $post['post_user_id'];
                        $page_exist = Page::Pagedetails($pageid);
                        $replace = explode(",",$page_exist['gen_page_filter']);
                        $comment->comment = ucfirst(str_ireplace($replace, '', $post_comment));
                    }
                    $comment->insert();
                    $last_insert_id = $comment->_id;
                    $notification =  new Notification();
                    $notification->comment_id =   "$last_insert_id";
                    $notification->user_id = "$userid";
                    $notification->post_id = $post_id;
                    $notification->notification_type = 'comment';
                    $notification->comment_content = $post_comment;
                    $notification->is_deleted = '0';
                    $notification->status = '1';
                    $notification->created_date = "$date";
                    $notification->updated_date = "$date";
                    $post_details = PostForm::find()->where(['_id' => $post_id])->one();
                    $notification->post_owner_id = $post_details['post_user_id'];
                    $notification->tag_id = $post_details['post_tags'];
                    if(isset($post_details['pagepost']) && !empty($post_details['pagepost']) && $post_details['pagepost'] == '1')
                        {
                            $page_id = Page::Pagedetails($post_details['post_user_id']);
                            $usrid = $page_id['created_by'];
                            $pageid = $post_details['post_user_id'];
                            $notification->page_id = "$pageid";
                            $notification->post_owner_id = "$usrid";
                            if($usrid != $uid && $post_details['post_privacy'] != "Private" && $page_id['not_add_comment'] == 'on')
                            {
                                $notification->insert();
                            }
                        }
                    else
                    {
                        if($post_details['post_user_id'] != "$userid" && $post_details['post_privacy'] != "Private"){
                            $notification->insert();
                        }
                    }

                   $last_comment_data =  Comment::find()->with('user')->with('post')->where(['_id' => "$last_insert_id",'status' => '1'])->one();

                   $data['photo'] = $last_comment_data['user']['photo'];
                   $data['username'] = $last_comment_data['user']['username'];
                   $data['fb_id'] = $last_comment_data['user']['fb_id'];
                   $data['gender'] = $last_comment_data['user']['gender'];
                   $data['comment'] = $last_comment_data['comment'];
                   $data['status'] = '1';
                   $data['msg'] = 'inserted';
                   if($last_comment_data['user']['fb_id'] == '' && $last_comment_data['user']['photo'] == '')
                   {
                       $photo = 'profile/'.$last_comment_data['user']['gender'].'.jpg';
                   }
                   else if($last_comment_data['user']['photo'] != '' && $last_comment_data['user']['fb_id'] == '')
                   {
                        $photo = 'profile/'.$last_comment_data['user']['photo'];
                   }
                   else
                   {
                       if(substr($last_comment_data['user']['photo'],0,4) == 'http')
                            $photo = $last_comment_data['user']['photo'];
                      else
                          $photo = 'profile/'.$last_comment_data['user']['photo'];
                   }
                   $id = $last_comment_data['user']['_id'];
                   $cid = "`".$last_comment_data['_id']."`";
                   $pid = "`".$last_comment_data['post_id']."`";
                   $status_comment = Like::getUserCommentLike($id,$last_comment_data['_id']);

                    if($status_comment == '1' ) {  
                      $flag = 'Unlike';
                    } else { 
                      $flag = 'Like'; 
                    }
                    $comment_time = Yii::$app->EphocTime->comment_time(time(),$last_comment_data['created_date']);    
                    $url = Url::to(["userwall/index", "id" => "$uid"]);

                    $init_comment['_id'] = $last_comment_data['_id'];
                    $init_comment =  $last_comment_data;
                    //$this->comment_html($post_id,$last_comment_data['_id']); 
                    $filter_date = date('M d, Y', $date);
                    ?>
                    <li>
                        <div class="ranker-box">
                            <div class="img-holder">
                                <img src="<?=$loggedThumb?>">
                            </div>
                            <div class="desc-holder">
                                <a href="javascript:void(0)" class="userlink"><?=$loggedName?></a>
                                <span class="comment-date"><?=$filter_date?></span>
                                <span class="info"><?=$post_comment?></span>
                            </div>
                        </div>
                    </li>
                    <?php

                }
            }
        }
        else {
            return 'checkuserauthclassg';
        }
        
    }
  
    public function actionReplyComment()
    { 
        $session = Yii::$app->session;
        $post_id = $_POST['post_id'];
        $user_id = $userid = $uid = (string)$session->get('user_id');
		$status = $session->get('status');
		if(isset($user_id) && $user_id != '') {
		$authstatus = UserForm::isUserExistByUid($user_id);
		if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
			$data['auth'] = $authstatus;
			return $authstatus;
		} 
		else {	
        $comment = new Comment();
        $date = time();
        $data = array();		
		$email = $session->get('email');
		$status = $session->get('status');
        $post = PostForm::find()->where(['_id' => $post_id])->one();
        $reply_exists = Comment::find()->where(['user_id' => "$uid",'comment'=>$_POST['reply']])->andWhere(['not','parent_comment_id', "0"])->all();
        //if(empty($reply_exists) && count($reply_exists<=0))
        //{
			if (isset($_FILES) && !empty($_FILES)) 
            {
				$imgcount = count($_FILES["imageReplypost"]["name"]);
				$img = '';
				$im = '';
                if (isset($_FILES["imageReplypost"]["name"]) && $_FILES["imageReplypost"]["name"] != "") {
                    $url = '../web/uploads/comment/';
                    $urls = '/uploads/comment/';
                    move_uploaded_file($_FILES["imageReplypost"]["tmp_name"],$url . $date . $_FILES["imageReplypost"]["name"]);
                    $img =  $urls . $date . $_FILES["imageReplypost"]["name"];
                    $im =  $im . $img;
                } else {
                }
            
				$comment->image = $im;
			}
        $comment->post_id = $_POST['post_id'];
        $comment->user_id = (string)$uid;       
        $post_reply = str_replace(array("\n","\r\n","\r"), '', $_POST['reply']);
        $comment->comment = $post_reply;
        $comment->comment_type = 'post';
        $comment->status = '1';
        $comment->parent_comment_id = $_POST['comment_id'];
        $comment->created_date = $date;
        $comment->updated_date = $date;
        
        if($post['pagepost']=='1') 
        {
            $pageid = $post['post_user_id'];
            $page_exist = Page::Pagedetails($pageid);
            $replace = explode(",",$page_exist['gen_page_filter']);
            $comment->comment = ucfirst(str_ireplace($replace, '', $post_reply));
        }
        $comment->insert();
        $last_insert_id = $comment->_id;
        
        // Insert record in notification table also
        $notification =  new Notification();
        $notification->comment_id = $_POST['comment_id'];
        $notification->reply_comment_id = "$last_insert_id";
        $notification->user_id = "$userid";
        $notification->post_id = $_POST['post_id'];
        $notification->notification_type = 'commentreply';
        $notification->is_deleted = '0';
        $notification->status = '1';
        $notification->created_date = "$date";
        $notification->updated_date = "$date";
        $post_details = PostForm::find()->where(['_id' => $_POST['post_id']])->one();
        $comment_details = Comment::find()->where(['_id' => $_POST['comment_id']])->one();
        $notification->post_owner_id = $comment_details['user_id'];
        if($comment_details['user_id'] != "$userid" && $post_details['post_privacy'] != "Private")
        {
            $notification->insert();
        }
             
        $result = LoginForm::find()->where(['_id' => $userid])->one();
        $comment_reply = $last_comment_data =  Comment::find()->with('user')->with('post')->where(['_id' => "$last_insert_id",'status' => '1'])->one();
        if(($userid == $post['post_user_id']) || ($userid == $result['_id']))
            {
				$afun_post = 'deleteComment';
				$atool_post = 'Delete';
            }
            else
            {
				$afun_post = 'hideComment';
				$atool_post = 'Hide';
            }
      
	   if($last_comment_data['user']['fb_id'] == '' && $last_comment_data['user']['photo'] == '')
       {
           $photo = 'profile/'.$last_comment_data['user']['gender'].'.jpg';
       }
       else if($last_comment_data['user']['photo'] != '' && $last_comment_data['user']['fb_id'] == '')
       {
            $photo = 'profile/'.$last_comment_data['user']['photo'];
       }
       else
       {
           if(substr($last_comment_data['user']['photo'],0,4) == 'http')
                $photo = $last_comment_data['user']['photo'];
          else
              $photo = 'profile/'.$last_comment_data['user']['photo'];
       }
       $id = $last_comment_data['user']['_id'];
       $cid = "`".$last_comment_data['_id']."`";
       $status_comment = Like::getUserCommentLike($id,$last_comment_data['_id']);
        if($status_comment == '1' )
        { $flag = 'Unlike';}else { $flag = 'Like'; }
        
        $comment_time = Yii::$app->EphocTime->comment_time(time(),$last_comment_data['created_date']);  
        $hidecomment = new HideComment();
		$hidecomment = HideComment::find()->where(['user_id' => (string)$user_id])->one();
		$hide_comment_ids = explode(',',$hidecomment['comment_ids']); 
		if(!(in_array($comment_reply['_id'],$hide_comment_ids)))
		{
			if(($userid == $post['post_user_id']) || ($userid == $comment_reply['user']['_id']))
			{
				$bfun_post = 'deleteComment';
				$btool_post = 'Delete';
			}
			else
			{
				$bfun_post = 'hideComment';
				$btool_post = 'Hide';
			}
			$comment_time = Yii::$app->EphocTime->comment_time(time(),$comment_reply['created_date']);

			$like_count = Like::find()->where(['comment_id' => (string) $comment_reply['_id']  ,'status' => '1'])->all();

			$user_ids = ArrayHelper::map(Like::find()->select(['user_id'])->where(['comment_id' => (string) $comment_reply['_id'], 'like_type' => 'comment', 'status' => '1'])->orderBy(['updated_date'=>SORT_DESC])->all(), 'user_id', 'user_id');
			$comlikeuseinfo = UserForm::find()->select(['_id','fname', 'lname'])->asArray()->where(['in','_id',$user_ids])->all();

			$usrbox = array();
			foreach ($comlikeuseinfo as $key => $single) {
				$fullnm = $single['fname'] . ' ' . $single['lname'];
				$usrbox[(string)$single['_id']] = $fullnm; 
			}

            $newlike_buddies = implode("<br/>", $usrbox);
            $id = $comment_reply['user']['_id'];
            ?>      <div class="pcomment comment-reply" id="comment_<?=$comment_reply['_id']?>">
                        <div class="img-holder">
                            <div class="profiletipholder" id="commentptip-6">
                                <span class="profile-tooltip tooltipstered">
                                    <img class="circle" src="<?=$this->getimage($comment_reply['user']['_id'],'thumb')?>">
                                </span>
                            </div>
                        </div>
                        <div class="desc-holder">
                            <div class="normal-mode">
                                <div class="desc">
                                    <a class="userlink" href="<?=Url::to(['userwall/index', 'id' => "$id"]) ?>"><?=ucfirst($comment_reply['user']['fname']).' '.ucfirst($comment_reply['user']['lname'])?></a>
                                    <p data-id="<?=$comment_reply['_id']?>" id="text_<?= $comment_reply['_id']?>"><?=$comment_reply['comment']?></p>
                                </div>
                                <div class="comment-stuff">
                                  <div class="more-opt">
                                     <a class='dropdown-button more_btn' href='javascript:void(0)' data-activates='subset_<?=$comment_reply['_id']?>'>
                                          <i class="zmdi zmdi-more"></i>
                                      </a>
                                                                                                      
                                      <!-- Dropdown Structure -->
                                      <ul id='subset_<?=$comment_reply['_id']?>' class='dropdown-content custom_dropdown'>
                                      <?php if($status == '10') { ?> 
                                          <li><a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$comment_reply['_id']?>')">Flag</a></li>
                                      <?php } else { ?>                                                                    
                                      <?php if(($userid != $post['post_user_id']) && ($userid != $comment_reply['user']['_id'])) { ?>
                                          <li><a href="javascript:void(0)" class="close-comment <?=$Auth?> directcheckuserauthclass" onclick="<?= $afun_post?>('<?=$comment_reply['_id']?>')"><i class="mdi mdi-close mdi-20px "></i><?=$atool_post?></a></li>
                                      <?php } else { ?>
                                          <?php if($userid == $comment_reply['user']['_id']){ ?>
                                          <li>
                                            <a class="edit-comment" href="javascript:void(0)">Edit</a>
                                          </li>
                                          <?php } ?>
                                          <li>
                                            <a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$comment_reply['_id']?>','<?=$post['_id']?>')">Delete</a>
                                          </li>
                                      <?php } ?>
                                      <?php } ?>
                                      </ul>        
                                  </div>
                              </div>	
                            </div>
                            <div style="clear:both"></div>
                            <div class="normode-action">
                              <span class="likeholder">
                                 <span class="like-tooltip">
                                    <div class="fixed-action-btn horizontal direction-top direction-left" >
                                      <?php
                                      $like_active = Like::find()->where(['comment_id' => (string)$comment_reply['_id'],'status' => '1','user_id' => (string) $user_id])->one();
                                      $likeIcon = '';
                                      if(!empty($like_active)) {
                                          $likeIcon = 'bold';
                                      }
                                      ?>
                                      <a href="javascript:void(0)" class="post-like commentcounttitle_<?=$comment_reply['_id']?> <?=$likeIcon?>" data-title='<?=$newlike_buddies?>' onclick="likeComment('<?=$comment_reply['_id']?>')" title="Like">Like</a>
                                    </div>
                                 </span>
                              </span>  
                              <a href="javascript:void(0)" class="pa-reply reply-comment post-reply" title="Reply">
                                 Reply
                              </a>
                              <div class="post-time"><?=$comment_time?></div>
                            </div>
                            <div class="edit-mode">
                                <div class="desc">
                  									<div class="cmntarea underlined fullwidth">
                  										<textarea data-adaptheight class="editcomment-tt data-adaptheight" data-id="<?=$comment_reply['_id'];?>" id="edit_comment_<?=$comment_reply['_id']?>"><?=$comment_reply['comment']?></textarea>
                  									</div>																			
                                    <a class="editcomment-cancel" href="javascript:void(0)"><i class="mdi mdi-close mdi-20px"></i></a>
                                </div>																			
                            </div>
                        </div>
                    </div>
        <?php 
		}                        
        //}
		}
		}
		else {
        	return 'checkuserauthclassg';
        }
    }
	
    public function actionIndex()
    { 
       $session = Yii::$app->session;
       $request = Yii::$app->request; 
       $user_id = $request->get('id');  
            
       if($session->get('email')){
               
            $user_data =  Personalinfo :: getPersonalInfo($user_id);
           
            $user_connections =  Connect::getuserConnections($user_id);
           
            $user_basicinfo = LoginForm::find()->where(['_id' => $user_id])->one();
      
            $posts = PostForm::getUserPost($user_id);

            $photos =  PostForm::getUserPostPhotos($user_id);
                    
            return $this->render('index',array('posts' => $posts,'user_connections' => $user_connections,'user_basicinfo' => $user_basicinfo,'user_data' => $user_data));
        } else {
                return $this->render('index', [
                   'model' => $model,
               ]);
        }
    }
  
    public function actionLoadComment()
    { 
		$session = Yii::$app->session;
    $status = $session->get('status');
        $email = $session->get('email');
        $post['_id'] = $post_id = $_POST['pid'];
        $from_ctr = $_POST['from_ctr'];
        $to_ctr = $_POST['to_ctr'];
        $uid = (string)$session->get('user_id');
        $user_id = $userid = (string)$session->get('user_id');
        $result = LoginForm::find()->where(['email' => $email])->one();
        $loaded_comments = Comment::find()->with('user')->with('post')->where(['post_id' => "$post_id",'status' => '1','parent_comment_id'=>'0'])->orderBy(['created_date'=>SORT_ASC])->offset($from_ctr)->limit($to_ctr)->all();
		$Auth = '';
		if(isset($user_id) && $user_id != '') 
		{
		$authstatus = UserForm::isUserExistByUid($user_id);
		if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') 
		{
			$Auth = $authstatus;
		}
		}   
		else    
		{
			$Auth = 'checkuserauthclassg';
		}
		
        ?>
                       
		<?php 
		 if(count($loaded_comments)>0){
			foreach($loaded_comments as $init_comment)
			{ 
				$post_user_id = new HideComment();
				$post_user_id = PostForm::find()->where(['_id' => (string)$init_comment['post_id']])->one(); 
				$post_user_id = $post_user_id['post_user_id'];
				$commentcount =Comment::getCommentReply($init_comment['_id']);
				$comment_time = Yii::$app->EphocTime->comment_time(time(),$init_comment['updated_date']);
				$hidecomments = new HideComment();
				$hidecomments = HideComment::find()->where(['user_id' => (string)$user_id])->one();
				$hide_comments_ids = explode(',',$hidecomments['comment_ids']); 
				if(!(in_array($init_comment['_id'],$hide_comments_ids)))
				{
					if(($userid == $post_user_id) || ($userid == $init_comment['user']['_id']))
					{
						$afun_post = 'deleteComment';
						$atool_post = 'Delete';
					}
					else
					{
						$afun_post = 'hideComment';
						$atool_post = 'Hide';
					}

				  $like_count = Like::find()->where(['comment_id' => (string) $init_comment['_id']  ,'status' => '1'])->all();

                  $user_ids = ArrayHelper::map(Like::find()->select(['user_id'])->where(['comment_id' => (string) $init_comment['_id'], 'like_type' => 'comment', 'status' => '1'])->orderBy(['updated_date'=>SORT_DESC])->all(), 'user_id', 'user_id');
                  $comlikeuseinfo = UserForm::find()->select(['_id','fname', 'lname'])->asArray()->where(['in','_id',$user_ids])->all();

                  $usrbox = array();
                  foreach ($comlikeuseinfo as $key => $single) {
                  $fullnm = $single['fname'] . ' ' . $single['lname'];
                  $usrbox[(string)$single['_id']] = $fullnm; 
                  }

                  $newlike_buddies = implode("<br/>", $usrbox);
                  $id = $init_comment['user']['_id'];

		?>
                    <div class="pcomment-holder"  id="comment_<?=$init_comment['_id']?>">
                        <div class="pcomment main-comment">
                            <div class="img-holder">
                                <div id="commentptip-4" class="profiletipholder">
                                    <span class="profile-tooltip">
                                        <?php $init_comment_img = $this->getimage($init_comment['user']['_id'],'thumb'); ?>
                                        <img class="circle" src="<?= $init_comment_img?>"/>
                                    </span>
                                </div>
                            </div>
                            <div class="desc-holder">
                                <div class="normal-mode">
                                    <div class="desc">
                                        <a href="<?=Url::to(['userwall/index', 'id' => "$id"])?>" class="userlink"><?=ucfirst($init_comment['user']['fname']).' '.ucfirst($init_comment['user']['lname'])?></a>
                                        <p data-id="<?=$init_comment['_id']?>" id="text_<?= $init_comment['_id']?>"><?=$init_comment['comment']?></p>
                                    </div>
                                    <div class="comment-stuff">
                                      <div class="more-opt">
                                         <a class='dropdown-button more_btn' href='javascript:void(0)' data-activates='subset_<?=$init_comment['_id']?>'>
                                              <i class="zmdi zmdi-more"></i>
                                          </a>
                                                                                                          
                                          <!-- Dropdown Structure -->
                                          <ul id='subset_<?=$init_comment['_id']?>' class='dropdown-content custom_dropdown'>
                                          <?php if($status == '10') { ?> 
                                              <li><a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$init_comment['_id']?>')">Flag</a></li>
                                          <?php } else { ?>                                                                    
                                          <?php if(($userid != $post['post_user_id']) && ($userid != $init_comment['user']['_id'])) { ?>
                                              <li><a href="javascript:void(0)" class="close-comment <?=$Auth?> directcheckuserauthclass" onclick="<?= $afun_post?>('<?=$init_comment['_id']?>')"><i class="mdi mdi-close mdi-20px "></i><?=$atool_post?></a></li>
                                          <?php } else { ?>
                                              <?php if($userid == $init_comment['user']['_id']){ ?>
                                              <li>
                                                <a class="edit-comment" href="javascript:void(0)">Edit</a>
                                              </li>
                                              <?php } ?>
                                              <li>
                                                <a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$init_comment['_id']?>','<?=$post['_id']?>')">Delete</a>
                                              </li>
                                          <?php } ?>
                                          <?php } ?>
                                          </ul>        
                                      </div>
                                  </div>
                                </div>
                                <div style="clear:both"></div>
                                <div class="normode-action">
                                  <span class="likeholder">
                                     <span class="like-tooltip">
                                        <div class="fixed-action-btn horizontal direction-top direction-left" >
                                          <?php
                                          $like_active = Like::find()->where(['comment_id' => (string)$init_comment['_id'],'status' => '1','user_id' => (string) $user_id])->one();
                                          $likeIcon = '';
                                          if(!empty($like_active)) {
                                              $likeIcon = 'bold';
                                          }
                                          ?>
                                          <a href="javascript:void(0)" class="post-like commentcounttitle_<?=$init_comment['_id']?> <?=$likeIcon?>" data-title='<?=$newlike_buddies?>' onclick="likeComment('<?=$init_comment['_id']?>')" title="Like">Like</a>
                                        </div>
                                     </span>
                                  </span>  
                                  <a href="javascript:void(0)" class="pa-reply reply-comment post-reply" title="Reply">
                                     Reply
                                  </a>
                                  <div class="post-time"><?=$comment_time?></div>
                                </div>
                                <div class="edit-mode">
                                    <div class="desc">
										<div class="cmntarea underlined fullwidth">
											<textarea data-adaptheight class="editcomment-tt data-adaptheight" data-id="<?=$init_comment['_id']?>" id="edit_comment_<?=$init_comment['_id']?>"><?=$init_comment['comment']?></textarea>
										</div>
                                        <a class="editcomment-cancel" href="javascript:void(0)"><i class="mdi mdi-close mdi-20px"></i></a>
                                    </div>                                                                          
                                </div>
                            </div>                              
                        </div>  
                        <div class="clear"></div>

                        <div class="comment-reply-holder reply_comments_<?=$init_comment['_id']?>">
                        <?php $comment_replies =Comment::getCommentReply($init_comment['_id']);
                            if(count($comment_replies)>0) {
                            $lastcomment = Comment::find()->where(['parent_comment_id' => (string)$init_comment['_id']])->orderBy(['updated_date'=>SORT_DESC])->one();
                            $last_comment_time = Yii::$app->EphocTime->comment_time(time(),$lastcomment['updated_date']);
                        ?>
                        <div class="comments-reply-summery">
                            <a href="javascript:void(0)" onclick="openReplies(this)">
                                <i class="mdi mdi-share"></i>
                                <?=count($comment_replies)?>
                                <?php if(count($comment_replies)>1) { ?> 
                                  Replies
                                <?php } else { ?> 
                                  Reply
                                <?php } ?>
                            </a>
                        </div>
                        <?php }
                            if(!empty($comment_replies))
                            {
                                foreach($comment_replies AS $comment_reply) { 
                                $hidecomment = new HideComment();
                                $hidecomment = HideComment::find()->where(['user_id' => (string)$user_id])->one();
                                $hide_comment_ids = explode(',',$hidecomment['comment_ids']); 
                                if(!(in_array($comment_reply['_id'],$hide_comment_ids)))
                                {
                                    if(($userid == $post_user_id) || ($userid == $comment_reply['user']['_id']))
                                    {
                                        $bfun_post = 'deleteComment';
                                        $btool_post = 'Delete';
                                    }
                                    else
                                    {
                                        $bfun_post = 'hideComment';
                                        $btool_post = 'Hide';
                                    }

                                    $like_count = Like::find()->where(['comment_id' => (string) $comment_reply['_id']  ,'status' => '1'])->all();

                  $user_ids = ArrayHelper::map(Like::find()->select(['user_id'])->where(['comment_id' => (string) $comment_reply['_id'], 'like_type' => 'comment', 'status' => '1'])->orderBy(['updated_date'=>SORT_DESC])->all(), 'user_id', 'user_id');
                  $comlikeuseinfo = UserForm::find()->select(['_id','fname', 'lname'])->asArray()->where(['in','_id',$user_ids])->all();

                  $usrbox = array();
                  foreach ($comlikeuseinfo as $key => $single) {
                  $fullnm = $single['fname'] . ' ' . $single['lname'];
                  $usrbox[(string)$single['_id']] = $fullnm; 
                  }

                  $newlike_buddies = implode("<br/>", $usrbox);
				  $comment_time = Yii::$app->EphocTime->comment_time(time(),$comment_reply['updated_date']);
          $id = $comment_reply['user']['_id'];
                        ?>
                        <div class="comments-reply-details">
                            <div class="pcomment comment-reply"  id="comment_<?=$comment_reply['_id']?>">
                                <div class="img-holder">
                                    <div class="profiletipholder" id="commentptip-6">
                                        <span class="profile-tooltip tooltipstered">
                                            <img class="circle" src="<?=$this->getimage($comment_reply['user']['_id'],'thumb')?>">
                                        </span>
                                    </div>
                                </div>
                                <div class="desc-holder">
                                    <div class="normal-mode">
                                        <div class="desc">
                                            <a class="userlink" href="<?=Url::to(['userwall/index', 'id' => "$id"])?>"><?=ucfirst($comment_reply['user']['fname']).' '.ucfirst($comment_reply['user']['lname'])?></a>
                                            <p data-id="<?=$comment_reply['_id']?>" id="text_<?= $comment_reply['_id']?>"><?=$comment_reply['comment']?></p>
                                        </div>
                                        <div class="comment-stuff">
                                          <div class="more-opt">
                                             <a class='dropdown-button more_btn' href='javascript:void(0)' data-activates='subset_<?=$comment_reply['_id']?>'>
                                                  <i class="zmdi zmdi-more"></i>
                                              </a>
                                                                                                              
                                              <!-- Dropdown Structure -->
                                              <ul id='subset_<?=$comment_reply['_id']?>' class='dropdown-content custom_dropdown'>
                                              <?php if($status == '10') { ?> 
                                                  <li><a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$comment_reply['_id']?>')">Flag</a></li>
                                              <?php } else { ?>                                                                    
                                              <?php if(($userid != $post['post_user_id']) && ($userid != $comment_reply['user']['_id'])) { ?>
                                                  <li><a href="javascript:void(0)" class="close-comment <?=$Auth?> directcheckuserauthclass" onclick="<?= $afun_post?>('<?=$comment_reply['_id']?>')"><i class="mdi mdi-close mdi-20px "></i><?=$atool_post?></a></li>
                                              <?php } else { ?>
                                                  <?php if($userid == $comment_reply['user']['_id']){ ?>
                                                  <li>
                                                    <a class="edit-comment" href="javascript:void(0)">Edit</a>
                                                  </li>
                                                  <?php } ?>
                                                  <li>
                                                    <a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$comment_reply['_id']?>','<?=$post['_id']?>')">Delete</a>
                                                  </li>
                                              <?php } ?>
                                              <?php } ?>
                                              </ul>        
                                          </div>
                                      </div>
                                    </div>
                                    <div style="clear:both"></div>
                                    <div class="normode-action">
                                      <span class="likeholder">
                                         <span class="like-tooltip">
                                            <div class="fixed-action-btn horizontal direction-top direction-left" >
                                              <?php
                                              $like_active = Like::find()->where(['comment_id' => (string)$comment_reply['_id'],'status' => '1','user_id' => (string) $user_id])->one();
                                              $likeIcon = '';
                                              if(!empty($like_active)) {
                                                  $likeIcon = 'bold';
                                              }
                                              ?>
                                              <a href="javascript:void(0)" class="post-like commentcounttitle_<?=$comment_reply['_id']?> <?=$likeIcon?>" data-title='<?=$newlike_buddies?>' onclick="likeComment('<?=$comment_reply['_id']?>')" title="Like">Like</a>
                                            </div>
                                         </span>
                                      </span>  
                                      <a href="javascript:void(0)" class="pa-reply reply-comment post-reply" title="Reply">
                                         Reply
                                      </a>
                                      <div class="post-time"><?=$comment_time?></div>
                                    </div>
                                    <div class="edit-mode">
                                        <div class="desc">
											<div class="cmntarea underlined fullwidth">
												<textarea data-adaptheight class="editcomment-tt data-adaptheight" data-id="<?=$comment_reply['_id']?>" id="edit_comment_<?=$comment_reply['_id']?>"><?=$comment_reply['comment']?></textarea>
											</div>																			
                                            <a class="editcomment-cancel" href="javascript:void(0)"><i class="mdi mdi-close mdi-20px"></i></a>
                                        </div>																			
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } } } ?>
                        </div>

                        <div class="comment-reply-holder comment-addreply">                                 
                            <div class="addnew-comment comment-reply">                          
                                <div class="img-holder"><a href="javascript:void(0)"><img src="<?= $this->getimage($result['_id'],'thumb');?>"/></a></div>
                                <div class="desc-holder">                                   
                                    <div class="cmntarea">
                                        <textarea data-adaptheight name="reply_txt" placeholder="Write a reply" data-postid="<?=$post['_id']?>" data-commentid="<?=$init_comment['_id']?>" id="reply_txt_<?=$init_comment['_id']?>" class="reply_class data-adaptheight"></textarea>
                                    </div>
                                </div>  
                            </div>
                        </div>
                    </div>
			<?php }
		    } 
		 }
    }
	
    function comment_html($post_id, $comment_id)
    {
        $session = Yii::$app->session;
        $post_id = $_POST['post_id'];
        $status = $session->get('status');
        $uid = $userid= (string)$session->get('user_id');
        $init_comment['_id'] = $comment_id;
	    $userid = $user_id = (string)$session->get('user_id');
		
		
		$Auth = '';
		if(isset($userid) && $userid != '') 
		{
			$authstatus = UserForm::isUserExistByUid($userid);
			if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') 
			{
				$Auth = $authstatus;
			}
		}   
		else    
		{
			$Auth = 'checkuserauthclassg';
		}

		 
        $post = PostForm::find()->where(['_id' => $post_id])->one();
        $init_comment =  Comment::find()->with('user')->with('post')->where(['_id' => "$comment_id",'status' => '1'])->one();
        $result = LoginForm::find()->where(['_id' => $uid])->one();
        $comment_time = Yii::$app->EphocTime->comment_time(time(),$init_comment['created_date']); 
        $comments = Comment::find()->with('user')->with('post')->where(['post_id' => "$post_id",'status' => '1','parent_comment_id'=>'0'])->orderBy(['created_date'=>SORT_DESC])->all();

        $comments = Comment::find()->with('user')->with('post')->where(['post_id' => "$post_id",'status' => '1','parent_comment_id'=>'0'])->orderBy(['created_date'=>SORT_DESC])->count();

        $commentscount = Comment::getAllPostLike($post_id);
			if(($userid == $post['post_user_id']) || ($userid == $init_comment['user']['_id']))
            {
                $afun_post = 'deleteComment';
                $atool_post = 'Delete';
            }
            else
            {
                $afun_post = 'hideComment';
                $atool_post = 'Hide';
            }

            $comment_time = Yii::$app->EphocTime->comment_time(time(),$init_comment['updated_date']);
            $hidecomments = new HideComment();
            $hidecomments = HideComment::find()->where(['user_id' => (string)$user_id])->one();
            $hide_comments_ids = explode(',',$hidecomments['comment_ids']);
            if(!(in_array($init_comment['_id'],$hide_comments_ids)))
            {
            if(($userid == $post['post_user_id']) || ($userid == $init_comment['user']['_id']))
            {
                $afun_post = 'deleteComment';
                $atool_post = 'Delete';
            }
            else
            {
                $afun_post = 'hideComment';
                $atool_post = 'Hide';
            }
            $like_count = Like::find()->where(['comment_id' => (string) $init_comment['_id']  ,'status' => '1'])->all();
            $user_ids = ArrayHelper::map(Like::find()->select(['user_id'])->where(['comment_id' => (string) $init_comment['_id'], 'like_type' => 'comment', 'status' => '1'])->orderBy(['updated_date'=>SORT_DESC])->all(), 'user_id', 'user_id');
            $comlikeuseinfo = UserForm::find()->select(['_id','fname', 'lname'])->asArray()->where(['in','_id',$user_ids])->all();
            $usrbox = array();
            foreach ($comlikeuseinfo as $key => $single) {
              $fullnm = $single['fname'] . ' ' . $single['lname'];
              $usrbox[(string)$single['_id']] = $fullnm; 
            }
            $newlike_buddies = implode("<br/>", $usrbox);
            $id = $init_comment['user']['_id'];
            ?>

			<div id="comment_<?=$init_comment['_id']?>" class="pcomment-holder commentcountid_<?=$comment_id?> recent" data-commentcount='<?=count($commentscount)?>'>

			<div class="pcomment main-comment">
				<div class="img-holder">
					<div id="commentptip-4" class="profiletipholder">
						<span class="profile-tooltip">
							<?php $init_comment_img = $this->getimage($init_comment['user']['_id'],'thumb'); ?>
							<img class="circle" src="<?= $init_comment_img?>"/>
						</span>
					</div>
				</div>
				<div class="desc-holder">
					<div class="normal-mode">
						<div class="desc">
							<a href="<?=Url::to(['userwall/index', 'id' => "$id"])?>" class="userlink"><?=ucfirst($init_comment['user']['fname']).' '.ucfirst($init_comment['user']['lname'])?></a>
							<p data-id="<?=$init_comment['_id']?>" id="text_<?= $init_comment['_id']?>"><?=$init_comment['comment']?></p>
						</div>
						<div class="comment-stuff">
              <div class="more-opt">
                  <a class='dropdown-button more_btn' href='javascript:void(0)' data-activates='subset_<?=$init_comment['_id']?>'>
                      <i class="zmdi zmdi-more"></i>
                  </a>
                                                                                  
                  <!-- Dropdown Structure -->
                  <ul id='subset_<?=$init_comment['_id']?>' class='dropdown-content custom_dropdown'>
                  <?php if($status == '10') { ?> 
                      <li><a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$init_comment['_id']?>')">Flag</a></li>
                  <?php } else { ?>                                                                    
                  <?php if(($userid != $post['post_user_id']) && ($userid != $init_comment['user']['_id'])) { ?>
                      <li><a href="javascript:void(0)" class="close-comment <?=$Auth?> directcheckuserauthclass" onclick="<?= $afun_post?>('<?=$init_comment['_id']?>')"><i class="mdi mdi-close mdi-20px "></i><?=$atool_post?></a></li>
                  <?php } else { ?>
                      <?php if($userid == $init_comment['user']['_id']){ ?>
                      <li>
                        <a class="edit-comment" href="javascript:void(0)">Edit</a>
                      </li>
                      <?php } ?>
                      <li>
                        <a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$init_comment['_id']?>','<?=$post['_id']?>')">Delete</a>
                      </li>
                  <?php } ?>
                  <?php } ?>
                  </ul>        
              </div>
          </div>
					</div>
          <div style="clear:both"></div>
          <div class="normode-action">
            <span class="likeholder">
               <span class="like-tooltip">
                  <div class="fixed-action-btn horizontal direction-top direction-left" >
                    <?php
                    $like_active = Like::find()->where(['comment_id' => (string)$init_comment['_id'],'status' => '1','user_id' => (string) $user_id])->one();
                    $likeIcon = '';
                    if(!empty($like_active)) {
                        $likeIcon = 'bold';
                    }
                    ?>
                    <a href="javascript:void(0)" class="post-like commentcounttitle_<?=$init_comment['_id']?> <?=$likeIcon?>" data-title='<?=$newlike_buddies?>' onclick="likeComment('<?=$init_comment['_id']?>')" title="Like">Like</a>
                  </div>
               </span>
            </span>  
            <a href="javascript:void(0)" class="pa-reply reply-comment post-reply" title="Reply">
               Reply
            </a>
            <div class="post-time"><?=$comment_time?></div>
          </div>
					<div class="edit-mode">
						<div class="desc">
							<div class="cmntarea underlined fullwidth">
								<textarea data-adaptheight class="editcomment-tt data-adaptheight" data-id="<?=$init_comment['_id']?>" id="edit_comment_<?=$init_comment['_id']?>"><?=$init_comment['comment']?></textarea>
							</div>                                                                          
							<a class="editcomment-cancel" href="javascript:void(0)"><i class="mdi mdi-close mdi-20px"></i></a>
						</div>                                                                          
					</div>
				</div>                              
			</div>  
			<div class="clear"></div>
                                    
			<div class="comment-reply-holder reply_comments_<?=$init_comment['_id']?>">
			<?php $comment_replies =Comment::getCommentReply($init_comment['_id']);
				if(!empty($comment_replies))
				{
					foreach($comment_replies AS $comment_reply) { 
					$hidecomment = new HideComment();
					$hidecomment = HideComment::find()->where(['user_id' => (string)$user_id])->one();
					$hide_comment_ids = explode(',',$hidecomment['comment_ids']); 
					if(!(in_array($comment_reply['_id'],$hide_comment_ids)))
					{
						if(($userid == $post['post_user_id']) || ($userid == $comment_reply['user']['_id']))
						{
							$bfun_post = 'deleteComment';
							$btool_post = 'Delete';
						}
						else
						{
							$bfun_post = 'hideComment';
							$btool_post = 'Hide';
						}
					$comment_time = Yii::$app->EphocTime->comment_time(time(),$comment_reply['updated_date']);

					$like_count = Like::find()->where(['comment_id' => (string) $comment_reply['_id']  ,'status' => '1'])->all();

				  $user_ids = ArrayHelper::map(Like::find()->select(['user_id'])->where(['comment_id' => (string) $comment_reply['_id'], 'like_type' => 'comment', 'status' => '1'])->orderBy(['updated_date'=>SORT_DESC])->all(), 'user_id', 'user_id');
				  $comlikeuseinfo = UserForm::find()->select(['_id','fname', 'lname'])->asArray()->where(['in','_id',$user_ids])->all();

				  $usrbox = array();
				  foreach ($comlikeuseinfo as $key => $single) {
					  $fullnm = $single['fname'] . ' ' . $single['lname'];
					  $usrbox[(string)$single['_id']] = $fullnm; 
					}

              $newlike_buddies = implode("<br/>", $usrbox);
              $id = $comment_reply['user']['_id'];
			?>
			<div class="pcomment comment-reply" id="comment_<?=$comment_reply['_id']?>">
				<div class="img-holder">
					<div class="profiletipholder" id="commentptip-6">
						<span class="profile-tooltip tooltipstered">
							<img class="circle" src="<?=$this->getimage($comment_reply['user']['_id'],'thumb')?>">
						</span>
					</div>
				</div>
				<div class="desc-holder">
					<div class="normal-mode">
						<div class="desc">
							<a class="userlink" href="<?=Url::to(['userwall/index', 'id' => "$id"])?>"><?=ucfirst($comment_reply['user']['fname']).' '.ucfirst($comment_reply['user']['lname'])?></a>
							<p data-id="<?=$comment_reply['_id']?>" id="text_<?=$comment_reply['_id']?>"><?=$comment_reply['comment']?></p>
						</div>
						<div class="comment-stuff">
              <div class="more-opt">
                  <a class='dropdown-button more_btn' href='javascript:void(0)' data-activates='subset_<?=$comment_reply['_id']?>'>
                      <i class="zmdi zmdi-more"></i>
                  </a>
                                                                                  
                  <!-- Dropdown Structure -->
                  <ul id='subset_<?=$comment_reply['_id']?>' class='dropdown-content custom_dropdown'>
                  <?php if($status == '10') { ?> 
                      <li><a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$comment_reply['_id']?>')">Flag</a></li>
                  <?php } else { ?>                                                                    
                  <?php if(($userid != $post['post_user_id']) && ($userid != $comment_reply['user']['_id'])) { ?>
                      <li><a href="javascript:void(0)" class="close-comment <?=$Auth?> directcheckuserauthclass" onclick="<?= $afun_post?>('<?=$comment_reply['_id']?>')"><i class="mdi mdi-close mdi-20px "></i><?=$atool_post?></a></li>
                  <?php } else { ?>
                      <?php if($userid == $comment_reply['user']['_id']){ ?>
                      <li>
                        <a class="edit-comment" href="javascript:void(0)">Edit</a>
                      </li>
                      <?php } ?>
                      <li>
                        <a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$comment_reply['_id']?>','<?=$post['_id']?>')">Delete</a>
                      </li>
                  <?php } ?>
                  <?php } ?>
                  </ul>        
              </div>
          </div>
					</div>
          <div style="clear:both"></div>
          <div class="normode-action">
            <span class="likeholder">
               <span class="like-tooltip">
                  <div class="fixed-action-btn horizontal direction-top direction-left" >
                    <?php
                    $like_active = Like::find()->where(['comment_id' => (string)$comment_reply['_id'],'status' => '1','user_id' => (string) $user_id])->one();
                    $likeIcon = '';
                    if(!empty($like_active)) {
                        $likeIcon = 'bold';
                    }
                    ?>
                    <a href="javascript:void(0)" class="post-like commentcounttitle_<?=$comment_reply['_id']?> <?=$likeIcon?>" data-title='<?=$newlike_buddies?>' onclick="likeComment('<?=$comment_reply['_id']?>')" title="Like">Like</a>
                  </div>
               </span>
            </span>  
            <a href="javascript:void(0)" class="pa-reply reply-comment post-reply" title="Reply">
               Reply
            </a>
            <div class="post-time"><?=$comment_time?></div>
          </div>
					<div class="edit-mode">
						<div class="desc">
							<div class="cmntarea underlined fullwidth">
								<textarea data-adaptheight class="editcomment-tt data-adaptheight" data-id="<?=$comment_reply['_id']?>" id="edit_comment_<?=$comment_reply['_id']?>"><?=$comment_reply['comment']?></textarea>
							</div>																			
							<a class="editcomment-cancel" href="javascript:void(0)"><i class="mdi mdi-close mdi-20px"></i></a>
						</div>																			
					</div>
				</div>
			</div>
			<?php } } } ?>
			</div>
                                    
			<div class="comment-reply-holder comment-addreply">                                 
				<div class="addnew-comment comment-reply">                          
					<div class="img-holder"><a href="javascript:void(0)"><img src="<?= $this->getimage($result['_id'],'thumb');?>"/></a></div>
					<div class="desc-holder">                                   
						<div class="cmntarea">
							<textarea data-adaptheight name="reply_txt" placeholder="Write a reply" data-postid="<?=$post['_id']?>" data-commentid="<?=$init_comment['_id']?>" id="reply_txt_<?=$init_comment['_id']?>" class="reply_class data-adaptheight"></textarea>
						</div>
					</div>  
				</div>
			</div>
		</div>
		<?php  } 
    }
	
   public function actionEditReplyComment()
   {
        $comment_id = (string)$_POST['comment_id'];
        $edit_comment = str_replace(array("\n","\r\n","\r"), '', $_POST['edit_comment']);
        if(!empty($comment_id) && isset($comment_id) && !empty($edit_comment) && isset($edit_comment))
        {
			$session = Yii::$app->session;
			$user_id = (string)$session->get('user_id');
			$date = time();
			$model = new Comment();
			$query = Comment::find()->where(['_id' => $comment_id, 'user_id' => $user_id])->one();
			if($query)
			{
				$editcomment = new Comment();
				$editcomment = Comment::find()->where(['_id' => $comment_id, 'user_id' => $user_id])->one();
				$editcomment->comment = $edit_comment;
				$editcomment->updated_date = $date;
				if($editcomment->update())
				{
					print true;
				}
				else{
					print false;
				}
			}
			else{
				print false;
			}
        }
        else{
            print false;
        }
   }
   
   public function actionViewGalaryComments() 
	{
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		$userid = (string)$session->get('user_id');
		$status = $session->get('status');
		$email = $session->get('email');
      
        $post_id = $_POST['post_id'];
        $uniqId = rand(999, 999999).time();
        
        $Auth = '';
        if(isset($userid) && $userid != '') 
        {
        $authstatus = UserForm::isUserExistByUid($userid);
        if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') 
        {
            $Auth = $authstatus;
        }
        }   
        else    
        {
            $Auth = 'checkuserauthclassg';
        }
        
        if ($session->get('email'))
        {
            $result = LoginForm::find()->where(['_id' => $user_id])->one();
            $post = PinImage::find()->where(['_id' => $post_id])->asarray()->one();

            $postId = $post['post_id'];
            $init_comments = Comment::getFirstPostComments($postId);
            $postData = PostForm::find()->where([(string)'_id' => $postId])->asarray()->one();
            $post = array_merge($post, $postData);
            $comments = Comment::getAllPostLike($postId);
		?>
<div class="modal_content_container">
  <div class="modal_content_child modal-content">
    <div class="popup-title ">
        <button class="hidden_close_span close_span">
        <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
        </button>     
        <h3>All Comments</h3>
        <a type="button" class="item_done crop_done waves-effect hidden_close_span custom_close" href="javascript:void(0)">Done</a>
        <a type="button" class="item_done crop_done waves-effect comment-close custom_close" href="javascript:void(0)"><i class="mdi mdi-close	"></i></a>
    </div>

		<div class="popup-content">
			<div class="content-holder nsidepad nbpad">
				<div class="photolike-list nice-scroll">
					<div class="post-holder">
						<div class="post-data">		
							<div class="comments-section panel open">

								<?php if(count($init_comments)>0){ ?>
								<div class="post-more">
									<?php if(count($comments)>1){ ?>
									<a href="javascript:void(0)" class="view-morec view-morec-<?=$post['_id']?>" onclick="showPreviousComments('<?=$post['_id']?>')">View more comments</a>
									
									<span class="total-comments commment-ctr-<?=$post['_id']?>" id="commment-ctr-<?=$post['_id']?>"><font class="ctrdis_<?=$post['_id']?>"><?=count($init_comments)?></font> of <font class="countdisplay_<?=$post['_id']?>"><?=count($comments)?></font></span>
									<?php } ?>
								</div>
								<?php } ?>
								<input type="hidden" name="from_ctr" id="from_ctr_<?=$post['_id']?>" value="<?=count($init_comments)?>">      
								<input type="hidden" name="to_ctr" id="to_ctr_<?=$post['_id']?>" value="<?=count($comments)?>">

                                <div class="post-comments post_comment_<?=$post['_id']?>">                     
                                	<div class="pcomments sub_post_comment_<?=$post['_id']?>">                                    
                                		<?php
                                		if(count($init_comments)>0) { 
                                			foreach($init_comments as $init_comment) {
                                				$comment_time = Yii::$app->EphocTime->comment_time(time(),$init_comment['updated_date']);$commentcount =Comment::getCommentReply($init_comment['_id']);
                                				$hidecomments = new HideComment();
                                				$hidecomments = HideComment::find()->where(['user_id' => (string)$user_id])->one();
                                				$hide_comments_ids = explode(',',$hidecomments['comment_ids']);
                                				if(!(in_array($init_comment['_id'],$hide_comments_ids)))
                                				{
                                					if(($userid == $post['post_user_id']) || ($userid == $init_comment['user']['_id']))
                                					{
                                						$afun_post = 'deleteComment';
                                						$atool_post = 'Delete';
                                					}
                                					else
                                					{
                                						$afun_post = 'hideComment';
                                						$atool_post = 'Hide';
                                					}
                                          $id = $init_comment['user']['_id'];
                                		?>
                                		<div class="pcomment-holder" id="comment_<?=$init_comment['_id']?>">
                                			<div class="pcomment main-comment">
                                				<div class="img-holder">
                                					<div id="commentptip-4" class="profiletipholder">
                                						<span class="profile-tooltip">
                                							<?php $init_comment_img = $this->getimage($init_comment['user']['_id'],'thumb'); ?>
                                							<img class="circle" src="<?= $init_comment_img?>"/>
                                						</span>
                                					</div>
                                				</div> 
                                				<div class="desc-holder">
                                					<div class="normal-mode">
                                						<div class="desc">
                                							<a href="<?=Url::to(['userwall/index', 'id' => "$id"])?>" class="userlink"><?=ucfirst($init_comment['user']['fname']).' '.ucfirst($init_comment['user']['lname'])?></a>
                                							<?php if(strlen($init_comment['comment'])>200){ ?>
                                								<p class="shorten" data-id="<?=$init_comment['_id']?>" id="text_<?= $init_comment['_id']?>"><?=$init_comment['comment']?>
                                								<a href="javascript:void(0)" class="overlay" onclick="explandReadMore(this)"><span class="readlink">Read More</span></a>
                                								</p>
                                							<?php }else{ ?>                                                     
                                								<p data-id="<?=$init_comment['_id']?>" id="text_<?= $init_comment['_id']?>"><?=$init_comment['comment']?></p>
                                							<?php } ?>
                                						</div>
                                						<div class="comment-stuff">
                                              <div class="more-opt">
                                                  <a class='dropdown-button more_btn' href='javascript:void(0)' data-activates='subset_<?=$init_comment['_id']?>'>
                                                      <i class="zmdi zmdi-more"></i>
                                                  </a>
                                                                                                                  
                                                  <!-- Dropdown Structure -->
                                                  <ul id='subset_<?=$init_comment['_id']?>' class='dropdown-content custom_dropdown'>
                                                  <?php if($status == '10') { ?> 
                                                      <li><a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$init_comment['_id']?>')">Flag</a></li>
                                                  <?php } else { ?>                                                                    
                                                  <?php if(($userid != $post['post_user_id']) && ($userid != $init_comment['user']['_id'])) { ?>
                                                      <li><a href="javascript:void(0)" class="close-comment <?=$Auth?> directcheckuserauthclass" onclick="<?= $afun_post?>('<?=$init_comment['_id']?>')"><i class="mdi mdi-close mdi-20px "></i><?=$atool_post?></a></li>
                                                  <?php } else { ?>
                                                      <?php if($userid == $init_comment['user']['_id']){ ?>
                                                      <li>
                                                        <a class="edit-comment" href="javascript:void(0)">Edit</a>
                                                      </li>
                                                      <?php } ?>
                                                      <li>
                                                        <a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$init_comment['_id']?>','<?=$post['_id']?>')">Delete</a>
                                                      </li>
                                                  <?php } ?>
                                                  <?php } ?>
                                                  </ul>        
                                              </div>
                                          </div>
                                					</div>
                                          <div style="clear:both"></div>
                                          <div class="normode-action">
                                            <span class="likeholder">
                                               <span class="like-tooltip">
                                                  <div class="fixed-action-btn horizontal direction-top direction-left" >
                                                    <?php
                                                    $like_active = Like::find()->where(['comment_id' => (string)$init_comment['_id'],'status' => '1','user_id' => (string) $user_id])->one();
                                                    $likeIcon = '';
                                                    if(!empty($like_active)) {
                                                        $likeIcon = 'bold';
                                                    }
                                                    ?>
                                                    <a href="javascript:void(0)" class="post-like commentcounttitle_<?=$init_comment['_id']?> <?=$likeIcon?>" data-title='<?=$newlike_buddies?>' onclick="likeComment('<?=$init_comment['_id']?>')" title="Like">Like</a>
                                                  </div>
                                               </span>
                                            </span>  
                                            <a href="javascript:void(0)" class="pa-reply reply-comment post-reply" title="Reply">
                                               Reply
                                            </a>
                                            <div class="post-time"><?=$comment_time?></div>
                                          </div>
                                					<div class="edit-mode">
                                						<div class="desc">
                                							<div class="cmntarea underlined fullwidth">
                                								<textarea data-adaptheight class="editcomment-tt materialize-textarea data-adaptheight" data-id="<?=$init_comment['_id'];?>" id="edit_comment_<?=$init_comment['_id']?>"><?=$init_comment['comment']?></textarea>
                                							</div>
                                							<a class="editcomment-cancel" href="javascript:void(0)"><i class="mdi mdi-close mdi-20px"></i></a>
                                						</div>                                                                          
                                					</div>
                                				</div>                              
                                			</div>  
                                			<div class="clear"></div>
                                			
                                			<div class="comment-reply-holder reply_comments_<?=$init_comment['_id']?>">
                                				<?php $comment_replies =Comment::getCommentReply($init_comment['_id']);
                                					if(count($comment_replies)>0) {
                                					$lastcomment = Comment::find()->where(['parent_comment_id' => (string)$init_comment['_id']])->orderBy(['updated_date'=>SORT_DESC])->one();
                                					$last_comment_time = Yii::$app->EphocTime->comment_time(time(),$lastcomment['updated_date']);
                                				?>
                                				<div class="comments-reply-summery">
                                					<a href="javascript:void(0)" onclick="openReplies(this)">
                                						<i class="mdi mdi-share"></i>
                                						<?=count($comment_replies)?>
                                            <?php if(count($comment_replies)>1) { ?>
                                              Replies
                                            <?php } else { ?>
                                              Reply
                                            <?php } ?>
                                					</a>
                                				</div>
                                				<?php }
                                				if(!empty($comment_replies))
                                				{
                                					foreach($comment_replies AS $comment_reply) { 
                                					$hidecomment = new HideComment();
                                					$hidecomment = HideComment::find()->where(['user_id' => (string)$user_id])->one();
                                					$hide_comment_ids = explode(',',$hidecomment['comment_ids']); 
                                					if(!(in_array($comment_reply['_id'],$hide_comment_ids)))
                                					{
                                						if(($userid == $post['post_user_id']) || ($userid == $comment_reply['user']['_id']))
                                						{
                                							$bfun_post = 'deleteComment';
                                							$btool_post = 'Delete';
                                						}
                                						else
                                						{
                                							$bfun_post = 'hideComment';
                                							$btool_post = 'Hide';
                                						}
                                					$comment_time = Yii::$app->EphocTime->comment_time(time(),$comment_reply['updated_date']);

                                					$like_count = Like::find()->where(['comment_id' => (string) $comment_reply['_id']  ,'status' => '1'])->all();

                                					$user_ids = ArrayHelper::map(Like::find()->select(['user_id'])->where(['comment_id' => (string) $comment_reply['_id'], 'like_type' => 'comment', 'status' => '1'])->orderBy(['updated_date'=>SORT_DESC])->all(), 'user_id', 'user_id');
                                					$comlikeuseinfo = UserForm::find()->select(['_id','fname', 'lname'])->asArray()->where(['in','_id',$user_ids])->all();

                                					$usrbox = array();
                                					foreach ($comlikeuseinfo as $key => $single) {
                                					$fullnm = $single['fname'] . ' ' . $single['lname'];
                                					$usrbox[(string)$single['_id']] = $fullnm; 
                                					}

                                					$newlike_buddies = implode("<br/>", $usrbox);
                                          $id = $comment_reply['user']['_id'];
                                			?>
                                			<div class="comments-reply-details">
                                				<div class="pcomment comment-reply" id="comment_<?=$comment_reply['_id']?>">
                                					<div class="img-holder">
                                						<div class="profiletipholder" id="commentptip-6">
                                							<span class="profile-tooltip tooltipstered">
                                								<img class="circle" src="<?=$this->getimage($comment_reply['user']['_id'],'thumb')?>">
                                							</span>
                                						</div>
                                					</div>
                                					<div class="desc-holder">
                                						<div class="normal-mode">
                                							<div class="desc">
                                								<a class="userlink" href="<?=Url::to(['userwall/index', 'id' => "$id"])?>"><?=ucfirst($comment_reply['user']['fname']).' '.ucfirst($comment_reply['user']['lname'])?></a>
                                								
                                								<?php if(strlen($comment_reply['comment'])>200){ ?>
                                									<p class="shorten" data-id="<?=$comment_reply['_id']?>" id="text_<?= $comment_reply['_id']?>"><?=$comment_reply['comment']?><a href="javascript:void(0)" class="overlay" onclick="explandReadMore(this)"><span class="readlink">Read More</span></a>
                                									</p>
                                								<?php }else{ ?>                                                         
                                									<p data-id="<?=$comment_reply['_id']?>" id="text_<?= $comment_reply['_id']?>"><?=$comment_reply['comment']?></p>
                                								<?php } ?>
                                							</div>
                                							<div class="comment-stuff">
                                                <div class="more-opt">
                                                    <a class='dropdown-button more_btn' href='javascript:void(0)' data-activates='subset_<?=$init_comment['_id']?>'>
                                                        <i class="zmdi zmdi-more"></i>
                                                    </a>
                                                                                                                    
                                                    <!-- Dropdown Structure -->
                                                    <ul id='subset_<?=$init_comment['_id']?>' class='dropdown-content custom_dropdown'>
                                                    <?php if($status == '10') { ?> 
                                                        <li><a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$init_comment['_id']?>')">Flag</a></li>
                                                    <?php } else { ?>                                                                    
                                                    <?php if(($userid != $post['post_user_id']) && ($userid != $init_comment['user']['_id'])) { ?>
                                                        <li><a href="javascript:void(0)" class="close-comment <?=$Auth?> directcheckuserauthclass" onclick="<?= $afun_post?>('<?=$init_comment['_id']?>')"><i class="mdi mdi-close mdi-20px "></i><?=$atool_post?></a></li>
                                                    <?php } else { ?>
                                                        <?php if($userid == $init_comment['user']['_id']){ ?>
                                                        <li>
                                                          <a class="edit-comment" href="javascript:void(0)">Edit</a>
                                                        </li>
                                                        <?php } ?>
                                                        <li>
                                                          <a href="javascript:void(0)" class="delete-comment" onclick="<?= $afun_post?>('<?=$init_comment['_id']?>','<?=$post['_id']?>')">Delete</a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php } ?>
                                                    </ul>        
                                                </div>
                                            </div> 
                                						</div>
                                            <div style="clear:both"></div>
                                            <div class="normode-action">
                                              <span class="likeholder">
                                                 <span class="like-tooltip">
                                                    <div class="fixed-action-btn horizontal direction-top direction-left" >
                                                      <?php
                                                      $like_active = Like::find()->where(['comment_id' => (string)$init_comment['_id'],'status' => '1','user_id' => (string) $user_id])->one();
                                                      $likeIcon = '';
                                                      if(!empty($like_active)) {
                                                          $likeIcon = 'bold';
                                                      }
                                                      ?>
                                                      <a href="javascript:void(0)" class="post-like commentcounttitle_<?=$init_comment['_id']?> <?=$likeIcon?>" data-title='<?=$newlike_buddies?>' onclick="likeComment('<?=$init_comment['_id']?>')" title="Like">Like</a>
                                                    </div>
                                                 </span>
                                              </span>  
                                              <a href="javascript:void(0)" class="pa-reply reply-comment post-reply" title="Reply">
                                                 Reply
                                              </a>
                                              <div class="post-time"><?=$comment_time?></div>
                                            </div>
                                						<div class="edit-mode">
                                							<div class="desc">
                                								<div class="cmntarea underlined fullwidth">
                                									<textarea data-adaptheight class="editcomment-tt materialize-textarea data-adaptheight" data-id="<?=$comment_reply['_id']?>" id="edit_comment_<?=$comment_reply['_id']?>"><?=$comment_reply['comment']?></textarea>
                                								</div>
                                								<a class="editcomment-cancel" href="javascript:void(0)"><i class="mdi mdi-close mdi-20px"></i></a>
                                							</div>                                                                          
                                						</div>
                                					</div>
                                				</div>
                                			</div>
                                			<?php } } } ?>
                                			</div>
                                			
                                			<div class="comment-reply-holder comment-addreply">                                 
                                				<div class="addnew-comment comment-reply">                          
                                					<div class="img-holder"><a href="javascript:void(0)"><img src="<?= $this->getimage($result['_id'],'thumb');?>"/></a></div>
                                					<div class="desc-holder">                                   
                                						<div class="cmntarea">
                                							<textarea data-adaptheight name="reply_txt" placeholder="Write a reply" data-postid="<?=$post['_id']?>" data-commentid="<?=$init_comment['_id']?>" id="reply_txt_<?=$init_comment['_id']?>" class="reply_class materialize-textarea data-adaptheight"></textarea>
                                						</div>  
                                					</div>  
                                				</div>
                                			</div>
                                		</div>
                                		<?php  } } } ?>
                                	</div>
                                	
                                </div>
							</div>
						</div>
					</div>

				</div>

			</div>
		</div>

        <?php if($post['comment_setting'] != 'Disable'){
            if(isset($post['trav_item']) && $post['trav_item']== '1')
            {
                $comment_placeholder = "Ask question or send query";
            }
            else{
                $comment_placeholder = "Write a comment";
            }   
            ?>
            <div class="addnew-comment valign-wrapper">
                <?php $comment_image = $this->getimage($result['_id'],'thumb'); ?>
                <div class="img-holder"><a href="javascript:void(0)"><img src="<?= $comment_image?>"/></a></div>
                <form name="imageCommentForm" id="imageCommentForm" enctype="multipart/form-data">
                    <div class="desc-holder">                                   
                        <div class="cmntarea">
                            <textarea data-adaptheight data-postid="<?=(string)$post['_id']?>" id="comment_txt_<?=(string)$post['_id']?>" placeholder="<?= $comment_placeholder;?>" class="comment_class  materialize-textarea data-adaptheight"></textarea>
                        </div>
                    </div>
                    
                </form>
            </div>
            <?php } ?>


  </div>
  </div>
		<?php	
		}
		else{
			return $this->goHome();
		}
	}
}