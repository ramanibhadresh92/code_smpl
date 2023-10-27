<?php
use frontend\assets\AppAsset;
use yii\widgets\ActiveForm;
use yii\mongodb\ActiveRecord;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$user_id = (string)$session->get('user_id');
$thumb = $this->context->getimage($user_id,'thumb');
$fullname = $this->context->getuserdata($user_id,'fullname');

$city = isset($data['city']) ? $data['city'] : '';
$country = isset($data['country']) ? $data['country'] : '';
$gender = isset($data['gender']) ? $data['gender'] : '';
$birth_date = isset($data['birth_date']) ? $data['birth_date'] : '';
if($birth_date != '') {
   $birth_date = date('d F, Y', strtotime($birth_date));
}
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>	
<div class="page-wrapper  completeprof-wrapper complete-profile">
	<div class="header-section">
		<?php include('../views/layouts/header.php'); ?>
	</div>
	<!-- <a href="javascript:void(0)" class="btn btn-custom text-center Complete_Pro">Complete your profile</a> -->
	<!-- <a href="javascript:void(0)" class="btn btn-custom text-center Complete_loged">loged</a> -->
<div id="Complete_Pro" class="modal tbpost_modal custom_modal split-page complete-popup open">
   <div class="modal_content_container">
      <div class="modal_content_child modal-content">
         <a type="button" class="item_done crop_done waves-effect hidden_close_span custom_close" href="javascript:void(0)"></a>
         <div class="custom_modal_content modal_content" id="createpopup">
            <div class="comp_popup profile-tab">
               <div class="comp_popup_box detail-box">
                  <div class="content-holder main-holder">
                     <div class="summery">
                        <div class="dsection bborder expandable-holder expanded">
                           <div class="form-area expandable-area">
                              <form class="hangoutevent-form">
                                 <div class="combined-column wide-open">
                                    <div class="complete-profile-page">
                                       <div class="complete-content">
                                          <div class="signup-part">
                                          	 <div class="signup-box " id="create-account">
                                                <div class="complete-profile-header">
                                                   <h5 class="m-0">Welcome <?=$fullname?></h5>
                                                   <p><i>Let's complete your profile</i></p>
                                                   <img src="<?=$thumb?>" class="center-block circle" alt="img">
                                                </div>
                                                <div class="box-content">
                                                   <div class="bc-row">
                                                      <div class="bc-component">
                                                         <div class="sliding-middle-out anim-area underlined">
                                                         	<input data-query="M" id="autocomplete" class="validate"  name="LoginForm[city]" onfocus="filderMapLocationModal(this)" autocomplete="off" placeholder="City" type="text" value="<?=$city?>">
                                                         </div>
                                                      </div>
                                                   </div>
                                                   <div class="bc-row">
                                                      <div class="bc-component">
                                                         <div class="sliding-middle-out anim-area underlined">
                                                         	<input type="text" id="country" name="LoginForm[country]" placeholder="Country" value="<?=$country?>">
                                                         </div>
                                                      </div>
                                                   </div>
                                                   <div class="bc-row">
                                                      <div class="bc-component">
                                                         <div class="sliding-middle-out anim-area underlined">
                                                         	<select name="gender" id="gender" class="select2 genderDrop" >
               																<option value="">Gender</option>
               																<option value="Male" <?=($gender == 'Male') ? 'selected' : '';?>>Male</option>
               																<option value="Female" <?=($gender == 'Female') ? 'selected' : '';?>>Female</option>
               															</select>
                                                         </div>
                                                      </div>
                                                   </div> 
                                                   <div class="bc-row">
                                                      <div class="bc-component">
                                                         <div class="sliding-middle-out anim-area underlined">
                                                         	<input type="text" onkeydown="return false;" placeholder="Birthdate" name="LoginForm[birth_date]" data-query="M" class="form-control datepickerinput" data-toggle="datepicker" id="datepicker" value="<?=$birth_date?>" readonly>
															               <input type="hidden" name="birth_access" value="Private" />
                                                         </div>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="nextholder center">
                                                   <a href="javascript:void(0)" class="profilebtn waves-effect" onclick="save_city()">Continue</a>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
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
</div>

	<?php include('../views/layouts/footer.php'); ?>	
</div>	

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>	
<script type="text/javascript" src="<?=$baseUrl?>/js/complete-profile.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/loginsignup.js"></script>
<script type="text/javascript">
   $(document).ready(function() {
      $('#Complete_Pro').modal('open');
   })
</script>
<?php $this->endBody() ?> 

