<?php

use frontend\assets\AppAsset;
use frontend\models\SecuritySetting;
use frontend\models\UserForm;
use frontend\models\Connect;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');
$result_security = SecuritySetting::find()->where(['user_id' => $user_id])->one();

$Auth = '';
$directcheckuserauthclass = '';

if(isset($user_id) && $user_id != '') {
    $authstatus = UserForm::isUserExistByUid($user_id);
    if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
        $Auth = $authstatus;
        $directcheckuserauthclass = $Auth .' directcheckuserauthclass';
    }
} else {
    $Auth = 'checkuserauthclassg';
    $directcheckuserauthclass = 'checkuserauthclassg directcheckuserauthclass';
}

if ($result_security) {
    $my_post_view_status = $result_security['my_post_view_status'];
    if ($my_post_view_status == 'Private') {
        $post_dropdown_class = 'lock';
    } else if ($my_post_view_status == 'Connections') {
        $post_dropdown_class = 'user';
    } else {
        $my_post_view_status = 'Public';
        $post_dropdown_class = 'globe';
    }
} else {
    $my_post_view_status = 'Public';
    $post_dropdown_class = 'globe';  
}

$place_type = 'none';
$from_place_page = 'no';
$is_place_data = 'no';
$header_text='';
if(strstr($_SERVER['REQUEST_URI'],'r=tripexperience')) {
    $placeholder = 'Share your trip experience with others';
	$from_place_page = 'yes';
} else if(strstr($_SERVER['HTTP_REFERER'],'r=tripstory')) {   
    $label = 'Write a experience';
    $placeholder = 'Share your trip story with others';
} else {
    $placeholder = 'What’s new?';
}

$usrfrd = Connect::getuserConnections($user_id);
$usrfrdlist = array();
foreach($usrfrd AS $ud)
{
    if(isset($ud['userdata']['fullname']) && $ud['userdata']['fullname'] != '') {
        $id = (string)$ud['userdata']['_id'];
        $fbid = isset($ud['userdata']['fb_id']) ? $ud['userdata']['fb_id'] : '';
        $dp = $this->context->getimage($ud['userdata']['_id'],'thumb');
        $nm = $ud['userdata']['fullname'];
        $usrfrdlist[] = array('id' => $id, 'fbid' => $fbid, 'name' => $nm, 'text' => $nm, 'thumb' => $dp);
    }
}
?>   
<form id='frmnewpost' action="">
    <div class="top-stuff">
		<?php if($place_type == 'tip' || $place_type == 'ask'){ ?>
			<div class="fake-caption">
				<?=$header_text;?>
			</div>
		<?php } ?>
		<?php if($place_type == 'reviews'){ ?>
		<div class="rating-stars setRating" onmouseout="setStarText(this,6)">
			<span>Your Rating</span>
			<i class="mdi mdi-star" data-value="5" onmouseover="setStarText(this,5)" onclick="setRating(this,5)"></i>
			<i class="mdi mdi-star" data-value="4" onmouseover="setStarText(this,4)" onclick="setRating(this,4)"></i>
			<i class="mdi mdi-star" data-value="3" onmouseover="setStarText(this,3)" onclick="setRating(this,3)"></i>
			<i class="mdi mdi-star" data-value="2" onmouseover="setStarText(this,2)" onclick="setRating(this,2)"></i>
			<i class="mdi mdi-star" data-value="1" onmouseover="setStarText(this,1)" onclick="setRating(this,1)"></i>
			<span class="star-text">Roll over stars, then click to rate</span>
		</div>
		<input type="hidden" id="pagereviewrate" value="0">
		<div class="clear"></div>
		<?php } ?>
        <div class="npost-title">
            <div class="sliding-middle-out anim-area">
                <input type="text" class="title npinput capitalize" placeholder="Title of this post" id="title">
            </div>
        </div>
        <div class="settings-icon">
            <div class="dropdown dropdown-custom dropdown-small resist">
                <?php if($Auth == 'checkuserauthclassg' || $Auth == 'checkuserauthclassnv') { ?>
                <a href="javascript:void(0)" class="dropdown-toggle <?=$Auth?> directcheckuserauthclass"  role="button" aria-haspopup="true" aria-expanded="false">
                    <i class="zmdi zmdi-more-vert"></i>
                </a>
                <?php } else { ?>
                <a href="javascript:void(0)" class="dropdown-toggle"  role="button" aria-haspopup="true" aria-expanded="false">
                    <i class="zmdi zmdi-more-vert"></i>
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <ul class="echeck-list">
							<?php if($is_place_data != 'yes'){ ?>
                            <li class="disable_share"><a href="javascript:void(0)"><i class="zmdi zmdi-check"></i>Disable Sharing</a></li>
							<?php } ?>
							<li class="disable_comment"><a href="javascript:void(0)"><i class="zmdi zmdi-check"></i>Disable Comments</a></li>
                            <li class="cancel_post"><a href="javascript:void(0)" class="close-popup notpopup">Cancel Post</a></li>
                        </ul>
                    </li>
                </ul>
                <?php } ?> 
            </div>
        </div>
    </div> 
    <div class="clear"></div>
    <div class="npost-content">
        <div class="post-mcontent">
            <i class="mdi mdi-pencil-box-outline main-icon"></i>
            <div class="desc">									
				<textarea type="text" class="textInput npinput materialize-textarea capitalize <?=$directcheckuserauthclass?>" id="textInput"  placeholder="<?=$placeholder?>"></textarea>
			</div>
            <div class="post-info-added">
            </div>
            <div class="post-photos">
                <div class="img-row">
                </div>
            </div>
            <div class="post-tag">
                <div class="areatitle">With</div>
                <div class="areadesc">
                    <div class="ptag select2-holder gensel conselect2 select2content">
                        <select id="taginput" class="userselect2" multiple=""></select>
                    </div>
                </div>
            </div>
            <div class="post-location">
                <div class="areatitle">At</div>
                <div class="areadesc">
                    <input type="text" id="cur_loc" class="getplacelocation" placeholder="Where are you?"/>
                </div>
            </div>
        </div>
        <?php if($Auth != 'checkuserauthclassg' && $Auth != 'checkuserauthclassnv') { ?>
        <div class="post-bcontent">
            <div class="post-toolbox">
                <a href="javascript:void(0)" class="add-photos">
                    <div class="custom-file">
					<?php if($place_type != 'reviews' && $place_type != 'ask' && $place_type != 'tip'){ ?>
                        <div class="title"><span><img src="<?=$baseUrl?>/images/uploadphotoBl.png"></span></div>
					<?php } ?>
                        <input type="file" id="imageFile1" name="upload[]" class="upload custom-upload custom-upload-new npinput" title="Choose a file to upload" required="" data-class=".post-photos .img-row" multiple="true"/>
                    </div>
                </a>
				<?php if($place_type != 'tip' && $place_type != 'ask'){ ?>
                <a href="javascript:void(0)" class="add-tag"><span><img src="<?=$baseUrl?>/images/tagconnectionsBl.png"></span></a>
				<?php } ?>
                <?php if($is_place_data == 'no'){ ?>
				<a href="javascript:void(0)" class="add-location"><span><img src="<?=$baseUrl?>/images/addplaceBl.png"></span></a>
				<?php } ?>
                <a href="javascript:void(0)" class="add-title"><span><img src="<?=$baseUrl?>/images/addtitleBl.png"></span></a>
            </div>
            <div class="post-bholder">
                <button class="btn btn-primary postbtn" type="button" disabled><span class="glyphicon glyphicon-send"></span>Post</button>
				<?php if($from_place_page == 'no'){ ?>
				<div class="custom-drop">
                    <div class="dropdown dropdown-custom dropdown-xsmall">
                        <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <span class="glyphicon glyphicon-<?= $post_dropdown_class ?>"></span><span class="sword"><?= $my_post_view_status ?></span> <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="post-private"><a href="javascript:void(0)"><span class="glyphicon glyphicon-lock"></span><span class="sword">Private</span></a></li>
                            <li class="post-connections"><a href="javascript:void(0)"><span class="glyphicon glyphicon-user"></span><span class="sword">Connections</span></a></li>
                            <li class="post-settings"><a href="javascript:void(0)"><span class="glyphicon glyphicon-globe"></span><span class="sword">Custom</span></a></li>
                            <li class="post-public"><a href="javascript:void(0)"><span class="glyphicon glyphicon-globe"></span><span class="sword">Public</span></a></li>
                            <input type="hidden" name="post_privacy" id="post_privacy" value="Public"/>
                        </ul>
                    </div>
                </div> 
				<?php } ?>		
                <input type="hidden" name="country" id="country" />					
                <input type="hidden" name="imgfilecount" id="imgfilecount" value="0" />
                <input type="hidden" name="share_setting" id="share_setting" value="Enable"/>
                <input type="hidden" name="comment_setting" id="comment_setting" value="Enable"/>
                <input type="hidden" name="link_title" id="link_title" />
                <input type="hidden" name="link_url" id="link_url" />
                <input type="hidden" name="link_description" id="link_description" />
                <input type="hidden" name="link_image" id="link_image" />
                <input type="hidden" id="hiddenCount" value="1">
            </div>
        </div>
        <?php } ?>
    </div>
</form>
<script type="text/javascript">
    var data1 = <?php echo json_encode($usrfrdlist); ?>;
</script>