<?php
use frontend\assets\AppAsset;
use frontend\models\PostForm;
use frontend\models\PhotoForm;

use frontend\models\PinImage;
use frontend\models\Comment;
use frontend\models\Like; 
use frontend\models\LoginForm;
use frontend\models\UserForm;
use frontend\models\Collections;
use frontend\models\Gallery;
use frontend\models\StoredData;

$baseUrl = AppAsset::register($this)->baseUrl; 
$session = Yii::$app->session;
$logged_user_id = (string)$session->get('user_id');
$logged_user_thumb = $this->context->getimage($logged_user_id, 'thumb');

if(isset($id)) { 
   	$ISDSK = explode('|||', $id);
   	if(count($ISDSK) == 2) {
   		$type = $ISDSK[1];
	   	$choicesTables = array("UserPhotos", "Gallery", "PostForm", "PlaceDiscussion", "Collections");
		if(in_array($type, $choicesTables)) {
			$id = $ISDSK[0]; 
			StoredData::increaseGalleryViewCount($id, $logged_user_id);
    
			$gallery = Gallery::getgallerydetail($id, $type);
			$gallery = json_decode($gallery, true);

			if(!empty($gallery)) { 
				$gallery_item_id = (string)$gallery['_id'];
		        $title = $gallery['title'];
		        $description = $gallery['description'];
		        $location = trim($gallery['location']); 
		        $tagged_connections = $gallery['tagged_connections'];
		        $visible_to = $gallery['visible_to'];
		        $post_user_id = $gallery['user_id'];        
		        
		        $puserdetails = LoginForm::find()->where(['_id' => $post_user_id])->one();
		        if($post_user_id != $logged_user_id) {
		            $post_user_name = $puserdetails['fullname'];
		        } else {
		            $post_user_name = 'You';
		        }


		        $post_user_thumb = $this->context->getimage($post_user_id, 'thumb');

		        $like_object_id = $gallery_item_id;
		        if($type == 'UserPhotos') {
		        	$fileinfo = pathinfo($imgsrc);
                    $like_object_id = $fileinfo['filename'] .'_'. $gallery_item_id;
		        }

		     	$like_buddies = Like::getLikeUser($like_object_id);
				$newlike_buddies = array();

				foreach($like_buddies as $like_buddy) {
			     	$newlike_buddies[] = ucwords(strtolower($like_buddy['user']['fullname']));
				}

				$newlike_buddiesImplode = implode('<br/>', $newlike_buddies);  
 
				$likeHtml = 'No likes found.';
				if(!empty($newlike_buddies)) {
					if(count($newlike_buddies) == 1) {
						$likeHtml = '<a href="javascript:void(0)">'.$newlike_buddies[0].'</a> liked this.';
					} else if(count($newlike_buddies) == 2) {
						$likeHtml = '<a href="javascript:void(0)">'.$newlike_buddies[0] . '</a> and <a href="javascript:void(0)">' . $newlike_buddies[1].'</a> liked this.';
					} else {
						$likeHtml = '<a href="javascript:void(0)">'.$newlike_buddies[0] . '</a>, <a href="javascript:void(0)">' . $newlike_buddies[1] .'</a> and <a href="javascript:void(0)" data-title="'.$newlike_buddiesImplode.'">'.count($newlike_buddies) . '</a> more people liked this.';
					}
				}
		 		
				if($type == 'UserPhotos') {
					$comments = Comment::getSliderCommentsUserPhotos($like_object_id);
				} else {
					$comments = Comment::getSliderComments($like_object_id);
				}


				$commentsLabel = 'No Comments'; 
				if(!empty($comments)) {
					if(count($comments)>1) {
						$commentsLabel = count($comments) .' Comment';
					} else {
						$commentsLabel = count($comments) .' Comments';
					}
				}

				$tagged_connections = explode(',', $tagged_connections);
				$tagged_connectionsArray = array();
				if(!empty($tagged_connections)) {
					foreach ($tagged_connections as $stagged_connections) {
						$tagged_connectId = $stagged_connections;
						$cuserdata = LoginForm::find()->select(['fullname'])->where([(string)'_id' => $tagged_connectId])->one();	
						if(!empty($cuserdata)) {
							$cuserdatafullname = $cuserdata['fullname'];
							$tagged_connectionsArray[] = $cuserdatafullname;
						}				
					}
				}

				$tagged_connectionsHtml = 'No tag connections found';
				if(!empty($tagged_connectionsArray)) {
					if(count($tagged_connectionsArray) == 1) {
						$tagged_connectionsHtml = '<a href="javascript:void(0)">'.$tagged_connectionsArray[0].'</a>';
					} else if(count($tagged_connectionsArray) == 2) {
						$tagged_connectionsHtml = '<a href="javascript:void(0)">'.$tagged_connectionsArray[0] . '</a> with <a href="javascript:void(0)">' . $tagged_connectionsArray[1].'</a>';
					} else {
						$tempcount = count($tagged_connectionsArray) - 2;
						$tempArray = array_slice($tagged_connectionsArray,2);
						$tagged_connectionsArraybuddiesImplode = implode('<br/>', $tempArray);
						
						$tagged_connectionsHtml = '<a href="javascript:void(0)">'.$tagged_connectionsArray[0] . '</a> with <a href="javascript:void(0)">' . $tagged_connectionsArray[1] .'</a> and <a href="javascript:void(0)" class="liveliketooltip" data-title="'.$tagged_connectionsArraybuddiesImplode.'">'.$tempcount. ' others.</a>';
					}
				}
				
				$viewRecord = StoredData::find()->where(['post_id' => $id, 'type' => 'viewcount'])->asarray()->one(); 
				$viewRecordCount = 0;
				if(!empty($viewRecord)) {
					$viewRecord = isset($viewRecord['viewids']) ? $viewRecord['viewids'] : '';
					$viewRecord = explode(',', $viewRecord);
					$viewRecord = array_values(array_filter($viewRecord));
					if(!empty($viewRecord)) {
						$viewRecordCount = count($viewRecord);
					}
				}

				$isHideDisplay = false;
				if($type == 'Gallery') {
					if(strstr($_SERVER['HTTP_REFERER'],'r=places')) {
						$isHideDisplay = true;
					}
				}

				?>
				<div class="sub-photo-view row mx-0">
					<div class="col col s12 m10 offset-m1">
						<div class="col m6 leftbox">
							<div class="left-sec"> 
								<div class="people-box">
									<div class="img-holder">
										<img src="<?=$post_user_thumb?>">
									</div>
									<div class="desc-holder">
										<a href="javascript:void(0)" class="userlink"><?=ucfirst(strtolower($title))?></a>
										<?php if($type == 'Gallery' && $logged_user_id == $post_user_id) { ?>
										<a href="javascript:void(0)" class="edit-icon waves-effect waves-theme edit-gallery" data-editid="<?=$like_object_id?>" data-type="<?=$type?>">
											<i class="mdi mdi-pencil"></i>
										</a>
										<?php } ?>
										<span class="info">By <?=$post_user_name?></span>
									</div>
								</div>
								<div class="photo-para">
									<p><?=ucfirst($description)?></p>
								</div>

								<div class="joined-tb p-0 vilicoareanew">
									<ul>
										<li><p>Views</p><h4 class="viewsCount"><?=$viewRecordCount?></h4></li>
										<li><p>Likes</p><h4 class="likesCount"><?=count($newlike_buddies)?></h4></li>
										<li class="notmarginright"><p>Comments</p><h4 class="commentsCount"><?=count($comments)?></h4></li>
										<li class="longtimelinesent"><p class="date">Taken on November 28, 2018</p></li>
									</ul>
								</div>

								<?php if($location != '') { ?>
								<div class="photo-location">
									<i class="zmdi zmdi-pin"></i>
									<span><?=$location?></span>
								</div>
								<?php } ?>
								<div class="photo-faved">
									<a href="javascript:void(0)" title="fave"><i class="mdi mdi-thumb-up-outline fave-icon"></i></a>
									<div class="faved-by likehtml">
										<?=$likeHtml?>
									</div>
								</div>
								<div class="photo-tagged">
									<i class="mdi mdi-account-multiple-outline"></i>
									<div class="tagged-by">
										<?=$tagged_connectionsHtml?>
									</div>
								</div>
								<div class="additional-info">
									<h5>Additional info</h5>	
									<ul>
										<li>
											<i class="mdi mdi-lock-open-outline"></i>
											<span class="privacy-label">Photo privacy</span> 
											<span class="privacy-value right"><?=$visible_to?></span>
										</li>
										<?php if($isHideDisplay) { ?>
										<li>
											<i class="mdi mdi-eye-off"></i>
											<a href="javascript:void(0)" onclick="galleryhidephoto()">Hide Photo</a>
										</li>
										<?php } ?>
										<li>
											<i class="mdi mdi-flag-outline"></i>
											<a href="javascript:void(0)" onclick="reportabuseopenpopup('<?=$like_object_id?>', 'Gallery');">Report Photo</a>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="col m6 rightbox">
							<div class="joined-tb p-0 vilicoarea">
								<ul>
									<li><p>Views</p><h4 class="viewsCount"><?=$viewRecordCount?></h4></li>
									<li><p>Likes</p><h4 class="likesCount"><?=count($newlike_buddies)?></h4></li>
									<li class="notmarginright"><p>Comments</p><h4 class="commentsCount"><?=count($comments)?></h4></li>
									<li class="longtimelinesent"><p class="date">Taken on November 28, 2018</p></li>
								</ul>
							</div>
							<div class="comment-sec"> 
								<div class="comment-count"><h4><?=$commentsLabel?></h4></div>
								<div class="addnew-comment valign-wrapper ">
									<div class="img-holder">
										<a href="javascript:void(0)"><img class="circle" src="<?=$logged_user_thumb?>"></a>
									</div>
									<div class="desc-holder">
										<div class="cmntarea">
											<textarea data-adaptheight class="materialize-textarea data-adaptheight" id="slidercommenttextarea" placeholder="Add a comment..."></textarea>
										</div>
									</div>
								</div>
								<div class="comment-list">
									<ul>
										<?php
										if(!empty($comments)) {
											foreach ($comments as $singlecomment) {
												$com_user_id = $singlecomment['user_id'];
												$com_user_thumb = $this->context->getimage($com_user_id, 'thumb');
												$com_name = $singlecomment['user']['fullname'];
												$com_comment = $singlecomment['comment'];
												$com_created_date = $singlecomment['created_date'];
												$filter_date = date('M d, Y', $com_created_date);
												?>
												<li>
													<div class="ranker-box">
						        						<div class="img-holder">
						        							<img src="<?=$com_user_thumb?>">
														</div>
														<div class="desc-holder">
															<a href="javascript:void(0)" class="userlink"><?=$com_name?></a>
															<span class="comment-date"><?=$filter_date?></span>
															<span class="info"><?=$com_comment?></span>
														</div>
													</div>
												</li>
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
			<?php 
			}
		}
	}
}

exit();?>