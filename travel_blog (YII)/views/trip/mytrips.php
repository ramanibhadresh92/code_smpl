<div class="section-holder nice-scroll">
	<div class="sectitle">
		Your Trips
	</div>
	<div class="secdesc">
		<ul class="triplist">
			<?php if(isset($trips) && !empty($trips)) {
				foreach($trips as $trip) {
					$tripid = $trip['_id'];
					$uniqId = rand(999, 999999).$tripid;
					$trip_name = $trip['trip_name'];
					$trip_summary = $trip['trip_summary'];
			?>
			<li id="trip_<?=$tripid?>">
				<div class="tripitem">
					<div class="itemicon"><i class="mdi mdi-airplane"></i></div>
					<h6><a href="javascript:void(0)" onclick="showLayer(this,'viewtrip')" data-tripid="<?=$tripid?>"><?=$trip_name?></a></h6>
					<div class="settings-icon">
						<div class="dropdown-sm">
							<a href='javascript:void(0)' class="dropdown-button" data-activates='<?=$uniqId?>'>	
								<i class="zmdi zmdi-hc-2x zmdi-more"></i>
							</a>
							<ul id='<?=$uniqId?>' class='dropdown-content custom_dropdown'>
								<li><a href="javascript:void(0)" data-tripid="<?=$tripid?>" onclick="showLayer(this,'edittrip')">Edit</a></li>
								<li><a href="javascript:void(0)" onclick="deltrip('<?=$tripid?>')">Delete</a></li> 
								<li><a href="javascript:void(0)" data-sharepostid="trip_<?=$tripid;?>" class="customsharepopup-modal">Share</a></li>
							</ul>
						</div>
					</div>
					<?php if(isset($trip_summary) && !empty($trip_summary)){ ?>
					<p>
					<?=$trip_summary?>
					</p>
					<?php } ?>
				</div>
			</li>

			<?php } } else{ ?>
				<?php $this->context->getnolistfound('notripfound'); ?>
			<?php } ?>
		</ul>
		<a href="javascript:void(0)" class="waves-effect waves-light btn modal-trigger btn-trip pull-trip right bottom-btn <?=$checkuserauthclass?>" onclick="showLayer(this,'newtrip')">Add New Trip</a>
	</div>
</div>
<?php exit;?>

