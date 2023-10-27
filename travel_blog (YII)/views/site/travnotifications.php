<?php
use frontend\assets\AppAsset;
use yii\helpers\Url;
use frontend\models\ReadNotification;
use frontend\models\HideNotification;
use frontend\models\LoginForm;
use frontend\models\Connect;
use frontend\models\Like;
use frontend\models\Page;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email');
$user_id = (string)$session->get('user_id');

$this->title = 'Notifications';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
    <div class="page-wrapper  notifications-page hidemenu-wrapper show-sidebar">
        <div class="header-section">
            <?php include('../views/layouts/header.php'); ?>
        </div>
            
        <div class="floating-icon">
        
            <div class="scrollup-btnbox anim-side btnbox scrollup-float">
                <div class="scrollup-button float-icon"><span class="icon-holder ispan"><i class="mdi mdi-arrow-up-bold-circle"></i></span></div>          
            </div>            
        </div>
        <div class="clear"></div>
        <div class="container page_container"> 
			<?php include('../views/layouts/leftmenu.php'); ?>
			<div class="fixed-layout">
			
				<div id="notification-layout" class="main-content with-lmenu sub-page notification">
					<div class="combined-column">
						<div class="content-box bshadow">
							<div class="cbox-title">						
								Notifications
							</div>
							<?php if(!empty($notifications) && count($notifications)>0){ ?>
							<div class="fullside-list">
								<ul class="noti-listing">
									<?php $notcnt = 0;
										foreach($notifications as $notification)
										{
											$liked = Like::getPageLike($notification['page_id']);
											if((isset($notification['page_id']) && !empty($notification['page_id']) && $liked && $notification['notification_type'] == 'post' && $liked['updated_date'] < $notification['updated_date']) || (($notification['notification_type'] == 'likepost' || $notification['notification_type'] == 'comment' || $notification['notification_type'] == 'pagereview' || $notification['notification_type'] == 'onpagewall') && $notification['page_id'] != null) || $notification['page_id'] == null){
											$connect_user_id = $notification['user_id'];
											$is_connect = Connect::find()->where(['from_id' => "$user_id",'to_id' => "$connect_user_id",'status' => '1'])->one();
											$connecton = $is_connect['updated_date'];
											$nottime = $notification['updated_date'];
											$xcollection_owner_id = $notification['collection_owner_id'];
											$xcollection_id = $notification['collection_id'];
											
											if($connecton <= $nottime){
											if($notification['notification_type'] == 'sharepost')
											{
												$not_img = $this->context->getimage($notification['shared_by'],'thumb');
											}
											else if($notification['notification_type'] == 'deletepostadmin' || $notification['notification_type'] == 'publishpost'|| $notification['notification_type'] == 'deletecollectionadmin' || $notification['notification_type'] == 'publishcollection' || $notification['notification_type'] == 'deletepageadmin' || $notification['notification_type'] == 'publishpage')
											{
												$not_img = $this->context->getimage('admin','thumb');
											}
											else if($notification['notification_type'] == 'editpostuser')
											{
												$not_img = $this->context->getimage($notification['post']['post_user_id'],'thumb');
											}
											else if ($notification['notification_type'] == 'editcollectionuser')
											{
												 $not_img = $this->context->getimage($notification['collection_owner_id'],'thumb');
											}
											else if($notification['notification_type'] == 'onpagewall')
											{
												$not_img = $this->context->getimage($notification['user_id'],'thumb');
											}
											else if($notification['notification_type'] == 'low_credits')
											{
												$not_img = $this->context->getimage($notification['user_id'],'thumb');
											}
											else if($notification['notification_type'] == 'followcollection' || $notification['notification_type'] == 'sharecollection') 
											{
												$not_img = $this->context->getimage($notification['user_id'],'thumb');
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
													$not_img = $this->context->getpageimage($notification['page_id']);
												}
												else
												{
													$not_img = $this->context->getimage($notification['user']['_id'],'thumb');
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
												$notificationOwnerName = $this->context->getuserdata($notification['user_id'],'fullname');
												$name = $notificationOwnerName;
											}
											else if($notification['notification_type'] == 'deletepostadmin' || $notification['notification_type'] == 'publishpost'|| $notification['notification_type'] == 'deletecollectionadmin' || $notification['notification_type'] == 'publishcollection' || $notification['notification_type'] == 'deletepageadmin' || $notification['notification_type'] == 'publishpage')
											{
												$name = 'Iaminjapan Admin';
											}
											else if($notification['notification_type'] == 'editpostuser')
											{
												$name = $this->context->getuserdata($notification['post']['post_user_id'],'fullname');
											}
											else if ($notification['notification_type'] == 'editcollectionuser')
											{
												$name = $this->context->getuserdata($notification['collection_owner_id'],'fullname');
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
											$nid = $notification['_id'];
											$userread = ReadNotification::find()->where(['user_id' => "$user_id"])->one();
											if ($userread)
											{
												if (strstr($userread['notification_ids'], "$nid"))
												{
													$read = 'read';
												}
												else
												{
													$read = 'unread';
												}
											}
											else
											{
												$read = 'unread';
											}
											$hidenot = HideNotification::find()->where(['user_id' => "$user_id"])->one();
											if ($hidenot)
											{
												if (strstr($hidenot['notification_ids'], "$nid"))
												{
													$hide = 'hide';
												}
												else
												{
													$hide = 'unhide';
												}
											}
											else
											{
												$hide = 'unhide';
											}
									?>
									<li class="mainli <?php if($hide == 'hide'){ ?>dis-none<?php } ?> <?php if($read == 'read'){ ?>read<?php } ?>" id="hidenot_<?=$nid?>">
										<div class="noti-holder">
											<?php if($notification['notification_type'] == 'connectrequestaccepted') { ?>
												<a href="<?php $fromid = $notification['user_id']; echo Url::to(['userwall/index', 'id' => "$fromid"]);?>">
											<?php } 
											
											else if($notification['notification_type'] == 'low_credits') { ?>
												<a href="<?php echo Url::to(['site/credits']);?>">
											<?php }
											else if($notification['notification_type'] == 'sharecollection') { ?>
												<a href="<?php echo Url::to(['collection/detail', 'col_id'=> "$xcollection_id" ]);?>">
											<?php }
											else if($notification['notification_type'] == 'pageinvite' || $notification['notification_type'] == 'pageinvitereview' || $notification['notification_type'] == 'pageinvitereview' || $notification['notification_type'] == 'likepage' || $notification['entity'] == 'page' || $notification['notification_type'] == 'page_role_type') {
												if($notification['entity'] == 'page') { $npostid = $notification['page_id']; } ?>
												<a href="<?php echo Url::to(['page/index', 'id' => "$npostid"]);?>">
											<?php } else if($notification['notification_type'] == 'deletecollectionadmin' || $notification['notification_type'] == 'editcollectionuser' || $notification['notification_type'] == 'publishcollection') { ?>
											<?php } else if($notification['notification_type'] == 'deletepageadmin' || $notification['notification_type'] == 'editpageuser' || $notification['notification_type'] == 'publishpage') { ?>
												<a href="<?php echo Url::to(['page/index', 'id' => "$npostid"]);?>">	
												<a href="<?php echo Url::to(['collection/detail', 'col_id' => "$npostid"]);?>">	
											<?php } else if($notification['notification_type'] == 'invitereferal' || $notification['notification_type'] == 'replyreferal') { $frnid = $notification['user_id'];?>
												<a href="<?php echo Url::to(['userwall/index', 'id' => "$frnid"]);?>">
											<?php } else if($notification['notification_type'] == 'addreferal') { $frnid = $notification['from_connect_id'];?>
												<a href="<?php echo Url::to(['userwall/index', 'id' => "$frnid"]);?>">
											<?php }  else { ?>
												<a href="<?php echo Url::to(['site/travpost', 'postid' => "$npostid"]);?>">
											<?php } ?>
												<span class="img-holder">
													<img src="<?= $not_img ?>" class="img-responsive">
												</span>
												<span class="desc-holder">
													<span class="desc">
															<?php if($notification['notification_type'] != 'sharepost') { ?> <span class="btext"><?php echo $name;?></span><?php } ?>
															<?php if($notification['notification_type']=='likepost' || $notification['notification_type']== 'like'){ ?> Likes your post: <?php echo $notification['post']['post_text'];?>
															<?php } else if($notification['notification_type']=='likecomment'){ ?> Likes your comment: View Post
															<?php } else if($notification['notification_type'] == 'sharepost'){ ?> <?php echo $name;?> <?php echo $notification['post']['post_text'];?>
															<?php } else if($notification['notification_type'] == 'comment'){ 
															if($notification['post_owner_id'] == "$userid"){ ?> Commented on your post: <?php } else {  ?>Commented on the post you are Tagged in: <?php echo $notification['post']['post_text']; } ?>
															<?php } else if($notification['notification_type'] == 'tag_connect'){ ?> Tagged in the post: <?php echo $notification['post']['post_text'];?>
															<?php } else if($notification['notification_type'] == 'post'){ ?>
															<?php if($notification['page_id'] != null){echo 'page';} ?>
															
															
															Added new post: <?php echo $notification['post']['post_text'];?>
															
															<?php } else if($notification['notification_type'] == 'commentreply'){ ?> Replied on your comment: <?php echo $notification['post']['post_text'];?>
															<?php } else if($notification['notification_type'] == 'connectrequestaccepted'){ ?> Accepted your connect request.
															<?php } else if($notification['notification_type'] == 'connectrequestdenied'){ ?> Denied your connect request.
															<?php } else if($notification['notification_type'] == 'onwall'){ ?> Write on your wall.
															<?php } else if($notification['notification_type'] == 'pageinvitereview'){
															$page_info = Page::Pagedetails($npostid);
															?> Invited to review <?=$page_info['page_name']?> page.
															<?php } else if($notification['notification_type'] == 'low_credits'){
															?> Credit is Tipping low.
															<?php } else if($notification['notification_type'] == 'followcollection'){
																$collectionName = $this->context->getcollectionname($notification['collection_id']);
																echo ' Followed your ' .$collectionName.' Collection';

															} else if($notification['notification_type'] == 'sharecollection'){
																$notificationOwnerName = $this->context->getuserdata($notification['user_id'],'fullname');
															?> Collection is Shared By <?=$notificationOwnerName?>.
															<?php else if($notification['notification_type'] == 'pagereview'){
															$page_info = Page::Pagedetails($npostid);
															?> Reviewed <?=$page_info['page_name']?> page.
															<?php } else if($notification['notification_type'] == 'pageinvite'){
															$page_details = Page::Pagedetails($npostid);
															?> Invited to like <?=$page_details['page_name']?> page.
															<?php } else if($notification['notification_type'] == 'likepage'){
															$page_details = Page::Pagedetails($npostid);
															?> Liked <?=$page_details['page_name']?> page.
															<?php } else if($notification['notification_type'] == 'onpagewall'){
															$page_info = Page::Pagedetails($npostid);
															?> Write on <?=$page_info['page_name']?> page.
															<?php } else if($notification['notification_type'] == 'deletepostadmin'){
															?> Flaged your post for <?php echo $notification['flag_reason']; ?>.
															<?php } else if($notification['notification_type'] == 'deletecollectionadmin'){
															?> Flaged your collection for <?php echo $notification['flag_reason']; ?>.
															<?php } else if($notification['notification_type'] == 'deletepageadmin'){
															?> Flaged your page for <?php echo $notification['flag_reason']; ?>.
															<?php } else if($notification['notification_type'] == 'publishpost'){
															?> Approved your post.
															<?php } else if($notification['notification_type'] == 'publishcollection'){
															?> Approved your collection.
															<?php } else if($notification['notification_type'] == 'publishpage'){
															?> Approved your Page.
															<?php } else if($notification['notification_type'] == 'editpostuser'){
															?> has edited flaged post.
															<?php } else if($notification['notification_type'] == 'editcollectionuser'){
															?> has edited flaged collection.
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
													<span class="time-stamp">
														<?php if($notification['notification_type']=='likepost' || $notification['notification_type']== 'like' || $notification['notification_type']== 'likepage'){ ?><i class="zmdi zmdi-thumb-up"></i>
														<?php } else if($notification['notification_type']== 'comment') {?> <i class="mdi mdi-comment"></i> 
														<?php }else if($notification['notification_type'] == 'sharepost'){ ?><i class="mdi mdi-share-variant"></i> 
														<?php }else if($notification['notification_type'] == 'pagereview'){ ?><i class="mdi mdi-pencil-square"></i> 
														<?php } ?>
													</span>
												</span>
											</a>
											<?php if($hide != 'hide'){ ?>
											<div class="dropdown dropdown-custom">
												<a class="dropdown-button nothemecolor" href="javascript:void(0)" data-activates="notification_<?=$notification['_id']?>">
 													<i class="zmdi zmdi-more-vert mdi-18px"></i>
 										  		</a>
												<ul id="notification_<?=$notification['_id']?>" class="dropdown-content custom_dropdown">
													<li><a href="javascript:void(0)" onclick="hideNot('<?=$notification['_id']?>')">Hide this notification</a></li>
													<li><a href="javascript:void(0)" onclick="delNot('<?=$notification['_id']?>')">Delete this notification</a></li>
													<li><a href="javascript:void(0)">Turn off notifications</a></li>
												</ul>
											</div>
											<?php } ?>
											<?php if($read != 'read'){ ?>
												<a href="javascript:void(0)" onclick="markNotRead(this,'<?=$notification['_id']?>')" class="readicon nothemecolor"><i class="mdi mdi-bullseye"></i></a>
											<?php } ?>
										</div>
									</li>
									<?php $notcnt++; } } } ?>
								</ul>	
							</div>
							<?php if($notcnt == 0){ ?>
							<?php $this->context->getnolistfound('nonotificationfound'); ?>
							<?php } ?>
							<?php } else { ?>
							<?php $this->context->getnolistfound('nonotificationfound'); ?>
							<?php } ?>
						</div>
					</div>
					<div id="chatblock">
						<div class="float-chat anim-side">
							<div class="chat-button float-icon directcheckuserauthclass" onclick="getchatcontent();"><span class="icon-holder">icon</span>
							</div>
						</div>
					</div>
					
				</div>
			</div>
        </div>	
        <?php include('../views/layouts/footer.php'); ?>
    </div>  
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<?php $this->endBody() ?> 
