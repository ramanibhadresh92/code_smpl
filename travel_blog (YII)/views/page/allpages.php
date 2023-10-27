<?php 
use yii\helpers\Url;
use frontend\models\Like;
use frontend\models\Page;
use frontend\models\LoginForm;
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
$isEmpty = true;
if(count($allpages) > 0){
	foreach($allpages as $allpage){
		if($allpage['is_deleted'] == '2' && $allpage['created_by'] != $user_id)
		{
			//continue;
		}
		$pageid = (string)$allpage['_id'];
		$pagelink = Url::to(['page/index', 'id' => "$pageid"]);
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
	<div class="col s6 m4 l3 gridBox127">
		<div class="card hoverable pageCard">
			<a href="javascript:void(0);" class="page-box general-box"onclick="pageLike(event,'<?=$pageid?>',this)">
				<div class="photo-holder waves-effect waves-block waves-light">
					<img src="<?=$cover_photo?>">
				</div>
				<div class="content-holder">
					<h4><?=$allpage['page_name']?></h4>						
					<div class="userinfo">
						<img src="<?=$page_img?>"/>
					</div>													
					<div class="username">
						<span class="pagelikecounteer"><?=$like_count?> Likes</span>
					</div>
					<div class="tag name dis-none"> 
						<span>tag names</span>
					</div>
					<?php if($allpage['created_by'] == $user_id) { ?>
					<div class="action-btns">
						<span class="likestatus<?=$pageid?>">Admin</span>
					</div>	
					<?php } else { ?>
					<div class="action-btns">
						<span class="noClick likestatus_<?=$pageid?>" onclick="pageLike(event,'<?=$pageid?>',this)"><?=$likestatus?></span>
					</div>
					<?php } ?>	
					
				</div> 
			</a>
		</div>
	</div>
	<?php } 
} 

if($isEmpty) { ?>
<div class="joined-tb">
    <i class="mdi mdi-file-outline"></i>
    <p>No page found.</p>
</div>
<?php }  
exit;?>