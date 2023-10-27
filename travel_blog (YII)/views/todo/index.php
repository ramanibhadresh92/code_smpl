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
		<div class="fixed-layout todopg">
	      <div class="main-content main-page places-page pb-0 m-t-50">
	         <div class="combined-column wide-open main-page full-page">
	            <div class="row mx-0 mainboxlt">
					<!-- <video autoplay="" loop="" class="video-background" muted plays-inline>
						<source src="<?=$baseUrl?>/images/japantodo.mp4" type="video/mp4">
					</video>  -->
					<img src="<?=$baseUrl?>/images/todophoto.png">  
					<div class="row mx-0 boxlayout box-header">
						<h2 class="mnlabel">To Do</h>
						<h5 class="smllabel">Online experience with locals from home</h5>
					</div>
				</div> 
	            <div class="typelt fbox">
	               <div class="row mx-0 boxlayout box-header">
	                  <div class="col s12 m12">
	                     <h4>Online Experience in Japan</h4>
	                  </div>
	               </div>

	               <div class="row mx-0 boxlayout">
	                 <div class="col s6 m4 l4 xl3">
	                   <div class="card">
	                     <div class="card-image">
	                        <img src="<?=$baseUrl?>/images/todo/oe/1.jpg">
                        	<span class="card-title">Adel Hasanat</span>
							<span class="card-rating">
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       		</span>
	                     </div>
	                     <div class="card-content">
	                        <p class="description">Muslim (widespread throughout the Muslim world): from the Arabic personal name Aman 'trust', 'safety', 'protection', 'tranquility'. Aman is often used in combination with other names, for example Aman Allah (Amanullah) 'trust of Allah'.</p>
	                        <p>From $20/person - 1hours</p>
	                     </div>
	                   </div>
	                 </div>

	                 <div class="col s6  m4 l4 xl3">
	                   <div class="card">
	                     <div class="card-image">
	                       <img src="<?=$baseUrl?>/images/todo/oe/2.jpg">
	                       <span class="card-title">Adel Hasanat</span>
							<span class="card-rating">
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       		</span>
	                     </div>
	                     <div class="card-content">
	                        
	                        <p class="description">Japan is a relatively small, semi-arid, almost landlocked country with an area of 89,342 km2 (34,495 sq mi) and a population numbering 10 million, making it the 11th-most populous Arab country.</p>
	                        <p>From $20/person - 1hours</p>
	                     </div>
	                   </div>
	                 </div>

	                 <div class="col s6  m4 l4 xl3">
	                   <div class="card">
	                     <div class="card-image">
	                       <img src="<?=$baseUrl?>/images/todo/oe/3.jpg">
	                       <span class="card-title">Adel Hasanat</span>
							<span class="card-rating">
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       		</span>
	                       
	                     </div>
	                     <div class="card-content">
	                        <p class="description">Japan is half built, half carved in stone. The awe-inspiring monuments of Japan are cut into cobblestone cliffs and mountains, that show a whole spectrum of colours at the rising and setting of the sun. At the thriving age of the Nabateans rule, Japan has a population soaring over 20,000 inhabitants.</p>
	                        <p>From $20/person - 1hours</p>
	                     </div>
	                   </div>
	                 </div>

	                 <div class="col s6 m4 l4 xl3">
	                   <div class="card">
	                     <div class="card-image">
	                       <img src="<?=$baseUrl?>/images/todo/oe/4.jpg">
	                       <span class="card-title">Adel Hasanat</span>
							<span class="card-rating">
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       			<i class="mdi mdi-star"></i>
                       		</span>
	                     </div>
	                     <div class="card-content">
	                        <!-- <p class="title" style="font-weight: 600;">JERASH</p> -->
	                        <p class="description">The Jerash Festival of Culture and Arts is an annual celebration of Arabic and international culture during the summer months. Jerash is located 48 km north of the capital city of Amman. The festival site is located within the ancient ruins of Jerash, some of which date to the Roman age (63 BC).</p>
	                        <p>From $20/person - 1hours</p>
	                     </div>
	                   </div>
	                 </div>
	               </div>
	            </div>

	            <div class="typelt">
	               <div class="row mx-0 boxlayout box-header">
	                  <div class="col s12 m12">
	                     <h4>Cultural Expression</h4>
	                  </div>
	               </div>

	               <div class="row mx-0 boxlayout">
	                 <div class="col s6 m4 l4 xl3">
	                   <div class="card">
	                     <div class="card-image">
	                       <img src="<?=$baseUrl?>/images/todo/ce/1.jpg">
	                       <!-- <span class="card-title">Amman</span> -->
	                     </div>
	                     <div class="card-content">
	                        <!-- <p class="title" style="font-weight: 600;">The Curve</p> -->
	                        <p class="description">Curve is a 2015 American horror-thriller film directed by Iain Softley and written by Kimberly Lofstrom Johnson and Lee Patteson.</p>
	                        <p>From $20/person - 1hours</p>
	                     </div>
	                   </div>
	                 </div>

	                 <div class="col s6 m4 l4 xl3">
	                   <div class="card">
	                     <div class="card-image">
	                       <img src="<?=$baseUrl?>/images/todo/ce/2.jpg">
	                     </div>
	                     <div class="card-content">
	                        <!-- <p class="title" style="font-weight: 600;">THEEB</p> -->
	                        <p class="description">It focuses on a young Bedouin boy, Theeb, who must survive in the wide-open Wadi Rum desert</p>
	                        <p>From $20/person - 1hours</p>
	                     </div>
	                   </div>
	                 </div>

	                 <div class="col s6 m4 l4 xl3">
	                   <div class="card">
	                     <div class="card-image">
	                       <img src="<?=$baseUrl?>/images/todo/ce/3.jpg">
	                       
	                     </div>
	                     <div class="card-content">
	                        <!-- <p class="title" style="font-weight: 600;">Under The Shadow</p> -->
	                        <p class="description">Taking place in 1988 Tehran, during the "war of the cities" phase of the nearly decade long Iran-Iraq war, "Under the Shadow" is the story of Shideh (Narges Rashidi) and Dorsa (Avin Manshadi), a mother and daughter holed up in their apartment, withstanding the missile bombardment</p>
	                        <p>From $20/person - 1hours</p>
	                     </div>
	                   </div>
	                 </div>

	                 <div class="col s6 m4 l4 xl3">
	                   <div class="card">
	                     <div class="card-image">
	                       <img src="<?=$baseUrl?>/images/todo/ce/4.jpg">
	                       <!-- <span class="card-title">Ahmedbad</span> -->
	                     </div>
	                     <div class="card-content">
	                        <!-- <p class="title" style="font-weight: 600;">Tiny Souls</p> -->
	                        <p class="description">The film follows the themes of displacement, refugees and family dynamics that she also explored in her short films</p>
	                        <p>From $20/person - 1hours</p>
	                     </div>
	                   </div>
	                 </div>
	               </div>
	            </div>

	            <div class="typelt">
	               <div class="row mx-0 boxlayout box-header">
	                  <div class="col s12 m12">
	                     <h4>Things To Do</h4>
	                  </div>
	               </div>

	               <div class="row mx-0 boxlayout">
	                 <div class="col s6 m4 l4 xl3">
	                   <div class="card">
	                     <div class="card-image">
	                       <img src="<?=$baseUrl?>/images/todo/td/1.jpg">
	                       <!-- <span class="card-title">Amman</span> -->
	                     </div>
	                     <div class="card-content">
	                        
	                        <p class="description">I am a very simple card. I am good at containing small bits of information.</p>
	                        <p>From $20/person - 1hours</p>
	                     </div>
	                   </div>
	                 </div>

	                 <div class="col s6 m4 l4 xl3">
	                   <div class="card">
	                     <div class="card-image">
	                       <img src="<?=$baseUrl?>/images/todo/td/2.jpg">
	                     </div>
	                     <div class="card-content">
	                        
	                        <p class="description">I am a very simple card. I am good at containing small bits of information.</p>
	                        <p>From $20/person - 1hours</p>
	                     </div>
	                   </div>
	                 </div>

	                 <div class="col s6 m4 l4 xl3">
	                   <div class="card">
	                     <div class="card-image">
	                       <img src="<?=$baseUrl?>/images/todo/td/3.png">
	                       
	                     </div>
	                     <div class="card-content">
	                        
	                        <p class="description">I am a very simple card. I am good at containing small bits of information.</p>
	                        <p>From $20/person - 1hours</p>
	                     </div>
	                   </div>
	                 </div>

	                 <div class="col s6 m4 l4 xl3">
	                   <div class="card">
	                     <div class="card-image">
	                       <img src="<?=$baseUrl?>/images/todo/td/4.jpg">
	                       <!-- <span class="card-title">Ahmedbad</span> -->
	                     </div>
	                     <div class="card-content">
	                        
	                        <p class="description">I am a very simple card. I am good at containing small bits of information.</p>
	                        <p>From $20/person - 1hours</p>
	                     </div>
	                   </div>
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