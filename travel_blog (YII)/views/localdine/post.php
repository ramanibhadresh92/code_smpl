<?php
use yii\helpers\Url; 
use frontend\models\Localdine;
$session = Yii::$app->session;
$status = $session->get('status');
$user_id = (string)$session->get('user_id');
$Localdine = Localdine::find()->Where(['not','flagger', "yes"])->asarray()->all();
$isEmpty = true;
$currency_icon = array('USD' =>'<i class="mdi mdi-currency-usd"></i>', 'EUR' =>'<i class="mdi mdi-currency-eur"></i>', 'YEN' =>'<i class="mdi mdi-currency-cny"></i>', 'CAD' =>'Can<i class="mdi mdi-currency-usd"></i>', 'AUE' =>'AUE');
if(!empty($Localdine)) { 
   foreach ($Localdine as $Localdine_s) { 
      $localdine_id = (string)$Localdine_s['_id'];
      $localdine_user_id = $Localdine_s['user_id'];
      $localdine_title = $Localdine_s['title'];
      $localdine_cuisine = $Localdine_s['cuisine'];
      $localdine_min_guests = $Localdine_s['min_guests'];
      $localdine_max_guests = $Localdine_s['max_guests'];
      $localdine_description = $Localdine_s['description'];
      $localdine_dish_name = $Localdine_s['dish_name'];
      $localdine_summary = $Localdine_s['summary'];
      $localdine_whereevent = $Localdine_s['whereevent'];
      $localdine_images = $Localdine_s['images'];
      $localdine_event_type = $Localdine_s['event_type'];
      $currency = $Localdine_s['currency'];
      $meal = $Localdine_s['meal'];
      $localdine_images = explode(',', $localdine_images);
      $localdine_images = array_values(array_filter($localdine_images));
      $main_image = $localdine_images[0];
      $created_at = $Localdine_s['created_at'];
      $profile = $this->context->getimage($localdine_user_id,'thumb');
      $localdine_u_name = $this->context->getuserdata($localdine_user_id,'fullname');
      $isEmpty = false;
      $isOwner = false;
      if($user_id == $localdine_user_id) {
         $isOwner = true;
      }
      ?>
      <div class="col l3 m6 s12 wow slideInLeft">
         <div class="tour-box">
            <span class="imgholder">
               <a href="<?php echo Url::to(['localdine/detail', 'id' => $localdine_id]); ?>">
                  <img src="<?=$main_image?>">
               </a>
                  <?php if($isOwner) { ?>
                  <i class="mdi mdi-delete" data-id="<?=$localdine_id?>" onclick="deleteLocaldine('<?=$localdine_id?>')"></i>
                  <?php } ?>
               <div class="price-tag">
                  <?php
                  if(array_key_exists($currency, $currency_icon)) {
                     echo $currency_icon[$currency].$meal;
                  }
                  ?>
               </div>
            </span>
            <span class="descholder">
               <a href="">
                  <img src="<?=$profile?>" alt="">
               </a>
               <small class="dine-hosttext">Hosted by <a dir="auto" href=""><?=$localdine_u_name?></a>
               <?php if($localdine_whereevent != '') {
                  echo 'in '.$localdine_whereevent;
               } ?>
               </small>
               <div class="dine-eventtags">
                  <div class="tag-inner"><?=ucfirst(strtolower($localdine_event_type))?></div>
               </div>
               <a class="dine-eventtitle" dir="auto" href=""><?=$localdine_title?></a>
               <div class="dine-rating pt-20 center">
                  <i class="mdi mdi-star"></i>
                  <i class="mdi mdi-star"></i>
                  <i class="mdi mdi-star"></i>
                  <i class="mdi mdi-star"></i>
                  <i class="mdi mdi-star"></i>
               </div>
            </span>
            <?php if($status == '10') { ?>  
            <a href="javascript:void(0)" class="dropdown-toggle dropdown-button prevent-gallery localdineflagger" data-activates='<?=$localdine_id?>' data-id='<?=$localdine_id?>'>
               <i class="mdi mdi-flag"></i>
            </a>
            <ul id='<?=$localdine_id?>' class="dropdown-content">
                <li class="prevent-gallery"> <a href="javascript:void(0)" data-id="<?=$localdine_id?>" data-module="localdine" onclick="flagpost(this)">Flag post</a> </li>
            </ul>
            <?php } ?>
         </div>
      </div>
      <?php 
   }
} 

if($isEmpty) { ?>
<div class="joined-tb">
    <i class="mdi mdi-file-outline"></i>
    <p>No local dine found.</p>
</div>
<?php } ?>
