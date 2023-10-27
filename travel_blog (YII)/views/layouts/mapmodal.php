<div class="map-model-content">    
  <div class="dis-none">
    <div class="map_header_container">
      <div class="search_container">
        <button class="close_span waves-effect">
          <i class="mdi mdi-close mdi-20px	"></i>
        </button>
        <div class="near_by_place">
          <input placeholder="Search nearby places.." id="nearby_place" type="text" class="validate nearby_place_new">
        </div>
        <span class="search_span search_map_new" id="map_search">
          <i class="zmdi zmdi-search material_close mdi-18px"></i>
        </span>
      </div>
    </div>
    <div class="map_frame">
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d238133.1523816246!2d72.68221020433099!3d21.15914250210564!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be04e59411d1563%3A0xfe4558290938b042!2sSurat%2C+Gujarat!5e0!3m2!1sen!2sin!4v1507141795136" style="width:100%;max-width:100%;height:200px" frameborder="0" allowfullscreen=""></iframe>
    </div>
    <div class="map_container"></div>
  </div>
  <div id="pac_container"  class="map_header_container">
    <div class="search_container">
      <div class="map_remove">
        <button class="close_span waves-effect">
          <i class="mdi mdi-close mdi-20px close_search_box"></i>
        </button>
      </div>
      <button class="back_arrow"> 
        <i class="zmdi zmdi-arrow-left mdi-20px"></i>
      </button>
      <input id="pac-input" class="controls" type="text" placeholder="Search Your Location">
      <span class="search_span search_map_new" id="map_search">
        <i class="zmdi zmdi-search material_close mdi-18px"></i>
      </span>
      <span class="empty_input empty_close">
        <i class="mdi mdi-close mdi-20px"></i>
      </span>
    </div>
  </div>
  <div id="map" class="map_div"></div>
  <div class="map_container"> </div>
</div>