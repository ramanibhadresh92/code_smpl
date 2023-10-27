<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Url;
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email'); 
$uid = (string)$session->get('user_id');

$controllerID = Yii::$app->controller->id;
$controllerActionID = Yii::$app->controller->action->id;
?>
<div class="sticky-nav">
	<div class="top-nav">
		<div class="row mx-0">
			<div class="col l12 s12">
				<div class="nav-links"> 
					<ul>
						<li class="<?php if($controllerActionID =='site/mainfeed'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['site/mainfeed']); ?>">Home</a></li>
						<li class="<?php if($controllerID =='virtualtours'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['virtualtours']); ?>">Virtual Tours</a></li>
						<li class="<?php if($controllerID =='todo'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['todo']); ?>">To Do</a></li>
						<li class="<?php if($controllerID =='watch'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['watch']); ?>">Watch</a></li>
						<li class="<?php if($controllerID =='discussion'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['discussion']); ?>">Discussion</a></li>
						<li class="<?php if($controllerID =='photostream'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['photostream']); ?>">Photos</a></li>
						<li class="<?php if($controllerID =='reviews'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['reviews']); ?>">Reviews</a></li>
						<li class="<?php if($controllerID =='blog'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['blog']); ?>">Blog</a></li>
						<li>
							<a class="dropdown-button" href="javascript:void(0)" data-activates="moreTopLinks"><i class="zmdi zmdi-more"></i></a>
							<ul id="moreTopLinks" class="dropdown-content custom_dropdown">
								<li class="<?php if($controllerID =='questions'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['questions']); ?>">Questions</a></li>
								<li class="<?php if($controllerID =='tripstory'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['tripstory']); ?>">Trip Story</a></li>
								<li class="<?php if($controllerID =='tips'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['tips']); ?>">Tips</a></li>
		                        <li class="<?php if($controllerID =='collections'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['collections']); ?>">Photo Collections</a></li>
		                        <li class="<?php if($controllerID =='locals'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['locals']); ?>">Japan Locals</a></li>
		                        <li class="<?php if($controllerID =='travellers'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['travellers']); ?>">People travelling to Japan</a></li>
		                        <li class="<?php if($controllerID =='localguide'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['localguide']); ?>">Local Guide</a></li>
		                        <li class="<?php if($controllerID =='localdriver'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['localdriver']); ?>">Local Driver</a></li>
		                        <li class="<?php if($controllerID =='cityguide'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['cityguide']); ?>">City Guide</a></li>
		                        <li class="<?php if($controllerID =='page'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['page']); ?>">Business pages</a></li>
		                     </ul>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>