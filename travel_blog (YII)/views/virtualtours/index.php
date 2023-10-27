<?php  
use frontend\assets\AppAsset;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Discussion';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$directauthcall = '';
if($checkuserauthclass == 'checkuserauthclassg' || $checkuserauthclass == 'checkuserauthclassnv') { 
$directauthcall = $checkuserauthclass . ' directcheckuserauthclass';
} 

$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<script src="<?=$baseUrl?>/js/chart.js"></script>
<div class="page-wrapper place-wrapper mainfeed-page "> 
    <div class="header-section">
        <?php include('../views/layouts/header.php'); ?>
    </div>
    <?php include('../views/layouts/menu.php'); ?>
    <div class="floating-icon">
	   <div class="scrollup-btnbox anim-side btnbox scrollup-float">
	      <div class="scrollup-button float-icon">
	         <span class="icon-holder ispan">
	            <i class="mdi mdi-arrow-up-bold-circle"></i>
	         </span>
	      </div>
	   </div>
	</div>
	<div class="clear"></div>
	<div>
		<?php include('../views/layouts/leftmenu.php'); ?>
		<div class="fixed-layout vtour">
	      <div class="main-content main-page places-page pb-0 m-t-50">
	         <div class="combined-column wide-open main-page full-page">
	            

	            <div class="row mx-0 mainboxlt">
					<!-- <video autoplay="" loop="" class="video-background" muted plays-inline>
						<source src="<?=$baseUrl?>/images/japanvtour.mp4" type="video/mp4">
					</video>  -->
					<img src="<?=$baseUrl?>/images/vtourphoto.png">
					<div class="row mx-0 boxlayout box-header">
						<h2 class="mnlabel">Tours</h>
						<h5 class="smllabel">Online experience with locals from home</h5>
					</div>
				</div> 	  
	            
	            <div class="row mx-0">
	              <div class="col s12 m12 l12 xl12 blocking">
	                <div class="videobox">
	                    <iframe width="853" height="480" src="//www.youtube.com/embed/Q8TXgCzxEnw?rel=0" frameborder="0" allowfullscreen></iframe>
	                </div>

	                <div class="description">
	                  <div class="desc-title">The waltz of isolation with the 8-year-old pianist stelis</div>
	                  <div class="desc-middleblock"><p class="viewer">424 views</p><span class="dot"></span><p class="timer">4 hours ago</p></div>
	                  <div class="desc-description">Japan is a relatively small, semi-arid, almost landlocked country with an area of 89,342 km2 (34,495 sq mi) and a population numbering 10 million, making it the 11th-most populous Arab country.As the crossroads of the Middle East, the lands of Japan and Palestine have served as a strategic nexus connecting Asia, Africa, and Europe. Thus, since the dawn of civilization, Japan's geography has given it an important role to play as a conduit for trade and communications, connecting east and west.</div>
	                </div>
	              </div>
	              <div class="divider"></div>

	              <div class="col s12 m12 l12 xl12 blocking">
	                <div class="videobox">
	                    <iframe width="853" height="480" src="//www.youtube.com/embed/Q8TXgCzxEnw?rel=0" frameborder="0" allowfullscreen></iframe>
	                </div>

	                <div class="description">
	                  <div class="desc-title">The waltz of isolation with the 8-year-old pianist stelis</div>
	                  <div class="desc-middleblock"><p class="viewer">424 views</p><span class="dot"></span><p class="timer">4 hours ago</p></div>
	                  <div class="desc-description">Japan is a relatively small, semi-arid, almost landlocked country with an area of 89,342 km2 (34,495 sq mi) and a population numbering 10 million, making it the 11th-most populous Arab country.As the crossroads of the Middle East, the lands of Japan and Palestine have served as a strategic nexus connecting Asia, Africa, and Europe. Thus, since the dawn of civilization, Japan's geography has given it an important role to play as a conduit for trade and communications, connecting east and west.</div>
	                </div>
	              </div>
	              <div class="divider"></div>

	              <div class="col s12 m12 l12 xl12 blocking">
	                <div class="videobox">
	                    <iframe width="853" height="480" src="//www.youtube.com/embed/Q8TXgCzxEnw?rel=0" frameborder="0" allowfullscreen></iframe>
	                </div>

	                <div class="description">
	                  <div class="desc-title">The waltz of isolation with the 8-year-old pianist stelis</div>
	                  <div class="desc-middleblock"><p class="viewer">424 views</p><span class="dot"></span><p class="timer">4 hours ago</p></div>
	                  <div class="desc-description">Japan is a relatively small, semi-arid, almost landlocked country with an area of 89,342 km2 (34,495 sq mi) and a population numbering 10 million, making it the 11th-most populous Arab country.As the crossroads of the Middle East, the lands of Japan and Palestine have served as a strategic nexus connecting Asia, Africa, and Europe. Thus, since the dawn of civilization, Japan's geography has given it an important role to play as a conduit for trade and communications, connecting east and west.</div>
	                </div>
	              </div>
	              <div class="divider"></div>

	              <div class="col s12 m12 l12 xl12 blocking">
	                <div class="videobox">
	                    <iframe width="853" height="480" src="//www.youtube.com/embed/Q8TXgCzxEnw?rel=0" frameborder="0" allowfullscreen></iframe>
	                </div>

	                <div class="description">
	                  <div class="desc-title">The waltz of isolation with the 8-year-old pianist stelis</div>
	                  <div class="desc-middleblock"><p class="viewer">424 views</p><span class="dot"></span><p class="timer">4 hours ago</p></div>
	                  <div class="desc-description">Japan is a relatively small, semi-arid, almost landlocked country with an area of 89,342 km2 (34,495 sq mi) and a population numbering 10 million, making it the 11th-most populous Arab country.As the crossroads of the Middle East, the lands of Japan and Palestine have served as a strategic nexus connecting Asia, Africa, and Europe. Thus, since the dawn of civilization, Japan's geography has given it an important role to play as a conduit for trade and communications, connecting east and west.</div>
	                </div>
	              </div>
	              <div class="divider"></div>

	              <div class="col s12 m12 l12 xl12 blocking">
	                <div class="videobox">
	                    <iframe width="853" height="480" src="//www.youtube.com/embed/Q8TXgCzxEnw?rel=0" frameborder="0" allowfullscreen></iframe>
	                </div>

	                <div class="description">
	                  <div class="desc-title">The waltz of isolation with the 8-year-old pianist stelis</div>
	                  <div class="desc-middleblock"><p class="viewer">424 views</p><span class="dot"></span><p class="timer">4 hours ago</p></div>
	                  <div class="desc-description">Japan is a relatively small, semi-arid, almost landlocked country with an area of 89,342 km2 (34,495 sq mi) and a population numbering 10 million, making it the 11th-most populous Arab country.As the crossroads of the Middle East, the lands of Japan and Palestine have served as a strategic nexus connecting Asia, Africa, and Europe. Thus, since the dawn of civilization, Japan's geography has given it an important role to play as a conduit for trade and communications, connecting east and west.</div>
	                </div>
	              </div>
	              <div class="divider"></div>

	              <div class="col s12 m12 l12 xl12 blocking">
	                <div class="videobox">
	                    <iframe width="853" height="480" src="//www.youtube.com/embed/Q8TXgCzxEnw?rel=0" frameborder="0" allowfullscreen></iframe>
	                </div>

	                <div class="description">
	                  <div class="desc-title">The waltz of isolation with the 8-year-old pianist stelis</div>
	                  <div class="desc-middleblock"><p class="viewer">424 views</p><span class="dot"></span><p class="timer">4 hours ago</p></div>
	                  <div class="desc-description">Japan is a relatively small, semi-arid, almost landlocked country with an area of 89,342 km2 (34,495 sq mi) and a population numbering 10 million, making it the 11th-most populous Arab country.As the crossroads of the Middle East, the lands of Japan and Palestine have served as a strategic nexus connecting Asia, Africa, and Europe. Thus, since the dawn of civilization, Japan's geography has given it an important role to play as a conduit for trade and communications, connecting east and west.</div>
	                </div>
	              </div>
	              <div class="divider"></div>

	              <div class="col s12 m12 l12 xl12 blocking">
	                <div class="videobox">
	                    <iframe width="853" height="480" src="//www.youtube.com/embed/Q8TXgCzxEnw?rel=0" frameborder="0" allowfullscreen></iframe>
	                </div>

	                <div class="description">
	                  <div class="desc-title">The waltz of isolation with the 8-year-old pianist stelis</div>
	                  <div class="desc-middleblock"><p class="viewer">424 views</p><span class="dot"></span><p class="timer">4 hours ago</p></div>
	                  <div class="desc-description">Japan is a relatively small, semi-arid, almost landlocked country with an area of 89,342 km2 (34,495 sq mi) and a population numbering 10 million, making it the 11th-most populous Arab country.As the crossroads of the Middle East, the lands of Japan and Palestine have served as a strategic nexus connecting Asia, Africa, and Europe. Thus, since the dawn of civilization, Japan's geography has given it an important role to play as a conduit for trade and communications, connecting east and west.</div>
	                </div>
	              </div>
	              <div class="divider"></div>

	            </div>
	         </div>
	      </div>
	   </div>
	</div>
</div>  
	
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>
<script type="text/javascript">
	var data1 = '';
	var place = "<?php echo (string)$place?>";
	var placetitle = "<?php echo (string)$placetitle?>";
	var placefirst = "<?php echo (string)$placefirst?>";
	var baseUrl = "<?php echo (string)$baseUrl; ?>";
	var lat = "<?php echo $lat; ?>";
	var lng = "<?php echo $lng; ?>"; 
</script>
<?php include('../views/layouts/commonjs.php'); ?>
<script src="<?=$baseUrl?>/js/post.js"></script>
<script src="<?=$baseUrl?>/js/discussion.js"></script>
<?php $this->endBody() ?> 