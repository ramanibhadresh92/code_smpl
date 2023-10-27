<?php 
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$user_id = (string)$session->get('user_id'); 
$directauthcall = '';
if($checkuserauthclass == 'checkuserauthclassg' || $checkuserauthclass == 'checkuserauthclassnv') { 
	$directauthcall = $checkuserauthclass . ' directcheckuserauthclass';
} 
 
$destimage = $this->context->getplaceimage($placetitle);

$placetitleLabel = '';     
if($placetitle != '') {
    $placetitleLabel = explode(",", $placetitle); 
    if(count($placetitleLabel) >1) {
        $first = reset($placetitleLabel);
        $last = end($placetitleLabel);
        $placetitleLabel = $first.', '.$last;
    } else {
        $placetitleLabel = implode(", ", $placetitleLabel);
    }
}
$userIMAGE = $this->context->getimage($user_id,'thumb');
?> 
<div class="row cshfsiput cshfsi">
	<div class="social-section profile-box detailBox">
		<div class="user-profile">
		    <div class="avatar-left">
		        <img src="<?=$userIMAGE?>">
		    </div>
		    <div class="avatar-middle">
		        <img src="<?=$userIMAGE?>">
		    </div>
		    <div class="avatar-right">
		        <img src="<?=$userIMAGE?>">
		    </div>
		</div>
		
		<div class="item titlelabel user-info discussiondetailBox">
        	<p class="center-align u-name mb0">Discussion for <?=$placefirst?> (<span class="count">&nbsp;</span>)</p>
        	<p class="user-desg center-align"></p>
        </div>

		<div class="item profile-info row mx-0 width-100">
		    <div class="sub-item reviewsdetailBox">
		     	<h5 class="">Reviews</h5>
		    	<p class="count"></p>
		    </div>
		    <div class="sub-item travellersdetailBox">
		     	<h5 class="">Travellers</h5>
		     	<p class="count"></p>
		    </div>
		    <div class="sub-item localsdetailBox">
		     	<h5 class="">Locals</h5>
		     	<p class="count"></p>
		    </div>
		    <div class="sub-item photosdetailBox">
		     	<h5 class="">Photos</h5>
		     	<p class="count"></p>
		    </div>
		    <div class="sub-item tipsdetailBox">
		     	<h5 class="">Tips</h5>
		     	<p class="count"></p>
		    </div>
		    <div class="sub-item askdetailBox">
		     	<h5 class="">Ask</h5>
		     	<p class="count"></p>
		    </div>
		</div>	
	</div>
	<div class="postBox">
		<div class="content-box">
			<div class="new-post base-newpost cshfsiput cshfsi compose_discus">
			    <div class="npost-content">
			       <div class="post-mcontent">
			          <i class="mdi mdi-pencil-box-outline main-icon"></i>
			          <div class="desc">
			             <div class="input-field comments_box">
			                <p>Discussion for <?=$placefirst?></p>
			             </div>
			          </div>
			       </div>
			    </div>
			</div>
			
			<div class="cbox-desc nm-postlist post-list cshfsiput cshfsi">
				<?php if(!empty($getpplacereviews)){ 
					$lpDHSU = 1;  
					$existing_posts = '1';
					$cls = '';
					foreach($getpplacereviews as $post) {
						if(($lpDHSU%8) == 0) {  
							$ads = $this->context->getad(true);
							if(isset($ads) && !empty($ads))
							{
								$cls = 'places-ads';
								$ad_id = (string) $ads['_id'];
								$this->context->display_last_discussion($ad_id, $existing_posts, '', $cls,'','restingimagefixes','',$lpDHSU);
							}
						}
						$this->context->display_last_discussion($post['_id'],'from_save','','tippost-holder bborder ','','restingimagefixes');
						$lpDHSU++;
					} 
				} else {
					$this->context->getnolistfound('becomefirstforplacediscussion');
				} ?>
			</div> 
			<?php if($checkuserauthclass == 'checkuserauthclassg' || $checkuserauthclass == 'checkuserauthclassnv') {?>
			<div class="new-post-mobile clear disscu_show">
		       <a href="javascript:void(0)" class="popup-window <?=$directauthcall?>" ><i class="mdi mdi-pencil"></i></a>
		    </div>
		    <?php } else { ?>
		    <div class="new-post-mobile clear disscu_show">
		       <a href="javascript:void(0)" class="popup-window compose_discus" ><i class="mdi mdi-pencil"></i></a>
		    </div>
		    <?php } ?>
		</div>
	</div>
</div>
<?php exit;?>