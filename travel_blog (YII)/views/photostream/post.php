<?php  
use frontend\assets\AppAsset;
use yii\helpers\Url; 
use frontend\models\Like;
use frontend\models\Comment;
use frontend\models\LoginForm;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$status = $session->get('status');
$user_id = (string)$session->get('user_id');  

$collectiondetail = array();
$collectiondetail['is_deleted'] = '';
$collectiondetail['_id'] = '';
?>

<div class="content-box">
    <div class="mbl-tabnav">
        <a href="javascript:void(0)" onclick="openDirectTab('places-all')"><i class="mdi mdi-arrow-left"></i></a> 
        <h6>Photos</h6>
    </div>
    <div class="cbox-desc gallery-content">
        <div class="left">
            <h3 class="heading-inner mt-0">PHOTOSTREAM <span class="lt"></span></h3>
            <p class="para-inner">Upload and share your travel photos</p>
        </div>
        <div class="cbox-title right">
            <a href="javascript:void(0)" class="right-link"></a>
            <div class="right po_asb">
                <form>
                    <div class="custom-file">
                        <div class="title "><a href="javascript:void(0)" class="upload-gallery <?=$checkuserauthclass?>"><i class="mdi mdi-cloud-upload"></i> UPLOAD</a></div>
                    </div>
                </form>
            </div>
        </div>
        <div id="placebox"class="lgt-gallery-photo lgt-gallery-justified dis-none">
        <?php
        $isEmpty = true;
        foreach($gallery as $gallery_item) {
            if($user_id != '') {
                $hideids = isset($gallery_item['hideids']) ? $gallery_item['hideids'] : '';
                $hideids = explode(',', $hideids);
                if(in_array($user_id, $hideids)) {
                    continue;
                }
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
                    <?php if($status == '10') { ?>  
                    <div class="dropdown dropdown-custom dropdown-xxsmall photostreamflagger">
                        <a href="javascript:void(0)" class="dropdown-toggle dropdown-button ramani prevent-gallery" data-activates='<?=$gallery_item_id?>' data-id='<?=$gallery_item_id?>'>
                        <i class="mdi mdi-flag"></i>
                        </a>
                        <ul id='<?=$gallery_item_id?>' class="dropdown-content">
                            <li class="prevent-gallery"> <a href="javascript:void(0)" data-id="<?=$gallery_item_id?>" data-module="photostream" onclick="flagpost(this)">Flag post</a> </li>
                        </ul>
                    </div>
                    <?php } else { ?>
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
                                
                                <a href="javascript:void(0)" class="prevent-gallery waves-effect">
                                    <i class="mdi mdi-comment-outline mdi-15px cmnt"></i>
                                </a>
                                <?php if($comments > 0){ ?>
                                    <span class="lcount commentcountdisplay_<?=$gallery_item_id?>"><?=$comments?></span>
                                <?php } else { ?>
                                    <span class="lcount commentcountdisplay_<?=$gallery_item_id?>"></span>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php 
            } 
        } 

        if($isEmpty) { ?>
        <div class="joined-tb">
            <i class="mdi mdi-file-outline"></i>
            <p>No photostream found.</p>
        </div>
        <?php } ?>
        </div>
    </div>
    <div class="new-post-mobile clear upload-gallery <?=$checkuserauthclass?>">
        <a href="javascript:void(0)" class="popup-window" ><i class="mdi mdi-camera"></i></a>
    </div>
</div>