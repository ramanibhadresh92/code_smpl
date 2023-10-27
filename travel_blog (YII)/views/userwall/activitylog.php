<?php
$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');  
?>
<div class="combined-column">												
	<div class="content-box nobg">
		<div class="cbox-title nborder maintitle">								
			<div class="subtitle"><h5>Activity Log</h5></div>
			<div class="right-tabs">
				<div class="connections-search">
					<div class="fsearch-form">
						<input type="text" placeholder="Search log text"/>
						<a href="javascript:void(0)"><i class="zmdi zmdi-search grey-text"></i></a>
					</div>
				</div>
			</div>
		</div>
		<div class="cbox-desc maindesc">
			<div class="activity-holder"> 
				<div class="content-box bshadow">
					<div class="cbox-title">
						Today
					</div>							
					<div class="cbox-desc" data-id="dels">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php exit();?>