<?php
use yii\helpers\Url; 
use frontend\models\Camping;
$session = Yii::$app->session;
$status = $session->get('status');
$user_id = (string)$session->get('user_id');
$isEmpty = true; 
$currency_icon = array('USD' =>'<i class="mdi mdi-currency-usd"></i>', 'EUR' =>'<i class="mdi mdi-currency-eur"></i>', 'YEN' =>'<i class="mdi mdi-currency-cny"></i>', 'CAD' =>'Can<i class="mdi mdi-currency-usd"></i>', 'AUE' =>'AUE');
$Camping = Camping::find()->Where(['not','flagger', "yes"])->asarray()->all();
if(!empty($Camping)) { 
	foreach ($Camping as $Camping_s) {
	$camping_id = (string)$Camping_s['_id'];
	$camping_uid = $Camping_s['user_id'];
	$camping_title = $Camping_s['title']; 
  $camping_currency = $Camping_s['currency'];
  $camping_rate = $Camping_s['rate'];
	$camping_images = $Camping_s['images'];
	$camping_images = explode(',', $camping_images);
	$camping_images = array_values(array_filter($camping_images));
	$main_image = $camping_images[0];
	$url = '../web/uploads/camping/'; 

	if(!file_exists($main_image)) {
		$main_image = $baseUrl.'/images/home-tour1.jpg';
	}

  $isOwner = false;
  if($user_id == $camping_uid) {
    $isOwner = true;
  }
?>
<div class="col l3 m6 s12 wow slideInLeft">
    <div class="tour-box">
       <span class="imgholder">
          <a href="<?php echo Url::to(['camping/detail', 'id' => $camping_id]); ?>">
          	<img src="<?=$main_image?>">
          </a>
          <?php if($isOwner) { ?>
          <i class="mdi mdi-delete" data-id="<?=$camping_id?>" onclick="deleteCamping('<?=$camping_id?>')"></i>
          <?php } ?>
          <div class="price-tag">
            <span>
            <?php
            if(array_key_exists($camping_currency, $currency_icon)) {
              echo $currency_icon[$camping_currency].$camping_rate;
            }
            ?>
            </span>
          </div>
       </span>
       <span class="descholder pb-0 pt-10">
          <a class="dine-eventtitle" dir="auto" href=""><?=$camping_title?></a>
       </span>
       <div class="camping-footer">
          <a href=""><i class="mdi mdi-map-marker"></i> View Map</a>
          <a href="<?php echo Url::to(['camping/detail', 'id' => $camping_id]); ?>" class="right">Read More</a>
       </div>
       <?php if($status == '10') { ?>  
        <a href="javascript:void(0)" class="dropdown-toggle dropdown-button prevent-gallery campingflagger" data-activates='<?=$camping_id?>' data-id='<?=$camping_id?>'>
           <i class="mdi mdi-flag"></i>
        </a>
        <ul id='<?=$camping_id?>' class="dropdown-content">
            <li class="prevent-gallery"> <a href="javascript:void(0)" data-id="<?=$camping_id?>" data-module="camping" onclick="flagpost(this)">Flag post</a> </li>
        </ul>
        <?php } ?>
    </div>
 </div>
 <?php } 
} ?>