<?php 
use yii\helpers\Url;
use frontend\assets\AppAsset;
use frontend\models\LoginForm;
use frontend\models\PostForm;
use frontend\models\Notification;
use frontend\models\UserForm;
use frontend\models\Page;

$baseUrl = AppAsset::register($this)->baseUrl;

$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');

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
$request = Yii::$app->request;
$page_id = (string)$request->get('id');

if(strstr($_SERVER['HTTP_REFERER'],'r=page'))
{
	$url = $_SERVER['HTTP_REFERER'];
	$urls = explode('&',$url);
	$url = explode('=',$urls[1]);
	$page_id = $url[1];
}	
$page_details = Page::Pagedetails($page_id);
?>

<div class="post-column reviews-column">
	<div class="new-post review-newpost compose_review" id="page_review">
		<form action="" id='frmnewpost'>
			<div class="rating-stars setRating <?=$Auth?> directcheckuserauthclass" onmouseout="ratingJustOut(this)">
	          <span>Let's start your rating</span>
	          <i class="mdi mdi-star ratecls1 ratecls2 ratecls3 ratecls4 ratecls5" data-value="1" onmouseover="ratingJustOver(this)"></i> 
	          <i class="mdi mdi-star ratecls2 ratecls3 ratecls4 ratecls5" data-value="2" onmouseover="ratingJustOver(this)"></i>
	          <i class="mdi mdi-star ratecls3 ratecls4 ratecls5" data-value="3" onmouseover="ratingJustOver(this)"></i>
	          <i class="mdi mdi-star ratecls4 ratecls5" data-value="4" onmouseover="ratingJustOver(this)"></i>
	          <i class="mdi mdi-star ratecls5" data-value="5" onmouseover="ratingJustOver(this)"></i>
	          &nbsp;&nbsp;
              <span class="star-text"></span>
	       </div>
		</form>
    </div>
	<?php if($page_details['created_by'] != $user_id){ ?>
	<?php } ?>
    <div class="post-list">
	    <?php
		    $lp = 1; 
		    foreach($pagereviews as $post)
		    { 
		        $existing_posts = 'from_save';
		        $postid = (string)$post['_id'];
				$postownerid = (string)$post['post_user_id'];
				$postprivacy = $post['post_privacy'];

				$isOk = $this->context->filterDisplayLastPost($postid, $postownerid, $postprivacy);
				if($isOk == 'ok2389Ko') {
					$this->context->display_last_post((string)$postid, $existing_posts);
				}
		    }
	    ?>
    </div>
	<?php if(empty($post))
	{
	?>	
		<div class="post-holder bshadow">      
			<div class="joined-tb">
				<i class="mdi mdi-star"></i>        
				<h4>Welcome to Iaminjapan</h4>
				<p>Be the first to review <?=$page_details['page_name']?> page</p>
			</div>    
		</div>
	<?php		
	}
	?>
</div>
<div class="wallcontent-column">
	<div class="content-box bshadow">
		<div class="cbox-desc">
			<div class="rating-summery">
				<div class="avg-summery">
					<?=  number_format($avgreview)?> <i class="mdi mdi-star"></i>
				</div>
				<div class="detail-summery">
					<ul>
						<li>
							<span>5 <i class="mdi mdi-star"></i></span>
							<div class="rate-progress"><span style="width:<?=$fivepagereviewscount/$totalpagereviewscount?>%"></span></div>
							<span><?=$fivepagereviewscount?></span>
						</li>
						<li>
							<span>4 <i class="mdi mdi-star"></i></span>
							<div class="rate-progress"><span style="width:<?=$fourpagereviewscount/$totalpagereviewscount?>%"></span></div>
							<span><?=$fourpagereviewscount?></span>
						</li>
						<li>
							<span>3 <i class="mdi mdi-star"></i></span>
							<div class="rate-progress"><span style="width:<?=$threepagereviewscount/$totalpagereviewscount?>%"></span></div>
							<span><?=$threepagereviewscount?></span>
						</li>
						<li>
							<span>2 <i class="mdi mdi-star"></i></span>
							<div class="rate-progress"><span style="width:<?=$twopagereviewscount/$totalpagereviewscount?>%"></span></div>
							<span><?=$twopagereviewscount?></span>
						</li>
						<li>
							<span>1 <i class="mdi mdi-star"></i></span>
							<div class="rate-progress"><span style="width:<?=$onepagereviewscount/$totalpagereviewscount?>%"></span></div>
							<span><?=$onepagereviewscount?></span>
						</li>
					</ul>
				</div>									
			</div>
		</div>
	</div>
	<div class="content-box bshadow">
		<div class="cbox-title">								
			Recent Review
		</div>
		<div class="cbox-desc">
			<div class="likes-summery">
				<div class="friend-likes connect-likes">
					<h5><a href="javascript:void(0)"><?php echo count($getPageReviewsCountPerson); ?> User<?php if(count($getPageReviewsCountPerson) > 1){?>s<?php } ?></a> reviewed <?=$pagedetails['page_name']?></h5>
					<?php if($pagereviewscount > 0){ ?>
					<ul>
						<?php foreach($getPageReviewPerson as $getPageReviewPerson1){
							$like_user_id = (string)$getPageReviewPerson1['user']['_id'];
							$user_img = $this->context->getimage($like_user_id,'thumb');
							$link = Url::to(['userwall/index', 'id' => $like_user_id]);
						?>
						<li><a href="<?=$link?>" title="<?=$getPageReviewPerson1['user']['fullname']?>"><img src="<?=$user_img?>"/></a></li>
						<?php } ?>
					</ul>
					<?php } else { ?>
					<?php $this->context->getnolistfound('becomefirsttoreview'); ?>
					<?php } ?>									
				</div>
				<ul class="review-summery">
					<li>
						<span><?=$pagereviewslastmonthcount?></span> User<?php if($pagereviewslastmonthcount > 1){?>s<?php } ?> last month
					</li>
					<li>
						<span><?=$pagereviewscount?></span> Total reviews
					</li>
				</ul>
				<div class="invite-likes">
					<?php 
						if(count($pagereviewscount) > 0){
					?>
					<p>Invite your friends to review this page</p>
					<?php } ?>
					<div class="invite-holder">
						<?php 
							if(count($reviewdconnect) > 0){
						?>
						<form onsubmit="return false;">
							<div class="tholder">
								<div class="sliding-middle-custom anim-area underlined">
									<input type="text" placeholder="Type a friend's name" class="invite_connect_review" data-id="invite_connect_review" >
									<a href="javascript:void(0)" onclick="removeinvitesearchinput(this);"><img src="<?=$baseUrl?>/images/cross-icon.png"/></a>
								</div>
							</div>
						</form>
						<?php } ?>
						<div class="list-holder blockinvite_connect_review">
							<ul>
								<?php 
								if(count($reviewdconnect) > 0){
									foreach($reviewdconnect as $invitedconnections){
									$connectid = (string)$invitedconnections['to_id'];
									$result = LoginForm::find()->where(['_id' => $connectid])->one();
									$frndimg = $this->context->getimage($connectid,'thumb');
									$pagereviewexist = $pagelikeexist = PostForm::find()->where(['page_id' => "$page_id", 'post_user_id' => "$connectid", 'is_deleted' => '0', 'is_page_review' => '1'])->one();
									$invitaionsent = Notification::find()->where(['post_id' => "$page_id", 'status' => '1', 'from_connect_id' => "$connectid", 'user_id' => "$user_id", 'notification_type' => 'pageinvitereview'])->one();
								?>
								<li class="invite_<?=$connectid?>">
									<div class="invitelike-friend invitelike-connect">
										<div class="imgholder"><img src="<?=$frndimg?>"/></div>
										<div class="descholder">
											<h6><?=$result['fullname']?></h6>
											<div class="btn-holder events_<?=$connectid?>">
												<?php if($pagereviewexist)
													{
														echo '<label class="infolabel"><i class="zmdi zmdi-check"></i> Reviewd</label>';
													}
													else if($invitaionsent)
													{
														echo '<label class="infolabel"><i class="zmdi zmdi-check"></i> Invited</label>';
													}
													else
													{ ?>
												<a href="javascript:void(0)" onclick="sendinvitereview('<?=$connectid?>','<?=$page_id?>')" class="btn-invite">Invite</a>
												<a href="javascript:void(0)" onclick="cancelinvite('<?=$connectid?>')" class="btn-invite-close"><i class="mdi mdi-close"></i></a>
												<?php } ?>
											</div>
											<div class="dis-none btn-holder sendinvitation_<?=$connectid?>">
												<label class="infolabel"><i class="zmdi zmdi-check"></i> Invitation sent</label>
											</div>
										</div>														
									</div>
								</li>
								<?php } } else { ?>
								<?php $this->context->getnolistfound('allconnectreviewthispage');?>
								<?php } ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php exit;?>