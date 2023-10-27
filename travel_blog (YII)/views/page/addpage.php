<?php 
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use frontend\models\BusinessCategory;
use frontend\models\CountryCode;
?>
<div class="vertical-tabs tabs-box">									
	<div class="tabs-list">
		<ul class="tabs">									
			<li class="active tab"><a href="#pagescreate-basicinfo" data-toggle="tab" aria-expanded="false">Provide basic information</a></li>
			<li class="tab"><a href="#pagescreate-uploadphoto" data-toggle="tab" aria-expanded="false">Upload photos</a></li>
			<li class="tab"><a href="#pagescreate-contact" data-toggle="tab" aria-expanded="false">Contact information</a></li>
			<li class="tab"><a href="#pagescreate-verification" data-toggle="tab" aria-expanded="true">Owner verification</a></li>
		</ul>
	</div>
	<input type="hidden" id="pageid" value="">
	<div class="tabs-detail">
		<div class="tab-content">
			<div class="tab-pane fade in active" id="pagescreate-basicinfo">												
				<div class="detailbox">
					<h4>Provide basic info about the page</h4>
					<div class="frow">
						<div class="sliding-middle-out anim-area underlined fullwidth">
							<?php $form = ActiveForm::begin(['id' => 'frm-language','options'=>['onsubmit'=>'return false;',],]); ?> 
								<?= $form->field($model,'name')->dropDownList(ArrayHelper::map(BusinessCategory::find()->orderBy(['name'=>SORT_ASC])->all(), 'name', 'name'),['class'=>'select2 pageCatDrop','style'=>'width: 100%','id'=>'pageCatDrop'])->label(false)?>
							<?php ActiveForm::end() ?>
						</div>
					</div>
					<div class="frow">
						<div class="sliding-middle-out anim-area underlined fullwidth">
							<input type="text" placeholder="Page name" id="pagename" onkeyup="textCaps('pagename')">
						</div>
					</div>
					<div class="frow">
						<div class="sliding-middle-out anim-area underlined fullwidth">
							<input type="text" placeholder="Short description of your page" id="pageshort" onkeyup="textCaps('pageshort')">
						</div>
					</div>
					<div class="frow">
						<div class="sliding-middle-out anim-area underlined fullwidth tt-holder">
							<textarea class="materialize-textarea" placeholder="Add a few sentences to tell people what your page about." id="pagedesc" onkeyup="textCaps('pagedesc')"></textarea>
						</div>
					</div>
					<div class="frow">
						<div class="sliding-middle-out anim-area underlined fullwidth">
							<input type="text" placeholder="List your external website, if you have one" id="pagesite">
						</div>
					</div>													
					<div class="btn-holder">													
						<div class="pull-right"><a href="javascript:void(0)" class="btn btn-primary waves-effect" onclick="navigateTabs('pagescreate-uploadphoto',this)">Continue</a></div>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="pagescreate-uploadphoto">
				<div class="detailbox">
					<h4>Upload profile photo for the page</h4>
					<div class="row">
						<div class="col-lg-6 col-sm-6 col-xs-12 pull-right photo-holder">
							<div class="uploadProfile-cropping">	
							<!-- crop section -->
							<div class="crop-holder">
								<div id="profCrop"></div>
							</div>
							</div>
						</div>
						<div class="col-lg-6 col-sm-6 col-xs-12 photo-detail">
							<div class="pick-photo">
								<div class="overlayUploader">
									<input type="file" id="uploadPagePhoto" accept="image/*">
									<a href="#crop-popup" class="add-pagephoto popup-modal">
										<i class="mdi mdi-camera"></i>
										Upload Profile Photo
									</a>
								</div>																
							</div>
						</div>
					</div>																									
					<div class="btn-holder">													
						<div class="pull-left"><a href="javascript:void(0)" class="btn btn-primary waves-effect btn-gray" onclick="navigateTabs('pagescreate-basicinfo',this)">Back</a></div>
						<div class="pull-right"><a href="javascript:void(0)" class="btn btn-primary waves-effect" onclick="cropPageChange(this)">Continue</a></div>
						<div class="pull-right"><a href="javascript:void(0)" class="btn btn-primary waves-effect skip-btn" onclick="navigateTabs('pagescreate-contact',this)">Skip</a></div>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="pagescreate-contact">
				<div class="detailbox">
					<h4>Provide page contact info</h4>
					<div class="frow">
						<div class="sliding-middle-out anim-area underlined fullwidth">
							<input type="text" placeholder="Bussines address" id="busaddress">
						</div>
					</div>
					<div class="frow">
						<div class="sliding-middle-out anim-area underlined fullwidth">
							<input type="text" placeholder="City / Town" id="autocomplete" class="getplacelocation">
							<input type="hidden" readonly="true" name="isd_code" id="isd_code"/>
							<input type="hidden" id="country_code" name="country_code" />
							<input type="hidden" id="country" name="country" />
						</div>
					</div>
					<div class="frow">
						<div class="sliding-middle-out anim-area underlined fullwidth">
							<input type="text" placeholder="Postal Code" id="buscode">
						</div>
					</div>
					<div class="frow">
						<div class="sliding-middle-out anim-area underlined fullwidth">
							<input type="text" placeholder="Business email" id="busemail">
						</div>
					</div>
					<div class="frow">
						<div class="sliding-middle-out anim-area underlined fullwidth">
							<input type="text" placeholder="Business phone" id="busphone">
						</div>
					</div>
					<div class="frow">
						<div class="sliding-middle-out anim-area underlined fullwidth">
							<input type="text" onkeydown="return false;" placeholder="Business start date" data-query="M" class="datepickerinput" id="datepicker" data-toggle="datepicker" readonly>
						</div>
					</div>
					<div class="btn-holder">													
						<div class="pull-left"><a href="javascript:void(0)" class="btn btn-primary waves-effect btn-gray" onclick="navigateTabs('pagescreate-uploadphoto',this)">Back</a></div>
						<div class="pull-right"><a href="javascript:void(0)" class="btn btn-primary waves-effect" onclick="navigateTabs('pagescreate-verification',this)">Continue</a></div>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="pagescreate-verification">
				<div class="detailbox">
					<h4>Owner  Verification</h4>
					<div class="frow">
						<p>Often pages offer public information about an businesses entity. Please enter an affiliated phone number or email address for owner verification. Only curret representative of the entity can create a page to represents any given business entity.</p>
					</div>
					<div class="frow">
						<span class="icon-span"><input type="radio" id="agreemobilepage" name="verify-radio"></span>Veryfiy ownership by sending  a text message to following number
					</div>
					<div class="frow">
						<div class="sliding-middle-out anim-area underlined fullwidth">
							<?php $cc = new CountryCode();
								$form = ActiveForm::begin(['options'=>['onsubmit'=>'return false;',],]); ?> 
								<?= $form->field($cc,'isd_code')->dropDownList(ArrayHelper::map(CountryCode::find()->orderBy(['isd_code'=>SORT_ASC])->all(), 'isd_code', 'isd_code'),['class'=>'select2 countryCodeDrop','style'=>'width: 100%','id'=>'countryCodeDrop'])->label(false)?>
                            <?php ActiveForm::end() ?>
						</div>
					</div>
					<div class="frow">
						<div class="sliding-middle-out anim-area underlined fullwidth">
							<input type="text" placeholder="Phone number">
						</div>
					</div>
					<div class="frow">
						<span class="icon-span"><input type="radio" id="agreeemailpage" name="verify-radio"></span>Veryfiy ownership by sending  a text message to following email
					</div>
					<div class="frow">
						<div class="sliding-middle-out anim-area underlined fullwidth">
							<input type="text" placeholder="Your company email address" id="verifyemailid">
						</div>
					</div>													
					<div class="frow">
						<div class="h-checkbox">
							<input type="checkbox" id="pagetncagreed" />
 		                    <label for="pagetncagreed">I verify that I am the official representative of this entity and have the right to act on behalf of my entity in the creation of this page.</label>					
						</div>
					</div>													
					<div class="btn-holder">													
						<div class="pull-left"><a href="javascript:void(0)" class="btn btn-primary waves-effect btn-gray" onclick="navigateTabs('pagescreate-contact',this)">Back</a></div>
						<div class="pull-right"><a href="javascript:void(0)" class="btn btn-primary waves-effect" onclick="navigateTabs('pagescreate-success',this)">Finish</a></div>
						<div class="pull-right"><a href="javascript:void(0)" class="btn btn-primary waves-effect skip-btn" onclick="navigateTabs('pagescreate-uploadphoto',this)">Cancel</a></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php exit;?>