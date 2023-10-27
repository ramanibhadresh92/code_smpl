<?php   
use yii\mongodb\ActiveRecord;
use frontend\assets\AppAsset;
use frontend\models\PostForm;
use frontend\models\Connect;
use frontend\models\UnfollowConnect;
use frontend\models\HidePost;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$post_id = $session->get('postid');
$user_id = (string)$session->get('user_id');

$postdetails = PostForm::find()->where(['_id' => $post_id,'is_deleted'=>'0'])->one();
$postuser = $postdetails['post_user_id'];

$connect = Connect::find()->where(['from_id' => $user_id,'to_id' => $postuser,'status' => '1'])->one();

$unfollow = new UnfollowConnect();
$unfollow = UnfollowConnect::find()->where(['user_id' => $user_id])->one();

$hidepost = new HidePost();
$hidepost = HidePost::find()->where(['user_id' => $user_id])->one();

$this->title = 'Post';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<script src="<?=$baseUrl?>/js/chart.js"></script>
    <div class="page-wrapper ">
        <div class="header-section">
            <?php include('../views/layouts/header.php'); ?>
        </div>
        <div class="floating-icon">
			<div class="scrollup-btnbox anim-side btnbox scrollup-float">
                <div class="scrollup-button float-icon"><span class="icon-holder ispan"><i class="mdi mdi-arrow-up-bold-circle"></i></span></div>          
            </div>            
        </div>
        <div class="clear"></div>
        <div class="container page_container travpostpage">
			<?php include('../views/layouts/leftmenu.php'); ?>
			<div class="fixed-layout"> 
				<div class="main-content with-lmenu">
					<div class="post-column mr-top">
						<div class="col s12 m12"></div>
						<?php                  
							if(!(strchr($unfollow['unfollow_ids'],$postuser)))
							{
								if(!(strchr($hidepost['post_ids'],$post_id)))
								{
									if(($postdetails['post_privacy'] != 'Private') || (($postdetails['post_privacy'] == 'Private') && ($user_id == $postuser)))
									{
									?>
										<div id="post-status-list" class="post-list"> 
											<?php 
												if($post_id != '') {
													$post = PostForm::find()->where([(string)'_id' => $post_id])->one();
													if(!empty($post)) {
														$postid = (string)$post['_id'];
														$postownerid = (string)$post['post_user_id'];
														$postprivacy = $post['post_privacy'];
														$isOk = $this->context->filterDisplayLastPost($postid, $postownerid, $postprivacy);
														if($isOk == 'ok2389Ko') {
															$this->context->display_last_post($postid);
														}
													}
												}
											?> 
										</div>
									<?php
									}
									else
									{
										$this->context->getnolistfound('postkeptprivate');
									}
								}
								else
								{
									$this->context->getnolistfound('posthide');
								}
							}
							else
							{
								$this->context->getnolistfound('postunfollow');
							}
						?>
					</div> 	
					<div class="scontent-column">
						<?php include('../views/layouts/people_you_may_know.php'); ?>
						
						<?php include('../views/layouts/people_view_you.php'); ?>
						
						<?php include('../views/layouts/recently_joined.php'); ?>
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
    

<?php $this->endBody() ?> 
<script type="text/javascript">
	$(document).ready(function() {
		var $length = $('.travpostpage').find('.post-column').find('.post-list').find('.post-holder.bshadow').length;
		if($length <= 0) {
			$('.travpostpage').find('.post-column').find('.post-list').html('<div class="post-holder bshadow"> <div class="joined-tb"> <i class="mdi mdi-file-outline"></i> <p>No record found.</p> </div> </div>');
		}
	});
</script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<script src="<?=$baseUrl?>/js/post.js"></script>