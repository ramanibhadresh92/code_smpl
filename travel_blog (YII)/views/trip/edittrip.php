<div class="section-holder nice-scroll">
	<div class="sectitle">
		Edit Trip
		<a href="javascript:void(0)" class="right deletelink redicon" onclick="showLayer(this,'alltrip')"><i class="mdi mdi-close	"></i> Cancel</a>
	</div>
	<div class="secdesc">
		<div class="trip-summery">
			<div class="triptitle edit">
				<div class="itemicon"><i class="mdi mdi-airplane"></i></div>
				<div class="sliding-middle-out anim-area underlined fullwidth">
					<input type="text" placeholder="Name your trip" value="<?=$stops['trip_name']?>" id="trip_name">
				</div>
			</div>
			<div class="map-thumb">
				<div id="tripmap" class="map-area"></div>
				<a href="javascript:void(0)" onclick="openDetailTripMap(this)">&nbsp;</a>
			</div>
			<div class="tripdetail">
				<div class="starttrip">
					<span>Trip starts on -</span> <?=$stops['trip_start_date']?>
					<input type="hidden" id="addtripdate" value="<?=$stops['trip_start_date']?>"/>
					<input type="hidden" id="startfrom" value="<?=$stops['start_from']?>"/>
				</div>
				<span id="trip_stops">
					<ul class="tripstops-list">
					<?php 
					$stop = explode('**',$stops['end_to']);
					$stop = array_filter($stop);
					$i = 1;
					foreach ($stop as $key => $sstop) { ?>
							<li>
								<div class="tripstop hasdelete">
									<div class="title">
										<span class="numbering"><?=$i?></span>
										<h5><?=$sstop?></h5>
									</div>
									<div class="deletebtn">
										<a href="javascript:void(0)" onclick="deletetripplace('<?=$sstop?>',  '<?=(string)$stops['_id']?>')" class="right deletelink redicon"><i class="zmdi zmdi-delete"></i></a>
									</div>
								</div>
							</li>
					<?php 
					$i++;
					} ?>	
					</ul>
				</span>
				<div class="drow">
					<div class="add-newstop">
						<div class="newstop opened">
							
							<div class="sliding-middle-out anim-area underlined fullwidth locinput">
								<input type="text" placeholder="Where you want to go next" id="nextstop" class="getplacelocation" data-query="M" onfocus="filderMapLocationModal(this)" autocomplete="off">
							</div>
							<div class="stop-spec trip-select" id="stopdiv"></div>
							<a href="javascript:void(0)" class="waves-effect waves-light btn modal-trigger btn-trip pull-trip right mt-10 mb-10" onclick="addNewStop('stop')">Add Stop</a>
						</div>
					</div>
				</div>
				<div class="dividing-line"></div>

				<div class="drow">
					<label for="trip_summary" class="active">Your trip summery</label>
					<div class="input-field col s12">
						<textarea class="materialize-textarea" id="trip_summary" placeholder="Your trip summery" style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 24px;"><?=$stops['trip_summary']?></textarea>
					</div>												
				</div>

				<div class="drow half">
					<div class="cap-holder">
						<label>Track Path</label>
					</div>
					<div class="comp-holder">
						<div class="right">
							<div class="radio-holder">
								<label class="control control--radio">Lines
								  <input type="radio" name="radio1" <?php if($stops['mapline'] == 'lines'){?>checked<?php } ?> value="lines"/>
								  <div class="control__indicator"></div>
								</label>
							</div>
							<div class="radio-holder">
								<label class="control control--radio">Curves
								  <input type="radio" name="radio1" <?php if($stops['mapline'] == 'curves'){?>checked<?php } ?> value="curves"/>
								  <div class="control__indicator"></div>
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="drow half">
					<div class="cap-holder">
						<label>Path Color</label>
					</div>
					<div class="comp-holder">
						<div class="right">
							<ul class="color-palette">
								<li <?php if($stops['tripcolor'] == 'bluedot'){?>class="active"<?php } ?>><a href="javascript:void(0)" class="colordot bluedot" data-color="bluedot"></a></li>
								<li <?php if($stops['tripcolor'] == 'purpledot'){?>class="active"<?php } ?>><a href="javascript:void(0)" class="colordot purpledot" data-color="purpledot"></a></li>
								<li <?php if($stops['tripcolor'] == 'greendot'){?>class="active"<?php } ?>><a href="javascript:void(0)" class="colordot greendot" data-color="greendot"></a></li>
								<li <?php if($stops['tripcolor'] == 'reddot'){?>class="active"<?php } ?>><a href="javascript:void(0)" class="colordot reddot" data-color="reddot"></a></li>
							</ul>
							<input type="hidden" id="tripcolor" value="<?=$stops['tripcolor']?>"/>
						</div>
					</div>
				</div>
				<div class="drow half">
					<div class="cap-holder" id="notescount"></div>
					<div class="comp-holder">
						<a href="javascript:void(0)" class="right" onclick="showSubLayer(this,'notes','addmode')"><i class="mdi mdi-plus"></i> Add Note</a>
					</div>
				</div>
				<div class="drow half">
					<div class="cap-holder">
						<label>Who can view your trip details</label>
					</div>
					<?php
					if($stops['privacy'] == 'Private'){$type = 'lock';}
					if($stops['privacy'] == 'Connections'){$type = 'user';}
					else{$type = 'globe';}
					?>
					<div class="comp-holder category-list">
						<div class="custom-drop">
							<div class="dropdown dropdown-custom dropdown-xsmall">
								<a href="javascript:void(0)" class="dropdown-button more_btn" data-activates="dropdown-category-edit">			
									Public <span class="mdi mdi-menu-down"></span>
									<span class="sword"><?=$stops['privacy']?></span> <span class="mdi mdi-menu-down"></span>
								</a>
								<ul id="dropdown-category-edit" class="dropdown-content custom_dropdown">
									<li class="post-private"><a href="javascript:void(0)">Private</a></li>
									<li class="post-connections"><a href="javascript:void(0)">Connections</a></li>
									<li class="post-settings"><a href="javascript:void(0)">Custom</a></li>
									<li><a href="#sharepost-popup" class="popup-modal pa-share">Public</a></li>
									<input type="hidden" name="post_privacy" id="post_privacy" value="<?=$stops['privacy']?>"/>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="drow">
					<a href="javascript:void(0)" class="waves-effect waves-light btn modal-trigger btn-trip pull-trip right" onclick="addNewStop('trip')">Save Trip</a>
				</div>
			</div>
		</div>								
	</div>
</div>
<script>
$(document).ready(function(){
	stopdiv('<?=$stops['_id']?>');
	stoplists('<?=$stops['_id']?>');
	notescount('<?=$stops['_id']?>');
});
</script>
<?php exit;?>