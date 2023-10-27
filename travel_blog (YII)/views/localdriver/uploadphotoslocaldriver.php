<?php   
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use frontend\assets\AppAsset;
use backend\models\LocalguideActivity;
$baseUrl = AppAsset::register($this)->baseUrl;
if(isset($post) && !empty($post)) {
	$postId = (string)$post['_id']['$id'];
	$uniqid = rand(9999, 99999).$postId;
	$postUId = $post['user_id'];
	$images = $post['images']; 
	$images = explode(',', $images);
	$profile = '';
	if(!empty($images)) {
		$profile = $images[0];
	}
	$name = $this->context->getuserdata($postUId,'fullname');
	$vehicletype = $post['vehicletype'];
	$onboard = $post['onboard'];
	$vehiclecapacity = $post['vehiclecapacity'];
	$restriction = $post['restriction'];
	$describeyourtalent = $post['describeyourtalent'];
	$activity = isset($post['activity']) ? $post['activity'] : '';
	$is_save = isset($post['is_saved']) ? $post['is_saved'] : false; 
	$is_invited = isset($post['is_invited']) ? $post['is_invited'] : false; 
	$arrival = isset($post['invitedInfo']['arrival_date']) ? $post['invitedInfo']['arrival_date'] : '';
	$departure = isset($post['invitedInfo']['departure_date']) ? $post['invitedInfo']['departure_date'] : '';
	$message = isset($post['invitedInfo']['message']) ? $post['invitedInfo']['message'] : '';
	$link = Url::to(['userwall/index', 'id' => $postUId]);
	if(isset($post['updated_at']) && $post['updated_at'] != '') {
	    $sendTime = $post['updated_at'];
	} else {
	    $sendTime = $post['created_at'];
	}
	$timelabel = Yii::$app->EphocTime->time_elapsed_A(time(),$sendTime);
	$placeholder = "Write your message to ".$name."";
	$isEmpty = false;

	// rating calculation area....
	$ratingHTML = $this->context->calculateLocalDriverRating($postUId);
  $my_fees = '';
?>
<div class="modal_content_container">
  <div class="modal_content_child modal-content">
     <div class="popup-title ">
        <button class="hidden_close_span close_span waves-effect">
        <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
        </button>         
        <h3>Upload driver photos</h3>
        <span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
        <a type="button" class="item_done crop_done hidden_close_span custom_close waves-effect" href="javascript:void(0)" onclick="uploadphotoslocaldriversave('<?=$postId?>')">Done</a>
     </div>
     <div class="custom_modal_content modal_content" id="createpopup">
        <div class="ablum-yours profile-tab">
           <div class="ablum-box detail-box">
              <div class="content-holder main-holder">
                 <div class="summery">
                    <div class="dsection bborder expandable-holder expanded">
                       <div class="form-area expandable-area">
                          <form class="ablum-form">
                             <div class="form-box">
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Vehicle type</label>
                                      </div>
                                      <div class="detail-holder">
                                        <span><?=$vehicletype?></span>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>On-board</label>
                                      </div>
                                      <div class="detail-holder">
                                        <span><?=$onboard?></span>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Vehicle capacity</label>
                                      </div>
                                      <div class="detail-holder">
                                        <span><?=$vehiclecapacity?></span>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Restriction</label>
                                      </div>
                                      <div class="detail-holder">
                                        <span><?=$restriction?></span>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Meet your driver</label>
                                      </div>
                                      <div class="detail-holder">
                                        <span><?=$describeyourtalent?></span>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="row">
                                      <div class="col s6">
                                         <div class="frow">
                                            <div class="caption-holder">
                                               <label>Activities that I can be hired for*</label>
                                            </div>
                                            <div class="detail-holder custom-hireaguide dropdown782">
                                               <span><?=$activity?></span>
                                            </div>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <div class="row">
                                            <div class="col l3 m4 s12">
                                               <label>My Fees*</label>
                                            </div>
                                            <div class="col l6 m8 s12">
                                              <span><?=$my_fees?></span>
                                            </div>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="frow nomargin new-post">
                                   <div class="caption-holder">
                                      <label>Awesome photos help guests want to hire you</label>
                                   </div>
                                   <div class="detail-holder">
                                      <div class="input-field ">
                                         <div class="post-photos new_pic_add">
                                            <div class="img-row">
											                   			 <div class="img-box">
                                                  <div class="custom-file addimg-box add-photo ablum-add">
                                                     <span class="icont">+</span><br><span class="">Upload photo</span>
                                                     <div class="addimg-icon">
                                                     </div>
                                                     <input class="upload custom-upload remove-custom-upload" title="Choose a file to upload" required="" data-class=".post-photos .img-row" multiple="true" type="file">
                                                  </div>
                                               </div>
                                            </div>
                                         </div>
                                      </div>
                                   </div>
                                   <p class="photolabelinfo">Please add three cover photos for your profile</p>
                                </div>
                             </div>
                          </form>
                       </div>
                    </div>
                 </div>
              </div>
           </div>
        </div>
     </div>
  </div>
</div> 
<div class="valign-wrapper additem_modal_footer modal-footer">
  <span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
  <a href="javascript:void(0)" class="btngen-center-align close_modal open_discard_modal waves-effect">Cancel</a> 
  <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="uploadphotoslocaldriversave('<?=$postId?>')">Publish</a>
</div>	
<?php }  
exit;