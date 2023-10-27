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
$homestay_facilities = isset($homestay['homestay_facilities']) ? $homestay['homestay_facilities'] : '';
$homestay_facilities = explode(',', $homestay_facilities);
$images = isset($homestay['images']) ? $homestay['images'] : '';
$images = explode(',', $images);
$images = array_values(array_filter($images));
$currency_array = array("USD", "EUR", "YEN", "CAD", "AUE");

//$Services = array('Waste tank discharge' => 'discharge','Public lavatory' => 'lavatory','Walking path' => 'Walking','Swimming pool' => 'Swimming','Fishing permits' => 'Fishing','Cooking facilities' => 'Cooking','Sports hall' => 'Sports hall','Washing machine' => 'Washing','Hot pot' => 'Hot pot','Sports field' => 'Sports field','Shower' => 'Shower','Golf course' => 'Golf','Sauna' => 'Sauna','Play ground' => 'ground');

$Services = array('Swimming pool' => 'Swimming','Cooking facilities' => 'Cooking','Washing machine' => 'Washing','Shower' => 'Shower', 'Tv' => 'Tv', 'Elevator' => 'Elevator', 'Parking' => 'Parking', 'Wifi' => 'Wifi');

if(!empty($homestay)) {
?>

<div class="modal_content_container">
  <div class="modal_content_child modal-content">
     <div class="popup-title ">
        <button class="hidden_close_span close_span waves-effect">
        <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
        </button>         
        <h3>Edit Homestay</h3>
        <span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
        <a type="button" class="item_done crop_done hidden_close_span custom_close waves-effect <?=$checkuserauthclass?>" onclick="edithomestay('<?=$postId?>', this)" href="javascript:void(0)">Done</a>
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
                                         <div class="input-field">
                                            <input type="text" placeholder="Homestay title: i.e One room for homestay" class="fullwidth locinput" id="edittitle" value="<?=$title?>" />
                                         </div>
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
                                            <a class="dropdown_text dropdown-button-left" href="javascript:void(0)" data-activates="homestayProp" id="editproperty_type">
                                               <span><?=$property_type?></span>
                                               <i class="zmdi zmdi-caret-down"></i>
                                            </a>
                                            <ul id="homestayProp" class="dropdown-privacy dropdown-content custom_dropdown">
                                               <li>
                                                  <a href="javascript:void(0)">House</a>
                                               </li>
                                               <li>
                                                  <a href="javascript:void(0)">Apartment</a>
                                               </li>
                                               <li>
                                                  <a href="javascript:void(0)">Condominium</a>
                                               </li>
                                               <li>
                                                  <a href="javascript:void(0)">Farmstay</a>
                                               </li>
                                               <li>
                                                  <a href="javascript:void(0)">Houseboat</a>
                                               </li>
                                               <li>
                                                  <a href="javascript:void(0)">Bed and breakfast</a>
                                               </li>
                                            </ul>
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
                                            <input name="editguests_room_type" type="radio" id="prRm" value="Private room">
                                            <label for="prRm">Private room</label>
                                            <input name="editguests_room_type" type="radio" id="shRm" value="Shared room">
                                            <label for="shRm">Shared room</label> 
                                            <?php } else if($guests_room_type == 'Private room') { ?>    
                                            <input name="editguests_room_type" type="radio" id="enPl" value="Entire place">
                                            <label for="enPl">Entire place</label>
                                            <input name="editguests_room_type" checked="" type="radio" id="prRm" value="Private room">
                                            <label for="prRm">Private room</label>
                                            <input name="editguests_room_type" type="radio" id="shRm" value="Shared room">
                                            <label for="shRm">Shared room</label>    
                                            <?php } else { ?>    
                                            <input name="editguests_room_type" type="radio" id="enPl" value="Entire place">
                                            <label for="enPl">Entire place</label>
                                            <input name="editguests_room_type" type="radio" id="prRm" value="Private room">
                                            <label for="prRm">Private room</label>
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
                                            <input name="editbath" type="radio" id="shBt" value="Shared">
                                            <label for="shBt">Shared</label>
                                            <?php } else { ?> 
                                            <input name="editbath" type="radio" id="prBt" value="Private">
                                            <label for="prBt">Private</label>
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
                                         <div class="fitem">
                                             <?php if(in_array('Males', $guest_type)) { ?>
                                             <div class="h-checkbox entertosend leftbox">
                                             <?php } else { ?>
                                             <div class="entertosend leftbox">
                                             <?php } ?>
                                               <input id="males" type="checkbox" name="editguest_type" value="Males">
                                               <label for="males">Males</label>
                                            </div>
                                         </div>
                                         <div class="fitem">
                                            <?php if(in_array('Females', $guest_type)) { ?>
                                             <div class="h-checkbox entertosend leftbox">
                                             <?php } else { ?>
                                             <div class="entertosend leftbox">
                                             <?php } ?>
                                               <input id="females" type="checkbox" name="editguest_type" value="Females">
                                               <label for="females">Females</label>
                                            </div>
                                         </div>
                                         <div class="fitem">
                                            <?php if(in_array('Couples', $guest_type)) { ?>
                                             <div class="h-checkbox entertosend leftbox">
                                             <?php } else { ?>
                                             <div class="entertosend leftbox">
                                             <?php } ?>
                                               <input id="couples" type="checkbox" name="editguest_type" value="Couples">
                                               <label for="couples">Couples</label>
                                            </div>
                                         </div>
                                         <div class="fitem">
                                            <?php if(in_array('Families', $guest_type)) { ?>
                                             <div class="h-checkbox entertosend leftbox">
                                             <?php } else { ?>
                                             <div class="entertosend leftbox">
                                             <?php } ?>
                                               <input id="families" type="checkbox" name="editguest_type" value="Families">
                                               <label for="families">Families</label>
                                            </div>
                                         </div>
                                         <div class="fitem">
                                             <?php if(in_array('Students', $guest_type)) { ?>
                                             <div class="h-checkbox entertosend leftbox">
                                             <?php } else { ?>
                                             <div class="entertosend leftbox">
                                             <?php } ?>
                                               <input id="students" type="checkbox" name="editguest_type" value="Students">
                                               <label for="students">Students</label>
                                            </div>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Homestay location</label>
                                      </div>
                                      <div class="detail-holder">
                                         <div class="input-field">
                                            <input type="text" placeholder="Enter city name" class="fullwidth locinput" data-query="all" onfocus="filderMapLocationModal(this)" id="edithomestay_location" autocomplete='off' value="<?=$homestay_location?>" />
                                         </div>
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
                                                  <div class="input-field">
                                                     <input type="text" placeholder="20" class="fullwidth input-rate" id="editadult_guest_rate" value="<?=$adult_guest_rate?>" />
                                                  </div>
                                               </div>
                                            </div>
                                            <div class="col s2">
                                              <a class="dropdown_text dropdown-button currency_drp" href="javascript:void(0)" data-activates="chooseCurrency" id="editcurrency">
                                                 <span class="currency_label"><?=$currency?></span>
                                                 <i class="zmdi zmdi-caret-down"></i>
                                              </a>
                                              <ul id="chooseCurrency" class="dropdown-privacy dropdown-content custom_dropdown guest-ddl">
                                                <?php foreach ($currency_array as $s8032n) { ?>
                                                 <li>
                                                    <a href="javascript:void(0)"><?=$s8032n?></a>
                                                 </li>
                                                <?php  } ?>
                                              </ul>
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
                                         <div class="input-field">
                                            <textarea placeholder="Tell people about your Homestay" id="editdescription"><?=$description?></textarea>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Homestay rules</label>
                                      </div>
                                      <div class="detail-holder">
                                         <div class="input-field">
                                            <textarea placeholder="Tell your guest about your rules for homestay" class="fullwidth locinput " id="editrules"><?=$rules?></textarea>
                                         </div>
                                      </div>
                                   </div>
                                </div>

                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder mb-10">
                                         <label>Services</label>
                                      </div>
                                      <div class="detail-holder">
                                         <a href="javascript:void(0)" class="check-image">
                                            <div class="image-select"></div>
                                            <img alt="spa" title="spa" src="<?=$baseUrl?>/images/amenity-spa.png">
                                         </a>
                                         <a href="javascript:void(0)" class="check-image">
                                            <div class="image-select"></div>
                                            <img alt="beach" title="beach" src="<?=$baseUrl?>/images/amenity-beach.png">
                                         </a>
                                         <a href="javascript:void(0)" class="check-image">
                                            <div class="image-select"></div>
                                            <img alt="wifi" title="wifi" src="<?=$baseUrl?>/images/amenity-wifi.png">
                                         </a>
                                         <a href="javascript:void(0)" class="check-image">
                                            <div class="image-select"></div>
                                            <img alt="breakfast" title="breakfast" src="<?=$baseUrl?>/images/amenity-breakfast.png">
                                         </a>
                                         <a href="javascript:void(0)" class="check-image">
                                            <div class="image-select"></div>
                                            <img alt="pool" title="pool" src="<?=$baseUrl?>/images/amenity-pool.png">
                                         </a>
                                      </div>
                                   </div>
                                </div>
        
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Homestay Facilities</label>
                                      </div>
                                      <div class="detail-holder">
                                        <div class="row">
                                        <?php
                                        foreach ($Services as $key => $Services_s) {
                                            $icon = strtolower($Services_s);
                                            $icon = $icon.'.png';
                                            $icon = $baseUrl.'/images/services-icon/'.$icon;
                                            if(!file_exists($_SERVER['DOCUMENT_ROOT'].$icon)) {
                                              continue;
                                            }
                                            $Services_s = ucwords(strtolower($Services_s));
                                            $alt = $key;
                                           ?>
                                           <div class="col s3 center-align servicesbox edithomestay_facilities">
                                              <?php if(in_array($Services_s, $homestay_facilities)) { ?>
                                              <a href="javascript:void(0)" class="check-image active-class" data-value="<?=$key?>">
                                              <?php } else { ?>
                                              <a href="javascript:void(0)" class="check-image" data-value="<?=$key?>">
                                              <?php } ?>
                                                 <div class="image-select"></div>
                                                 <img alt="<?=$alt?>" title="<?=$alt?>" src="<?=$icon?>">
                                                 <div><?=$Services_s?></div>
                                              </a>
                                           </div>
                                           <?php
                                        }
                                        ?>
                                       
                                   </div>
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
                                                <?php
                                                foreach ($images as $images_s){
                                                  ?>
                                                  <div class="img-box"><img src="<?=$images_s?>" class="upldimg thumb-image vimg"><div class="loader" style="display: none;"></div><a href="javascript:void(0)" onclick="removepichomestay_modal('<?=$postId?>', this)" class="removePhotoFilelocalguide"><i class="mdi mdi-close"></i></a></div>

                                                  <?php
                                                }
                                                if(count($images) <3) { 
                                                ?>      
                                                <div class="img-box">
                                                    <div class="custom-file addimg-box add-photo ablum-add">
                                                       <span class="icont">+</span><br><span class="">Upload photo</span>
                                                       <div class="addimg-icon">
                                                       </div>
                                                       <input class="upload custom-upload remove-custom-upload" title="Choose a file to upload" required="" data-class=".post-photos .img-row" multiple="true" type="file">
                                                    </div>
                                                 </div>
                                              <?php } ?>
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
  <a href="javascript:void(0)" class="btngen-center-align waves-effect <?=$checkuserauthclass?>" onclick="edithomestay('<?=$postId?>', this)">Publish</a>
</div>
<?php }
} 
exit; ?> 
