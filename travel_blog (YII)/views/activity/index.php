<?php
use yii\helpers\Url;
use frontend\models\CollectionFollow;
use frontend\models\Like;
use frontend\models\Connect;
use frontend\models\Referal;
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$uid = (string) $session->get('user_id'); 
?>
<div class="combined-column">												
	<div class="content-box nobg">
		<div class="cbox-title nborder maintitle">								
			<div class="subtitle"><h5>Activity Log</h5></div>
			<div class="right-tabs">
				<div class="connections-search">												
					<div class="fsearch-form closable-search">
						<input type="text" placeholder="Search log text"/>
						<a href="javascript:void(0)"><i class="zmdi zmdi-search grey-text"></i></a>
					</div>
				</div>
			
			</div>
		</div>
		<div class="cbox-desc maindesc">
			<div class="activity-holder">
				<div class="content-box bshadow">
					<div class="cbox-title"> Today </div>
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
														<h6><?= $this->context->getpostdata($activitie['post_id'],'post_title');?></h6>
														<p><?= $this->context->getpostdata($activitie['post_id'],'post_text');?></p>
													</div>	
												<?php }?>		
												</a>
											</div>
										</div>
										<div class="activity-post-detail" id="like-activity-post-<?= $activitie['post_id'];?>">
										</div>
									</li>

								<!-- Collection Code start -->
								<?php } else if(isset($activitie['entity_collection']) && $activitie['entity_collection']= "1"){ ?>
									<li class="main-li">
										<div class="activity-box">
											<div class="iconholder"><i class="mdi mdi-account-group"></i></div>
											<div class="summeryholder">
												<p><a href="<?php $id = $activitie['user_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['user_id'],'fullname');?></a> created a collection</p>
											</div>														
											<div class="descholder">	
												<div class="collection-preview gen-preview">
													<div class="imgholder"><img src="<?= $this->context->getcollectionimage($activitie['_id']);?>"></div>
													<h6><?= $activitie['name'];?></h6>
													<span><?= CollectionFollow::getcollectionfollowcount($activitie['_id']);?> Followers</span>
												</div>
											</div>
										</div>
									</li>
								
								<!-- Page Code start -->
								<?php } else if(isset($activitie['page_id'])){ ?>
									<li class="main-li">
										<div class="activity-box">
											<div class="iconholder"><i class="zmdi zmdi-view-list-alt zmdi-hc-lg"></i></div>
											<div class="summeryholder">
												<p><a href="<?php $id = $activitie['created_by']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['created_by'],'fullname');?></a> created a page</p>
											</div>														
											<div class="descholder">	
												<div class="page-preview gen-preview">
													<div class="imgholder"><img src="<?= $this->context->getpageimage($activitie['page_id']);?>"></div>
													<h6><?= $activitie['page_name'];?></h6>
													<p>
														<i class="zmdi zmdi-thumb-up"></i>
														<?= Like::getLikeCount($activitie['page_id']);?> people liked this
													</p>																
												</div>
											</div>
										</div>
									</li>
									
								<!-- Trip Expreince Code Start -->	
								<?php } else if(isset($activitie['is_trip']) && $activitie['is_trip']= "1"){ ?>
									<li class="main-li">
										<div class="activity-box">
											<div class="iconholder"><i class="mdi mdi-file-document"></i></div>
											<div class="summeryholder">
												<p><a href="<?php $id = $activitie['post_user_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['post_user_id'],'fullname');?></a> added a trip experience</p>
											</div>														
											<div class="descholder">
												<a href="javascript:void(0)" data-type="trip" act-id="<?= $activitie['_id'];?>" class="mainlink a_postlink di_act_post">
													<div class="post-para">
														<h6><?= $activitie['post_title'];?></h6>
														<p><?= $activitie['post_text'];?></p>
													</div>												
												</a>
											</div>
										</div>
										<div class="activity-post-detail" id="trip-activity-post-<?= $activitie['_id'];?>">
										</div>
									</li>
								
								<!-- Trav Store Code Start -->	
								<?php } else if(isset($activitie['trav_item']) && $activitie['trav_item']= "1"){ ?>
									<li class="main-li">
										<div class="activity-box">
											<div class="iconholder"><i class="mdi mdi-file-document"></i></div>
											<div class="summeryholder">
												<p><a href="<?php $id = $activitie['post_user_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['post_user_id'],'fullname');?></a> added a trav store</p>
											</div>														
											<div class="descholder">
												<a href="javascript:void(0)" data-type="travstore" act-id="<?= $activitie['_id'];?>" class="mainlink a_postlink di_act_post">
													<div class="post-para">
														<h6><?= $activitie['post_title'];?></h6>
														<p><?= $activitie['post_text'];?></p>
													</div>												
												</a>
											</div>
										</div>
										<div class="activity-post-detail" id="travstore-activity-post-<?= $activitie['_id'];?>">
										</div>
									</li>
								
								<!-- Album Code Start -->	
								<?php } else if(isset($activitie['is_album']) && $activitie['is_album']= "1"){ ?>
									<li class="main-li">
										<div class="activity-box">
											<div class="iconholder"><i class="mdi mdi-file-document"></i></div>
											<div class="summeryholder">
												<p><a href="<?php $id = $activitie['post_user_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['post_user_id'],'fullname');?></a> added an album</p>
											</div>														
											<div class="descholder">
												<a href="javascript:void(0)" data-type="album" act-id="<?= $activitie['_id'];?>" class="mainlink a_postlink di_act_post">
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
									
								<!-- Trip Code Start -->	
								<?php } else if(isset($activitie['abouttrip'])) {
									$lookingfor = isset($activitie['lookingfor']) ? $activitie['lookingfor'] : '';
									if($lookingfor == 'host') 
									{
										$with = 'local host';
									} 
									else 
									{
										$with = 'travel buddy';
									}
									$countperson = isset($activitie['countperson']) ? $activitie['countperson'] : '';
									$arriving = $activitie['arriving'];
									$leaving = $activitie['leaving'];
									$days = strtotime($leaving) - strtotime($arriving);
									$days = floor($days / (60 * 60 * 24));
								?>
									<li class="main-li">
										<div class="activity-box">
											<div class="iconholder"><i class="mdi mdi-airplane"></i></div>
											<div class="summeryholder">
												<p><a href="<?php $id = $activitie['user_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['user_id'],'fullname');?></a> added a trip plan</p>
											</div>														
											<div class="descholder">
												<a href="javascript:void(0)" class="mainlink a_postlink">
													<div class="summery tripdetail">
														<div class="trip-list expandable-area">
															<ul class="editable-tripli mainlist">
																<li>
																	<div class="mode-holder">
																		<div class="normal-mode">
																			<div class="dull-link"><i class="mdi mdi-airplane"></i><?= $activitie['address'];?></div>
																		</div>
																	</div>	
																</li>
															</ul>
														</div>
													</div>
												</a>
											</div>
										</div>
										<div class="activity-post-detail">
											<div class="summery tripdetail">
												<div class="trip-list expandable-area">
													<ul class="editable-tripli mainlist">
														<li>
															<div class="mode-holder opened">
																<div class="normal-mode">
																	<a href="javascript:void(0)" class="dull-link" onclick="open_detail(this),close_alledit(this),resetAllTripLi()"><i class="mdi mdi-airplane"></i><?= $activitie['address'];?></a>
																</div>
																<div class="detail-mode">
																	<div class="tripbox">	
																		<div class="tripdetail">
																			<div class="infomode">
																				<h6>Visiting <span><?= $activitie['address'];?></span></h6>
																				<ul>
																					<li><i class="mdi-checkbox-blank-circle"></i><?= $arriving;?> - <?= $leaving;?> (<?= $days;?> Days)</li>
																					<li><i class="mdi-checkbox-blank-circle"></i><?= $countperson;?> Traveller</li>
																				</ul>
																				<p>Looking to spend <?= $days;?> days with a <?= $with;?></p>
																			</div>
																		</div>	
																	</div>	
																</div>	
															</div>	
														</li>
													</ul>
												</div>																
											</div>
										</div>
									</li>
									
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
													<p><a href="<?php $id = $activitie['post_user_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><?= $this->context->getuserdata($activitie['post_user_id'],'fullname');?></a> <?=$text?></p>
												</div>														
												<div class="descholder">
													<a href="javascript:void(0)" data-type="newpost" act-id="<?= $activitie['_id'];?>" class="mainlink a_postlink di_act_post">
														<div class="post-para">
															<h6><?= $activitie['post_title'];?></h6>
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
</div>
<script type="text/javascript" src="<?=$baseUrl?>/js/activity.js"></script>
<?php exit();?>