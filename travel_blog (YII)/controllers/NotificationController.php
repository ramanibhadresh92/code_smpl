<?php
namespace frontend\controllers;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;
use frontend\models\LoginForm;
use frontend\models\Like;
use frontend\models\Page;
use frontend\models\Notification;
use frontend\models\ReadNotification;
use frontend\models\HideNotification;
use frontend\models\Connect;
use frontend\models\UserForm;

class NotificationController extends Controller
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

    public function actionNewNotificationCount() {
        $session = Yii::$app->session;
        $userid = (string)$session->get('user_id');
        $uxi = array('status' => false, 'notcount' => '', 'frdcount' => '');

        if($userid != '' && $userid != 'undefined') {
            $uxi = array('status' => true);
            $notInfo = Notification::getUserPostBudge();
            if($notInfo > 0) {
                $uxi['notcount'] = $notInfo;
            }
            $frdInfo = Connect::connectRequestbadge();
            if($frdInfo > 0) {
                $uxi['frdcount'] = $frdInfo;
            }
        }

        return json_encode($uxi, true);
    }
 
    public function actionNewNotification()
    {    
        $session = Yii::$app->session; 
        $userid = (string)$session->get('user_id');

        if($userid != '' && $userid != 'undefined') {
            $budge = 0;
            $budge = Notification::getUserPostBudge();
            $notifications = Notification::getAllNotification();
            ?>
            <input type="hidden" name="new_budge" id= "new_budge" value="<?=$budge?>"/>
            <span class="not-title">Notifications</span> 
            <?php if(!empty($notifications) && count($notifications)>0){ ?>
            <div class="not-resultlist nice-scroll">
                <ul class="noti-listing">
                    <?php $notcnt = 0;
                        foreach($notifications as $notification)
                        {
                            $notificationId = (string)$notification["_id"];
                            $liked = Like::getPageLike($notification['page_id']);
                            if((isset($notification['page_id']) && !empty($notification['page_id']) && $liked && $notification['notification_type'] == 'post' && $liked['updated_date'] < $notification['updated_date']) || (($notification['notification_type'] == 'likepost' || $notification['notification_type'] == 'comment' || $notification['notification_type'] == 'pagereview' || $notification['notification_type'] == 'onpagewall') && $notification['page_id'] != null) || $notification['page_id'] == null) {
                            $connect_user_id = $notification['user_id'];
                            $is_connect = Connect::find()->where(['from_id' => "$userid",'to_id' => "$connect_user_id",'status' => '1'])->one();
                            $connecton = $is_connect['updated_date'];
                            $nottime = $notification['updated_date'];
    						$xcollection_owner_id = $notification['collection_owner_id'];
    						$xcollection_id = $notification['collection_id'];
    						
                            if($connecton <= $nottime) {
                            if($notification['notification_type'] == 'sharepost')
                            {
                                $not_img = $this->getimage($notification['shared_by'],'thumb');
                            }
                            else if($notification['notification_type'] == 'deletepostadmin' || $notification['notification_type'] == 'publishpost' || $notification['notification_type'] == 'deletecollectionadmin' || $notification['notification_type'] == 'publishcollection' || $notification['notification_type'] == 'deletepageadmin' || $notification['notification_type'] == 'publishpage')
                            {
                                $not_img = $this->getimage('admin', 'thumb');
                            }
                            else if($notification['notification_type'] == 'editpostuser')
                            {
                                $not_img = $this->getimage($notification['post']['post_user_id'],'thumb');
                            }
    						else if ($notification['notification_type'] == 'editcollectionuser')
    						{
    							 $not_img = $this->getimage($notification['collection_owner_id'],'thumb');
    						}
                            else if($notification['notification_type'] == 'onpagewall')
                            {
                                $not_img = $this->getimage($notification['user_id'],'thumb');
                            }
    						else if($notification['notification_type'] == 'low_credits')
    						{
    							$not_img = $this->getimage($notification['user_id'],'thumb');
    						}
    						else if($notification['notification_type'] == 'followcollection' || $notification['notification_type'] == 'sharecollection')
    						{
    							$not_img = $this->getimage($notification['user_id'],'thumb');
    						}
    						else
                            {
                                if(isset($notification['page_id']) && !empty($notification['page_id']) && $notification['notification_type'] == 'post')
                                {
                                    if($_SERVER['HTTP_HOST'] == 'localhost')
                                    {
                                        $baseUrll = '/iaminjapan-code/frontend/web';
                                    }
                                    else
                                    {
                                        $baseUrll = '/frontend/web/assets/baf1a2d0';
                                    }
                                    $not_img = $this->getpageimage($notification['page_id']);
                                }
                                else
                                {
                                    $not_img = $this->getimage($notification['user']['_id'],'thumb');
                                }
                            }
                            if(isset($notification['post']['post_text']) && !empty($notification['post']['post_text'])) {
                                if(strlen($notification['post']['post_text']) > 20){
                                    $notification['post']['post_text'] = substr($notification['post']['post_text'],0,20);
                                    $notification['post']['post_text'] = substr($notification['post']['post_text'], 0, strrpos($notification['post']['post_text'], ' '));
                                }
                                else{
                                    $notification['post']['post_text'] = $notification['post']['post_text'];
                                }
                            }
                            if(empty($notification['post']['post_text']) && $notification['notification_type'] != 'likereply') {
                                if($notification['notification_type']!='connectrequestaccepted' && $notification['notification_type']!='connectrequestdenied' && $notification['notification_type']!='pageinvite' && $notification['notification_type']!='pageinvitereview' && $notification['notification_type']!='likepage')
                                {
                                    //$notification['post']['post_text'] = 'View Post';
                                }
                            } 
                            if($notification['notification_type'] == 'tag_connect')
                            {
                                $name = 'You';
                            }
    						else if($notification['notification_type'] == 'low_credits' || $notification['notification_type'] == 'followcollection' || $notification['notification_type'] == 'sharecollection')
    						{
    							$name = 'Your';
    						}
                            else if($notification['notification_type'] == 'deletepostadmin' || $notification['notification_type'] == 'publishpost' || $notification['notification_type'] == 'deletecollectionadmin' || $notification['notification_type'] == 'publishcollection' || $notification['notification_type'] == 'deletepageadmin' || $notification['notification_type'] == 'publishpage')
                            {
                                $name = 'Iaminjapan Admin';
                            }
                            else if($notification['notification_type'] == 'editpostuser')
                            {
                                $name = $this->getuserdata($notification['post']['post_user_id'],'fullname');
                            }
    						else if ($notification['notification_type'] == 'editcollectionuser')
    						{
    							$name = $this->getuserdata($notification['collection_owner_id'],'fullname');
    						}
    						else if($notification['notification_type'] == 'sharepost')
                            {
                                $usershare = LoginForm::find()->where(['_id' => $notification['user_id']])->one();
                                $usershare_id = $usershare['_id'];
                                if($notification['user_id'] == $userid){$user_name = 'Your';}else{ $user_name = $usershare['fullname']; }

                                $post_owner_id = LoginForm::find()->where(['_id' => $notification['post_owner_id']])->one();
                                $post_owner_id_name_id = $post_owner_id['_id'];
                                if($notification['post_owner_id'] == $userid){$post_owner_id_name = 'Your';}else{ $post_owner_id_name = $post_owner_id['fullname'].'\'s'; }

                                $shared_by = LoginForm::find()->where(['_id' => $notification['shared_by']])->one();
                                $shared_by_name_id = $shared_by['_id'];
                                if($notification['shared_by'] == $userid){$shared_by_name = 'You';}else{ $shared_by_name = $shared_by['fullname']; }
                                $name = "";
                                $name .= "<span class='btext'>";
                                $name .= $shared_by_name;
                                $name .= "</span> Shared <span class='btext'>";
                                $name .= $post_owner_id_name;
                                $name .= "</span> Post on <span class='btext'>";
                                $name .= $user_name;
                                $name .= "</span> Wall: ";
                            }
                            else
                            {
                                if(isset($notification['page_id']) && !empty($notification['page_id']) && $notification['notification_type'] == 'post')
                                {
                                    $page_id = Page::Pagedetails($notification['page_id']);
                                    $name = $page_id['page_name'];
                                }
                                else
                                {
                                    $name = ucfirst($notification['user']['fname']).' '.ucfirst($notification['user']['lname']);
                                }
                            }
                            $notification_time = Yii::$app->EphocTime->time_elapsed_A(time(),$notification['updated_date']);
                            $npostid = $notification['post_id'];
                            $fromid = $notification['user_id'];
                    ?>
                    <li id="noti_<?=$notificationId?>">
                        <div class="noti-holder">
                            <?php if($notification['notification_type'] == 'connectrequestaccepted') { ?>
                                <a href="<?=Url::to(['userwall/index', 'id' => "$fromid"])?>">
                            <?php } 
    						
    						if($notification['notification_type'] == 'low_credits') { ?>
    							<a href="<?=Url::to(['site/credits'])?>">
    						<?php } else if($notification['notification_type'] == 'followcollection') { ?>
    							<a href="<?=Url::to(['collection/detail', 'col_id'=> "$xcollection_id" ])?>">
    						<?php } else if($notification['notification_type'] == 'sharecollection') { ?>
    							<a href="<?=Url::to(['collection/detail', 'col_id'=> "$xcollection_id" ])?>">
    						<?php }
    						
    						else if($notification['notification_type'] == 'pageinvite' || $notification['notification_type'] == 'pageinvitereview' || $notification['notification_type'] == 'likepage' || $notification['entity'] == 'page' || $notification['notification_type'] == 'page_role_type') {
                                if($notification['entity'] == 'page') { $npostid = $notification['page_id']; } ?>
                                <a href="<?=Url::to(['page/index', 'id' => "$npostid"])?>">
                            <?php } else if($notification['notification_type'] == 'deletecollectionadmin' || $notification['notification_type'] == 'editcollectionuser' || $notification['notification_type'] == 'publishcollection') { ?>
    							<a href="<?=Url::to(['collection/detail', 'col_id' => "$npostid"])?>">
    						<?php } else if($notification['notification_type'] == 'deletepageadmin' || $notification['notification_type'] == 'editpageuser' || $notification['notification_type'] == 'publishpage') { ?>
    							<a href="<?=Url::to(['page/index', 'id' => "$npostid"])?>">	
    						<?php }  else if($notification['notification_type'] == 'invitereferal' || $notification['notification_type'] == 'replyreferal') { $frnid = $notification['user_id'];?>
    							<a href="<?=Url::to(['userwall/index', 'id' => "$frnid"])?>">
    						<?php } else if($notification['notification_type'] == 'addreferal') { $frnid = $notification['from_connect_id'];?>
    							<a href="<?=Url::to(['userwall/index', 'id' => "$frnid"])?>">
                             <?php } else { ?>
                                <a href="<?=Url::to(['site/travpost', 'postid' => "$npostid"])?>">
                            <?php } ?>
                                <span class="img-holder">
                                    <img class="img-responsive" src="<?= $not_img ?>">
                                </span>
                                <span class="desc-holder">
                                    <span class="desc">
                                        <?php if($notification['notification_type'] != 'sharepost') { ?> <span class="btext"><?=$name?></span><?php } ?>
                                        <?php if($notification['notification_type']=='likepost' || $notification['notification_type']== 'like'){ ?> Likes your post: <?=$notification['post']['post_text']?>
                                        <?php } else if($notification['notification_type']=='likecomment'){ ?> Likes your comment: View Post
                                        <?php } else if($notification['notification_type'] == 'sharepost'){ ?> <?=$name?> <?=$notification['post']['post_text']?>
                                        <?php } else if($notification['notification_type'] == 'comment'){  
    									             if($notification['post_owner_id'] == "$userid"){ ?> Commented on your post: <?php } else {  ?>Commented on the post you are Tagged in: <?=$notification['post']['post_text']?><?php } ?>
                                        <?php } else if($notification['notification_type'] == 'tag_connect'){ ?> Tagged in the post: <?=$notification['post']['post_text']?>
                                        <?php } else if($notification['notification_type'] == 'post'){ ?>
                                        Added new post: <?=$notification['post']['post_text']?>
    									<?php } else if($notification['notification_type'] == 'commentreply'){ ?> Replied on your comment: <?=$notification['post']['post_text']?>
                                        <span class="notif-icon mdc-bg-light-blue">
                                            <i class="zmdi zmdi-thumb-up"></i>
                                        </span>
                                        <?php } else if($notification['notification_type'] == 'connectrequestaccepted'){ ?> Accepted your connect request.
                                        <?php } else if($notification['notification_type'] == 'connectrequestdenied'){ ?> Denied your connect request.
                                        <?php } else if($notification['notification_type'] == 'onwall'){ ?> Write on your wall.
                                        <?php } else if($notification['notification_type'] == 'pageinvitereview'){
                                        $page_info = Page::Pagedetails($npostid);
                                        ?> Invited to review <?=$page_info['page_name']?> page.
    									<?php } else if($notification['notification_type'] == 'low_credits'){
    									?> Credit is Tipping low.
    									<?php } else if($notification['notification_type'] == 'followcollection'){
    										$name_collection_owner = $this->getuserdata($notification['user_id'],'fullname');
    									?> Collection is Followed By <?=$name_collection_owner?>.
    									<?php } else if($notification['notification_type'] == 'sharecollection'){
    										$name_collection_owner = $this->getuserdata($notification['user_id'],'fullname');
    									?> Collection is Shared By <?=$name_collection_owner?>.
    									<?php }
    									
    									else if($notification['notification_type'] == 'pagereview'){
                                        $page_info = Page::Pagedetails($npostid);
                                        ?> Reviewed <?=$page_info['page_name']?> page.
                                        <?php } else if($notification['notification_type'] == 'pageinvite'){
                                        $page_info = Page::Pagedetails($npostid);
                                        ?> Invited to like <?=$page_info['page_name']?> page.
                                        <?php } else if($notification['notification_type'] == 'likepage'){
                                        $page_info = Page::Pagedetails($npostid);
                                        ?> Liked <?=$page_info['page_name']?> page.
                                        <?php } else if($notification['notification_type'] == 'onpagewall'){
                                        $page_info = Page::Pagedetails($npostid);
                                        ?> Write on <?=$page_info['page_name']?> page.
                                        <?php } else if($notification['notification_type'] == 'deletepostadmin'){
                                        ?> Flaged your post for <?=$notification['flag_reason']?>.
    									<?php } else if($notification['notification_type'] == 'deletecollectionadmin'){
                                        ?> Flaged your collection for <?=$notification['flag_reason']?>.
    									<?php } else if($notification['notification_type'] == 'deletepageadmin'){
                                        ?> Flaged your page for <?=$notification['flag_reason']?>.
    									<?php } else if($notification['notification_type'] == 'editpostuser'){
                                        ?> has edited flaged post.
    									<?php } else if($notification['notification_type'] == 'editcollectionuser'){
                                        ?> has edited flaged collection.
                                        <?php } else if($notification['notification_type'] == 'publishpost'){
                                        ?> Approved your post.
    									<?php } else if($notification['notification_type'] == 'publishcollection'){
                                        ?> Approved your collection.
    									<?php } else if($notification['notification_type'] == 'publishpage'){
                                        ?> Approved your Page.
    									<?php } else if($notification['notification_type'] == 'invitereferal'){
                                        ?> Invited you for referal.
                                        <?php } else if($notification['notification_type'] == 'addreferal'){
                                        ?> Added referal for you.
                                        <?php } else if($notification['notification_type'] == 'replyreferal'){
                                        ?> Replied on your referal.
                                        <?php } else if($notification['notification_type'] == 'page_role_type'){
                                            $page_info = Page::Pagedetails($npostid);
                                            if($notification['status'] == '0'){$lblrole = 'Removed';}else{$lblrole = 'Added';}
                                        ?> <?=$lblrole?> you as <?=$notification['page_role_type']?> for <?=$page_info['page_name']?> page.
                                        <?php } else{ ?> Likes post<?php } ?>
                                    </span>

                                    <?php if($notification['notification_type']=='likepost' || $notification['notification_type']== 'like' || $notification['notification_type']== 'likepage'){ ?>
                                        <span class="notif-icon mdc-bg-light-blue">
                                            <i class="zmdi zmdi-thumb-up"></i>
                                        </span>
                                    <?php } else if($notification['notification_type']== 'comment') {?>
                                        <span class="notif-icon mdc-bg-green">
                                            <i class="zmdi zmdi-comment"></i>
                                        </span> 
                                    <?php }else if($notification['notification_type'] == 'sharepost'){ ?>
                                        <span class="notif-icon mdc-bg-amber">
                                            <i class="mdi mdi-share-variant"></i> 
                                        </span>
                                    <?php }else if($notification['notification_type'] == 'pageinvite'){ ?>
                                        <span class="notif-icon mdc-bg-light-blue">
                                            <i class="zmdi zmdi-thumb-up"></i>
                                        </span>
                                    <?php }else if($notification['notification_type'] == 'pageinvitereview'){ ?>
                                        <span class="notif-icon mdc-bg-amber">
                                            <i class="mdi mdi-pencil"></i> 
                                        </span>
                                    <?php }else if($notification['notification_type'] == 'pagereview'){ ?>
                                        <span class="notif-icon mdc-bg-amber">
                                            <i class="mdi mdi-pencil-square"></i> 
                                        </span>
                                    <?php } else { ?>
                                        <span class="notif-icon mdc-bg-amber">
                                            <i class="zmdi zmdi-local-activity"></i>
                                        </span>
                                    <?php } ?> 

                                    <span class="time-stamp">
                                        <?= $notification_time;?>
                                    </span>
                                </span>
                            </a>
                        </div>
                    </li>
            <?php $notcnt++; } } } ?>
            </ul>        
            </div>

            <?php if($notcnt > 0){ ?>
            <span class="not-result bshadow seemorenoti">
                <a href="<?=Url::to(['site/travnotifications'])?>">See More Notifications <i class="mdi mdi-menu-right"></i></a>
            </span>
            <?php } else { ?>
                <?php $this->getnolistfound('nonotificationfound');?>
            <?php } ?>
            <?php } else { ?>
                <?php $this->getnolistfound('nonotificationfound');?>
            <?php }
        }
    }
    public function actionViewNotification()
    { 
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
       
        $date = time();
        $data = array();
        $user_data = LoginForm::find()->where(['_id' => "$uid"])->one();
               
        if(!empty($user_data)) {
            $date = time();
            $user_data->last_logout_time = "$date";
            $user_data->update();
        }
        return 'done';
    }
    
    public function actionMarkasread()
    {
        if (isset($_POST['nid']) && !empty($_POST['nid'])) {
            $not_id = (string) $_POST["nid"];
            $session = Yii::$app->session;
            $user_id = (string) $session->get('user_id');
            $readnot = new ReadNotification();
            $userexist = ReadNotification::find()->where(['user_id' => $user_id])->one();
            if ($userexist)
            {
                if (strstr($userexist['notification_ids'], $not_id))
                {
                    $readnot = ReadNotification::find()->where(['user_id' => $user_id])->one();
                    $readnot->notification_ids = str_replace($not_id.',', '', $userexist['notification_ids']);
                    if ($readnot->update())
                    {
                        if(strlen($readnot['notification_ids'] < 11))
                        {
                            $readnot->delete();
                        }
                        print true;
                    }
                    else
                    {
                        print false;
                    }
                }
                else
                {
                    $readnot = ReadNotification::find()->where(['user_id' => $user_id])->one();
                    $readnot->notification_ids = $userexist['notification_ids'] . $not_id . ',';
                    if ($readnot->update())
                    {
                        print true;
                    }
                    else
                    {
                        print false;
                    }
                }
            }
            else
            {
                $readnot->user_id = $user_id;
                $readnot->notification_ids = $not_id . ',';
                if ($readnot->insert())
                {
                    print true;
                }
                else
                {
                    print false;
                }
            }
        }
    }
    
    public function actionHidenotification()
    {
        if (isset($_POST['nid']) && !empty($_POST['nid'])) { 
            $not_id = (string) $_POST["nid"];
            $session = Yii::$app->session;
            $user_id = (string) $session->get('user_id');
            $hidenot = new HideNotification(); 
            $userexist = HideNotification::find()->where(['user_id' => $user_id])->one();
            if (!empty($userexist)) {
                $notificationArray = $userexist['notification_ids'];
                $notificationArray = explode(',', $notificationArray);
                $notificationArray = array_filter($notificationArray);
                
                if(in_array($not_id, $notificationArray)) {
                    return true;
                } else {
                    $notificationArray[] = $not_id;
                    $hidenot = HideNotification::find()->where(['user_id' => $user_id])->one();
                    $hidenot->notification_ids = implode(",", $notificationArray);
                    if ($hidenot->update()) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                $hidenot->user_id = $user_id;
                $hidenot->notification_ids = $not_id;
                if ($hidenot->insert()) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }    
}
