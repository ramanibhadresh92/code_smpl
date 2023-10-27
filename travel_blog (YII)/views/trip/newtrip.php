<div class="section-holder nice-scroll">
	<div class="sectitle">
		Add a new trip
		<a href="javascript:void(0)" class="right deletelink redicon" onclick="showLayer(this,'alltrip')"><i class="mdi mdi-close	"></i> Cancel</a>
	</div>
	<div class="secdesc">
		<div class="trip-summery">
			<div class="tripdetail">
				<div class="drow">
					<div class="sliding-middle-out anim-area underlined fullwidth">
						<input type="text" placeholder="Name your trip" id="trip_name">
					</div>
				</div>
				<div class="starttrip">
					<span>Trip starts on -</span>
					<span> 
						<div class="sliding-middle-out anim-area underlined">
							<input type="text" onkeydown="return false;" data-toggle="datepicker" data-query="M" class="datepickerinput" placeholder="Date" id="addtripdate" readonly/>
						</div>
					</span>
				</div>
				<div class="drow">
					<div class="sliding-middle-out anim-area underlined fullwidth">
						<input type="text" id="startfrom" data-query="M" placeholder="Where your trip starts from" onfocus="filderMapLocationModal(this)" autocomplete='off'>
					</div>
					<div class="leftbox colored location-chkbox mt-10">
						<p>
					    	<input type="checkbox" id="homelocation" />
							<label for="homelocation">Start from my home location</label>
					    </p>
					</div>											
				</div>
				<span id="trip_stops"></span>			
				<div class="drow">
					<div class="add-newstop">
						<div class="newstop opened">
						<div class="sliding-middle-out anim-area underlined fullwidth locinput">
							<input type="text" data-query="M" placeholder="Where you want to go next" id="nextstop" onfocus="filderMapLocationModal(this)" autocomplete='off'>
						</div> 
						<div class="stop-spec trip-select" id="stopdiv"></div>
						<a href="javascript:void(0)" class="waves-effect waves-light btn modal-trigger btn-trip pull-trip right mt-10 mb-10 stopbtn" onclick="addNewStop('stop')">Add Stop</a>
						</div>
					</div>
				</div>
				<div class="dividing-line"></div>

				<div class="drow">
					<label for="trip_summary" class="active">Your trip summery</label>
					<div class="input-field col s12">
						<textarea class="materialize-textarea" id="trip_summary" placeholder="Your trip summery" style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 24px;"></textarea>
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
								  <input type="radio" name="radio1" checked value="lines"/>
								  <div class="control__indicator"></div>
								</label>
							</div>
							<div class="radio-holder">
								<label class="control control--radio">Curves
								  <input type="radio" name="radio1" value="curves"/>
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
								<li class="active"><a href="javascript:void(0)" class="colordot bluedot" data-color="bluedot"></a></li>
								<li><a href="javascript:void(0)" class="colordot purpledot" data-color="purpledot"></a></li>
								<li><a href="javascript:void(0)" class="colordot greendot" data-color="greendot"></a></li>
								<li><a href="javascript:void(0)" class="colordot reddot" data-color="reddot"></a></li>
							</ul>
							<input type="hidden" id="tripcolor" value="bluedot"/>
						</div>
					</div>
				</div>
				<div class="drow half">
					<div class="cap-holder" id="notescount">
						<label>Your trip notes
							<span class="notetext" id="notescount">
								<a href="javascript:void(0)" onclick="showSubLayer(this,'notes','addmode')">0 notes added</a>
							</span>
						</label>
					</div>
					<div class="comp-holder">
						<a href="javascript:void(0)" class="right" onclick="showSubLayer(this,'notes','addmode')"><i class="mdi mdi-plus"></i> Add Note</a>
					</div>
				</div>
				<div class="drow half">
					<div class="cap-holder">
						<label>Who can view your trip details</label>
					</div>
					<div class="comp-holder category-list">
						<div class="custom-drop">
							<div class="dropdown dropdown-custom dropdown-xsmall">
								<a href="javascript:void(0)" class="dropdown-button more_btn" data-activates="dropdown-category">			
									Public <span class="mdi mdi-menu-down caret"></span>
								</a>
								<ul id="dropdown-category" class="dropdown-content custom_dropdown">
									<li class="post-private"><a href="javascript:void(0)">Private</a></li>
									<li class="post-connections"><a href="javascript:void(0)">Connections</a></li>
									<li class="post-settings"><a href="javascript:void(0)">Custom</a></li>
									<li><a href="#sharepost-popup" class="popup-modal pa-share">Public</a></li>
									<input type="hidden" name="post_privacy" id="post_privacy" value="Public"/>
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
<?php exit;?>