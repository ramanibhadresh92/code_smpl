<?php
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<div class="section-holder moreinfo-outer nice-scroll">
	<div class="moreinfo-box">
		<a href="javascript:void(0)" onclick="hideBookmarkDetail(this)" class="backarrow"><i class="mdi mdi-arrow-left-bold-circle"></i></a>
		<div class="infoholder nice-scroll">
			<div class="imgholder"><img src="<?=$baseUrl?>/images/hotel1.png"/></div>
			<div class="descholder">
				<h4>The Guest House</h4>
				<div class="clear"></div>
				<div class="reviews-link">
					<span class="checks-holder">
						<i class="mdi mdi-star active"></i>
						<i class="mdi mdi-star active"></i>
						<i class="mdi mdi-star active"></i>
						<i class="mdi mdi-star active"></i>
						<i class="mdi mdi-star"></i>
						<label>34 Reviews</label>
					</span>
				</div>
				<span class="distance-info">Middle Eastem &amp; African, Mediterranean</span>
				<div class="clear"></div>
				<div class="more-holder">
					<ul class="infoul">
						<li>
							<i class="zmdi zmdi-pin"></i>
							132 Brick Lane | E1 6RU, Japan E1 6RU, Japan
						</li>
						<li>
							<i class="mdi mdi-phone"></i>
							+44 20 7247 8210
						</li>
						<li>
							<i class="mdi mdi-earth"></i>
							http://www.yourwebsite.com
						</li>
						<li>
							<i class="mdi mdi-clock-outline"></i>
							Today, 12:00 PM - 12:00 AM
						</li>
						<li>
							<i class="mdi mdi-certificate "></i>
							Ranked #1 in Japan Hotels
						</li>										
					</ul>
					<div class="tagging" onclick="explandTags(this)">
						Popular with:
						<span>Budget</span>
						<span>Foodies</span>
						<span>Family</span>
					</div>
				</div>
			</div>
		</div>						
	</div>
</div>
<?php exit;?>