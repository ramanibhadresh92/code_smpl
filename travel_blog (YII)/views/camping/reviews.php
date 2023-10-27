<?php
use frontend\models\CampingReview;
$postid = $_GET['id'];
$reviews = CampingReview::find()->where(['post_id' => $postid])->asarray()->all();
?>
<div class="row reviews-row mx-0">
    <div class="container">
       <div class="col m8 s12">
          <div class="reviews-section mt-20">
             <div class="row mx-0 valign-wrapper">
                <div class="left">
                   <h5>REVIEWS</h5>
                </div>
                <div class="right ml-auto">
                   <?php if($isvisible == 'yes') { ?>
                   <a href="javascript:void(0)" class="campingreview <?=$checkuserauthclass?>">+ Review</a>
                   <?php } ?>
                </div>
             </div>
             <ul class="collection">
	         	<?php
	         	foreach ($reviews as $reviews_s) {
	         		$reviews_s_userid = $reviews_s['post_user_id'];

	         		$reviews_s_profile = $this->context->getuserdata($reviews_s_userid,'thumbnail');
	         		$reviews_s_username = $this->context->getuserdata($reviews_s_userid,'fullname');
	         		$reviews_s_review = $reviews_s['placereview'];
	         		$reviews_s_desc = $reviews_s['post_text'];
	         		$reviews_s_date = $reviews_s['post_created_date'];
	         		$reviews_s_date = date('MM d, YYYY', $reviews_s_date);
	         		?>
		            <li class="collection-item avatar">
		               <img src="profile/<?=$reviews_s_profile?>" alt="" class="circle">
		               <span class="title"><?=$reviews_s_username?></span>
		               <span class="ratings">
		               		<?php if($reviews_s_review == 1) { ?>
								<i class="mdi mdi-star"></i>
								<i class="mdi mdi-star unfill"></i>
								<i class="mdi mdi-star unfill"></i>
								<i class="mdi mdi-star unfill"></i>
								<i class="mdi mdi-star unfill"></i>
		               		<?php } else if($reviews_s_review == 2) { ?>
		               			<i class="mdi mdi-star"></i>
								<i class="mdi mdi-star"></i>
								<i class="mdi mdi-star unfill"></i>
								<i class="mdi mdi-star unfill"></i>
								<i class="mdi mdi-star unfill"></i>
		               		<?php } else if($reviews_s_review == 3) { ?>
		               			<i class="mdi mdi-star"></i>
								<i class="mdi mdi-star"></i>
								<i class="mdi mdi-star"></i>
								<i class="mdi mdi-star unfill"></i>
								<i class="mdi mdi-star unfill"></i>
		               		<?php } else if($reviews_s_review == 4) { ?>
		               			<i class="mdi mdi-star"></i>
								<i class="mdi mdi-star"></i>
								<i class="mdi mdi-star"></i>
								<i class="mdi mdi-star"></i>
								<i class="mdi mdi-star unfill"></i>
		               		<?php } else if($reviews_s_review == 5) { ?>
		               			<i class="mdi mdi-star"></i>
								<i class="mdi mdi-star"></i>
								<i class="mdi mdi-star"></i>
								<i class="mdi mdi-star"></i>
								<i class="mdi mdi-star"></i>
		               		<?php } ?>
		               </span>
		               <p class="date"><?=$reviews_s_date?></p>
		               <p><?=$reviews_s_desc?></p>
		            </li>
	         		<?php
	         	}
	         	?>
	         </ul>
          </div>
       </div>
    </div>
 </div>