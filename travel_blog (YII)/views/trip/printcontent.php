<?php 
use frontend\assets\AppAsset;
use frontend\models\Trip;
use frontend\models\TripNotes;
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<html>
	<head>
		<meta charset="utf-8" />
		<title>I am in Japan</title>
		<style>

			@import url(https://fonts.googleapis.com/css?family=Roboto:300,400,500);

		   /* The rest of your CSS here */
		   .bodyclass{margin:0;padding:0;background:#fff;font-family:'Roboto';}
		   .bodyclass *{box-sizing:border-box;}
		   .maindiv{color: #353535; float:left; font-size: 13px;width:100%;text-align:center;padding:40px 0 0;}
		   .main-wrapper{width:720px;display:inline-block;padding:0 0 30px;}
		   .clear{clear:both}
		   .box{border:1px solid #ddd;margin:0 0 10px;}
		   .box-wrapper{background:#fff;padding:20px 0;text-align:left;width:600px;display:inline-block;}
		   .box-wrapper h3{font-size:20px;font-weight:500;text-align:center;margin:50px 0;display:inline-block;width:100%;}
		   .btmrow{color: #333;font-size: 13px;padding:0 20px;display:inline-block;width:100%}
		   .bottom{width:600px;display:inline-block;font-size:11px;padding:0 20px;}
		   .copyright{color: #777;text-align: left;}
		   .support{text-align: left;width: 100%;margin:5px  0 0;color:#777;}
		   		   
		   .trip-holder{padding:20px 0;display:inline-block;width:100%}
		   ul{list-style:none;}
		   ul.triplist{margin: 0;float: left;width: 100%;padding:0 20px;}
		   ul.triplist > li{float: left;width: 100%;padding: 20px 0 0;border-bottom: 1px solid #ddd;}
		   ul.triplist > li:first-child{padding-top: 0;}
		   ul.triplist > li:last-child{border:none;}
		   .tripitem{float: left;width: 100%;position: relative;padding-left: 30px;}
		   .tripitem h6{margin: 0;font-weight:normal;color: #464646;padding-right: 60px;font-size: 14px;font-weight:500;}
		   .tripicon{position: absolute;left: 0;top: 0;font-size: 17px;}
		   .tripicon img{display:inline-block;}
		   .starttrip{float: left;width: 100%;color: #999;font-size: 13px;line-height: 30px;margin: 0 0 20px;}
		   .starttrip span{color: #333;line-height: 30px;margin-right: 10px;font-size: 13px;}
		   .drow{clear: both;float: left;width: 100%;margin: 0 0 20px;}
		   .drow label {color: #313131;font-weight:500;font-size: 14px;margin: 0 0 10px;display:inline-block;}
		   .drow p {font-size: 13px;color: #999;margin: 0 0 10px;}
		   .notetext{color: #dbbb24;font-size:14px;}

		   ul.tripstops-list{padding: 10px 0 0;margin: 0;float: left;width: 100%;}
		   ul.tripstops-list > li{float: left;width: 100%;margin: 0 0 25px;}
		   .tripstop{width: 100%;float: left;position: relative;}
		   .tripstop .title{width:100%;float: left;}
		   .tripstop .title .numbering{width: 18px;height: 18px;text-align: center;font-size: 11px;background: #ddd;line-height: 18px;border-radius: 50%;position: absolute;top: 0;left: 0;}
		   .tripstop .title h5{margin: 0;line-height: 18px;font-size: 14px;font-weight: 500;}
		   .tripstop .title .dest-col{width: 33.33%;float: left;position: relative;padding-left:30px;}
		   .tripstop .title .date-col{width: 33.33%;float: left;position: relative;font-size:13px;color:#999;}
		   .tripstop .title .bm-col{width: 33.33%;float: right;position: relative;font-size:13px;color:#3399cc;text-align:right;}
		   .tripstop .bm-detail{margin:20px 0 0;width:100%;float: left;padding:0 0 0 30px;}
		   .tripstop .bm-detail span.tspan{width:auto;color:#3399cc;font-size:13px;margin:0 0 10px;float:left;}
		   .tripstop .bm-detail .strow{width:100%;float:left;margin:3px 0;}
		   .tripstop .bm-detail .strow span{width:29%;color:#999;font-size:13px;display:inline-block;}
		   
		   .mapholder{margin:10px 20px;float:left;padding:5px;background:#fff;border:1px solid #ddd;}
		   .mapholder img{width:100%;}
		   
		   .notedetails{width:100%;float:left;padding:10px 20px 0;}
		   .notedetails .notetext{font-size:15px;}
		   .notedetails ul.notelist{float:left;width:100%;padding:0;}
		   .notedetails ul.notelist > li{float:left;width:100%;margin:10px 0;}
		   .notedetails ul.notelist > li h5{font-weight:500;font-size:13px;margin:0 0 10px;}
		   .notedetails ul.notelist > li p{padding:10px 15px;border-radius:3px;border:1px solid #f3e89d;width:100%;margin:0;color:#999;}
		   
		</style>
	</head>
	<body class="bodyclass">
		<div  class="maindiv">
			<div class="main-wrapper">				
				<div class="clear"></div>
				<div class="box">
					<div class="box-wrapper">
						<h3>Trip Details</h3>						
						<div class="trip-holder">
							<ul class="triplist">
								<?php 
								if($which == 'empty') {
									$trips = Trip::getMyTrips($user_id);
									if($trips) {
										foreach($trips as $trip) {
											$tripid = $trip['_id'];
											$trip_name = $trip['trip_name'];
											$trip_summary = $trip['trip_summary'];
											$trip_start_date = $trip['trip_start_date'];
											$start_from = $trip['start_from'];
											$stop = explode('**',$trip['end_to']);
											$notescount = TripNotes::getNotesCount($tripid);
											$notes = TripNotes::getTripNotes($tripid);
								?>
								<li>
									<div class="tripitem">
										<div class="tripicon">
											<img src="<?=$baseUrl?>/images/tripicon.png"/>
										</div>
										<h6>
											<?=$trip_name?>
										</h6>
										<div class="starttrip">
											Trip starts on - <span><?=$trip_start_date?></span>
										</div>
										<div class="starttrip">
											Trip starts on - <span><?=$start_from?></span>
										</div>
										<div class="drow">
											<?php if(isset($trip_summary) && !empty($trip_summary)){ ?>
											<label>Your trip summery</label>
											<p>
											<?=$trip_summary?>
											</p>
											<?php } ?>
											<span class="notetext"><?=$notescount?> trip notes added</span>
										</div>
									</div>
									<ul class="tripstops-list">
										<?php $i = 1; foreach ($stop as $name) { ?>
										<li>
											<div class="tripstop">
												<div class="title">
													<div class="dest-col">
														<span class="numbering"><?=$i?></span>
														<h5><?=$name?></h5>
													</div>
													<div class="date-col">
														Arrives on 22-11-2016
													</div>
													<div class="bm-col">
														2 Bookmarks
													</div>
												</div>
											</div>
										</li>
										<?php $i++;} ?>
									</ul>
								</li>
								<?php } } else { ?>
									<?php $this->context->getnolistfound('notripfound'); ?>
								<?php } } else {
									$trips = Trip::getTripDetails($which);
									$tripid = $which;
									$trip_name = $trips['trip_name'];
									$trip_summary = $trips['trip_summary'];
									$trip_start_date = $trips['trip_start_date'];
									$start_from = $trips['start_from'];
									$stop = explode('**',$trips['end_to']);
									$stop = array_filter($stop);
									$notescount = TripNotes::getNotesCount($tripid);
									$notes = TripNotes::getTripNotes($tripid);
								?>
								<li>
									<div class="tripitem">
										<div class="tripicon">
											<img src="<?=$baseUrl?>/images/tripicon.png"/>
										</div>
										<h6>
											<?=$trip_name?>
										</h6>
										<div class="starttrip">
											Trip starts on - <span><?=$trip_start_date?></span>
										</div>
										<div class="starttrip">
											Trip starts on - <span><?=$start_from?></span>
										</div>
										<div class="drow">
											<?php if(isset($trip_summary) && !empty($trip_summary)){ ?>
											<label>Your trip summery</label>
											<p>
											<?=$trip_summary?>
											</p>
											<?php } ?>
											<span class="notetext"><?=$notescount?> trip notes added</span>
										</div>
									</div>
									<ul class="tripstops-list">
										<?php $i = 1; foreach ($stop as $name) { ?>
										<li>
											<div class="tripstop">
												<div class="title">
													<div class="dest-col">
														<span class="numbering"><?=$i?></span>
														<h5><?=$name?></h5>
													</div>
													<div class="date-col">
														Arrives on 22-11-2016
													</div>
													<div class="bm-col">
														2 Bookmarks
													</div>
												</div>
												<?php if($which != 'empty'){ ?>
												<div class="bm-detail">
													<span class="tspan">Bookmarks</span>
													<?php for($j=1;$j<3;$j++){ ?>
													<div class="strow">
														<span>XYZ Hotel</span>
														<span>999-999-999</span>
													</div>
													<?php } ?>
												</div>
												<?php } ?>
											</div>
										</li>
										<?php $i++;} ?>
									</ul>
								</li>
								<?php } ?>
							</ul>
							<?php if($which != 'empty'){
								if(!empty($notes)){ ?>
							<div class="notedetails">
								<span class="notetext">Trip Notes</span>
								<ul class="notelist">
									<?php
									foreach($notes as $note){
										$notetitle = $note['notetitle'];
										$notetext = $note['notetext'];
									?>
									<li>
										<h5><?=$notetitle?></h5>
										<p><?=$notetext?></p>
									</li>
									<?php } ?>
								</ul>
							</div>
							<?php } } ?>
						</div>
						<div class="btmrow">Thank you for using Iaminjapan!</div>
						<div class="btmrow">The Iaminjapan Team</div>
					</div>
				</div>
				<div class="clear"></div>
				<div class="bottom">
				   <div class="copyright">&copy;  www.iaminjapan.com All rights reserved.</div>
				   <div class="support">For support, you can reach us directly at <a href="csupport@iaminjapan.com" style="color:#4083BF">csupport@iaminjapan.com</a></div>
			   </div>
			</div>
		</div>
	</body>
</html>
<?php exit;?>