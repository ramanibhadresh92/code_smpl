<?php

use yii\helpers\Url;
use frontend\assets\AppAsset;
use frontend\models\CollectionFollow;
use frontend\models\PostForm;
use frontend\models\Page;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$user_id = (string)$session->get('user_id'); 
$fname = $this->context->getuserdata($wall_user_id,'fname'); 
?>
<div class="subtitle"><h5><?= $this->context->getuserdata($wall_user_id,'fullname');?>'s Contribution to Iaminjapan</h5></div>

<div class="wall-subcontent">
	<div class="fullwidth">	
		<!-- Posts Code Start -->
		<div class="contibute-box bshadow">
			<div class="iholder"><i class="mdi mdi-pencil-box-outline"></i></div>
			<div class="dholder">
				<h5>Posts <span>to <?= $fname?></span></h5>
				<div class="contribution-details">
					<ul>
						<li>
							<div class="frow">								
								<h6>Posted on the site <span><?= $post_total = count($posts);?> post</span></h6>													
								<div class="clear"></div>
								<span class="points">Points earned: <span><?= $post_total;?></span></span>
							</div>
						</li>
						<li>
							<div class="frow">								
								<h6><?= $fname?>'s posts shared by others <span><?= $post_share;?> post</span></h6>
								<div class="clear"></div>
								<span class="points">Points earned: <span><?= $post_share;?></span></span>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>
				
		<!-- Photos Code Start -->
		<div class="contibute-box bshadow">
			<div class="iholder"><i class="mdi mdi-file-image"></i></div>
			<div class="dholder">
				<h5>Photos <span>by <?= $fname?></span></h5>
				<div class="contribution-details">
					<ul>
						<li>
							<div class="frow">								
								<h6>Wall photos uploaded <span><?= $wallphotos;?> photos</span></h6>													
								<div class="clear"></div>
								<span class="points">Points earned: <span><?= $wallphotos;?></span></span>
							</div>
						</li>
						<li id="strcube" class="strcube dis-none">
							<div class="frow">								
								<h6>Wall photos uploaded <span><?= $wallphotos;?> photos</span></h6>													
								<div class="clear"></div>
								<span class="points">Points earned: <span><?= $wallphotos;?></span></span>
							</div>
						</li>
						<li>
							<div class="frow">								
								<h6>Place photos uploaded <span><?= $placephotos;?> photos</span></h6>
								<div class="clear"></div>
								<span class="points">Points earned: <span><?= $placephotos;?></span></span>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>
		
		<!-- Connections Code Start -->
		<div class="contibute-box bshadow">
			<div class="iholder"><i class="mdi mdi-account"></i></div>
			<div class="dholder">
				<h5>Connections <span>of <?= $fname?></span></h5>
				<div class="contribution-details">
					<ul>
						<li>
							<div class="frow">								
								<h6>Travel buddies <span><?= $buddy_count;?> buddies</span></h6>
								<div class="clear"></div>
								<span class="points">Points earned: <span><?= $buddy_point = ($buddy_count *10);?></span></span>
							</div>
						</li>
						<li>
							<div class="frow">								
								<h6>People hosted by <?= $fname;?> <span><?= $host_count;?> buddies</span></h6>
								<div class="clear"></div>
								<span class="points">Points earned: <span><?= $host_point = ($host_count *10);?></span></span>
							</div>
						</li>
						<li>
							<div class="frow">								
								<h6>Iaminjapan connections <span><?= $iaminjapanconnections;?> connections</span></h6>
								<div class="clear"></div>
								<span class="points">Points earned: <span><?= $connect_count = ($iaminjapanconnections * 10);?></span></span>
							</div>
						</li>
					</ul>																			
				</div>
			</div>
		</div>		
		
		<!-- Pages Code Start -->
		<div class="contibute-box bshadow" data-id="dencity">
			<div class="iholder"><i class="zmdi zmdi-view-list-alt zmdi-hc-lg"></i></div>
			<div class="dholder">
				<h5>Pages <span>owned by <?= $fname?></span></h5>
				<div class="contribution-details">
					<ul>
						<li>
							<div class="frow">								
								<h6>Business pages owned <span><?= count($pages);?> business page</span></h6>													
							</div>
						</li>											
					</ul>
					<div class="clear"></div>
					<div class="resizable-holder contribution-section page-contribution">
						<div class="resizable">
							<div class="page-list">
								<div class="row">												
									<?php 
									$page_count = 0;
									foreach($pages as $page)
									{
										$page_id = $page['_id'];
										$pageimageid = $page['page_id'];
										$likes = Page::getPageLikes($page_id);
										$pageimage = $this->context->getpageimage($pageimageid);
										$pagelink = Url::to(['page/index', 'id' => "$pageimageid"]);
										$page_post = count(PostForm::getUserPost($pageimageid));
									?>	 
									<div class="col m8 s12">
										<div class="page-preview">														
											<img src="<?=$pageimage;?>"/>
										
											<div class="page-details">
												<div class="page-title"><a href="<?= $pagelink;?>"><?= $page['page_name'];?></a></div>
												<div class="page-info"><?= count($likes);?> Likes - <?= $page_post;?> Posts </div>
												<div class="clear"></div>
												<span class="points">Points earned: <span><?= (count($likes)+ $page_post);?></span></span>
											</div>	
										</div>
									</div>
									<?php 
									$page_count += (count($likes)+ $page_post);
									}?>	
								</div>
							</div>
						</div>
						<div class="resize-link" onclick="openResizable(this)"><a href="javascript:void(0)">View all pages</a></div>
					</div>	
				</div>
			</div>
		</div>
				
		<!-- Trip Experiences Code Start -->
		<div class="contibute-box bshadow" data-id="dels">
			<div class="iholder"><i class="mdi mdi-file-document"></i></div>
			<div class="dholder">
				<h5>Trip Experiences <span>by <?= $fname?></span></h5>
				<div class="contribution-details">
					<ul>
						<li>
							<div class="frow">								
								<h6>Trip experiences posted <span><?= $tripexperiences;?> experiences</span></h6>													
								<div class="clear"></div>
								<span class="points">Points earned: <span><?= $tripexperiences;?></span></span>
							</div>
						</li>											
					</ul>										
				</div>
			</div>
		</div>
		
		<!-- Trips Code Start -->
		<div class="contibute-box bshadow" data-id="dels">
			<div class="iholder"><i class="mdi mdi-airplane"></i></div>
			<div class="dholder">
				<h5>Trips <span>by <?= $fname?></span></h5>
				<div class="contribution-details">
					<ul>
						<li>
							<div class="frow">								
								<h6>Trips planned <span><?= $trips_count = count($trips);?> trips</span></h6>													
							</div>
						</li>											
					</ul>
					<div class="clear"></div>
					<div class="trip-list">
						<div class="row">
							<?php 
							$trips_point = 0;
							if (is_array($trips) || is_object($trips)) {
							foreach($trips as $trip)
							{
								$end = strtotime($trip['leaving']);
								$start = strtotime($trip['arriving']);
								$days_between = ceil(abs($end - $start) / 86400).' days';
								$trips_point = ($trips_count * 10);
							?>		
							<div class="col m10 s12">
								<div class="trip-preview" data-id="dels">
									<i class="mdi mdi-airplane"></i>
									<div class="trip-details">
										<div class="trip-title"><?= $trip['address'];?><span><?= $trip['countperson'];?> Traveller</span></div>
										<div class="trip-info"><?= $trip['arriving'].' - '.$trip['leaving'].' ('.$days_between.')';?></div>
										<div class="clear"></div>
										<span class="points">Points earned: <span><?= '10';?></span></span>
									</div>														
								</div>
							</div>
							<?php }?>
							<?php }?>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="contibute-box bshadow">
			<div class="iholder"><i class="mdi mdi-star"></i></div>
			<div class="dholder">
				<h5>Reviews <span>for Nimish</span></h5>
				<div class="contribution-details">
					<ul>
						<li>
							<div class="frow">								
								<h6>Reviews done for places<span>40 reviews</span></h6>													
								<div class="clear"></div>
								<span class="points">Points earned: <span>40</span></span>
							</div>
						</li>
						<li>
							<div class="frow">								
								<h6>Reviews done for pages<span>17 reviews</span></h6>													
								<div class="clear"></div>
								<span class="points">Points earned: <span>17</span></span>
							</div>
						</li>																					
					</ul>																			
				</div>
			</div>
		</div>

		<!-- References Code Start -->
		<div class="contibute-box bshadow">
			<div class="iholder"><i class="zmdi zmdi-thumb-up"></i></div>
			<div class="dholder">
				<h5>References <span>for <?= $fname?></span></h5>
				<div class="contribution-details">
					<ul>
						<li>
							<div class="frow">								
								<h6>Positive references <span><?= $positivereferences;?> references</span></h6>													
								<div class="clear"></div>
								<span class="points">Points earned: <span><?= $reference_count = ($positivereferences * 5);?></span></span>
							</div>
						</li>											
					</ul>																			
				</div>
			</div>
		</div>
	</div>
</div>
<?php
$total = ($post_total+ $post_share + $wallphotos + $placephotos + $collection_point + $page_count + $tripexperiences + $trips_point + $connect_count + $buddy_point + $host_point + $reference_count);
?>	
<div class="scontent-column">
	<div class="content-box bshadow">
		<div class="sbox_content"> 
		</div>
	</div>	
</div>

<script>
$( document ).ready(function() {
	var total = '<?= $total;?>';
	var wall_user_id = '<?= $wall_user_id;?>';
    $.ajax({
		url: '?r=userwall/totalpoint',  
		type: 'POST',
		data: "wall_user_id="+wall_user_id+"&total="+total,
		success: function(data)
		{
			$('.sbox_content').html(data);
		}
	});
});	
</script>
<?php exit();?>