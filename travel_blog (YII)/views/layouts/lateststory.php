<?php
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<div class="content-box bshadow">
    <div class="cbox-title">
        Latest Stories
    </div>
    <div class="cbox-desc">
        <div class="lateststory-list">
            <ul>
                <li>
                    <div class="lateststory">
                        <div class="imgholder"><img src="<?=$baseUrl?>/images/album1.png"/></div>
                        <div class="descholder">
                            <h6><a href="javascript:void(0)">
                                <span>24 Hours in Room</span>
                                <i class="mdi mdi-chevron-right"></i>
                            </a></h6>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="lateststory">
                        <div class="imgholder"><img src="<?=$baseUrl?>/images/album2.png"/></div>
                        <div class="descholder">
                            <h6><a href="javascript:void(0)">
                                <span>Discover Ho Chi Minh City - A City of</span>
                                <i class="mdi mdi-chevron-right"></i>
                            </a></h6>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="lateststory">
                        <div class="imgholder"><img src="<?=$baseUrl?>/images/album3.png"/></div>
                        <div class="descholder">
                            <h6><a href="javascript:void(0)">
                                <span>Most interesting borders of world</span>
                                <i class="mdi mdi-chevron-right"></i>
                            </a></h6>
                        </div>
                    </div>
                </li>
            </ul>
            <span class="morelink">
                <a href="javascript:void(0)"><i class="mdi mdi-chevron-right"></i> More Stories</a>
            </span>
        </div>
    </div>
</div>