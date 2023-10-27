<a class="dropdown-button wasin-country wasin-dropdown" href="javascript:void(0)" data-activates="wasinCountry"><i class="zmdi zmdi-chevron-down mdi-30px"></i></a>
<a href="javascript:void(0)" class="compose_iwasinCountryAction wasin-country wasin-modal"><i class="zmdi zmdi-chevron-down mdi-30px"></i></a> 
<ul id="wasinCountry" class="dropdown-content">
<?php
$selfsites = Yii::$app->params['selfsites'];
$selfIAM = Yii::$app->params['selfIAM'];
foreach ($selfsites as $s_selfsites) {
   $s_selfsites_src = $s_selfsites['src'];
   $s_selfsites_name = $s_selfsites['name'];
   if($selfIAM != $s_selfsites_name) {
   ?>
   <li><a href="<?=$s_selfsites_src?>" target="_blank" class="collection-item"><?=$s_selfsites_name?></a></li>
   <?php
   }
}
?>     
</ul>
