<?php

use frontend\assets\AppAsset;
use frontend\models\SecuritySetting;
use backend\models\TravstoreCategory;

$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');
$result_security = SecuritySetting::find()->where(['user_id' => $user_id])->one();
if ($result_security)
{
    $my_post_view_status = $result_security['my_post_view_status'];
    if ($my_post_view_status == 'Private')
    {
        $post_dropdown_class = 'lock';
    }
    else if ($my_post_view_status == 'Connections')
    {
        $post_dropdown_class = 'user';
    }
    else
    {
        $my_post_view_status = 'Public';
        $post_dropdown_class = 'globe';
    }
}
else
{
    $my_post_view_status = 'Public';
    $post_dropdown_class = 'globe';
}
$placeholder = 'Create your travel store ad';
$trav_cat = TravstoreCategory::getTravCat();
?>  
<form id='frmnewpost' action="">
	<div class="top-stuff">
		<div class="npost-title">
			<div class="sliding-middle-out anim-area">
				<input type="text" class="title npinput capitalize" placeholder="Title of this item" id="title">
			</div>
		</div>
		<div class="settings-icon">
			<div class="dropdown dropdown-custom dropdown-small resist">
				<a href="javascript:void(0)" class="dropdown-toggle"  role="button" aria-haspopup="true" aria-expanded="false">
					<i class="zmdi zmdi-more-vert"></i>
				</a>
				<ul class="dropdown-menu">
					<li>
						<ul class="echeck-list">
							<li class="disable_share"><a href="javascript:void(0)"><i class="zmdi zmdi-check"></i>Disable Sharing</a></li>
							<li class="disable_comment"><a href="javascript:void(0)"><i class="zmdi zmdi-check"></i>Disable Comments</a></li>
							<li class="cancel_post"><a href="javascript:void(0)" class="close-popup notpopup">Cancel Post</a></li>
						</ul>
					</li>
				</ul> 
			</div>
		</div>
	</div> 
	<div class="clear"></div>
	<div class="npost-content">
		<div class="post-mcontent">
			<i class="mdi mdi-pencil-box-outline main-icon"></i>
			<div class="desc">									
				<textarea type="text" class="textInput npinput materialize-textarea capitalize" id="textInput"  placeholder="<?=$placeholder?>"></textarea>
			</div>
			<div class="post-info-added">
			</div>
			<div class="post-photos">
				<div class="img-row">
				</div>
			</div>
			<div class="post-location">
				<div class="areatitle">At</div>
				<div class="areadesc">
					<input type="text" id="cur_loc" class="getplacelocation" placeholder="Where are you?"/>
				</div>
			</div>
			<div class="post-price">	
				<div class="pricetitle">Price</div>
				<div class="pricedesc">
					<input type="text"  id="trav_pricce" class="pprice" placeholder="what's the price?"/>
				</div>
			</div>
			<div class="post-category">
			<div class="cattitle">For</div>
				<div class="catdesc" id="trav_cat">Beauty</div>
			</div>
		</div>
		<div class="post-bcontent">
			<div class="post-toolbox">
				<a href="javascript:void(0)" class="add-photos">
					<div class="custom-file">
						<div><span class="glyphicon glyphicon-camera"></span></div>                                
						<input type="file" id="imageFile1" name="upload" class="upload custom-upload custom-upload-neww npinput" title="Choose a file to upload" required="" data-class=".post-photos .img-row"/>
					</div>
				</a>
				<a href="javascript:void(0)" class="add-location"><span class="glyphicon glyphicon-map-marker"></span></a>                          
				<a href="javascript:void(0)" class="add-title"><span class="glyphicon glyphicon-text-size"></span></a>
				<a href="javascript:void(0)" class="add-price"><span class="glyphicon glyphicon-usd"></span></a>
				<div class="dropdown dropdown-custom cat-dropdown add-category">
					<a href="javascript:void(0)" class="dropdown-toggle"  data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
					<i class="mdi mdi-menu-down"></i>
					</a>
					<ul class="dropdown-menu">		
						<li>
							<div class="cbox-desc cat-drop">
								<h5>Choose Category</h5>
								<?php foreach ($trav_cat as $trav_cats){
									$trav_cat_name = $trav_cats['name'];?>
								<div class="radio-holder">
									<label class="control control--radio" onclick="setAdCategory(this,'<?=$trav_cat_name?>')"><?=$trav_cat_name?>
									  <input type="radio" name="category" checked value="<?=$trav_cat_name?>"/>
									  <div class="control__indicator"></div>
									</label>
								</div>
								<?php } ?>
							</div>
						</li>
					</ul>
				</div>
			</div>
			<div class="post-bholder">
				<span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
				<button class="btn btn-primary posttravstore" type="button" disabled><span class="glyphicon glyphicon-send"></span>Post</button>
				<input type="hidden" name="country" id="country" />					
				<input type="hidden" name="imgfilecount" id="imgfilecount" value="-1" />
				<input type="hidden" name="share_setting" id="share_setting" value="Enable"/>
				<input type="hidden" name="comment_setting" id="comment_setting" value="Enable"/>
				<input type="hidden" name="link_title" id="link_title" />
				<input type="hidden" name="link_url" id="link_url" />
				<input type="hidden" name="link_description" id="link_description" />
				<input type="hidden" name="link_image" id="link_image" />
				<input type="hidden" id="pagename" value="travstore"/>
				<input type="hidden" id="hiddenCount" value="1">
			</div>
		</div>
	</div>
</form>