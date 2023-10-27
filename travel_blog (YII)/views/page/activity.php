<?php 
use yii\helpers\Url;
use frontend\models\LoginForm;
use frontend\models\Page;
use frontend\models\ReadNotification;
use frontend\models\HideNotification;
?>
<h4><i class="zmdi zmdi-chart"></i> Activity Log</h4>
<div class="content-box">
		<div class="fullside-list">
				<ul class="noti-listing page-activity" id="page-activity">
	<?php if($notcount>0){ ?>
			<?php
				foreach($notification as $notification){
					$notification_time = Yii::$app->EphocTime->time_elapsed_A(time(),$notification['updated_date']);
					$name = ucfirst($notification['user']['fname']).' '.ucfirst($notification['user']['lname']);
					if($notification['user_id'] == $user_id) {
						$name = 'You';
					}
					$not_img = $this->context->getimage($notification['user']['_id'],'thumb');
					if($notification['notification_type'] == 'sharepost') {
						$not_img = $this->context->getimage($notification['shared_by'],'thumb');
					}
					if($notification['entity'] == 'page') { $npostid = $notification['page_id']; $pagelink = 'page/index'; $getid = 'id';}
					else{$npostid = $notification['post_id']; $pagelink = 'site/travpost'; $getid = 'postid';}
					if($notification['notification_type'] == 'sharepost') {
						$usershare = LoginForm::find()->where(['_id' => $notification['user_id']])->one();
						$usershare_id = $usershare['_id'];
						if($notification['user_id'] == $user_id){$user_name = 'Your';}else{ $user_name = $usershare['fullname']; }

						$post_owner_id = LoginForm::find()->where(['_id' => $notification['post_owner_id']])->one();
						$post_owner_id_name_id = $post_owner_id['_id'];
						if($notification['post_owner_id'] == $user_id){$post_owner_id_name = $post_owner_id['fullname'].'\'s';}else{ $post_owner_id_name = $post_owner_id['fullname'].'\'s'; }

						$shared_by = LoginForm::find()->where(['_id' => $notification['shared_by']])->one();
						$shared_by_name_id = $shared_by['_id'];
						if($notification['shared_by'] == $user_id){$shared_by_name = 'You';}else{ $shared_by_name = $shared_by['fullname']; }
						$name = "";
						$name .= "<span class='btext'>";
						$name .= $shared_by_name;
						$name .= "</span> Shared <span class='btext'>";
						$name .= $post_owner_id_name;
						$name .= "</span> Post on <span class='btext'>";
						$name .= $user_name;
						$name .= "</span> Wall: ";
						$pagelink = 'site/travpost';
						$getid = 'postid';
					}
					$npostid = $notification['post_id'];
					$nid = $notification['_id'];
					$userread = ReadNotification::find()->where(['user_id' => "$user_id"])->one();
					if ($userread) {
						if (strstr($userread['notification_ids'], "$nid")) {
							$read = 'read';
							$rt = 'Mark as unread';
						} else {
							$read = 'unread';
							$rt = 'Mark as read';
						}
					} else {
						$read = 'unread';
						$rt = 'Mark as read';
					}
					$hidenot = HideNotification::find()->where(['user_id' => "$user_id"])->one();
					if ($hidenot) {
						if (strstr($hidenot['notification_ids'], "$nid")) {
							$hide = 'hide';
						} else {
							$hide = 'unhide';
						}
					} else {
						$hide = 'unhide';
					}

					$time = time().rand(999, 9999);
				?>
				<li class="mainli <?php if($hide == 'hide'){ ?>dis-none<?php } ?> <?php if($read == 'read'){ ?>read<?php } ?>" id="hidenot_<?=$nid?>">
				<div class="noti-holder">
					<a href="<?php echo Url::to([$pagelink, $getid => "$npostid"]);?>">
						<span class="img-holder">
							<img class="img-responsive" src="<?=$not_img?>">
						</span>
						<span class="desc-holder">
							<span class="desc">
								<?php if($notification['notification_type'] != 'sharepost') { ?> <span class="btext"><?php echo $name;?></span><?php } ?>
								<?php if($notification['notification_type']=='likepost' || $notification['notification_type']== 'like'){ ?> Likes your post: <?php echo $notification['post']['post_text'];?>
								<?php } else if($notification['notification_type']=='likecomment'){ ?> Likes your comment: View Post
								<?php } else if($notification['notification_type'] == 'sharepost'){ ?> <?php echo $name;?> <?php echo $notification['post']['post_text'];?>
								<?php } else if($notification['notification_type'] == 'comment'){ ?> Commented on your post: <?php echo $notification['post']['post_text'];?>
								<?php } else if($notification['notification_type'] == 'tag_connect'){ ?> Tagged in the post: <?php echo $notification['post']['post_text'];?>
								<?php } else if($notification['notification_type'] == 'post'){ ?>
								<?php if($notification['post']['is_album'] == '1'){ ?>
								Added new album: <?php echo $notification['post']['album_title'];?>
								<?php } else { ?>
								Added new post: <?php echo $notification['post']['post_text'];?>
								<?php } ?>
								<?php } else if($notification['notification_type'] == 'commentreply'){ ?> Replied on your comment: <?php echo $notification['post']['post_text'];?>
								<?php } else if($notification['notification_type'] == 'connectrequestaccepted'){ ?> Accepted your connect request.
								<?php } else if($notification['notification_type'] == 'connectrequestdenied'){ ?> Denied your connect request.
								<?php } else if($notification['notification_type'] == 'onwall'){ ?> Write on your wall.
								<?php } else if($notification['notification_type'] == 'pagereview'){
								$page_info = Page::Pagedetails($page_id);
								?> Reviewed <?=$page_info['page_name']?> page.
								<?php } else if($notification['notification_type'] == 'pageinvite'){
								$page_info = Page::Pagedetails($page_id);
								?> Invited to like <?=$page_info['page_name']?> page.
								<?php } else if($notification['notification_type'] == 'likepage'){
								$page_info = Page::Pagedetails($page_id);
								?> Liked <?=$page_info['page_name']?> page.
								<?php } else if($notification['notification_type'] == 'onpagewall'){
								$page_info = Page::Pagedetails($page_id);
								?> Write on <?=$page_info['page_name']?> page.
								<?php } else{ ?> Likes post<?php } ?>
							</span>
							<span class="time-stamp">
								<?php if($notification['notification_type']=='likepost' || $notification['notification_type']== 'like' || $notification['notification_type']== 'likepage'){ ?><i class="zmdi zmdi-thumb-up"></i>
								<?php }else if($notification['notification_type'] == 'sharepost'){ ?><i class="mdi mdi-share-variant"></i> 
								<?php } else if($notification['notification_type']== 'comment') {?> <i class="mdi mdi-comment"></i>
								<?php }else if($notification['notification_type'] == 'pagereview'){ ?><i class="mdi mdi-pencil-square"></i> 
								<?php }else { ?><i class="mdi mdi-earth"></i> <?php }?> <?= $notification_time;?>
							</span>											
						</span>
					</a> 
					<?php if($hide != 'hide'){ ?>
					<div class="dropdown dropdown-custom"> 
						<a href="javascript:void(0)" class="dropdown-button more_btn nothemecolor" data-activates="<?=$time?>">
							<i class="mdi mdi-dots-horizontal mdi-25px"></i>
						</a> 
						<ul id="<?=$time?>" class="dropdown-content custom_dropdown">
							<li><a href="javascript:void(0)" onclick="hideNot('<?=$notification['_id']?>')">Hide this log</a></li>
						</ul>
					</div>
					<?php } ?>
					<a href="javascript:void(0)" onclick="markNotRead(this,'<?=$notification['_id']?>')" class="readicon nothemecolor" title="<?=$rt?>"><i class="mdi mdi-bullseye"></i></a>
				</div>
				</li>
			<?php } ?>
	<?php } else { ?>
	<?php $this->context->getnolistfound('nonotificationfound'); ?>
	<?php } ?>
		</ul>
	</div>
</div>
<?php exit;?>