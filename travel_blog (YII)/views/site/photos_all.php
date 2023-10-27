<?php 
use yii\helpers\Url;
use frontend\assets\AppAsset;

use frontend\models\PinImage;
use frontend\models\Comment;
use frontend\models\Like;
use frontend\models\PostForm;
use frontend\models\LoginForm;
use frontend\models\Gallery;

$session = Yii::$app->session;
$user_id = (string)$session->get('user_id'); 
   
?>
<div class="content-box">
	<div class="mbl-tabnav">
		<a href="javascript:void(0)" onclick="openDirectTab('places-all')">
            <i class="mdi mdi-arrow-left"></i>
        </a> 
        <h6>Photos</h6>
	</div>
	<div class="cbox-title"> 
		<?=$placefirst?> Photos <span class="lt"></span>
		<a href="javascript:void(0)" class="right-link"></a>
		 <div class="right po_asb">
			<form enctype="multipart/form-data" name="imagePlaceForm"> 
				<div class="custom-file">
					<div class="title add-photo-photos upload-gallery"><i class="mdi mdi-plus"></i> Upload photo</div>
				</div>
			</form>
		</div>
	</div>
	<div class="cbox-desc gallery-content">
		<div class="gloader"><img src="<?=$baseUrl?>/images/loading.gif" class="g-loading"/></div>
		<div id="placebox"class="lgt-gallery-photo lgt-gallery-justified dis-none">
		<?php

        foreach($gallery as $gallery_item) {
            $hideids = isset($gallery_item['hideids']) ? $gallery_item['hideids'] : '';
            $hideids = explode(',', $hideids);
            if(in_array($user_id, $hideids)) {
                continue;
            }

            $galimname = $gallery_item['image'];
            if(file_exists($galimname)) {
                $gallery_item_id = $gallery_item['_id'];
                $eximg = $galimname;
                $inameclass = preg_replace('/\\.[^.\\s]{3,4}$/', '', $galimname);
                
                $picsize = $imgclass = '';
                $like_count = Like::getLikeCount((string)$gallery_item_id);
                $comments = Comment::getAllPostLikeCount((string)$gallery_item_id);
                $title = $gallery_item['title'];
                
                $like_active = Like::find()->where(['post_id' => (string) $gallery_item_id,'status' => '1','user_id' => (string) $user_id])->one();
                if(!empty($like_active)) {
                    $like_active = 'active';
                } else {
                    $like_active = '';
                }
                
                $time = Yii::$app->EphocTime->comment_time(time(),$gallery_item['created_at']);
                $puserid = (string)$gallery_item['user_id'];
                
                $puserdetails = LoginForm::find()->where(['_id' => $puserid])->one();
                if($puserid != $user_id) {
                    $galusername = ucfirst($puserdetails['fname']) . ' ' . ucfirst($puserdetails['lname']);
                    $isOwner = false;
                } else {
                    $galusername = 'You';
                    $isOwner = true;
                }
                
                $like_buddies = Like::getLikeUser($inameclass .'_'. $gallery_item['_id']);
                $newlike_buddies = array();
                foreach($like_buddies as $like_buddy) {
                    $newlike_buddies[] = ucwords(strtolower($like_buddy['fullname']));
                }
                $newlike_buddies = implode('<br/>', $newlike_buddies);  

                $val = getimagesize($eximg);
                $picsize .= $val[0] .'x'. $val[1] .', ';
                if($val[0] > $val[1]) {
                    $imgclass = 'himg';
                } else if($val[1] > $val[0]) {
                    $imgclass = 'vimg';
                } else {
                    $imgclass = 'himg';
                }
                
                $isEmpty = false;
                ?> 
                <div data-src="<?=$eximg?>" class="allow-gallery" data-sizes="<?=$gallery_item_id?>|||Gallery">
                    <img class="himg" src="<?=$eximg?>"/>
                    <?php if($isOwner) { ?> 
                    <a href="javascript:void(0)" class="removeicon prevent-gallery" data-id="<?=$gallery_item_id?>" onclick="removepic(this)"><i class="mdi mdi-delete"></i></a>
                    <?php } ?>   
                    <div class="caption">
                        <div class="left">
                            <span class="title"><?=$title?> ( <?=$time?> )</span> <br>
                            <span class="attribution">By <?=$galusername?></span>
                        </div>
                        <div class="right icons">
                            <a href="javascript:void(0)" class="prevent-gallery like custom-tooltip pa-like liveliketooltip liketitle_<?=$gallery_item_id?> <?=$like_active?>" onclick="doLikeAlbumbImages('<?=$gallery_item_id?>');" data-title="<?=$newlike_buddies?>">
                                <i class="mdi mdi-thumb-up-outline mdi-15px"></i>
                            </a>
                            <?php if($like_count >0) { ?>
                                <span class="likecount_<?=$gallery_item_id?> lcount"><?=$like_count?></span>
                            <?php } else { ?>
                                <span class="likecount_<?=$gallery_item_id?> lcount"></span>
                            <?php } ?>
                            
                            <a href="javascript:void(0)">
                                <i class="mdi mdi-comment-outline mdi-15px cmnt"></i>
                            </a>
                            <?php if($comments > 0){ ?>
                                <span class="lcount commentcountdisplay_<?=$gallery_item_id?>"><?=$comments?></span>
                            <?php } else { ?>
                                <span class="lcount commentcountdisplay_<?=$gallery_item_id?>"></span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php 
            } 
        } ?>
		</div>
	</div>
</div>
<?php exit;?>		
	
