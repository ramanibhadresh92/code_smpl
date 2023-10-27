<div class="section-holder nice-scroll">
	<div class="secopt">
		<a href="javascript:void(0)" class="backarrow" onclick="showLayer(this,'alltrip')"><i class="di mdi-chevron-left"></i></a>
		<a href="javascript:void(0)" class="left iconlink editlink" onclick="showLayer(this,'edittrip')" data-tripid="<?=$tripid?>"><i class="mdi mdi-pencil-box-outline"></i> Edit Trip</a>
		<a href="javascript:void(0)" class="right iconlink deletelink" onclick="deletetrip(this,'<?=$stops['_id']?>')"><i class="zmdi zmdi-delete"></i> Delete Trip</a>
	</div>
	<div class="secdesc">
		<div class="trip-summery">
			<div class="triptitle">
				<div class="itemicon"><i class="mdi mdi-airplane"></i></div>
				<h6><?=$stops['trip_name']?></h6>
				<?php 
				$trip_distance = $this->context->getdistance($stops['_id'],'trip');
				if($trip_distance == 'No'){$trip_distance = '-';}
				?>
				<?php if($trip_distance != '-'){ ?>
				<span class="sub"><?=$trip_distance?> km / <?=((int)str_replace(',','',$trip_distance)*0.62)?> miles</span>
				<?php } ?>
			</div>
			<div class="map-thumb">
				<div  id="tripmap" class="map-area"></div>
				<a href="javascript:void(0)" onclick="openDetailTripMap(this)">&nbsp;</a>
			</div>
			<div class="tripdetail">
				<div class="starttrip">
					<span>Trip starts on -</span> <?=$stops['trip_start_date']?>
					<br/>
					<span>Trip starts from -</span> <?=$stops['start_from']?>
				</div>
				<ul class="tripstops-list">
					<?php $stop = explode('**',$stops['end_to']);
					$stop = array_filter($stop);
						{ $i = 1; foreach ($stop as $name) { ?>
					<li>
						<div class="tripstop">
							<div class="title">
								<span class="numbering"><?=$i?></span>
								<h5><a href="javascript:void(0)"><?=$name?></a></h5>
							</div>
							<div class="morelinks">
								<a href="javascript:void(0)" class="left" onclick="openAccommodations(this,'<?=$name?>')">Show accommodations</a>
								<a href="javascript:void(0)" class="bookmark-link active"><i class="mdi mdi-bookmark-o"></i></a>
							</div>
						</div>
					</li>
					<?php $i++;} } ?>
				</ul>
				<div class="dividing-line"></div>
				<?php if(isset($stops['trip_summary']) && !empty($stops['trip_summary'])){?>
				<div class="drow">
					<label>Your trip summery</label>
					<p><?=$stops['trip_summary']?></p>
				</div>
				<?php } ?>
				<div class="drow half">
					<div class="cap-holder">
						<label>Track Path</label>
					</div>
					<div class="comp-holder">
						<p><?=ucwords($stops['mapline'])?></p>
					</div>
				</div>
				<div class="drow half">
					<div class="cap-holder">
						<label>Path Color</label>
					</div>
					<div class="comp-holder">
						<div class="right">
							<ul class="color-palette">
								<li><a href="javascript:void(0)" class="colordot <?=$stops['tripcolor']?>"></a></li>
							</ul>
						</div>
					</div>
				</div>
				<div class="drow half">
					<div class="cap-holder" id="notescount"></div>
					<div class="comp-holder">
						<a href="javascript:void(0)" class="right" onclick="showSubLayer(this,'notes','addmode')">View Notes</a>
					</div>
				</div>
				<div class="drow half">
					<div class="cap-holder">
						<label>Who can view your trip details</label>
					</div>
					<div class="comp-holder">
						<div class="security-text">
							<?php
							if($stops['privacy'] == 'Private'){$type = 'lock';}
							if($stops['privacy'] == 'Connections'){$type = 'user';}
							else{$type = 'earth';}
							?>
							<i class="mdi mdi-<?=$type?>"></i> <?=$stops['privacy']?>
						</div>
					</div>
				</div>
			</div>
		</div>								
	</div>
</div>
<script>
$(document).ready(function(){
	notescount('<?=$stops['_id']?>');
});
</script>
<?php exit;?>