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
<div class="page-wrapper place-wrapper mainfeed-page blog-page watch-page"> 
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
		<div class="fixed-layout ipad-mfix tours-page-layout">
	      <div class="content-box nbg noboxshadow">
	         <div class="hcontent-holder home-section gray-section tours-page tours watch-wrapper pb-0 m-t-50 main-content main-page places-page">
	            <div class="row mx-0 mainboxlt">
					<!-- <video autoplay="" loop="" class="video-background" muted plays-inline>
						<source src="<?=$baseUrl?>/images/japanwatch.mp4" type="video/mp4">
					</video> -->
					<img src="<?=$baseUrl?>/images/watchphoto.png"> 
					<div class="row mx-0 boxlayout box-header">
						<h2 class="mnlabel">Watch</h>
						<h5 class="smllabel">Watch your favorite live shows from your home.</h5>
					</div>
				</div>
	            <div class="container mt-10">
	               <div class="tours-section">
	                  <div class="row mx-0">
	                     <ul class="collection">
	                        <li class="collection-item avatar">
	                           <!-- <img src="<?=$baseUrl?>/images/todo/movies/2.jpg" alt=""> -->
	                           <video controls>
	                              <source src="https://www.w3schools.com/html/movie.mp4" type="video/mp4">
	                              <source src="https://www.w3schools.com/html/movie.mp4" type="video/ogg">
	                           </video>
	                           <a href="blog-detail.php">
	                              <span class="title">What we are doing to support sofar artist community</span>
	                              <p>
	                              A dispute over escalated with implications for the firm, the tech sector and consumers A dispute over Huawei has escalated with implications for the firm, the tech sector and consumers A dispute over Huawei has escalated with implications for the firm, the tech sector and consumers A dispute over Huawei has escalated with implications for the firm, the tech sector and consumers.
	                              </p>
	                              <span class="post-by">May, 02 2020</span>
	                           </a>
	                        </li>
	                        <li class="collection-item avatar">
	                           <!-- <img src="<?=$baseUrl?>/images/todo/movies/1.jpg" alt=""> -->
	                           <video controls>
	                              <source src="https://www.w3schools.com/html/movie.mp4" type="video/mp4">
	                              <source src="https://www.w3schools.com/html/movie.mp4" type="video/ogg">
	                           </video>
	                           <a href="blog-detail.php">
	                              <span class="title">Highlight of the week: what happening in the sofar listening room</span>
	                              <p>
	                              A dispute over escalated with implications for the firm, the tech sector and consumers. A dispute over Huawei has escalated with implications for the firm, the tech sector and consumers. A dispute over Huawei has escalated with implications for the firm, the tech sector and consumers.
	                              </p>
	                              <span class="post-by">May, 02 2020</span>
	                           </a>
	                        </li>
	                     </ul>
	                  </div>
	               </div>
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