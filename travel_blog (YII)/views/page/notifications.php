<?php 
use yii\helpers\Url;
use frontend\models\LoginForm;
use frontend\models\Page;
?>
<div class="combined-column">
	<div class="content-box bshadow">
	<div class="cbox-title hidetitle-mbl">						
		Notifications
	</div>
	<?php if($notcount>0){ ?>
	<div class="fullside-list"> 
		<ul class="noti-listing">
			<?php
				foreach($notification as $notification) {
					$rand = rand(999, 99999);
					$notification_time = Yii::$app->EphocTime->time_elapsed_A(time(),$notification['updated_date']);
					$name = ucfirst($notification['user']['fname']).' '.ucfirst($notification['user']['lname']);
					if($notification['user_id'] == $user_id)
					{
						$name = 'You';
					}
					$not_img = $this->context->getimage($notification['user']['_id'],'thumb');
					if($notification['notification_type'] == 'sharepost')
					{
						$not_img = $this->context->getimage($notification['shared_by'],'thumb');
					}
					if($notification['entity'] == 'page') { $npostid = $notification['page_id']; $pagelink = 'page/index'; $getid = 'id';}
					else{$npostid = $notification['post_id']; $pagelink = 'site/travpost'; $getid = 'postid';}
					if($notification['notification_type'] == 'sharepost')
					{
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
			?>
			<li class="mainli" id="hidenot_<?=(string)$notification['_id']?>">
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
					<div class="dropdown dropdown-custom">
					    <a class="dropdown-button more_btn" href="javascript:void(0);" data-activates="business_btn<?=$rand?>">
							<i class="zmdi zmdi-hc-2x zmdi-more"></i>
						</a>

						<ul id="business_btn<?=$rand?>" class="dropdown-content custom_dropdown">
							<li><a href="javascript:void(0)" onclick="hideNot('<?=(string)$notification['_id']?>')">Hide this notification</a></li>
							<li><a href="javascript:void(0)">Turn off notification</a></li>
						</ul>
					</div>
					<a href="javascript:void(0)" onclick="markNotRead(this)" class="readicon"><i class="mdi mdi-bullseye"></i></a>
				</div>
			</li>
			<?php } ?> 
		</ul>	
	</div>
	<?php } else { ?>
	<div class="cbox-desc">
		<?php $this->context->getnolistfound('nonotificationfound'); ?>
	</div>
	<?php } ?>
	</div>
</div>
<?php exit;?>