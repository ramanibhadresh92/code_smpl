<div class="wallcontent-column">
	<div class="sidebar-stuff">
		<div class="content-box bshadow rightmap rp_map" id="rp_map">								
			<div class="cbox-desc">
				<div class="placeintro-side width-100">
					<h5><?=$place?></h5>
					<div class="map-holder"> 
					<?php $this->context->GetMap('japan');?>
					<?php /* 
						<iframe src="https://maps.google.it/maps?q=<?=$placetitle?>&output=embed" width="600" height="450" frameborder="0"  allowfullscreen></iframe> */ ?>
					</div>
				</div>
			</div>
		</div>
		<div class="content-box bshadow rp_travellers" id="rp_travellers">
			<div class="cbox-title nborder">
				<i class="mdi mdi-airplane"></i>
				<a href="javascript:void(0)" onclick="openDirectTab('places-travellers')">People Travelling to <?=$placefirst?></a>
			</div>
			<div class="cbox-desc">
				<div class="connect-list grid-list">
					<div class="row" id='p_travellers'>
					</div>
				</div>
			</div>
		</div>
		<div class="content-box bshadow rp_locals" id="rp_locals">
			<div class="cbox-title nborder">
				<i class="mdi-map"></i>
				<a href="javascript:void(0)" onclick="openDirectTab('places-locals')"><?=$placefirst?> Locals</a>
			</div>
			<div class="cbox-desc">
				<div class="connect-list grid-list">
					<div class="row" id="p_locals">
					</div>
				</div>  
			</div>
		</div>
		<div class="content-box bshadow rp_reviews" id="rp_reviews">
			<div class="cbox-title nborder">
				<i class="zmdi zmdi-view-list-alt zmdi-hc-lg"></i>
				<a href="javascript:void(0)" onclick="openDirectTab('places-reviews')"><?=$placefirst?> Reviews</a>
			</div>
			<div class="cbox-desc" id="p_reviews">
			</div>
		</div>
   		<div class="content-box bshadow rp_hotel_deal" id="rp_hotel_deal">								
			<div class="cbox-title nborder">
				<i class="mdi mdi-office-building"></i>
				<a href="javascript:void(0)" class="allhotels" onclick="openDirectTab('places-lodge')">Hotel Deals in <?=$placefirst?></a>
			</div>
			<div class="cbox-desc">
				<div class="places-dealsad">
					<ul>
						<li>
							<center><div class="lds-css ng-scope"> <div class="lds-rolling lds-rolling100"> <div></div> </div></div></center>
						</li>
					</ul>
				</div>
			</div>
		</div>  
		<div class="content-box bshadow rp_recent_questions" id="rp_recent_questions">
			<div class="cbox-title nborder">
				<i class="zmdi zmdi-help"></i>    
				<a href="javascript:void(0)" onclick="openDirectTab('places-ask')"><?=$placefirst?> Recent Questions</a>
			</div>
			<div class="cbox-desc">
				<div class="question-holder" id="p_ask">
				</div>    
			</div>
		</div>
		<div class="content-box bshadow rp_tips" id="rp_tips">														
			<div class="cbox-title nborder"> 
				<a href="javascript:void(0)" onclick="openDirectTab('places-tip')">Tips for <?=$placefirst?></a>
			</div>
			<div class="cbox-desc nsp"> 
				<div class="question-holder tips-holder" id="p_tip">
				</div>									
			</div>
		</div>
		<div class="places-travad rp_booking" id="rp_booking">
			<a href="javascript:void(0)">
				<img src="<?=$baseUrl?>/images/booking-ad.jpg"/>
			</a>    
		</div>
		<div class="content-box bshadow" id="rp_place_explore">
			<img src="" class="fullimg" id="place_explore"/>
			<div class="cbox-title nborder">
				<a href="javascript:void(0)" onclick="openDirectTab('places-all')">Explore <?=$placefirst?></a>
			</div>
			<div class="cbox-desc">
				<div class="explore-box">
					<ul class="explore-list">
						<li class="allhotels"><a href="javascript:void(0)" onclick="openDirectTab('places-lodge')"><i class="mdi mdi-menu-right"></i>Hotels</a></li>
						<li class="allrest"><a href="javascript:void(0)" onclick="openDirectTab('places-dine')"><i class="mdi mdi-menu-right"></i>Restaurants</a></li>
						<li class="allthings"><a href="javascript:void(0)" onclick="openDirectTab('places-todo')"><i class="mdi mdi-menu-right"></i>Attractions</a></li>
						<li class="allevents"><a href="javascript:void(0)" onclick="openDirectTab('places-events')"><i class="mdi mdi-menu-right"></i>Events</a></li>
					</ul>
				</div>
			</div>
		</div>
		<div class="content-box bshadow adcontainerRight" id="adcontainerRight">
			<div class="cbox-desc">
				<iframe marginwidth="0" marginheight="0" allowtransparency="true" scrolling="no" style="visibility: visible; height: 862px;" name='{"name": "master-1", "master-1": {"container": "adcontainerRight", "linkTarget": "_blank", "lines": 3, "colorText": "#666666", "colorTitleLink": "#0088cc", "colorBackground": "#ffffff", "fontSizeTitle": "14px", "adsLabel": false, "adIconLocation": "ad-left", "domainLinkAboveDescription": true, "detailedAttribution": true, "type": "ads", "columns": 1, "horizontalAlignment": "left", "resultsPageQueryParam": "query"} }' id="master-1" src="https://www.google.com/afs/ads?q=Hotels%20in%20<?=$place?>&amp;adpage=1&amp;r=m&amp;fexp=21404&amp;client=pub-3667005479230723&amp;channel=5999909305&amp;adtest=off&amp;type=0&amp;oe=UTF-8&amp;ie=UTF-8&amp;format=n4&amp;ad=n4&amp;nocache=5881491977752232&amp;num=0&amp;output=uds_ads_only&amp;v=3&amp;adext=as1%2Csr1&amp;bsl=10&amp;u_his=1&amp;u_tz=330&amp;dt=1491977752236&amp;u_w=1366&amp;u_h=768&amp;biw=1349&amp;bih=605&amp;psw=1349&amp;psh=9337&amp;frm=0&amp;uio=uv3cs1vp1sl1sr1st14va1da1-&amp;jsv=13774&amp;rurl=https%3A%2F%2Fwww.iaminjapan.com#master-1" width="100%" frameborder="0">
				</iframe>
			</div>
		</div>
	</div>
</div>