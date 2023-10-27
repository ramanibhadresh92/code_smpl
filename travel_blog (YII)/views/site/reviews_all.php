<?php 
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$user_id = (string)$session->get('user_id');

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
    
		<div class="item titlelabel user-info reviewsdetailBox">
        	<p class="center-align u-name mb0">Reviews for <?=$placefirst?> (<span class="count">&nbsp;</span>)</p>
        	<p class="user-desg center-align"></p>
        </div>

		<div class="item profile-info row mx-0 width-100">
		    <div class="sub-item discussiondetailBox">
		     	<h5 class="">Discussion</h5>
		    	<p class="count">10</p>
		    </div>
		    <div class="sub-item travellersdetailBox">
		     	<h5 class="">Travellers</h5>
		     	<p class="count"></p>
		    </div>
		    <div class="sub-item localsdetailBox">
		     	<h5 class="">Locals</h5>
		     	<p class="count">20</p>
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
			<div class="new-post cshfsiput cshfsi base-newpost compose_newreview">
                <form action=""> 
                   <div class="rating-stars setRating" onmouseout="ratingJustOut(this)">
                      <span>Let's start your rating</span>
                      <i class="mdi mdi-star ratecls1 ratecls2 ratecls3 ratecls4 ratecls5" data-value="1" onmouseover="ratingJustOver(this)"></i> 
                      <i class="mdi mdi-star ratecls2 ratecls3 ratecls4 ratecls5" data-value="2" onmouseover="ratingJustOver(this)"></i>
                      <i class="mdi mdi-star ratecls3 ratecls4 ratecls5" data-value="3" onmouseover="ratingJustOver(this)"></i>
                      <i class="mdi mdi-star ratecls4 ratecls5" data-value="4" onmouseover="ratingJustOver(this)"></i>
                      <i class="mdi mdi-star ratecls5" data-value="5" onmouseover="ratingJustOver(this)"></i>&nbsp;&nbsp;
                      <span class="star-text"></span>
                   </div>
                </form>
            </div>

			<div class="cbox-desc nm-postlist post-list reviews-column cshfsiput cshfsi">
				<?php if(!empty($getpplacereviews)) {
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
								$this->context->display_last_place_review($ad_id, $existing_posts, '', $cls,'','','',$lpDHSU);
							}
						}
						$this->context->display_last_place_review($post['_id'],'from_save','','reviewpost-holder bborder ');
						$lpDHSU++;
					}
				} else {
						$this->context->getnolistfound('becomefirstforplacereview');
					//echo '<div class="no-listcontent">Become a first to review for '.$placefirst.' place</div>';
				} ?>
			</div>
			<?php if($checkuserauthclass == 'checkuserauthclassg' || $checkuserauthclass == 'checkuserauthclassnv') {?>
			<div class="new-post-mobile clear">
		       <a href="javascript:void(0)" class="popup-window <?=$directauthcall?>" ><i class="mdi mdi-pencil"></i></a>
		    </div>
		    <?php } else { ?>
		    <div class="new-post-mobile clear">
		       <a href="javascript:void(0)" class="popup-window compose_newreview" ><i class="mdi mdi-pencil"></i></a>
		    </div>
		    <?php } ?>
		</div>
	</div>
</div>
<?php exit;?>