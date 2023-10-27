<?php
use yii\helpers\Url; 
use frontend\models\Homestay;
$session = Yii::$app->session;
$status = $session->get('status');
$user_id = (string)$session->get('user_id');
$post = Homestay::find()->Where(['not','flagger', "yes"])->asarray()->all();
$currency_icon = array('USD' =>'<i class="mdi mdi-currency-usd"></i>', 'EUR' =>'<i class="mdi mdi-currency-eur"></i>', 'YEN' =>'<i class="mdi mdi-currency-cny"></i>', 'CAD' =>'Can<i class="mdi mdi-currency-usd"></i>', 'AUE' =>'AUE');
if(!empty($post)) { 
foreach ($post as $key => $post_s) { 

$postId = (string)$post_s['_id'];	
$postUId = $post_s['user_id'];	
$title = $post_s['title'];	
$property_type = $post_s['property_type'];	
$guests_room_type = $post_s['guests_room_type'];	
$bath = $post_s['bath'];	
$guest_type = $post_s['guest_type'];	 
$homestay_location = $post_s['homestay_location'];	
$adult_guest_rate = $post_s['adult_guest_rate'];	
$description = $post_s['description'];	
$currency = strtoupper($post_s['currency']);	
$rules = $post_s['rules'];	
$images = $post_s['images'];	
$images = explode(',', $images);
$images = array_values(array_filter($images));
$main_image = $images[0];
$profile = $this->context->getimage($postUId,'thumb');
$name = $this->context->getuserdata($postUId,'fullname');
$isOwner = false;
if($user_id == $postUId) {
	$isOwner = true;
}
?>

<div class="col l3 m6 s12 wow slideInLeft"> 
	<div class="tour-box">
	   <span class="imgholder">
			<a href="<?php echo Url::to(['homestay/detail', 'id' => $postId]); ?>">
				<img src="<?=$main_image?>">
			</a>
			<?php if($isOwner) { ?>
			<i class="mdi mdi-delete" data-id="<?=$postId?>" onclick="deleteHomestay('<?=$postId?>')"></i>
			<?php } ?>
			<div class="price-tag">
				<span>
					<?php
					if(array_key_exists($currency, $currency_icon)) {
						echo $currency_icon[$currency].$adult_guest_rate;
					}
					?>
				</span>
			</div>
	   </span>
	   <span class="descholder">
	      <a href="javascript:void(0)">
	         <img src="<?=$profile?>" alt="">
	      </a>
	      <small class="dine-hosttext">Hosted by <a dir="auto" href=""><?=$name?></a> in <?=$homestay_location?></small>
	      <div class="dine-eventtags">
	      	<div class="tag-inner">one room <?=$bath?> bath</div>
	      </div>
	      <a class="dine-eventtitle" dir="auto" href=""><?=$title?></a>
	      <div class="dine-rating pt-10 center">
	         <i class="mdi mdi-star"></i>
	         <i class="mdi mdi-star"></i>
	         <i class="mdi mdi-star"></i>
	         <i class="mdi mdi-star"></i>
	         <i class="mdi mdi-star"></i>
	      </div>
	   </span>
	    <?php if($status == '10') { ?>  
        <a href="javascript:void(0)" class="dropdown-toggle dropdown-button prevent-gallery homestayflagger" data-activates='<?=$postId?>' data-id='<?=$postId?>'>
           <i class="mdi mdi-flag"></i>
        </a>
        <ul id='<?=$postId?>' class="dropdown-content">
            <li class="prevent-gallery"> <a href="javascript:void(0)" data-id="<?=$postId?>" data-module="homestay" onclick="flagpost(this)">Flag post</a> </li>
        </ul>
        <?php } ?>
	</div>
</div>
<?php } } ?>