<?php
use frontend\assets\AppAsset; 
use yii\widgets\ActiveForm;
use frontend\models\LoginForm;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$user_id = (string)$session->get('user_id');
$email = $session->get('email');
?>
<link href="<?=$baseUrl?>/css/custom-croppie.css" rel="stylesheet">
<div class="popup-title">          
	<a class="close_span waves-effect" href="javascript:void(0)">
		<i class="mdi mdi-close mdi-20px"></i>
	</a>
</div>
<div class="modal-content">
	<div class="home-content">
		<div class="login-part homel-part">                     
			<div class="homebox login-box">
				<div class="sociallink-area">
					<a id="FacebookBtn" href="javascript:void(0)" class="fb-btn">
						<span><i class="mdi mdi-facebook"></i></span>Connect with Facebook
					</a>
				</div>
				<div class="home-divider">
					<span class="div-or">or</span>
				</div>
				
				<input type="hidden" name="_csrf">
				<input type="hidden" value="" id="lat" name="lat">
				<input type="hidden" value="" id="long" name="long">
				<input type="hidden" value="1" name="login">
				<div class="box-content">
					<div class="bc-row">
						<!--<div class="bc-component">-->
						<div class="sliding-middle-out anim-area underlined">
							<input type="text" placeholder="Email Address" onchange="validate_lemail()" onkeyup="validate_lemail()" id="lemail" name="LoginForm[email]" max="75">
						</div>
						<div style="display: none" class="frm-validicon" id="leml-success"><img src="<?=$baseUrl?>/images/frm-check.png"></div>
						<div style="display: none" class="frm-validicon" id="leml-fail"><img src="<?=$baseUrl?>/images/frm-cross.png"></div>
						<!--</div>-->
					</div>
					<div class="bc-row">
						<div class="sliding-middle-out anim-area underlined">
							<input type="password" placeholder="Password" onchange="validate_lpassword()" onkeyup="validate_lpassword()" id="lpassword" name="LoginForm[password]" max="30">
						</div>
						<div style="display: none" class="frm-validicon" id="lpwd-success"><img src="<?=$baseUrl?>/images/frm-check.png"></div>
						<div style="display: none" class="frm-validicon" id="lpwd-fail"><img src="<?=$baseUrl?>/images/frm-cross.png"></div>
					</div>
					<div class="clear"></div>
				</div>
				<div class="nextholder">
					<a class="bebebe" href="javascript:void(0)" onclick="setForgotPassStep();flipSectionTo('forgot');">Forgot Password?</a>
					<a href="javascript:void(0)" class="homebtn" onclick="tbLogin()">Log in</a>
				</div>
				<div class="btn-holder">
					<p><span class="bebebe">Do not have an account?</span>
						<a onclick="flipSectionTo('signup');" href="javascript:void(0)">Sign Up</a>
					</p>
				</div>
			</div>
		</div>
		<div class="signup-part homes-part">
			<input type="hidden" name="user_email" id="user_email" value="" />
			<?php $form = ActiveForm::begin(['id' => 'frm','options'=>['onsubmit'=>'return false;','enableAjaxValidation' => true,],]); ?>
				<div class="homebox signup-box" id="create-account">
					<div class="box-content">
						<h5>Create Account</h5>
						<div class="bc-row">
							<div class="bc-component">
								<div class="sliding-middle-out anim-area underlined">
									<input type="text" id="fname" name="LoginForm[fname]" placeholder="First Name" onkeyup="validate_fname()" max="50" class="capitalize">
								</div>
								<div id='fnm-success' class="frm-validicon" style='display: none'><img src="<?=$baseUrl?>/images/frm-check.png"/></div>
								 <div id='fnm-fail' class="frm-validicon" style='display: none'><img src="<?=$baseUrl?>/images/frm-cross.png"/></div>
							</div>
						</div>
						<div class="bc-row">
							<div class="bc-component">
								<div class="sliding-middle-out anim-area underlined">
									<input type="text" id="lname" name="LoginForm[lname]" placeholder="Last Name" onkeyup="validate_lname()" max="50" class="capitalize">
								</div>
								<div id='lnm-success' class="frm-validicon" style='display: none'><img src="<?=$baseUrl?>/images/frm-check.png"/></div>
								 <div id='lnm-fail' class="frm-validicon" style='display: none'><img src="<?=$baseUrl?>/images/frm-cross.png"/></div>
							</div>
						</div>
						<div class="bc-row">
							<div class="bc-component">
								<div class="sliding-middle-out anim-area underlined">
									<input type="text" id="email" name="LoginForm[email]" value="" placeholder="Email" onkeyup="validate_email()" onchange="validate_email()" max="75">
								</div>
								<div id='eml-success' class="frm-validicon" style='display: none'><img src="<?=$baseUrl?>/images/frm-check.png"/></div>
								 <div id='eml-fail' class="frm-validicon" style='display: none'><img src="<?=$baseUrl?>/images/frm-cross.png"/></div>
							</div>
						</div>
						<div class="bc-row">
							<div class="bc-component">
								<div class="sliding-middle-out anim-area underlined">
									<input type="password" id="signup_password" name="LoginForm[password]" placeholder="Password" onkeyup="validate_spassword()" max="30">
								</div>
								<div id='spwd-success' class="frm-validicon" style='display: none'><img src="<?=$baseUrl?>/images/frm-check.png"/></div>
								 <div id='spwd-fail' class="frm-validicon" style='display: none'><img src="<?=$baseUrl?>/images/frm-cross.png"/></div>
							</div>
						</div>
					</div>
					<div class="nextholder">
						<a href="javascript:void(0)" class="homebtn su-nextbtn" data-class="profile-setting" onclick="tbSignupNavigation(this)">Next</a>
					</div>
					<div class="btn-holder">
						<p>Have an account?
							<a onclick="flipSectionTo('login');" href="javascript:void(0)">Login</a>
						</p>
					</div>
				</div>
			<?php ActiveForm::end() ?>
			<?php $form = ActiveForm::begin(['id' => 'frm2','options'=>['onsubmit'=>'return false;','enableAjaxValidation' => true,],]); ?>
				<div class="homebox signup-box profile-setting-new" id="profile-setting">
					<div class="box-content">
						<h5>Profile Setting</h5>
						<div class="bc-row">
							<div class="bc-component">
								<div class="sliding-middle-out anim-area underlined">
									<input type="text" id="autocomplete" data-query="none" onfocus="filderMapLocationModal(this)" class="" autocomplete="off" name="LoginForm[city]" placeholder="City"/>
								</div>
							</div>
						</div>
						<div class="bc-row">
							<div class="bc-component">
								<div class="sliding-middle-out anim-area underlined">
									<input type="text" id="country" name="LoginForm[country]" readonly="true" placeholder="Country">
								</div>
							</div>
						</div> 
							<input type="hidden" readonly="true" name="isd_code" id="isd_code"/>
							<input type="hidden" id="country_code" name="country_code" />
						<div class="bc-row">
							<div class="bc-component">
								<div class="sliding-middle-out anim-area underlined">
									<select name="gender" id="gender" class="select2">
										<option value="" disabled selected>Gender</option>
										<option value="Male">Male</option>
										<option value="Female">Female</option>
									</select>
								</div>
							</div>
						</div>      
						<div class="bc-row">
							<div class="bc-component">
								<div class="sliding-middle-out anim-area underlined">
									<input type="text" onkeydown="return false;" placeholder="Birthdate" name="LoginForm[birth_date]" data-toggle="datepicker" data-query="M" class="datepickerinput" id="datepicker" readonly>
									<input type="hidden" name="birth_access" value="Private" />
								</div>
							</div>
						</div>                                      
					</div>
					<div class="nextholder">
						<a href="javascript:void(0)" class="homebtn su-nextbtn" data-class="upload-photo" onclick="tbSignupNavigation(this)">Next</a>
					</div>
					<div class="btn-holder">
						<p>Have an account?
							<a onclick="flipSectionTo('login');" href="javascript:void(0)">Login</a>
						</p>
					</div>
				</div>
			<?php ActiveForm::end() ?>
				<div class="homebox signup-box upload-photo" id="upload-photo">
					<div class="box-content">
						<div class="upload-header"></div>
						<div class="home-cropper">
							<div class="cropper cropper-wrapper">
								<div class="image-upload">
									<label for="file-input">
										<i class="zmdi zmdi-camera-bw"></i>
									</label>
									
									<input id="file-input" type="file" class="js-cropper-upload" value="Select" onclick="$('.js-cropper-result').hide();$('.crop').show();$('.image-upload').hide();$('.cropper-nae').hide();$('.text-hide-show').hide();"/>
								</div>
								<div class="js-cropper-result">
									<img src="<?=$baseUrl?>/images/demo-profile.jpg">
								</div> 
								<div class="crop dis-none"> <div class="grag-title">Drag to crop</div>
									<div class="js-cropping"></div> 
									<i class="js-cropper-result--btn zmdi zmdi-check upload-btn" onclick="$('.cropper-nae').show();"></i>
									<i class="js-cropper-result--btn zmdi zmdi-check upload-btn" onclick="$('.cropper-nae').show();$('.text-hide-show').show();"></i>
									<i class="mdi mdi-close	 img-cancel-btn" onclick="imageCancelUpload();"></i>

								</div> 
								<h1 class="cropper-nae" id="uname"></h1>
							</div> 
							<p class="note red-danger text-hide-show">
								Your photo needs to be 200x200 with gif or jpeg format
							</p>
						</div>
					</div>             
					<div class="nextholder">
						<a href="javascript:void(0)" class="homebtn su-skipbtn left" data-class="security-check" onclick="tbSignupNavigation(this)">Skip</a>
						<a href="javascript:void(0)" class="homebtn su-nextbtn" data-class="security-check" onclick="cropTriggerClk()">Next</a>
					</div>              
					<div class="btn-holder">
						<p>Have an account?
							<a onclick="flipSectionTo('login');" href="javascript:void(0)">Login</a>
						</p>
					</div>
				</div>                  
				<div class="homebox signup-box" id="security-check">
					<div class="box-content">
						<h5>Security Check</h5>
						<div class="security-box">
							<p>To guard against automated systems and robots, please checked the box below</p>
							<div class="bc-row" id="recaptchacodebox">
								<div class="bc-component">
									<center>
		                                <div class="g-recaptcha" id="g-recaptcha" data-callback="recaptchaCallback" data-sitekey="6LdZs1sUAAAAAKtNHR72Wb__55sQXghN-AKs_Qct" disabled="disabled"></div>
		                             </center>
								</div>
							</div>

							<input type="checkbox" class="robot-chk agree-chk" id="filled-in-box2"/>
							<label for="filled-in-box2" class="home-f-n">Agreed to the terms and conditions</label>
						</div>
					</div>
					<div class="nextholder">
						<a href="javascript:void(0)" class="homebtn su-nextbtn" data-class="confirm-email" onclick="tbSignupNavigation(this),verifycount()">Next</a>
					</div>
					<div class="btn-holder">
						<p>Have an account?
							<a onclick="flipSectionTo('login');" href="javascript:void(0)">Login</a>
						</p>
					</div>
				</div>
				<div class="homebox signup-box" id="confirm-email">
					<div class="box-content">
						<h5>You’re almost done, just confirm your email</h5>
						<div class="text-center fullwidth">
							<img src="<?=$baseUrl?>/images/confirm-msg.png" class="confirm-img"/>                                 
						</div>
						<h6 class="white-text">Please confirm your email to have full access to your account</h6>
						<div class="nextholder" id="confirmlink">
						</div>                      
					</div>      
				</div>
		</div>
		<div class="forgot-part homes-part">
			<div class="homebox forgot-box" id="fp-step-1">                             
				<div class="box-content">
					<div class="fphome-notice">
						<span class="success-note"></span>
						<span class="error-note"></span>
						<span class="info-note"></span>
					</div>
					<h5>Change Your Password</h5>
					<div class="fp-box">
							<p class="text-center"> Let's find your account</p>
							<div class="bc-row mt25">
								<div class="sliding-middle-out anim-area underlined">
									<input placeholder="Email address or alternate email" type='text' id="forgotemail" name="forgotemail" onkeyup="validateforgotemail()">
									<div style="display: none" class="frm-validicon" id="fpeml-success"><img src="<?=$baseUrl?>/images/frm-check.png"></div>
									<div style="display: none" class="frm-validicon" id="fpeml-fail"><img src="<?=$baseUrl?>/images/frm-cross.png"></div>
								</div>
						</div>
					</div>
				</div>
				<div class="nextholder">
					<a href="javascript:void(0)" class="homebtn" data-class="fp-step-2" onclick="forgotPassNavigation(this)">Next</a>
				</div>
			</div>
			<div class="homebox forgot-box" id="fp-step-2">                             
				<div class="box-content">
					<h5>We've sent a link to change your password</h5>
					<div class="fp-box">
						<p>check your email and follow the link to quickly reset your password</p>
					</div>
					<div class="nextholder" id="displayresetlink">
					</div>
				</div>
			</div>
			<div class="homebox forgot-box" id="fp-step-3">                             
			<?php
			if(isset($_GET['enc']) && !empty($_GET['enc']))
			{
				$enc = $_GET['enc'];
				$trav_id = $enc;
				$travid =  base64_decode(strrev($trav_id));
				$postresult = LoginForm::find()->where(['_id' => $travid])->one();
				if($postresult)
				{
				?>
				<div class="box-content">
					<div class="fphome-notice">
						<span class="success-note"></span>
						<span class="error-note"></span>
						<span class="info-note"></span>
					</div>
					<input type="hidden" name="travid" id="travid" value="<?= $travid?>">
					<h5>Choose New Password</h5>
					<div class="bc-row">                                        
						<div class="bc-component">
						<div class="sliding-middle-out anim-area underlined">
						<input type="password" id="fppassword" placeholder="Type your new password" onkeyup="tbFp()">
						<div style="display: none" class="frm-validicon" id="pwd-success"><img src="<?=$baseUrl?>/images/frm-check.png"></div>
						<div style="display: none" class="frm-validicon" id="pwd-fail"><img src="<?=$baseUrl?>/images/frm-cross.png"></div>
						</div>
						</div>
					</div>
					<div class="bc-row">                                        
						<div class="bc-component">
						<div class="sliding-middle-out anim-area underlined">
						<input type="password" id="fppasswordcon" placeholder="Confirm your new password" onkeyup="tbConFp()">
						<div style="display: none" class="frm-validicon" id="conpwd-success"><img src="<?=$baseUrl?>/images/frm-check.png"></div>
						<div style="display: none" class="frm-validicon" id="conpwd-fail"><img src="<?=$baseUrl?>/images/frm-cross.png"></div>
						</div>
						</div>
					</div>
					<p class="note">Passwords are case sensitive, must be at least 6 characters</p>
					<a href="javascript:void(0)" class="homebtn" data-class="fp-step-4" onclick="forgotPassNavigation(this)">Continue</a>
				</div>
				<?php } else { ?>
					<div class="box-content">
						<h5>No user found !!!</h5>
						<h5>Please paste correct url from your email !!!</h5>
					</div>
				<?php } } else { ?>
				<div class="box-content">
						<h5>Please paste correct url from your email !!!</h5>
					</div>
				<?php }?>
			</div>
			<div class="homebox forgot-box" id="fp-step-4">                             
				<div class="box-content">
					<h5>Your password has been reset</h5>
					<div class="fp-box">
						<p class="text-center">Now you can login with your new password!</p>
					</div>
					<a href="javascript:void(0)" onclick="flipSectionTo('login');" class="homebtn">Log in</a>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="<?=$baseUrl?>/js/loginsignup.js"></script>
<?php 
exit;