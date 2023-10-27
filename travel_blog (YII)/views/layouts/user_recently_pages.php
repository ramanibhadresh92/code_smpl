<?php

use yii\helpers\Url;
use frontend\models\Like;
use frontend\models\Page;
use frontend\models\PageVisitor;
use frontend\assets\AppAsset;

$baseUrl = AppAsset::register($this)->baseUrl;
?>
<div class="content-box bshadow">
    <div class="cbox-title">
        Recently viewed page
    </div>
    <div class="cbox-desc">
        <div class="recentpage-list grid-list">
            <div class="row">
                <?php
                    $recentlyviewdpages = PageVisitor::find()->where(['visitor_id' => "$user_id"])->orderBy(['visited_date'=>SORT_DESC])->limit(6)->offset(0)->all();
                    $visitedpage = count($recentlyviewdpages);
                    if($visitedpage > 0){
                        foreach($recentlyviewdpages as $recentlyviewdpage)
                        {
                            $pageid = (string)$recentlyviewdpage['page_id'];
                            $pagelink = Url::to(['page/index', 'id' => "$pageid"]);
                            $page_img = $this->context->getpageimage($pageid);
                            $pagedetail = Page::find()->where([(string)'_id' => $pageid])->one();
                            $title = $pagedetail['page_name'];
                            $like_count = Like::getLikeCount($pageid);
                            $likeexist = Like::getPageLike($pageid);
                            if($likeexist){$likestatus = 'Liked';}
                            else{$likestatus = 'Like';}

                ?>
                    <div class="grid-box">
                        <div class="recentpage-box himg-box">
                            <div class="imgholder">
                                <a href="<?=$pagelink?>"><img src="<?=$page_img?>"/></a>
                            </div>
                            <div class="descholder">																<a href="javascript:void(0)" class="userlink">
                                    <?=$title?>
                                    <span class="info" data-pageid="<?=$pageid?>" onclick="recentPageLikeBox(this);">
                                        <?php if($like_count > 0) {
                                            if($like_count > 1) {
                                                echo $like_count .' Likes';
                                            } else {
                                                echo $like_count .' Like';
                                            }
                                        } else {
                                            echo '&nbsp;'; 
                                        } ?>
                                    </span>
                                </a>
                            </div>
                        </div>							
                    </div>
                    <?php } } else { ?>
                    <?php $this->context->getnolistfound('novisitedpage'); ?>
                    <?php } ?>
                </div>
            </div>
        </div>
</div>