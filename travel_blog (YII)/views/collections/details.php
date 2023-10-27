<?php  
use frontend\assets\AppAsset;
 
use frontend\models\Like;
use frontend\models\Comment;
use frontend\models\LoginForm;
use frontend\models\Collections;
use backend\models\Googlekey;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Collections';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$GApiKeyL = $GApiKeyP = Googlekey::getkey();

$collections_s = Collections::find()->where(['_id' => $id])->andWhere(['not','flagger', "yes"])->asarray()->one();
$collectionsId = (string)$collections_s['_id'];
$collectionsUId = (string)$collections_s['user_id'];
$collectionsTitle = $collections_s['title'];
$collectionsImg = $collections_s['image'];
$collectionsImg = explode(',', $collectionsImg);
$collectionsImg = array_filter($collectionsImg);
$collectionsName = $collections_s['title'];
if(count($collectionsImg) >1) {
	$totalphotos = count($collectionsImg).' photos';
} else {
	$totalphotos = count($collectionsImg).' photo';
}

if(count($collectionsImg) > 3) {
	$isallowdelete = true; 
} else {
	$isallowdelete = false;
}

$createdBy = $this->context->getuserdata($collectionsUId,'fullname'); 

if($user_id == $collectionsUId) {
    $isOwner = 'yes';
} else {
    $isOwner = 'no';
}
?>

	<script src="<?=$baseUrl?>/js/chart.js"></script>
    <div class="page-wrapper  mainfeed-page"> 
        <div class="header-section">
            <?php include('../views/layouts/header.php'); ?>
        </div>
        <?php include('../views/layouts/menu.php'); ?>
        <div class="floating-icon">
		   <div class="scrollup-btnbox anim-side btnbox scrollup-float">
		      <div class="scrollup-button float-icon">
		         <span class="icon-holder ispan">
		            <i class="mdi mdi-arrow-up-bold-circle"></i>
		         </span>
		      </div>
		   </div>
		</div>
		<div class="clear"></div> 
		<div>
			<?php include('../views/layouts/leftmenu.php'); ?>
			<div class="fixed-layout">
			    <div class="main-content main-page places-page photostream-page pb-0 m-t-50 collectiondetail-page">
			        <div class="combined-column wide-open main-page full-page">
			            <div class="places-content places-all">
			                <div class="container cshfsiput cshfsi">
			                    <div class="places-column cshfsiput cshfsi width-100 m-top">
			                        <div class="tab-content">
			                            <div id="places-photos" class="placesphotos-content subtab bottom_tabs">
			                                <div class="content-box">
			                                    <div class="mbl-tabnav">
			                                        <a href="javascript:void(0)" onclick="openDirectTab('places-all')"><i class="mdi mdi-arrow-left"></i></a> 
			                                        <h6>Photos</h6>
			                                    </div>
			                                    <div class="cbox-desc gallery-content">
			                                        <div class="left">
			                                            <h3 class="heading-inner mt-0"><?=$collectionsName?> <span class="lt">(<?=count($collectionsImg)?>)</span></h3>
			                                            <p class="para-inner">Upload your collection photos</p>
			                                        </div>
			                                        <?php if($collectionsUId == $user_id) { ?>
													<div class="cbox-title right">
					                                    <a href="javascript:void(0)" class="right-link"></a>
					                                    <div class="right po_asb">
					                                       <form>
					                                          <div class="custom-file prevent-gallery edit-gallery_cols_s <?=$checkuserauthclass?>" data-editid="<?=$id?>">
					                                             <div class="title ">+ Upload</div>
					                                          </div>
					                                       </form>
					                                    </div>
					                                </div>
													<?php } ?>
<div id="placebox"class="lgt-gallery-photo lgt-gallery-justified dis-none">
<?php
foreach($collectionsImg as $collectionsImg_s) {
    if(file_exists($collectionsImg_s)) {
        $eximg = $collectionsImg_s;
        $inameclass = preg_replace('/\\.[^.\\s]{3,4}$/', '', $collectionsImg_s);
        
        $picsize = $imgclass = '';
        $val = getimagesize($eximg);
        $picsize .= $val[0] .'x'. $val[1] .', ';
        if($val[0] > $val[1]) {
            $imgclass = 'himg';
        } else if($val[1] > $val[0]) {
            $imgclass = 'vimg';
        } else {
            $imgclass = 'himg';
        }

        $time = time();
        $random = rand(9999999, 99999999);
        $uniq = $time.'_'.$random;
        
        $isEmpty = false;
        ?>

        <div data-src="<?=$eximg?>" class="allow-gallery" data-sizes="<?=$id?>|||Collections">
			<img class="himg main_pic" src="<?=$eximg?>"/>  
			<?php 
			if($isOwner == 'yes') { 
				if($isallowdelete) {?> 
					<a href="javascript:void(0)" class="removeicon prevent-gallery" data-id="<?=$uniq?>" data-postid="<?=$collectionsId?>" onclick="removepiccollections_re(this, true)"><i class="mdi mdi-delete"></i></a>
				<?php 
				} 
			} 
			?>
            <div class="caption">
               <div class="left">
                  <span class="title"><?=$collectionsTitle?></span> <br>
                  <span class="attribution">By <?=$createdBy?></span>
               </div>
               <div class="right icons">
                  <a href="javascript: void(0)" class="prevent-gallery like">
                  	<i class="mdi mdi-thumb-up-outline mdi-15px" onclick="toggleIcons(this)"></i>
                  	<span class="lcount">7</span>
                  </a>
                  <a href="javascript: void(0)" class="prevent-gallery">
                  	<i class="mdi mdi-comment-outline mdi-15px cmnt"></i>
                  </a>
               </div>
            </div>
        </div>
    <?php 
    } 
} 
?>
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
			</div>
		</div>
    </div>  
 
	<input type="hidden" name="pagename" id="pagename" value="feed" />
	<input type="hidden" name="tlid" id="tlid" value="<?=(string)$user_id?>" />
	
    <div id="compose_tool_box" class="modal compose_tool_box post-popup custom_modal main_modal">
    </div> 
	 
	<div id="composeeditpostmodal" class="modal compose_tool_box edit_post_modal post-popup main_modal custom_modal compose_edit_modal">
    </div>
	
	<div id="sharepostmodal" class="modal sharepost_modal post-popup main_modal custom_modal">
	</div>
	
	<!-- Post detail modal -->
	<div id="postopenmodal" class="modal modal_main compose_tool_box custom_modal postopenmodal_main postopenmodal_new">	
	</div>
	
	<div id="compose_mapmodal" class="map_modalUniq modal map_modal compose_inner_modal modalxii_level1">
		<?php include('../views/layouts/mapmodal.php'); ?>
	</div>
    <?php include('../views/layouts/addpersonmodal.php'); ?>
    <?php include('../views/layouts/custom_modal.php'); ?>
    
   	<div id="upload-gallery-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose upload-gallery-popup"></div>

	<div id="edit-collection-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose upload-gallery-popup"></div>

	<div id="userwall_tagged_users" class="modal modalxii_level1">
		<div class="content_header">
			<button class="close_span waves-effect">
				<i class="mdi mdi-close mdi-20px"></i>
			</button>
			<p class="selected_photo_text"></p>
			<a href="javascript:void(0)" class="chk_person_done_new done_btn focoutTRV03 action_btn">Done</a>
		</div>
		<nav class="search_for_tag">
			<div class="nav-wrapper">
			  <form>
			    <div class="input-field">
			      <input id="tagged_users_search_box" class="search_box" type="search" required="">
			        <label class="label-icon" for="tagged_users_search_box">
			          <i class="zmdi zmdi-search mdi-22px"></i>
			        </label>
			      </div>
			  </form>
			</div>
		</nav>
		<div class="person_box"></div>
	</div>
	
    <?php include('../views/layouts/commonjs.php'); ?>

    <script src="<?=$baseUrl?>/js/photostream.js"></script>
    <script src="<?=$baseUrl?>/js/collections.js"></script>
<?php $this->endBody() ?> 