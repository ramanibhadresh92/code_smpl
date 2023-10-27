<?php   
use yii\helpers\Url; 
use frontend\models\Camping;
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;

$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');
$isEmpty = true; 

$Camping = Camping::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->asarray()->one();
if(!empty($Camping)) { 
  $camping_id = (string)$Camping['_id'];
  $camping_uid = $Camping['user_id'];
  $camping_title = $Camping['title'];
  $camping_min_guests = $Camping['min_guests'];
  $camping_max_guests = $Camping['max_guests'];
  $camping_rate = $Camping['rate'];
  $camping_currency = $Camping['currency'];
  $camping_description = $Camping['description'];
  $camping_location = $Camping['location'];
  $camping_telephone = $Camping['telephone'];
  $camping_email = $Camping['email'];
  $camping_website = $Camping['website'];
  $camping_period_s = $Camping['period_s'];
  $camping_period_e = $Camping['period_e'];
  //check date is valid with range or not
  $firstdate_filter = '';
  $seconddate_filter = '';
  $dated = '';

  if($camping_period_s != '' && $camping_period_e != '') {
      $firstdate = trim($camping_period_s);
      $seconddate = trim($camping_period_e);

      $f_date_box = array_map('trim', explode('-', $firstdate));

      if(count($f_date_box) == 3) {
          if(checkdate($f_date_box[0], $f_date_box[1], $f_date_box[2])) {
              $firstdate_filter = $f_date_box[0].'/'.$f_date_box[1];
          }
      }

      if($firstdate_filter != '') {
          $s_date_box = array_map('trim', explode('-', $seconddate));

          if(count($s_date_box) == 3) {
              if(checkdate($s_date_box[0], $s_date_box[1], $s_date_box[2])) {
                  $seconddate_filter = $s_date_box[0].'/'.$s_date_box[1];
              }
          }
      }
  }

  if($firstdate_filter != '' && $seconddate_filter != '') {
      $dated = $firstdate_filter .' - '.$seconddate_filter;
  }


  $camping_services = $Camping['services'];
  $camping_services = explode(',', $camping_services);
  $camping_images = $Camping['images'];
  $camping_images = explode(',', $camping_images);
  $camping_images = array_values(array_filter($camping_images));
  $guests_array = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31", "32", "33", "34", "35", "36", "37", "38", "39", "40", "41", "42", "43", "44", "45", "46", "47", "48", "49", "50", "51", "52", "53", "54", "55", "56", "57", "58", "59", "60", "61", "62", "63", "64", "65", "66", "67", "68", "69", "70", "71", "72", "73", "74", "75", "76", "77", "78", "79", "80", "81", "82", "83", "84", "85", "86", "87", "88", "89", "90", "91", "92", "93", "94", "95", "96", "97", "98", "99", "100");
  $currency_array = array("USD", "EUR", "YEN", "CAD", "AUE");

  if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') { ?>
  <div class="modal_content_container">
    <div class="modal_content_child modal-content">
       <div class="popup-title ">
          <button class="hidden_close_span close_span waves-effect">
          <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
          </button>         
          <h3>Edit camp with locals detail</h3>
          <span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
          <a type="button" class="item_done crop_done hidden_close_span custom_close waves-effect" href="javascript:void(0)" onclick="editcampingsave('<?=$camping_id?>', this)">Done</a>
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
                                              <input type="text" placeholder="Experience title: camping under the moon" class="fullwidth locinput " id="editcamping_title" value="<?=$camping_title?>" />
                                           </div>
                                        </div>
                                     </div>
                                  </div>
                                  <div class="fulldiv">
                                     <div class="row">
                                        <div class="col s6">
                                           <div class="frow pr-5 dropdown782">
                                              <div class="caption-holder">
                                                 <label>Min guests</label>
                                              </div>
                                              <a class="dropdown_text dropdown-button-left" href="javascript:void(0)" data-activates="campMinGuest" id="editcamping_minguests">
                                                 <span><?=$camping_min_guests?></span>
                                                 <i class="zmdi zmdi-caret-down"></i>
                                              </a>
                                              <ul id="campMinGuest" class="dropdown-privacy dropdown-content custom_dropdown select-dropdown guest-ddl">
                                                <?php foreach ($guests_array as $guests_array_s) { ?>
                                                <li>
                                                  <a href="javascript:void(0)"><?=$guests_array_s?></a>
                                                </li>
                                                <?php } ?>
                                              </ul>
                                           </div>
                                        </div>
                                        <div class="col s6">
                                           <div class="frow pl-5 dropdown782">
                                              <div class="caption-holder">
                                                 <label>Max guests</label>
                                              </div>
                                              <a class="dropdown_text dropdown-button-left" href="javascript:void(0)" data-activates="campMaxGuest" id="editcamping_maxguests">
                                                 <span><?=$camping_max_guests?></span>
                                                 <i class="zmdi zmdi-caret-down"></i>
                                              </a>
                                              <ul id="campMaxGuest" class="dropdown-privacy dropdown-content custom_dropdown select-dropdown guest-ddl">
                                                <?php foreach ($guests_array as $guests_array_s) { ?>
                                                <li>
                                                  <a href="javascript:void(0)"><?=$guests_array_s?></a>
                                                </li>
                                                <?php } ?>
                                              </ul>
                                           </div>
                                        </div>
                                     </div>
                                  </div>
                                  <div class="fulldiv">
                                     <div class="frow">
                                        <div class="caption-holder">
                                           <div class="row">
                                              <div class="col s4">
                                                 <label>Rate per adult guest</label>
                                              </div>
                                              <div class="col s6">
                                                 <div class="detail-holder">
                                                    <div class="input-field">
                                                       <input type="text" placeholder="20" class="fullwidth input-rate" id="editcamping_rate" value="<?=$camping_rate?>" />
                                                    </div>
                                                 </div>
                                              </div>
                                              <div class="col s2">
                                                <a class="dropdown_text dropdown-button currency_drp" href="javascript:void(0)" data-activates="chooseCurrency" id="editcamping_currency">
                                                   <span class="currency_label"><?=$camping_currency?></span>
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
                                           <label>Describe your camp</label>
                                        </div>
                                        <div class="detail-holder">
                                           <div class="input-field">
                                              <textarea placeholder="Tell people about your camp" class="fullwidth locinput " id="editcamping_description"><?=$camping_description?></textarea>
                                           </div>
                                        </div>
                                     </div>
                                  </div>
                                  <div class="row">
                                     <div class="col s6">
                                        <div class="fulldiv mobile275 pr-5">
                                           <div class="frow">
                                              <div class="caption-holder">
                                                 <label>Camp Location</label>
                                              </div>
                                              <div class="detail-holder">
                                                 <div class="input-field">
                                                    <input type="text" placeholder="Enter city name" class="fullwidth locinput" data-query="all" onfocus="filderMapLocationModal(this)" id="editcamping_location" value="<?=$camping_location?>"/>
                                                 </div>
                                              </div>
                                           </div>
                                        </div>
                                     </div>
                                     <div class="col s6">
                                        <div class="fulldiv mobile275 pl-5">
                                           <div class="frow">
                                              <div class="caption-holder">
                                                 <label>Telephone</label>
                                              </div>
                                              <div class="detail-holder">
                                                 <div class="input-field">
                                                    <input type="text" placeholder="Enter telephone number" class="fullwidth locinput " id="editcamping_telephone" value="<?=$camping_telephone?>"/>
                                                 </div>
                                              </div>
                                           </div>
                                        </div>
                                     </div>
                                  </div>
                                  <div class="row">
                                     <div class="col s6">
                                        <div class="fulldiv mobile275 pr-5">
                                           <div class="frow">
                                              <div class="caption-holder">
                                                 <label>E-mail</label>
                                              </div>
                                              <div class="detail-holder">
                                                 <div class="input-field">
                                                    <input type="text" placeholder="Enter email address" class="fullwidth locinput " id="editcamping_email" value="<?=$camping_email?>"/>
                                                 </div>
                                              </div>
                                           </div>
                                        </div>
                                     </div>
                                     <div class="col s6">
                                        <div class="fulldiv mobile275 pl-5">
                                           <div class="frow">
                                              <div class="caption-holder">
                                                 <label>Website</label>
                                              </div>
                                              <div class="detail-holder">
                                                 <div class="input-field">
                                                    <input type="text" placeholder="Enter website url" class="fullwidth locinput " id="editcamping_website" value="<?=$camping_website?>"/>
                                                 </div>
                                              </div>
                                           </div>
                                        </div>
                                     </div>
                                  </div>
                                  <div class="fulldiv">
                                     <div class="frow">
                                        <div class="caption-holder">
                                           <label>Opening period</label>
                                        </div>
                                        <div class="detail-holder">
                                           <div class="input-field">
                                             <input type="text" placeholder="28/5 - 15/9" class="fullwidth locinput " id="editcamping_period" value="<?=$dated?>" name="datetimes"/>
                                           </div>
                                        </div>
                                     </div>
                                  </div>
                                  <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder mb-10">
                                         <label>Services</label>
                                      </div>
                                      <div class="detail-holder" id="editcamping_services">
                                         <?php if(in_array('spa', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image active-class">
                                         <?php } else { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                         <?php } ?>
                                            <div class="image-select"></div>
                                            <img alt="spa" title="spa" src="<?=$baseUrl?>/images/amenity-spa.png">
                                         </a>

                                         <?php if(in_array('beach', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image active-class">
                                         <?php } else { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                         <?php } ?>
                                            <div class="image-select"></div>
                                            <img alt="beach" title="beach" src="<?=$baseUrl?>/images/amenity-beach.png">
                                         </a>

                                         <?php if(in_array('wifi', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image active-class">
                                         <?php } else { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                         <?php } ?>
                                            <div class="image-select"></div>
                                            <img alt="wifi" title="wifi" src="<?=$baseUrl?>/images/amenity-wifi.png">
                                         </a>

                                         <?php if(in_array('breakfast', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image active-class">
                                         <?php } else { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                         <?php } ?>
                                            <div class="image-select"></div>
                                            <img alt="breakfast" title="breakfast" src="<?=$baseUrl?>/images/amenity-breakfast.png">
                                         </a>

                                         <?php if(in_array('pool', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image active-class">
                                         <?php } else { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                         <?php } ?>
                                            <div class="image-select"></div>
                                            <img alt="pool" title="pool" src="<?=$baseUrl?>/images/amenity-pool.png">
                                         </a>
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
                                                foreach ($camping_images as $images_s){
                                                  ?>
                                                  <div class="img-box"><img src="<?=$images_s?>" class="upldimg thumb-image vimg"><div class="loader" style="display: none;"></div><a href="javascript:void(0)" onclick="removepiccamping_modal('<?=$camping_id?>', this)" class="removePhotoFilelocalguide"><i class="mdi mdi-close"></i></a></div>

                                                  <?php
                                                }
                                                if(count($camping_images) <3) { 
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
                                     <p class="photolabelinfo">Please add three cover photos for your camp profile</p>
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
    <a href="javascript:void(0)" class="btngen-center-align waves-effect"  onclick="editcampingsave('<?=$camping_id?>', this)">Publish</a>
  </div>
<?php } 
}
exit; ?> 
