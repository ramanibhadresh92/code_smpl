<?php 
use frontend\assets\AppAsset;
use frontend\models\PostForm;
use frontend\models\PlaceDiscussion;
use frontend\models\Travelbuddytrip;
use frontend\models\Gallery;
use frontend\models\Destination;
use frontend\models\PlaceTip;
use frontend\models\PlaceReview;
use frontend\models\PlaceAsk;
use frontend\models\LoginForm;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$user_id = (string)$session->get('user_id');
$place = Yii::$app->params['place'];
$placetitle = Yii::$app->params['placetitle'];
$placefirst = Yii::$app->params['placefirst'];
$lat = Yii::$app->params['lat'];
$lng = Yii::$app->params['lng'];      

$placetitleLabel = '';      
if($placetitle != '') {
    $placetitleLabel = explode(",", $placetitle);
    if(count($placetitleLabel) >1) {
        $first = reset($placetitleLabel);
        $last = end($placetitleLabel);
        $placetitleLabel = $first.', '.$last;
    } else {
        $placetitleLabel = implode(", ", $placetitleLabel);
    } 
}

$discussioncount = PlaceDiscussion::getPlaceReviewsCount($place,'discussion');
$tipscount = PlaceTip::getPlaceReviewsCount($place,'tip'); 
$getpplacereviewscount = PlaceReview::getPlaceReviewsCount($place,'reviews');
$questionscount = count(PlaceAsk::getPlaceReviews($place,'ask','all'));
$photostream = 0;
$gallery = Gallery::find()->where(['type' => 'places'])->andWhere(['not','flagger', "yes"])->asarray()->all(); 
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
    	$photostream++;
    }
} 

$Locals = LoginForm::find()->where(['like','city', $placetitle])->andwhere(['status'=>'1'])->asarray()->all();
if(empty($Locals))
{
    $placetitle = str_replace(',',' -',$placetitle);
    $Locals = LoginForm::find()->where(['like','city',$placetitle])->andwhere(['status'=>'1'])->asarray()->all();
    if(empty($Locals))
    { 
        if(substr( $placetitle, 0, 14 ) === "Japan District")
        {
            $placetitle = "Japan";
        }
        $Locals = LoginForm::find()->where(['like','city',$placetitle])->andwhere(['status'=>'1'])->asarray()->all();
    } 
}

$Travellers = Travelbuddytrip::gettripplaecsdata($placetitle, $placefirst, $user_id);
$getdest = Destination::getDestUsers($placetitle,'future', $user_id);
$merged = array_merge($Travellers, $getdest);
$Travellers = array_values(array_filter($merged));
$userIMAGE = $this->context->getimage($user_id,'thumb');
?> 
<div class="social-section profile-box detailBox">
    <div class="user-profile">
        <div class="avatar-left">
            <img src="<?=$userIMAGE?>">
        </div>
        <div class="avatar-middle">
            <img src="<?=$userIMAGE?>">
        </div>
        <div class="avatar-right">
            <img src="<?=$userIMAGE?>">
        </div>
    </div>
    
	<div class="item titlelabel user-info discussiondetailBox">
    	<p class="center-align u-name mb0">Cityguide for <?=$placefirst?> (<span class="count">0</span>)</p>
    	<p class="user-desg center-align"></p>
    </div>

	<div class="item profile-info row mx-0 width-100">
		<div class="sub-item">
            <h5 class="">Discussion</h5> 
            <p class=""><?=$discussioncount?></p>
        </div>
		<div class="sub-item">
			<h5 class="">Tips</h5>
			<p class=""><?=$tipscount?></p>
		</div>
		<div class="sub-item">
			<h5 class="">Reviews</h5>
			<p class=""><?=$getpplacereviewscount?></p>
		</div>
		<div class="sub-item">
			<h5 class="">Questions</h5>
			<p class=""><?=$questionscount?></p>
		</div>
		<div class="sub-item">
			<h5 class="">Photostream</PB_photosh5>
			<p class=""><?=$photostream?></p>
		</div>
		<div class="sub-item">
			<h5 class="">Locals</h5>
			<p class=""><?=count($Locals)?></p>
		</div>
		<div class="sub-item">
			<h5 class="">Travellers</h5>
			<p class=""><?=count($Travellers)?></p>
		</div>
	</div>	
</div>