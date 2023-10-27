<?php 
use yii\helpers\Url;
use frontend\models\Like;
use frontend\models\Page;
use frontend\models\LoginForm;
use frontend\models\UserForm;
use frontend\models\BusinessCategory;
use frontend\models\CountryCode;
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;

$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');

$Auth = '';
if(isset($user_id) && $user_id != '') 
{
$authstatus = UserForm::isUserExistByUid($user_id);
if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') 
{
	$Auth = $authstatus;
}
}	
else	
{
	$Auth = 'checkuserauthclassg';
}
?>
	<div class="col s6 m4 l3 gridBox127 add-cbox">
		<div class="card hoverable pageCard">
			<div class="general-box">
				<a href="javascript:void(0)" class="add-page add-general <?=$Auth?> directcheckuserauthclass"" onclick="openAddItemModal()">
					<span class="icont">+</span>
					Add new page
				</a>
			</div>
		</div>
	</div>
	<?php 
	$isEmpty = true;
	foreach($mypages as $mypage){
		$pageid = (string)$mypage['_id'];
		$pagelink = Url::to(['page/index', 'id' => "$pageid"]);
		$pagedetail = Page::find()->where([(string)'_id' => $pageid])->one();
		if($pagedetail['is_deleted'] == '2' && $pagedetail['created_by'] != $user_id)
		{
			continue;
		}
		$like_count = Like::getLikeCount($pageid);
		$likeexist = Like::getPageLike($pageid);
		if($likeexist){$likestatus = 'Liked';}
		else{$likestatus = 'Like';}
		$page_img = $this->context->getpageimage($pageid);
		$getPageCover = Page::find()->where(['_id' => $pageid])->one();
		if(isset($getPageCover['cover_photo']) && !empty($getPageCover['cover_photo']))
		{
			$cover_photo = "uploads/cover/".$getPageCover['cover_photo'];
		}
		else
		{
			$cover_photo = $baseUrl."/images/wallbanner.jpg";
		}
		$isEmpty = false;
	?>
	<div class="col s6 m4 l3 gridBox127 <?=$getPageCover['cover_photo']?>">
		<div class="card hoverable pageCard">
			<a href="javascript:void(0);" class="page-box general-box" onclick="pageLike(event,'<?=$pageid?>',this)">
				<div class="photo-holder waves-effect waves-block waves-light">
					<img src="<?=$cover_photo?>">
				</div>
				<div class="content-holder">
					<h4><?=$mypage['page_name']?></h4>						
					<div class="userinfo">
						<img src="<?=$page_img?>"/>
					</div>													
					<div class="username">
						<span class="pagelikecounteer"><?=$like_count?> Likes</span>
					</div>
					<?php if($pagedetail['created_by'] == $user_id) { ?>
			<div class="action-btns">
				<span class="likestatus_<?=$pageid?> disabled" onclick="pageLike(event,'<?=$pageid?>',this)">Admin</span>
			</div>
			<?php } else { ?>
			<div class="action-btns">
				<span class="noClick likestatus_<?=$pageid?> disabled" onclick="pageLike(event,'<?=$pageid?>',this)"><?=$likestatus?></span>
			</div>
			<?php } ?>	
				</div>
			</a>
		</div>
	</div>
	<?php }

	if($isEmpty) { ?>
	<div class="joined-tb">
	    <i class="mdi mdi-file-outline"></i>
	    <p>No page found.</p>
	</div>
	<?php }  
exit;?>