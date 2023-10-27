<?php  
use yii\helpers\Url; 
use frontend\assets\AppAsset;
use frontend\models\Homestay;

$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');
 
if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') { 
$homestay = Homestay::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->asarray()->one();
$postId = (string)$homestay['_id'];
$postUId = (string)$homestay['user_id'];
$title = isset($homestay['title']) ? $homestay['title'] : '';
$property_type = isset($homestay['property_type']) ? $homestay['property_type'] : '';
$guests_room_type = isset($homestay['guests_room_type']) ? $homestay['guests_room_type'] : '';
$bath = isset($homestay['bath']) ? $homestay['bath'] : '';
$guest_type = isset($homestay['guest_type']) ? $homestay['guest_type'] : '';
$guest_type = explode(',', $guest_type);
$homestay_location = isset($homestay['homestay_location']) ? $homestay['homestay_location'] : '';
$adult_guest_rate = isset($homestay['adult_guest_rate']) ? $homestay['adult_guest_rate'] : '';
$currency = isset($homestay['currency']) ? $homestay['currency'] : '';
$description = isset($homestay['description']) ? $homestay['description'] : '';
$rules = isset($homestay['rules']) ? $homestay['rules'] : '';
$images = isset($homestay['images']) ? $homestay['images'] : '';
$images = explode(',', $images);
$images = array_values(array_filter($images));
if(!empty($homestay)) {
?>

<div class="modal_content_container">
  <div class="modal_content_child modal-content">
     <div class="popup-title ">
        <button class="hidden_close_span close_span waves-effect">
        <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
        </button>         
        <h3>Upload homestay photos</h3>
        <span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
        <a type="button" class="item_done crop_done hidden_close_span custom_close waves-effect <?=$checkuserauthclass?>" onclick="uploadphotoshomestaysave('<?=$postId?>', this)" href="javascript:void(0)">Done</a>
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
                                         <label>Title</label>
                                      </div>
                                      <div class="detail-holder">
                                        <span><?=$title?></span>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="row">
                                      <div class="col s6">
                                         <div class="frow pr-5">
                                            <div class="caption-holder">
                                               <label>Property type</label>
                                            </div>
                                            <span><?=$property_type?></span>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder mb-5">
                                         <label>What will guests have?</label>
                                      </div>
                                      <div class="detail-holder">
                                         <div class="detail-holder inline-radio">
                                            <?php if($guests_room_type == 'Entire place') { ?>
                                            <input name="editguests_room_type" checked="" type="radio" id="enPl" value="Entire place">
                                            <label for="enPl">Entire place</label>
                                            <?php } else if($guests_room_type == 'Private room') { ?>    
                                            <input name="editguests_room_type" checked="" type="radio" id="prRm" value="Private room">
                                            <label for="prRm">Private room</label>
                                            <?php } else { ?>    
                                            <input name="editguests_room_type" checked="" type="radio" id="shRm" value="Shared room">
                                            <label for="shRm">Shared room</label>  
                                            <?php } ?>    
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv mb-5">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Bath</label>
                                      </div>
                                      <div class="detail-holder">
                                         <div class="detail-holder inline-radio">
                                            <?php if($bath == 'Private') { ?>
                                            <input name="editbath" checked="" type="radio" id="prBt" value="Private">
                                            <label for="prBt">Private</label>
                                            <?php } else { ?> 
                                            <input name="editbath" checked="" type="radio" id="shBt" value="Shared">
                                            <label for="shBt">Shared</label>  
                                            <?php } ?>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Welcomed guest</label>
                                      </div>
                                      <div class="detail-holder" id="ddddd">
                                         <?php if(in_array('Males', $guest_type)) { ?>
                                         <div class="fitem">
                                             <div class="h-checkbox entertosend leftbox">
                                               <input id="males" type="checkbox" name="editguest_type" value="Males" disabled="disabled" >
                                               <label for="males">Males</label>
                                            </div>
                                         </div>
                                         <?php } ?>
                                         <?php if(in_array('Females', $guest_type)) { ?>
                                         <div class="fitem">
                                             <div class="h-checkbox entertosend leftbox">
                                               <input id="females" type="checkbox" name="editguest_type" value="Females" disabled="disabled" >
                                               <label for="females">Females</label>
                                            </div>
                                         </div>
                                         <?php } ?>
                                         <?php if(in_array('Couples', $guest_type)) { ?>
                                         <div class="fitem">
                                             <div class="h-checkbox entertosend leftbox">
                                               <input id="couples" type="checkbox" name="editguest_type" value="Couples" disabled="disabled" >
                                               <label for="couples">Couples</label>
                                            </div>
                                         </div>
                                         <?php } ?>
                                         <?php if(in_array('Families', $guest_type)) { ?>
                                         <div class="fitem">
                                             <div class="h-checkbox entertosend leftbox">
                                               <input id="families" type="checkbox" name="editguest_type" value="Families" disabled="disabled" >
                                               <label for="families">Families</label>
                                            </div>
                                         </div>
                                         <?php } ?>
                                         <?php if(in_array('Students', $guest_type)) { ?>
                                         <div class="fitem">
                                             <div class="h-checkbox entertosend leftbox">
                                               <input id="students" type="checkbox" name="editguest_type" value="Students" disabled="disabled" >
                                               <label for="students">Students</label>
                                            </div>
                                         </div>
                                         <?php } ?>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Homestay location</label>
                                      </div>
                                      <div class="detail-holder">
                                        <span><?=$homestay_location?></span>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <div class="row">
                                            <div class="col l4 m4 s12">
                                               <label>Rate per adult guest</label>
                                            </div>
                                            <div class="col s6">
                                               <div class="detail-holder">
                                                <span><?=$adult_guest_rate?></span>
                                               </div>
                                            </div>
                                            <div class="col s2">
                                              <span><?=$currency?></span>
                                            </div>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Descibe your homestay</label>
                                      </div>
                                      <div class="detail-holder">
                                        <span><?=$description?></span>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Homestay rules</label>
                                      </div>
                                      <div class="detail-holder">
                                        <span><?=$rules?></span>
                                      </div>
                                   </div>
                                </div>
                                <div class="frow nomargin new-post">
                                   <div class="caption-holder">
                                      <label>Awesome photos help guests want to join up</label>
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
                                   <p class="photolabelinfo">Please add three cover photos for your homestay profile</p>
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
  <a href="javascript:void(0)" class="btngen-center-align waves-effect <?=$checkuserauthclass?>" onclick="uploadphotoshomestaysave('<?=$postId?>', this)">Publish</a>
</div>
<?php }
} 
exit; ?> 
