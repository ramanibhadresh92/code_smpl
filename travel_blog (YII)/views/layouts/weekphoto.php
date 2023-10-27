<?php
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<div class="content-box bshadow" id="week_photo">	
    <div class="cbox-title">
        Photo of The Week
    </div>
    <div class="cbox-desc">	
        <div class="photo-week">
            <img src="<?=$baseUrl?>/images/gallrey3.jpg"/>
            <a href="javascript:void(0)">Mother Russia</a>
            <div class="by">by <span>hdmission</span></div>
        </div>
    </div>
</div> 