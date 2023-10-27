<div class="row mx-0">
  <div class="menu-links">
     <ul class="ul-main"> 
        <li>
          <a href="<?php echo Yii::$app->urlManager->createUrl(['site/mainfeed']); ?>">
              <img src="<?=$baseUrl?>/images/home-icon.png" alt="menu icon">
              Japan
           </a>
        </li>
        
        <li>
           <a href="<?php echo Yii::$app->urlManager->createUrl(['virtualtours']); ?>">
              <img src="<?=$baseUrl?>/images/hotels-icon.png" alt="menu icon">
              <span class="virtualword">Virtual </span>Tours
           </a>
        </li>
        <li>
           <a href="<?php echo Yii::$app->urlManager->createUrl(['todo']); ?>">
              <img src="<?=$baseUrl?>/images/to-do-icon.png" alt="menu icon">
              To Do
           </a>
        </li>
        <li>
           <a href="<?php echo Yii::$app->urlManager->createUrl(['watch']); ?>">
              <img src="<?=$baseUrl?>/images/live_shows.png" alt="menu icon">
              Watch
           </a>
        </li>
        <li>
           <a href="<?php echo Yii::$app->urlManager->createUrl(['discussion']); ?>">
              <img src="<?=$baseUrl?>/images/discussion-icon.png" alt="menu icon">
              Discussion
           </a>
        </li>
        <li>
           <a href="<?php echo Yii::$app->urlManager->createUrl(['photostream']); ?>">
              <img src="<?=$baseUrl?>/images/photostream-icon.png" alt="menu icon">
              Photos
           </a>
        </li>
        <li>
           <a href="<?php echo Yii::$app->urlManager->createUrl(['reviews']); ?>">
              <img src="<?=$baseUrl?>/images/tips-icon.png" alt="menu icon">
              Reviews
           </a>
        </li>
        <li>
           <a href="<?php echo Yii::$app->urlManager->createUrl(['blog']); ?>">
              <img src="<?=$baseUrl?>/images/blog-icon.png" alt="menu icon">
              Blog
           </a>
        </li>
        <li>
           <a class="dropdown-button" href="javascript:void(0)" data-activates="moreLinks">
              <img src="<?=$baseUrl?>/images/more-icon.png" alt="menu icon">
              More 
           </a>
           <ul id="moreLinks" class="dropdown-content custom_dropdown">
              <li><a href="<?php echo Yii::$app->urlManager->createUrl(['questions']); ?>">Questions</a></li>
              <li><a href="<?php echo Yii::$app->urlManager->createUrl(['tripstory']); ?>">Trip Story</a></li>
              <li><a href="<?php echo Yii::$app->urlManager->createUrl(['tips']); ?>">Tips</a></li>
              <li><a href="<?php echo Yii::$app->urlManager->createUrl(['collections']); ?>">Photo Collections</a></li>
              <li><a href="<?php echo Yii::$app->urlManager->createUrl(['locals']); ?>">Japan Locals</a></li>
              <li><a href="<?php echo Yii::$app->urlManager->createUrl(['travellers']); ?>">People travelling to Japan</a></li>
              <li><a href="<?php echo Yii::$app->urlManager->createUrl(['localguide']); ?>">Local Guide</a></li>
              <li><a href="<?php echo Yii::$app->urlManager->createUrl(['localdriver']); ?>">Local Driver</a></li>
              <li><a href="<?php echo Yii::$app->urlManager->createUrl(['cityguide']); ?>">City Guide</a></li>
              <li><a href="<?php echo Yii::$app->urlManager->createUrl(['page']); ?>">Business pages</a></li>
           </ul>
        </li>
     </ul>
  </div>
</div>