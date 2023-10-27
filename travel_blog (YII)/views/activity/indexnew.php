<?php
use yii\helpers\Url;
use frontend\models\CollectionFollow;
use frontend\models\Like;
use frontend\models\Connect;
use frontend\models\Referal;
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string) $session->get('user_id'); 
?>
<h4><i class="zmdi zmdi-chart"></i> Activity Log</h4>
<div>												
	<div>
		<div class="activity-holder">
			<div class="content-box bshadow nopadding">
				<div class="cbox-desc">
					<ul>
						<?php 
						if(empty($activities))
						{
							$this->context->getnolistfound('noactivityfound');
						}
						
						$created_time ='';
						$curdate = date('d M Y');
						$yesterday = date('d M Y',strtotime("-1 days"));
						$r = $y = true;
						$i = 1;
						foreach($activities as $activitie)
						{ 
							if(isset($activitie["post_created_date"]))
							{
								$created_time = $activitie['post_created_date'];
							}
							else if(isset($activitie["created_at"]))
							{
								$created_time = $activitie['created_at'];
							}
							else
							{
								$created_time = $activitie["created_date"];
							}
							$activitydate = date("d M Y",$created_time);
							$test[$i] = '';
							$r = $i-1;
							if($curdate == $activitydate)
							{
								$test[$i] = '<div class="cbox-title">Today</div>';
								if($i>1)
								{
									if($test[$r] == $test[$i]){
										$test[$i] =  '';
									}
									else
									{
										$test[$i] =  '';
									}
								}
								else
								{
									echo $test[$i];	
								}
							}
							else if($yesterday == $activitydate)
							{
								$test[$i] = '<div class="cbox-title">Yesterday</div>';
								if($i>1)
								{
									if($test[$r] != $test[$i])
									{
										echo '<div class="cbox-title">Yesterday</div>';
									}
									else
									{
										echo "";
									}
								} 
								else 
								{
									echo $test[$i];	
								}
							}
							else
							{
								$test[$i] = '<div class="cbox-title">'.$activitydate.'</div>';
								if($i>1)
								{
									if($test[$r] != $test[$i])
									{
										echo '<div class="cbox-title">'.$activitydate.'</div>';
									}
									else
									{
										echo "";
									}
								} 
								else 
								{
									echo $test[$i];
								}	
							}
							$i++;	
							
							//Connect request accept Code start
							if(isset($activitie['notification_type']) && $activitie['notification_type'] == 'connectrequestaccepted'){?>
								<li class="main-li">
									<div class="activity-box">
										<div class="iconholder"><i class="mdi mdi-account-plus"></i></div>
										<div class="summeryholder">
										<?php if($activitie['from_connect_id'] == $user_id) { ?>
											<p><a href="<?php $id = $activitie['from_connect_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['from_connect_id'],'fullname');?></a> become friend with <a href="<?php $id = $activitie['user_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['user_id'],'fullname');?></a></p>
										<?php } else {?>
											<p><a href="<?php $id = $activitie['user_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['user_id'],'fullname');?></a> become friend with <a href="<?php $id = $activitie['from_connect_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['from_connect_id'],'fullname');?></a></p>
										<?php } ?>
										</div>
									</div>
								</li>
							
							<!-- Comment Code start -->		
							<?php } else if(isset($activitie['parent_comment_id'])){
								$post_owner_id = $this->context->getpostdata($activitie['post_id'],'post_user_id');
								$profilepic = $this->context->getpostdata($activitie['post_id'],'is_profilepic');
								$coverpic = $this->context->getpostdata($activitie['post_id'],'is_coverpic');
								$album = $this->context->getpostdata($activitie['post_id'],'is_album');
								$travstore = $this->context->getpostdata($activitie['post_id'],'trav_item');
								 
								if($profilepic) {
									$text = "profile photo";
								} else if($coverpic){
									$text = "cover photo";	
								} else if($album){
									$text = "album";	
								} else if($travstore){
									$text = "trav store";
								} else {
									$text = "post";
								}
							?>
								<li class="main-li">
									<div class="activity-box">
										<div class="iconholder"><i class="mdi mdi-comment-outline"></i></div>
										<div class="summeryholder">
											<p><a href="<?php $id = $activitie['user_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['user_id'],'fullname');?></a> commented on <a href="<?= Url::to(['userwall/index', 'id' => "$post_owner_id"]); ?>"><?= $this->context->getuserdata($post_owner_id,'fullname');?></a>&apos;s <?= $text;?></p>
										</div>														
										<div class="descholder">
											<a href="javascript:void(0)" data-type="comment" act-id="<?= $activitie['post_id'];?>" class="mainlink a_postlink di_act_post">
												<div class="post-para">
													<p><?= (string)$activitie['comment'];?></p>
												</div>
											</a>
										</div>
									</div>
									<div class="activity-post-detail" id="comment-activity-post-<?= $activitie['post_id'];?>">
									</div>
								</li>
								
							<!-- Like Post Code start -->	
							<?php } else if(isset($activitie['like_type']))
							{ 
								if(isset($activitie['post_id'])) {
								$post_user_id = $this->context->getpostdata($activitie['post_id'],'post_user_id');
								$profilepic = $this->context->getpostdata($activitie['post_id'],'is_profilepic');
								$coverpic = $this->context->getpostdata($activitie['post_id'],'is_coverpic');
								$album = $this->context->getpostdata($activitie['post_id'],'is_album');
								$travstore = $this->context->getpostdata($activitie['post_id'],'trav_item');
								
								if($profilepic) {
									$text = "profile photo";
								} else if($coverpic){
									$text = "cover photo";	
								} else if($album){
									$text = "album";	
								} else if($travstore){
									$text = "trav store";
								} else {
									$text = "post";
								}
								?>
								<li class="main-li">
									<div class="activity-box">
										<div class="iconholder"><i class="zmdi zmdi-thumb-up"></i></div>
										<div class="summeryholder">
											<p><a href="<?php $id = $activitie['user_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['user_id'],'fullname');?></a> liked <a href="<?= Url::to(['userwall/index', 'id' => "$post_user_id"]); ?>"><?= $this->context->getuserdata($post_user_id,'fullname');?></a>&apos;s <?= $text;?></p>
										</div>														
										<div class="descholder">
											<a href="javascript:void(0)" data-type="like" act-id="<?= $activitie['post_id'];?>" class="mainlink a_postlink di_act_post">
											<?php if($profilepic) { ?>
												<div class="post-para">
													<div class="imgpreview one-img">
														<div class="pimg-holder himg-box imgfix">
															<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/profile/'.$this->context->getpostdata($activitie['post_id'],'image'); ?>" class="himg"/>
														</div>
													</div>
												</div>
											<?php } else if($coverpic){ ?>
												<div class="post-para">
													<div class="pimg-holder himg-box imgfix">
														<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/uploads/cover/'.$this->context->getpostdata($activitie['post_id'],'image'); ?>" class="himg"/>
													</div>
												</div>	
											<?php } else {?>
												<div class="post-para">
													<?php if($this->context->getpostdata($activitie['post_id'],'post_title') != '') { ?>
													<h6><?= $this->context->getpostdata($activitie['post_id'],'post_title');?></h6>
													<?php } ?>
													<p><?= $this->context->getpostdata($activitie['post_id'],'post_text');?></p>
												</div>	
											<?php }?>		
											</a>
										</div>
									</div>
									<div class="activity-post-detail" id="like-activity-post-<?= $activitie['post_id'];?>">
									</div>
								</li>
							
							<!-- Album Code Start -->	
							<?php } } else if(isset($activitie['is_album']) && $activitie['is_album']= "1"){ ?>
								<li class="main-li">
									<div class="activity-box">
										<div class="iconholder"><i class="mdi mdi-file-image"></i></div>
										<div class="summeryholder">
											<p><a href="<?php $id = $activitie['shared_by']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['shared_by'],'fullname');?></a> uploaded a new album</p>
										</div>														
										<div class="descholder">
											<a href="javascript:void(0)" data-type="album" act-id="<?= $activitie['_id'];?>" class="mainlink a_postlink di_act_post">
												<?php
												$images = $activitie['image'];
												$images = array_filter(explode(",", $images));

												$totalimgs = count($images); 
				                                $imgcountcls="";
				                                if($totalimgs == '1'){$imgcountcls = 'one-img';}
				                                if($totalimgs == '2'){$imgcountcls = 'two-img';}
				                                if($totalimgs == '3'){$imgcountcls = 'three-img';}
				                                if($totalimgs == '4'){$imgcountcls = 'four-img';}
				                                if($totalimgs == '5'){$imgcountcls = 'five-img';}
				                                if($totalimgs > '5'){$imgcountcls = 'more-img';}
				                                ?>
												<div class="imgpreview <?=$imgcountcls?>">
												<?php
												foreach ($images as $image) {
													if (file_exists('../web'.$image)) {  
														$picsize = '';
														$val = getimagesize('../web'.$image);
														$picsize .= $val[0] .'x'. $val[1] .', ';
														if($val[0] > $val[1]) {
															$imgclass = 'himg';
														} else if($val[1] > $val[0]) {
															$imgclass = 'vimg';
														} else {
															$imgclass = 'himg';
														}  
														?>
														<div class="pimg-holder <?=$imgclass?>-box"><img src="../web<?=$image?>" class="<?=$imgclass?>"></div>
														<?php
													}
												}
												?>
												</div>
												<div class="space"></div>
												<div class="post-para">
													<h6><?= $activitie['album_title'];?></h6>
													<p><?= $activitie['post_text'];?></p>
												</div>												
											</a>
										</div>
									</div>
									<div class="activity-post-detail" id="album-activity-post-<?= $activitie['_id'];?>">
									</div>
								</li>
							
							<!-- Profile Pic Code Start -->	
							<?php } else if(isset($activitie['is_profilepic']) && $activitie['is_profilepic']= "1"){ ?>
								<li class="main-li">
									<div class="activity-box">
										<div class="iconholder"><i class="mdi mdi-file-document"></i></div>
										<div class="summeryholder">
											<p><a href="<?php $id = $activitie['post_user_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['post_user_id'],'fullname');?></a> updated profile picture</p>
										</div>														
										<div class="descholder">
											<a href="javascript:void(0)" data-type="profile" act-id="<?= $activitie['_id'];?>" class="mainlink a_postlink di_act_post">
												<div class="post-para">
													<div class="imgpreview one-img">
														<div class="pimg-holder himg-box imgfix">
															<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/profile/'.$activitie['image'] ?>" class="himg"/>
														</div>
													</div>
												</div>												
											</a>
										</div>
									</div>
									<div class="activity-post-detail" id="profile-activity-post-<?= $activitie['_id'];?>">
									</div>
								</li>
							
							
							<!-- Cover Picture Code Start -->	
							<?php } else if(isset($activitie['is_coverpic']) && $activitie['is_coverpic']= "1"){ ?>
								<li class="main-li">
									<div class="activity-box">
										<div class="iconholder"><i class="mdi mdi-file-document"></i></div>
										<div class="summeryholder">
											<p><a href="<?php $id = $activitie['post_user_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['post_user_id'],'fullname');?></a> updated cover picture</p>
										</div>														
										<div class="descholder">
											<a href="javascript:void(0)" data-type="cover" act-id="<?= $activitie['_id'];?>" class="mainlink a_postlink di_act_post">
												<div class="post-para">
														<div class="pimg-holder himg-box imgfix">
															<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= '/uploads/cover/'.$activitie['image'] ?>" class="himg"/>
														</div>
												</div>												
											</a>
										</div>
									</div>
									<div class="activity-post-detail" id="cover-activity-post-<?= $activitie['_id'];?>">
									</div>
								</li>
							
							<!-- Referal Code Start -->	
							<?php } else if(isset($activitie['referal_text'])) {?>	
								<?php if(!isset($activitie['referal_id'])){?>
									<li class="main-li">
										<div class="activity-box">
											<div class="iconholder"><i class="zmdi zmdi-thumb-up"></i></div>
											<div class="summeryholder">
												<p><a href="<?php $id = $activitie['sender_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['sender_id'],'fullname');?></a> wrote a referral for <a href="<?php $id = $activitie['user_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['user_id'],'fullname');?></a></p>
											</div>														
											<div class="descholder">
												<a href="javascript:void(0)" act-id="<?= $activitie['_id'];?>" class="mainlink a_postlink di_act_referal">
													<div class="post-feedback">
														<div class="feedback <?= lcfirst($activitie['recommend']);?>">
															<i class="mdi mdi-star<?php if($activitie['recommend']=='Negative'){echo "-o";}?>"></i> <?= ucfirst($activitie['recommend']);?>
														</div>
													</div>
													<div class="post-desc">
														<p><?= $activitie['referal_text'];?></p>
													</div>
												</a>	
											</div>
										</div>
										<div class="activity-post-detail" id="activity-referal-<?= $activitie['_id'];?>">
											
										</div>
									</li>
								<?php }?>
							
							<!-- Create new post Code start -->	
							<?php } else {
								if(isset($activitie['post_user_id'])){
									$text = 'posted a new post';
									if(isset($activitie['placetype']) && !empty($activitie['placetype']))
									{
										$pt = $activitie['placetype'];
										if($pt == 'reviews'){$text = 'added review for';}
										if($pt == 'tip'){$text = 'added tip for';}
										if($pt == 'ask'){$text = 'has a question about';}
										$text .= ' '.$activitie['currentlocation'];
									}
							?> 
									<li class="main-li">
										<div class="activity-box">
											<div class="iconholder"><i class="mdi mdi-pencil-box-outline"></i></div>
											<div class="summeryholder">
												<p>
													<a href="<?php $id = $activitie['shared_by']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>">
														<?php 
														echo $this->context->getuserdata($activitie['shared_by'],'fullname');?>
														</a> <?= $text;?></p>
											</div>														
											<div class="descholder">
												<a href="javascript:void(0)" data-type="newpost" act-id="<?= $activitie['_id'];?>" class="mainlink a_postlink di_act_post">
													<div class="post-para">
														<?php if($activitie['post_title'] != '') { ?>
															<h6><?= $activitie['post_title'];?></h6>
														<?php } ?>
														<p><?= $activitie['post_text'];?></p>
													</div>
												</a>
											</div>
										</div>
										<div class="activity-post-detail" id="newpost-activity-post-<?= $activitie['_id'];?>">
										</div>
									</li>	
								<?php }?>
						<?php 
							} 
						} 
						?>
					</ul>										
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="<?=$baseUrl?>/js/activity.js"></script>
<?php exit();?>