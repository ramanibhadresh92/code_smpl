<?php 
if($lazyhelpcount > $total_count) {
} else {
  if(!empty($tourslist)) {
  foreach($tourslist as $tours) { 
    $categories = array();
    $_id = (string)$tours['_id'];
    $categories[] = isset($tours['Category1']) ? $tours['Category1'] : '';
    $categories[] = isset($tours['Category2']) ? $tours['Category2'] : '';
    $categories[] = isset($tours['Category3']) ? $tours['Category3'] : '';
    $categories = array_values(array_filter($categories));
    $Country = $tours['Country'];
    $Region = $tours['Region'];
    $City = $tours['City'];
    $Introduction = $tours['Introduction'];
  ?>    
  <li class="tourbox <?=$_id?>">
      <a href="<?=$tours['ProductURL']?>" target="_new">
      <div class="hotel-li expandable-holder dealli mobilelist">
        <div class="summery-info">
          <span class="imgholder"><img src="<?=str_replace('/graphicslib','/graphicslib/thumbs674x446/',$tours['ProductImage'])?>"/></span>
           <div class="descholder">
              <a href="javascript:void(0)" class="expand-link" onclick="mng_expandable(this,'hasClose')">
                 <h4><?=$tours['ProductName']?></h4>
                 <div class="clear"></div>
                 <div class="reviews-link">
                    <span class="review-count"><?=$tours['Group1']?></span>
                 </div>
                 <span class="address"><?=$City?>,&nbsp;<?=$Country?></span>
                 <span class="distance-info"><p class="dpara"><i class="mdi mdi-format-quote-open"></i><?=$Introduction?></p></span>
                 <div class="more-holder">
                    <div class="tagging" onclick="explandTags(this)">
                       Categories:
                       <?php
                       foreach ($categories as $categories_s) {
                           echo '<span>'.$categories_s.'</span>';
                       }
                       ?>
                    </div>
                 </div>
              </a>
              <div class="info-action">
                 <span class="duration">3 hours ( aprx. )</span>
                 <div class="clear"></div>
                 <span class="price">$<?=$tours['PriceUSD']?></span>
                 <div class="clear"></div>
                 <a href="javascript:void(0)" class="booknow-btn waves-effect waves-light">Book Now <i class="mdi mdi-chevron-right"></i></a>
              </div>
           </div>
        </div>
      </div>
      </a>
  </li>
  <?php 
  }
  } else {
    ?>
    <li class="tourbox"></li>
    <?php
  } 
}
?>
<?php
exit; ?>