<?php   
use frontend\assets\AppAsset;
use backend\models\LocaldriverActivity;
$baseUrl = AppAsset::register($this)->baseUrl;
 
//$information = LocaldriverActivity::getallactivity();
//$information = json_decode($information, true);
$information = array("Touring", "Site Seeing", "Parks", "Museum", "Beaches", "Showing the city", "Outdoor event");

$rates = array("$35" => "$35 per day", "$40" => "$40 per day", "$45" => "$45 per day", "$50" => "$50 per day", "$55" => "$55 per day", "$60" => "$60 per day", "$65" => "$65 per day", "$70" => "$70 per day", "$75" => "$75 per day", "$80" => "$80 per day", "$90" => "$90 per day", "$100" => "$100 per day");
?>
<?php if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') { ?>
<div class="modal_content_container">
  <div class="modal_content_child modal-content">
     <div class="popup-title ">
        <button class="hidden_close_span close_span waves-effect">
        <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
        </button>         
        <h3>Create driver profile</h3>
        <span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
        <a type="button" class="item_done crop_done hidden_close_span custom_close waves-effect" href="javascript:void(0)" onclick="actioncreatepost()">Done</a>
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
                                         <div class="input-field">
                                            <input type="text" id="localdriver_vehicletype" placeholder="i.e Toyata van with air condition" class="fullwidth locinput "/>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>On-board</label>
                                      </div>
                                      <div class="detail-holder">
                                         <div class="input-field">
                                            <input type="text" id="localdriver_onboard" placeholder="i.e fresh water bottles, cooler, charger..." class="fullwidth locinput "/>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Vehicle capacity</label>
                                      </div>
                                      <div class="detail-holder">
                                         <div class="input-field">
                                            <input type="text" id="localdriver_vehiclecapacity" placeholder="From 1 to 6 people i.e six travellers "/>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Restriction</label>
                                      </div>
                                      <div class="detail-holder">
                                         <div class="input-field">
                                            <input type="text" id="localdriver_restriction" placeholder="i.e things that you can not do"/>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Meet your driver</label>
                                      </div>
                                      <div class="detail-holder">
                                         <div class="input-field">
                                            <input type="text" id="localdriver_describeyourtalent" placeholder="Tell people about your talent"/>
                                         </div>
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
                                               <p class="firs-show mt-5 mb0">
                                                  <input type="checkbox" id="check_all_everything" class="check-all" onChange="javascript:checkedEverything(this)"/>
                                                   <label for="check_all_everything">I&apos;m up for everything</label>
                                               </p>
                                               <div class="input-field input-field1 dropdown782">
                                                  <select data-fill="n" data-action="hireguideevent" data-selectore="hireguideeventname" id="localdriver_activity" class="eventname hireguideeventname localdrivereventname" multiple> 
                                                    <?php 
                                                    if(isset($information) && !empty($information)) {
                                                      foreach ($information as $inform) { ?>
                                                        <option><?=$inform?></option>
                                                        <?php     
                                                      }
                                                    }
                                                    ?>
                                                  </select>
                                               </div>
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
                                               <div class="detail-holder">
                                                  <div class="input-field dropdown782">
                                                     <select id="localdriver_rate" class="feedrp" data-selectore="feedrp" data-fill="n" data-action="fee">
                                                        <option value="" disabled selected>Choose Fee</option>
                                                        <?php
                                                           foreach ($rates as $key => $s8032n) {
                                                              echo "<option value=".$key.">$s8032n</option>";
                                                           }
                                                         ?>
                                                     </select>
                                                  </div>
                                               </div>
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
  <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="actioncreatepost()">Publish</a>
</div>
<?php } ?> 
<?php exit; ?> 
