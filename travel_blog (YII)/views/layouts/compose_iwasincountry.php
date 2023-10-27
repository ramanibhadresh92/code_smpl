<div class="content_header">
  <button class="close_span waves-effect">
  <i class="mdi mdi-close mdi-20px material_close"></i>
  </button>
  <p class="selected_person_text">Select Destination</p>
</div>
<div class="person_box">
  <div class="collection visit-country-list">
    <?php
    $selfsites = Yii::$app->params['selfsites'];
    $selfIAM = Yii::$app->params['selfIAM'];
    foreach ($selfsites as $s_selfsites) {
        $s_selfsites_src = $s_selfsites['src'];
        $s_selfsites_name = $s_selfsites['name'];
        if($selfIAM != $s_selfsites_name) {
        ?>
        <a href="<?=$s_selfsites_src?>" target="_blank" class="collection-item"><?=$s_selfsites_name?></a>
        <?php
        }
    }
    ?>
  </div>
</div>