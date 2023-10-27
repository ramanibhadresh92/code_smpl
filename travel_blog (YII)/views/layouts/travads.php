<?php

use frontend\assets\AppAsset;
use frontend\models\TravAds;
use frontend\models\Page;
use frontend\models\PageEndorse;
use frontend\models\Like;

$session = Yii::$app->session;
$uid = (string)$session->get('user_id');
$baseUrl = AppAsset::register($this)->baseUrl;
 
$page_like_ads = TravAds::getrandAd('pagelikes',$uid);
$brand_ads = TravAds::getrandAd('brandawareness',$uid);
$page_end_ads = TravAds::getrandAd('pageendorse',$uid);
$web_leads = TravAds::getrandAd('websiteleads',$uid);
$web_conversion = TravAds::getrandAd('websiteconversion',$uid);
$trav_store_ads = TravAds::getrandAd('travstorefocus',$uid);
?>

<!-- side advertise -->
<?php if(!empty($brand_ads)){
$adid = $brand_ads['_id'];
$adcatch = $brand_ads['adcatch'];
$text = $brand_ads['adtext'];
$link = $brand_ads['adurl'];
if(!empty($link))
{
	if(substr( $link, 0, 4 ) === "http")
	{
		$link = $link;
	}
	else
	{
		$link = 'http://'.$link;
	}
}
else
{
	$link = 'Not added';
}
$image = $brand_ads['adimage'];
if($image == 'undefined' || !file_exists('../web/'.$image) || $image == '')
{
	$image = $baseUrl.'/images/brandaware-demo.png';
}
?>
<div class="content-box bshadow">
	<div class="cbox-desc">
		<div class="side-travad brand-travad">
			<div class="travad-maintitle"><?=$adcatch?></div>
			<div class="imgholder">
				<img src="<?=$image?>">
			</div>
			<div class="descholder">
				<div class="travad-subtitle"><?=$text?></div>
				<a href="javascript:void(0)" onclick="viewAdSite('<?=$adid?>','<?=$link?>','click')" class="btn btn-primary btn-sm adbtn">Explore</a>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<?php if(!empty($trav_store_ads)){
$adid = $trav_store_ads['_id'];
$item_name = $trav_store_ads['post_title'];
$price = $trav_store_ads['trav_price'];
$actbtn = $trav_store_ads['adbtn'];
$link = $trav_store_ads['adurl'];
if(!empty($link))
{
	if(substr( $link, 0, 4 ) === "http")
	{
		$link = $link;
	}
	else
	{
		$link = 'http://'.$link;
	}
}
else
{
	$link = 'Not added';
}
if(!isset($trav_store_ads['image']) || empty($trav_store_ads['image']))
{
	$image = $baseUrl.'/images/travitem-default.png';
}
else
{
	$image = substr($trav_store_ads['image'],0,-1);
}
?>
<div class="content-box bshadow">
	<div class="cbox-desc">
		<div class="side-travad travstore-travad">
			<div class="imgholder">
				<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?=$image?>">
			</div>
			<div class="descholder">
				<div class="travad-title"><?=$item_name?></div>
				<div class="travad-price">$<?=$price?></div>
				<div class="travad-info">Sponsered by <a href="<?=$link?>"><?=$link?></a></div>
				<a href="javascript:void(0)" onclick="viewAdSite('<?=$adid?>','<?=$link?>','click')" class="btn btn-primary btn-sm adbtn"><?=$actbtn?></a>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<?php if(!empty($page_end_ads)){
$adid = $page_end_ads['_id'];
$page_idnew = $page_end_ads['adid'];
$page_img = $this->context->getpageimage($page_idnew);
$page_detailsnew = Page::Pagedetails($page_idnew);
$endorse_count = PageEndorse::getAllEndorseCount($page_idnew);
$header = $page_end_ads['adheadeline'];
$title = $page_end_ads['adtext'];
$image = $page_end_ads['adimage'];
if($image == 'undefined' || !file_exists('../web/'.$image))
{
	$image = $baseUrl.'/images/pagead-endorse-demo.png';
}
?>
<div class="content-box bshadow">
	<div class="cbox-desc">
		<div class="side-travad page-travad">
			<div class="travad-maintitle"><img src="<?=$page_img?>"><h6><?=$page_detailsnew['page_name']?></h6><span>Sponsored</span></div>
			<div class="imgholder">
				<img src="<?=$image?>"/>
			</div>
			<div class="descholder">
				<div class="travad-title"><?=$header?></div>
				<div class="travad-subtitle"><?=$title?></div>
				<div class="travad-info"><?=$endorse_count?> people endorsed this page</div>
				<a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn" onclick="viewAdObjSite('<?=$adid?>','<?=$page_idnew?>','action','pageendorse')">Endorse</a>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<?php if(!empty($web_conversion)){
$adid = $web_conversion['_id'];
$title = $web_conversion['adtitle'];
$logo = $web_conversion['adlogo'];
if($logo == 'undefined' || !file_exists('../web/'.$logo) || $logo == '')
{
	$logo = $baseUrl.'/images/demo-business.jpg';
}
$header = $web_conversion['adheadeline'];
$text = $web_conversion['adtext'];
$adbtn = $web_conversion['adbtn'];
$link = $web_conversion['adurl'];
if(!empty($link))
{
	if(substr( $link, 0, 4 ) === "http")
	{
		$link = $link;
	}
	else
	{
		$link = 'http://'.$link;
	}
}
else
{
	$link = 'Not added';
}
$image = $web_conversion['adimage'];
if($image == 'undefined' || !file_exists('../web/'.$image) || $image == '')
{
	$image = $baseUrl.'/images/webconversion-demo.png';
}
?>
<div class="content-box bshadow">
	<div class="cbox-desc">
		<div class="side-travad action-travad">
			<div class="travad-maintitle"><img src="<?=$logo?>"><h6><?=$title?></h6><span>Sponsored</span></div>
			<div class="imgholder">
				<img src="<?=$image?>"/>
			</div>
			<div class="descholder">
				<div class="travad-title"><?=$header?></div>
				<div class="travad-subtitle"><?=$text?></div>
				<a href="javascript:void(0)" onclick="viewAdSite('<?=$adid?>','<?=$link?>','click')" class="btn btn-primary adbtn"><?=$adbtn?></a>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<?php if(!empty($web_leads)){
$adid = $web_leads['_id'];
$title = $web_leads['adtitle'];
$logo = $web_leads['adlogo'];
if($logo == 'undefined' || !file_exists('../web/'.$logo) || $logo == '')
{
	$logo = $baseUrl.'/images/demo-business.jpg';
}
$header = $web_leads['adheadeline'];
$text = $web_leads['adtext'];
$adurl = $web_leads['adurl'];
$link = $web_leads['adurl'];
if(!empty($link))
{
	if(substr( $link, 0, 4 ) === "http")
	{
		$link = $link;
	}
	else
	{
		$link = 'http://'.$link;
	}
}
else
{
	$link = 'Not added';
}
$image = $web_leads['adimage'];
if($image == 'undefined' || !file_exists('../web/'.$image) || $image == '')
{
	$image = $baseUrl.'/images/webleads-demo.png';
}
?>
<div class="content-box bshadow">
	<div class="cbox-desc">
		<div class="side-travad weblink-travad">
			<div class="travad-maintitle"><img src="<?=$logo?>"><h6><?=$title?></h6><span>Sponsored</span></div>
			<div class="imgholder">
				<img src="<?=$image?>"/>
			</div>
			<div class="descholder">
				<div class="travad-title"><?=$header?></div>
				<div class="travad-subtitle"><?=$text?></div>
				<a href="javascript:void(0)" onclick="viewAdSite('<?=$adid?>','<?=$link?>','click')"><i class="mdi mdi-earth"></i><span><?=$adurl?></span></a>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<?php if(!empty($page_like_ads)){
$adid = $page_like_ads['_id'];
$page_idnew = $page_like_ads['adid'];
$page_img = $this->context->getpageimage($page_idnew);
$page_detailsnew = Page::Pagedetails($page_idnew);
$like_count = Like::getLikeCount($page_idnew);
$header = $page_like_ads['adheadeline'];
$title = $page_like_ads['adtext'];
$image = $page_like_ads['adimage'];
if($image == 'undefined' || !file_exists('../web/'.$image) || $image == '')
{
	$image = $baseUrl.'/images/pagead-demo.png';
}
$likeexist = Like::getPageLike($page_idnew);
if($likeexist){$likestatus = 'Liked';}
else{$likestatus = 'Like';}
?>
<div class="content-box bshadow">
	<div class="cbox-desc">
		<div class="side-travad page-travad">
			<div class="travad-maintitle"><img src="<?=$page_img?>"><h6><?=$page_detailsnew['page_name']?></h6><span>Sponsored</span></div>
			<div class="imgholder">
				<img src="<?=$image?>"/>
			</div>
			<div class="descholder">
				<div class="travad-title"><?=$header?></div>
				<div class="travad-subtitle"><?=$title?></div>
				<div class="travad-info">
				<span class="likecount_<?=$page_idnew?>">
					<?php if($like_count > 0){ ?><?=$like_count?> liked this page
					<?php } else { ?>Become a first to like this page<?php } ?>
				</span>
				</div>
				<?php if($page_detailsnew['created_by'] == $uid) { ?>
				<a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn" onclick="viewAdObjSite('<?=$adid?>','<?=$page_idnew?>')"><span class="likestatus_<?=$page_idnew?>">Admin</span></a>
				<?php } else { ?>
				<a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn" onclick="viewAdObjSite('<?=$adid?>','<?=$page_idnew?>','action','pagelikes')"><i class="zmdi zmdi-thumb-up"></i><span class="likestatus_<?=$page_idnew?>"><?=$likestatus?></span></a>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<?php } ?>
<!-- end side advertise -->