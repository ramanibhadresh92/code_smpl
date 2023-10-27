<?php 
use yii\helpers\Url;
use frontend\models\Referal;
$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');
	
if(isset($posts) && !empty($posts)) { 
	foreach ($posts as $key => $value) {
			$idArray = array_values($value['_id']);
			$postId = $idArray[0];
            //$postId = (string)$value['_id']['$id'];
            $postUId = $value['user_id'];
            $profile = $value['profile']; 
            $name = $value['fullname'];
            $description = preg_replace('/\s+/S', " ", $value['description']);
            $creproactivity = $value['creproactivity'];
            $creproactivity = str_replace(',', ', ', $creproactivity);
            $language = $value['language'];
            $creprofee = $value['creprofee'];
            $address = trim($value['city'] .', '.$value['country']);
			$address = explode(",", $address);
            $address = array_filter($address);
			if(count($address) >1) {
		        $first = reset($address);
		        $last = end($address);
		        $address = $first.', '.$last;
			} else {
				$address = implode(", ", $address);
			}
            if($creprofee == 0 || $creprofee == null) {
                $creprofee = 'Negotiable';
            } else {
                $creprofee = '$' . $creprofee . ' per hour.';
            }
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
        	$ratingHTML = $this->context->calculateLocalGuideRating($postUId);
	?>
	<div class="post-holder hireguide-post bshadow postid_<?=$postId?>">
	    <div class="post-topbar">
	        <div class="post-userinfo">
	            <div class="img-holder">
	                <div id="profiletip-1" class="profiletipholder">
	                    <span class="profile-tooltip tooltipstered">
	                       <img class="circle" src="<?=$profile?>"/>
	                    </span>
	                </div>
	            </div>
	            <div class="desc-holder">
	                <a href="javascript:void(0)"><?=$name?></a> is <span>Local Guide</span> in <span class="etext"><?=$address?></span>
	                <span class="timestamp"><?=$timelabel?></span>
	            </div>
	        </div>
	        <div class="settings-icon">
	            <div class="dropdown dropdown-custom dropdown-med">
	                <a href="javascript:void(0)" class="dropdown-toggle <?=$checkuserauthclass?> directcheckuserauthclass" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">                                   
	                    <i class="zmdi zmdi-more-vert"></i>
	                </a>
	                <?php if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') { ?>
	                <ul class="dropdown-menu">
                       <?php if($postUId == $user_id) { ?>
                            <li class="nicon" onclick="actionDeleteMyEventFromList('<?=$postId?>', this)"><a href="javascript:void(0)" class="savepost-link">Delete guide profile</a></li>
                        <?php } else { ?>
                            <li>
                            	<ul class="post-sicon-list">
		                            <?php if(!$is_save) { ?>
		                                <li class="nicon"  onclick="actionSavePost('<?=$postId?>', this)"><a href="javascript:void(0)">Save</a></li>
		                            <?php } ?>
		                            <li class="nicon" onclick="actionDeletePost('<?=$postId?>', this)"><a href="javascript:void(0)" class="savepost-link">Hide guide profile</a></li>
		                            <li class="nicon" onclick="actionBlockUserforPost('<?=$postId?>', this)"><a href="javascript:void(0)" class="savepost-link">Block this user</a></li>
		                            <li class="nicon"><a href="#reportpost-popup" class="popup-modal" onclick="reportabuseopenpopup('<?=$postId?>', 'Localguide');">Report abuse</a></li>
	                            </ul>                                     
                            </li>
                        <?php } ?>
	                </ul>
                    <?php } ?>
	            </div>
	        </div>
	    </div>
	    <div class="post-content">
	        <div class="post-details">
	            <div class="post-hireguide">
	            	<div class="drow">
                      <div class="caption-holder">Rating</div>
                      <div class="detail-holder">
                      		<div class="ratingarea">
	                            <?=$ratingHTML?>
	                            <span>(2)</span>
	                        </div>
                      </div>
                   </div>

                   <div class="drow">
                      <div class="caption-holder">Languages</div>
                      <div class="detail-holder"><?=$language?></div>
                   </div>
	            </div>
	            <div class="content-holder mode-holder">
	                <div class="normal-mode">
	                    <div class="post-desc">
	                        <p class="dpara"><i class="mdi mdi-format-quote-open"></i><?=$description?></p>                         
	                    </div>
	                    <div class="drow">
	                        <div class="caption-holder">Guide activities</div>
	                        <div class="detail-holder"><?=$creproactivity?></div>
	                    </div>
	                    <div class="drow">
	                        <div class="caption-holder">Guide Fees</div>
	                        <div class="detail-holder"><?=$creprofee?></div>
	                    </div>
	                    <?php if($postUId != $user_id) { ?>
	                        <a href="javascript:void(0)" onclick="open_detail(this)" class="cborder-btn right btn-primary <?=$checkuserauthclass?> directcheckuserauthclass">Invite</a>
	                    <?php } ?>
	                </div>
	                <?php if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') { ?>
	                <div class="detail-mode">
	                    <div class="post-desc">
	                        <p class="dpara"><i class="mdi mdi-format-quote-open"></i><?=$description?></p>                           
	                    </div>
	                    <div class="drow">
	                        <div class="caption-holder">Guide activities</div>
	                        <div class="detail-holder"><?=$creproactivity?></div>
	                    </div>
	                    <div class="drow">
	                        <div class="caption-holder">Guide Fees</div>
	                        <div class="detail-holder"><?=$creprofee?></div>
	                    </div>
	                    <div class="invite-section">    
	                        <div class="inner-box">
	                            <h5>Send request to guide</h5>
	                            <div class="form-holder">
	                            <form class="formpostid_<?=$postId?>">
	                                <ul>
	                                    <li>
	                                        <div class="caption-holder"><label>Arrival Date</label></div>
	                                        <div class="detail-holder">
	                                            <div class="sliding-middle-out anim-area dateinput">
	                                                <input type="text" onkeydown="return false;" placeholder="Choose Date" class="form-control datetime-pickernewone" id="arrival_date" data-value="<?=$arrival?>" value="<?=$arrival?>">
	                                            </div>
	                                        </div>
	                                    </li>
	                                    <li>
	                                        <div class="caption-holder"><label>Departure Date</label></div>
	                                        <div class="detail-holder">
	                                            <div class="sliding-middle-out anim-area dateinput">
	                                                <input type="text" onkeydown="return false;" placeholder="Choose Date" class="form-control datetime-pickernewone" id="departune_date" data-value="<?=$departure?>" value="<?=$departure?>">
	                                            </div>
	                                        </div>
	                                    </li>
	                                    <li>
	                                        <div class="fullwidth"><label>Traveller message to guide</label></div>
	                                        <div class="fullwidth">
	                                            <div class="cmntarea underlined fullwidth">
	                                                <textarea data-adaptheight class="materialize-textarea data-adaptheight" id="message" placeholder="<?=$placeholder?>"><?=$message?></textarea>
	                                            </div>
	                                        </div>
	                                    </li>
	                                </ul>
	                                <div class="btn-holder">
	                                    <a href="javascript:void(0)" class="btn btn-primary btn-xs graybtn" onclick="close_detail(this)">Cancel</a>
	                                    <a href="javascript:void(0)" class="btn btn-primary btn-xs" onclick="sendinvitepost('<?=$postId?>', this)">Send</a>
	                                </div>
	                            </form>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	                <?php } ?>
	            </div>
	        </div>          
	    </div>
	</div>
<?php } } else { ?>
	<?php $this->context->getnolistfound('norecordfound'); ?>
<?php }
exit;?>							