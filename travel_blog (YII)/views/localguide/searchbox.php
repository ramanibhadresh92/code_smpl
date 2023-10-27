<div class="main-info">
 <a href="javascript:void(0)" class="expand-link mbl-filter-icon main-icon gray-text-555" onclick="mbl_mng_drop_searcharea(this,'searcharea1')"><i class="mdi mdi-tune mdi-20px"></i></a>
 <div class="search-area side-area main-search" id="searcharea1">
    <a href="javascript:void(0)" class="expand-link" onclick="mng_drop_searcharea(this)">Advanced Search</a>
    <div class="expandable-area">
       <a href="javascript:void(0)" class="closearea" onclick="mng_drop_searcharea(this)">
       <img src="<?=$baseUrl?>/images/cross-icon.png"/><span>DONE</span>
       </a>
       <div class="tab-content">
          <div class="tab-pane fade active in" id="find-traveller">
             <div class="srow">
                <h6>Refine search</h6>
             </div>
            	<div class="srow" id="checkallsearchdiv">	
					<h6>Guide Info</h6>
					<p>
				      	<input type="checkbox" class="check-all" id="checkallsearch" onChange="checkallselected();"/>
				      	<label for="checkallsearch">Check all</label>
				   	</p>
					<ul>
						<li>
							<input type="checkbox" id="has_photo" name="localguideInfo" value="hasphoto">
							<label for="has_photo">Has photo</label>
						</li>
						<li>
							<input type="checkbox" id="has_verified" name="localguideInfo" value="verified">
							<label for="has_verified">Verified</label>
						</li>
						<li>
							<input type="checkbox" id="has_references" name="localguideInfo" value="refrence">
							<label for="has_references">Has references</label>
						</li>                                               
					</ul>
				</div>
				<div class="srow">  
                    <h6>Language spoken</h6>
                    <div class="input-field dropdown782" id="languagedropdown">
                        <select id="chooseLanguage" class="languagedrp" data-selectore="languagedrp" data-fill="n" data-action="language" multiple>
                            <option value="" disabled selected>Choose a language</option>
                        </select>
                    </div>
                </div>
             	<div class="srow">  
                    <h6>Age</h6>
                    <div class="range-slider">
                        <div id="age-slider"></div>
                    </div>
                </div>
             	<div class="srow">  
                    <h6>Gender</h6>
                    <ul>
                        <li>
                            <input type="checkbox" id="male_check" name="gendername" value="Male">
                            <label for="male_check">Male</label>
                        </li>
                        <li>
                            <input type="checkbox" id="female_check" name="gendername" value="Female">
                            <label for="female_check">Female</label>
                        </li>
                        <li>
                            <input type="checkbox" id="several_check" name="gendername" value="Several_people">
                            <label for="several_check">Several People</label>
                        </li>                                           
                    </ul>
                </div>
             	<div class="btn-holder">
                    <a href="javascript:void(0)" onclick="resetSearchArea()" class="btn-custom waves-effect">Reset</a>
                    <a href="javascript:void(0)" onclick="searchClk()" class="btn-custom waves-effect">Search</a>
                </div>
          </div>
       </div>
    </div>
 </div>
 <a href="javascript:void(0)" class="expand-link mbl-filter-icon offer-profile-icon" onclick="mbl_mng_drop_searcharea(this,'searcharea2')"><img src="<?=$baseUrl?>/images/profile-filter.png"/></a>
 <div class="search-area side-area offer-profile" id="searcharea2">
    <a href="javascript:void(0)" class="expand-link" onclick="mng_drop_searcharea(this)"><i class="mdi mdi-menu-right"></i>User's Profile</a>
    <div class="expandable-area">
       <a href="javascript:void(0)" class="closearea" onclick="mng_drop_searcharea(this)">
       <i class="mdi mdi-close"></i>
       </a>
       <div class="user-profile">
          <div class="desc-holder">
             <div class="img-holder"><img src="<?=$baseUrl?>/images/offer-propic.jpg"/></div>
             <div class="content-area">
                <h4>Linka_U</h4>
                <div class="row-sec">
                   <div class="inforow">
                      <div class="icon-holder"><i class="zmdi zmdi-pin"></i></div>
                      Lives in <span>Poland</span>
                   </div>
                </div>
                <div class="row-sec">
                   <div class="inforow">
                      <div class="icon-holder"><i class="mdi mdi mdi-gender-male-female"></i></div>
                      <span>34 / Female</span>
                   </div>
                   <div class="inforow">
                      <div class="icon-holder"><i class="mdi mdi-comment"></i></div>
                      Speaks <span>English, French</span>
                   </div>
                   <div class="inforow">
                      <div class="icon-holder"><i class="mdi mdi-briefcase"></i></div>
                      Works as <span>Graphics Designer</span>
                   </div>
                   <div class="inforow">
                      <div class="icon-holder"><i class="mdi mdi-format-quote-open"></i></div>
                      Lastly logged in <span>Yesterday</span>
                   </div>
                </div>
                <div class="inforow">
                   <div class="icon-holder"><i class=”mdi mdi-account-group”></i></i></div>
                   Connections <span>56</span>
                </div>
                <div class="inforow">
                   <div class="icon-holder"><i class="mdi mdi-bookmark"></i></div>
                   References <span>20</span>
                </div>
             </div>
          </div>
          <div class="sub-info">
             <div class="sub-title">
                <h5>Trip Plan</h5>
             </div>
             <div class="content-area">
                <div class="inforow">
                   <div class="icon-holder"><i class="zmdi zmdi-pin"></i></div>
                   <label>City</label>
                   <p>Salt Lake City, Utah, United States</p>
                </div>
                <div class="inforow">
                   <div class="icon-holder"><i class="mdi mdi-airplane"></i></div>
                   <label>Arrival Date</label>
                   <p>4 Nov 2016</p>
                </div>
                <div class="inforow">
                   <div class="icon-holder"><i class="mdi mdi-reply"></i></div>
                   <label>Departure Date</label>
                   <p>10 Nov 2016</p>
                </div>
                <div class="inforow">
                   <div class="icon-holder"><i class="mdi mdi-account"></i></div>
                   <label>Total Travellers</label>
                   <p>2</p>
                </div>
             </div>
          </div>
          <div class="sub-info">
             <div class="sub-title">
                <h5>Host Services</h5>
             </div>
             <div class="content-area hostservice">
                <div class="inforow">
                   <label>Host Availability</label>
                   <p>Accepting Guests</p>
                </div>
                <div class="inforow">
                   <label>Host Amenities</label>
                   <ul>
                      <li><i class="mdi mdi-coffee"></i>Hang Around</li>
                      <li><i class="mdi mdi-binoculars"></i>Dininig</li>
                      <li><i class="mdi mdi-bus"></i>Site Touring</li>
                   </ul>
                </div>
                <div class="inforow">
                   <label>Host Message</label>
                   <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.</p>
                </div>
             </div>
          </div>
       </div>
    </div>
 </div>
</div>