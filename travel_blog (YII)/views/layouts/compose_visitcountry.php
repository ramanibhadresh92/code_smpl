<div class="content_header">
  <button class="close_span waves-effect">
  <i class="mdi mdi-close mdi-20px material_close"></i>
  </button>
  <p class="selected_person_text">Select Country</p>
</div>
<div class="person_box">
    <div class="collection visit-country-list">
         <?php
         $sites = Yii::$app->params['sites'];
         foreach ($sites as $s_sites) {
            $s_sites_src = $s_sites['src'];
            $s_sites_name = $s_sites['name'];
            ?>
            <a href="<?=$s_sites_src?>" target="_blank" class="collection-item"><?=$s_sites_name?></a>
            <?php
         }
         ?>    
      </div>
</div>