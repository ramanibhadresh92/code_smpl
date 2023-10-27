<?php
use yii\helpers\Url; 
$session = Yii::$app->session;
$status = $session->get('status');
$user_id = (string)$session->get('user_id');
$isEmpty = true; 
?>
<div class="post-column">
  <div class="tab-content ">
     <div class="tab-pane fade main-pane" id="travelbuddy-recent">
        <div class="post-list">
        	<?php
        	if(isset($posts) && !empty($posts)) { 
				foreach ($posts as $key => $value) {

				$postId = (string)$value['_id'];
				$uniqid = rand(9999, 99999).$postId;
				$postUId = $value['user_id'];
	            $images = $value['images']; 
	            $images = explode(',', $images);
	            $profile = $this->context->getimage($postUId,'thumb');
	            $name = $this->context->getuserdata($postUId,'fullname');
	            $vehicletype = $value['vehicletype'];
	            $onboard = $value['onboard'];
	            $vehiclecapacity = $value['vehiclecapacity'];
	            $restriction = $value['restriction'];
	            $describeyourtalent = $value['describeyourtalent'];
	            $activity = $value['activity'];
	            $activity = str_replace(',', ', ', $activity);
	            $rate = $value['rate'];

	            $is_save = isset($value['is_saved']) ? $value['is_saved'] : false; 
	            $is_invited = isset($value['is_invited']) ? $value['is_invited'] : false; 
	            $arrival = isset($value['invitedInfo']['arrival_date']) ? $value['invitedInfo']['arrival_date'] : '';
	            $departure = isset($value['invitedInfo']['departure_date']) ? $value['invitedInfo']['departure_date'] : '';
	            $message = isset($value['invitedInfo']['message']) ? $value['invitedInfo']['message'] : '';
	            $link = Url::to(['userwall/index', 'id' => $postUId]);
	            if(isset($value['updated_at']) && $value['updated_at'] != '') {
	                $sendTime = $value['updated_at'];
	            } else {
	                $sendTime = $value['created_at'];
	            }
	            $timelabel = Yii::$app->EphocTime->time_elapsed_A(time(),$sendTime);
	            $placeholder = "Write your message to ".$name."";

				// rating calculation area....
				$ratingHTML = $this->context->calculateLocalDriverRating($postUId);
				$isEmpty = false;
				$isOwner = false;
				if($user_id == $postUId) {
					$isOwner = true;
				}
				?>
				<div class="post-holder localdriver-post bshadow postid_<?=$postId?> localdriverrecentbox">
	              <div class="post-topbar">
	                 <div class="post-userinfo">
	                    <div class="img-holder">
	                       <div id="profiletip-1" class="profiletipholder">
	                          <span class="profile-tooltip">
	                          	<img class="circle" src="<?=$profile?>"/>
	                          </span>
	                          <span class="profiletooltip_content slidingpan-holder">
	                             <div class="profile-tip dis-none" style="display:none;">
	                                <div class="profile-tip-avatar">
	                                   <img alt="user-photo" class="img-responsive" src="<?=$baseUrl?>/images/demo-profile.jpg">                                       
	                                   <div class="sliding-pan location-span">
	                                      <div class="sliding-span location-span">Ahmedabad, Gujarat, India</div>
	                                   </div>
	                                </div>
	                                <div class="profile-tip-name">
	                                   <a href="javascript:void(0)"><?=$name?></a>
	                                </div>
	                                <div class="profile-tip-info">
	                                   <div class="profiletip-icon">
	                                      <a href="javascript:void(0)" class="sliding-link" onclick="manageSlidingPan(this,'location-span')" title="Ahmedabad, Gujarat, India"><span class="ptip-icon"><i class="zmdi zmdi-pin"></i></span></a>
	                                   </div>
	                                   <div class="profiletip-icon">
	                                      <a href="javascript:void(0)"><i class="mdi mdi-account-plus"></i></a>
	                                   </div>
	                                   <div class="profiletip-icon">
	                                      <a href="javascript:void(0)" title="View Profile"><span class="ptip-icon"><i class="mdi mdi-eye"></i></span></a>
	                                   </div>
	                                </div>
	                             </div>
	                          </span>
	                       </div>
	                    </div>
	                    <div class="desc-holder">
			                <a href="javascript:void(0)"><?=$name?></a> is <span class="etext">Local Driver</span> in <span class="etext">Japan</span>
			                <span class="timestamp"><?=$timelabel?></span>
			            </div>
	                 </div>
	                 <div class="settings-icon">
	                    <div class="dropdown dropdown-custom dropdown-med">
	                    	<?php if($status == '10') { ?>  
						        <a href="javascript:void(0)" class="dropdown-toggle dropdown-button prevent-gallery" data-activates='<?=$uniqid?>' data-id='<?=$postId?>'>
						           <i class="mdi mdi-flag"></i>
						        </a>
						        <ul id='<?=$uniqid?>' class="dropdown-content">
						            <li class="prevent-gallery"> <a href="javascript:void(0)" data-id="<?=$postId?>" data-module="localdriver" onclick="flagpost(this)">Flag post</a> </li>
						        </ul>
					        <?php } else { ?>
					        	<a href="javascript:void(0)" class="dropdown-button more_btn <?=$checkuserauthclass?> directcheckuserauthclass" data-activates='<?=$uniqid?>'>                                   
				                     <i class="zmdi zmdi-more"></i>
				                </a>
		                       	<?php if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') { ?>
				                <ul id="<?=$uniqid?>" class='dropdown-content custom_dropdown'>
				                	<?php
				                	if($isOwner) {
				                	?>
				                		<li class="nicon"><a href="javascript:void(0)" onclick="editpostpopupopen('<?=$postId?>')">Edit driver profile</a></li>	
										<li class="nicon" onclick="actionBlockUserforPost('<?=$postId?>', this)"><a href="javascript:void(0)" class="savepost-link">Mute this person posts</a></li>
				                		<li class="nicon" onclick="actionDeleteMyEventFromList('<?=$postId?>', this)"><a href="javascript:void(0)" class="savepost-link">Delete driver profile</a></li>
				                	<?php 
				                	} else {
					                	if(!$is_save) { 
					                	?>
				                        <li class="nicon"  onclick="localdriversaveevents(this, '<?=$postId?>', this)"><a href="javascript:void(0)">Save driver profile</a></li>
				                        <?php } else { ?>
										<li class="nicon"  onclick="localdriversaveevents(this, '<?=$postId?>')"><a href="javascript:void(0)">Unsave driver profile</a></li>
				                        <?php } ?>
				                        <li class="nicon" onclick="actionDeletePost('<?=$postId?>', this)"><a href="javascript:void(0)" class="savepost-link">Hide driver profile</a></li>
				                        <li class="nicon" onclick="actionBlockUserforPost('<?=$postId?>', this)"><a href="javascript:void(0)" class="savepost-link">Mute this person posts</a></li>
				                        <li class="nicon"><a href="#reportpost-popup" class="popup-modal" onclick="reportabuseopenpopup('<?=$postId?>', 'Localdriver');">Report abuse</a></li>
				                        <li class="nicon"><a href="javascript:void(0)" onclick="blockFriend(this, '<?=$postUId?>')">Block</a></li>
				                    <?php 
				                	}
				                	?>
			                        	
				                </ul>
			                    <?php } ?>
		                    <?php } ?>
	                    </div>
	                 </div>
	              </div>
	              <div class="post-content">
	                 <div class="post-details">
	                    <div class="post-localdriver">
	                       <span class="ispan"><i class="zmdi zmdi-pin"></i>Lives in London</span>
	                       <div class="more-info">
	                          <span class="ispan"><i class="mdi mdi-comment-outline"></i>Speaks English</span>
	                          <span class="ispan"><i class="mdi mdi-bookmark"></i>References 12</span>
	                          <span class="ispan connectionslabel"><i class=”mdi mdi-account-group”></i></i>Connections 35</span>                                                
	                       </div>
	                    </div>
	                    <div class="content-holder mode-holder">
	                       <div class="normal-mode">
	                          <div class="post-desc">
	                            	<?php if(strlen($describeyourtalent)>187){ ?>
		                                <div class="para-section hire-para">
		                                    <div class="para">
		                                        <p class="dpara"><i class="mdi mdi-format-quote-open"></i><?=$describeyourtalent?></p>
		                                    </div>
		                                    <a href="javascript:void(0)" class="readlink">Read More</a>
		                                </div>
		                            <?php } else { ?>                 
		                                <p class="dpara"><i class="mdi mdi-format-quote-open"></i><?=$describeyourtalent?></p>
		                            <?php } ?> 
	                          </div>
	                          <div class="drow">
	                             <div class="caption-holder">Vehicle Type</div>
						         <div class="detail-holder"><?=$vehicletype?>&nbsp;</div>
	                          </div>
	                          <div class="drow">
	                             <div class="caption-holder">Vehicle Capacity</div>
						         <div class="detail-holder"><?=$vehiclecapacity?>&nbsp;</div>
	                          </div>
	                          <div class="drow">
	                             <div class="caption-holder">Driver activities</div>
						         <div class="detail-holder"><?=$activity?></div>
	                          </div>
	                          <div class="drow">
	                             <div class="caption-holder">Fees</div>
						         <div class="detail-holder"><?=$rate?></div>
	                          </div>
	                          <div class="right">
	                            <a href="<?php echo Url::to(['localdriver/detail', 'id' => $postId]); ?>">LEARN MORE</a>
	                          	<?php 
	                          		if($user_id != 'undefined' && $user_id != '') {
	                          		if($postUId != $user_id) { 
							        	if($is_invited == false) { ?> 
										<a href="javascript:void(0)" onclick="open_detail(this)" class="pl-2 <?=$checkuserauthclass?> directcheckuserauthclass">CONTACT</a>
										<?php } else { ?>
										<a href="javascript:void(0)" class="pl-2 <?=$checkuserauthclass?> directcheckuserauthclass" style="color: #a1a1a1 !important;">CONTACT</a>	
										<?php 
										} 
									}
									} 
								?>	
	                          </div>
	                       	</div>
	                       	<?php if($postUId != $user_id) { 
						    if($is_invited == false) { ?>
						    <?php if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') { ?>
	                       	<div class="detail-mode">
	                          	<div class="post-desc">
						        	<?php if(strlen($describeyourtalent)>187){ ?>
			                            <div class="para-section hire-para">
			                                <div class="para">
			                                    <p class="dpara"><i class="mdi mdi-format-quote-open"></i><?=$describeyourtalent?></p>
			                                </div>
			                                <a href="javascript:void(0)" class="readlink">Read More</a>
			                            </div>
			                        <?php } else { ?>                 
			                            <p class="dpara"><i class="mdi mdi-format-quote-open"></i><?=$describeyourtalent?></p>
			                        <?php } ?> 
						        </div>
								<div class="drow">
									 <div class="caption-holder">Vehicle Type</div>
									 <div class="detail-holder"><?=$vehicletype?></div>
								</div>
								<div class="drow">
									 <div class="caption-holder">On Board</div>
									 <div class="detail-holder"><?=$onboard?></div>
								</div>
								<div class="drow">
									 <div class="caption-holder">Vehicle Capacity</div>
									 <div class="detail-holder"><?=$vehiclecapacity?></div>
								</div>
								<div class="drow">
									 <div class="caption-holder">Activity</div>
									 <div class="detail-holder"><?=$activity?></div>
								</div>
								<div class="drow">
									 <div class="caption-holder">Restriction</div>
									 <div class="detail-holder"><?=$restriction?></div>
								</div>
								<div class="drow">
								 <div class="detail-holder"><?=$describeyourtalent?></div>
								</div>
	                           <div class="invite-section">
	                             <div class="inner-box">
	                                <h5>Send request to driver</h5>
	                                <div class="form-holder">
	                                	<form class="formpostid_<?=$postId?>">
	                                    	<ul>
						                        <li>
						                            <div class="caption-holder"><label>Arrival Date</label></div> 
						                            <div class="detail-holder">
						                                <div class="halfwidth dateinput">
						                                    <input type="text" onkeydown="return false;" placeholder="Choose Date" data-toggle="datepicker" data-query="all" class="datepickerinput form-control" id="arrival_date" value="<?=$arrival?>" readonly>
						                                </div>
						                            </div>
						                        </li>
						                        <li>
						                            <div class="caption-holder"><label>Departure Date</label></div>
						                            <div class="detail-holder">
						                               <div class="halfwidth dateinput">
						                                    <input type="text" onkeydown="return false;" placeholder="Choose Date" data-toggle="datepicker" data-query="all" class="datepickerinput form-control" id="departune_date" value="<?=$departure?>" readonly>
						                                </div>
						                            </div>
						                        </li>
						                        <li>
						                            <div class="fullwidth"><label>Traveller message to driver</label></div>
						                            <div class="fullwidth">
						                                <div class="fullwidth tt-holder">
						                                	<textarea class="materialize-textarea mb0 md_textarea descinput" id="message" placeholder="<?=$placeholder?>"><?=$message?></textarea>
						                                </div>
						                            </div>
						                        </li>
						                    </ul>
	                                   		<div class="btn-holder">
						                        <a href="javascript:void(0)" class="btngen-center-align " onclick="close_detail(this)">Cancel</a>
						                        <a href="javascript:void(0)" class="btngen-center-align " onclick="sendinvitepost('<?=$postId?>', this)">Send
						                        </a>
						                    </div>
	                               		</form>
	                                </div>
	                             </div>
	                           </div>
	                       	</div>
	                       	<?php } } } ?>
	                    </div>
	                 </div>
	              </div>
	           </div>
	           <?php } 
	      	} 
			
			if($isEmpty) { ?>
			<div class="joined-tb">
			    <i class="mdi mdi-file-outline"></i>
			    <p>No local driver found.</p>
			</div>
			<?php } ?>							
        </div>
     </div>
  </div>
</div>