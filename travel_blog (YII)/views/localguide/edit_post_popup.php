<?php   
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use frontend\assets\AppAsset;
use backend\models\LocalguideActivity;
use frontend\models\Language;
use frontend\models\LocalguidePost;

$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');

$baseUrl = AppAsset::register($this)->baseUrl;
$languages = Language::languages();
//$information = LocalguideActivity::getallactivity();
//$information = json_decode($information, true);
$information = array("Site Seeing", "Hiking", "Hot Air Balloon", "Photography", "Zoo","Amuesement Park", "Business Events","Museums","Hanging Out", "Golf", "Skiing", "Snowboarding", "Giving Tours", "Beach", "Dinner", "Movie", "Outdoors", "Biking" ,"Picnic","Shopping","Coffee House","Introduce you to people");
$post = LocalguidePost::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();

if(isset($post) && !empty($post)) {
	$id = (string)$post['_id'];
	$activity = isset($post['activity']) ? $post['activity'] : '';
	$activity = explode(",", $activity); 
	//$title = $post['title'];
	$description = $post['description'];
  $credentials = $post['credentials'];
	$restriction = $post['restriction'];
	$language = $post['language'];
	$language = explode(',', $language);
	$guideFee = $post['guideFee'];
	$images = $post['images'];
	$images = explode(',', $images);
	$licensed = isset($post['licensed']) ? $post['licensed'] : 'no';
	$staticFeesArray = array('' => 'Negotiable', '5' => '$5 per hour', '10' => '$10 per hour', '15' => '$15 per hour', '20' => '$20 per hour', '25' => '$25 per hour', '30' => '$30 per hour', '35' => '$35 per hour');
?>
<div class="modal_content_container">
  <div class="modal_content_child modal-content">
     <div class="popup-title ">
        <button class="hidden_close_span close_span waves-effect">
        <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
        </button>         
        <h3>Edit guide profile</h3>
        <span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
        <a type="button" class="item_done crop_done hidden_close_span custom_close waves-effect" href="javascript:void(0)" onclick="editpostsave('<?=$id?>')">Done</a>
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
                                                  <select data-fill="n" data-action="hireguideevent" data-selectore="localguideeventname" id="localguide_ed_activity" class="eventname localguideeventname guide-ddl" multiple>
                                                     <?php 
													if(isset($information) && !empty($information)) {
														foreach ($information as $inform) {
															$cls = '';
															if(in_array($inform, $activity)) {
																$cls = 'selected';
															}
															?>
															<option value="<?=$inform?>" <?=$cls?>><?=$inform?></option>
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
                                      <div class="caption-holder  mb-5">
                                         <label>Are you licensed guide</label>
                                      </div>
                                      <div class="detail-holder">
                                         <div class="detail-holder inline-radio">
                                         	<?php if($licensed == 'yes') { ?>
                                            <input name="localguide_ed_licensed" checked="" type="radio" id="yes1" value="yes">
                                            <label for="yes1">Yes</label>
                                            <input name="localguide_ed_licensed" type="radio" id="no1" value="no">
                                            <label for="no1">No</label>
                                        	<?php } else { ?>
                                        	<input name="localguide_ed_licensed" type="radio" id="yes1" value="yes">
                                            <label for="yes1">Yes</label>
                                            <input name="localguide_ed_licensed" checked="" type="radio" id="no1" value="no">
                                            <label for="no1">No</label>
                                        	<?php } ?>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Description</label>
                                      </div>
                                      <div class="detail-holder">
                                         <div class="input-field">
                                            <input type="text" placeholder="Write description." id="localguide_ed_description" value="<?=$description?>"/>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Credentials</label>
                                      </div>
                                      <div class="detail-holder">
                                         <div class="input-field">
                                            <input type="text" placeholder="Tell people your credentials" id="localguide_ed_credentials" value="<?=$credentials?>"/>
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
                                            <input type="text" placeholder="List any restriction that you may have" id="localguide_ed_restriction" value="<?=$restriction?>" />
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="row">
                                      <div class="col s6">
                                         <div class="frow">
                                            <div class="caption-holder">
                                               <label>Language spoken</label>
                                            </div>
                                            <div class="detail-holder">
                                               <div class="input-field dropdown782">
                                                  <select id="localguide_ed_language" class="languagedrp" data-selectore="languagedrp" data-fill="n" data-action="language" multiple>
                                                    <option value="" disabled selected>Choose language</option>
                                                    <?php
                                                    foreach ($languages as $languages_s) {
                                                     	$name = $languages_s['name'];
                                                     	echo "<option value=".$name.">$name</option>";
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
                                                     <select id="localguide_ed_guideFee" class="feedrp" data-selectore="feedrp" data-fill="n" data-action="fee">
                                                        <?php
                                                           $fee = array("$35 per day", "$40 per day", "$45 per day", "$50 per day", "$55 per day", "$60 per day", "$65 per day", "$70 per day", "$75 per day", "$80 per day", "$90 per day", "$100 per day");
                                                           foreach ($fee as $s8032n) {
                                                              if($guideFee == $s8032n) {
                                                              echo "<option value=".$s8032n." selected>$s8032n</option>";
                                                              } else {
                                                              echo "<option value=".$s8032n.">$s8032n</option>";  
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
                                </div>
                                <div class="frow nomargin new-post">
                                   <div class="caption-holder">
                                      <label>Awesome photos help guests want to hire you</label>
                                   </div>
                                   <div class="detail-holder">
                                      <div class="input-field ">
                                         <div class="post-photos new_pic_add">
                                            <div class="img-row">
												<?php
	                                            foreach ($images as $images_s){
	                                                $unid = uniqid();
	                                                $rand = rand(999, 99999);
	                                                $uniqid = $unid.'_'.$rand;
	                                                ?>
	                                                <div class="img-box"><img src="<?=$images_s?>" class="upldimg thumb-image vimg"><div class="loader" style="display: none;"></div><a href="javascript:void(0)" onclick="removepiclocalguide_modal('<?=$id?>', this)" class="removePhotoFilelocalguide"><i class="mdi mdi-close"></i></a></div>

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
  <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="editpostsave('<?=$id?>')">Publish</a>
</div>
<?php }  
exit;