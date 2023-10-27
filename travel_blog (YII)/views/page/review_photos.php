<?php
use yii\helpers\Url;
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;  
$user_id = (string)$session->get('user_id'); 
$isEmpty = true;
?>
<h4><i class="zmdi zmdi-image-o"></i> Review Photos</h4>
<div class="albums-grid images-container">
    <div class="row">
        <?php 
        foreach ($reviewdposts as $gallery_item) {
            $postid = (string)$gallery_item['_id'];
            $processDataImages = $gallery_item['image'];
            $eximgs = array_filter(explode(',', $processDataImages));
            if(!empty($eximgs)) { 
                foreach ($eximgs as $eximg) {
                    $picsize = '';
                    $imgclass = '';
                    $iname = '';
                    if(file_exists('../web'.$eximg)) {
                        $val = getimagesize('../web'.$eximg);
                        $iname = $this->context->getimagename($eximg);
                        $inameclass = $this->context->getimagefilename($eximg);
                        $picsize .= $val[0] .'x'. $val[1] .', ';
                        if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}
                    }
                    if(!(isset($inameclass) && !empty($inameclass)))
                    { 
                        $inameclass = '';
                    }

                    $isEmpty = false;
                    $rand = rand(9999, 999999).time();
                    $getBaseUrl = Yii::$app->getUrlManager()->getBaseUrl();
                    $imgencode64 = base64_encode($eximg);
                    ?>
                    <div class="grid-box countgrid-box" id="reviewphoto_<?=$postid?>">
                        <div class="photo-box">
                            <div class="imgholder <?=$imgclass?>-box">
                                <figure>
                                    <a href="<?=$getBaseUrl?><?=$eximg?>" data-imgid="<?=$inameclass?>" data-size="1600x1600" data-med="<?=$getBaseUrl?><?=$eximg?>" data-med-size="1024x1024" data-author="Folkert Gorter">
                                      <img src="<?=$getBaseUrl?><?=$eximg;?>" class="<?=$imgclass?>"/>
                                    </a>
                                </figure>
                            </div>
                            <div class="descholder">
                                <div class="post-review" >
                                    <a href="javascript:void(0)" class="approve-btn" onclick="aprvReviewPhoto(this, '<?=$imgencode64?>')"><i class="zmdi zmdi-check"></i></a>
                                    <a href="javascript:void(0)" class="reject-btn" onclick="rjctReviewPhoto(this, '<?=$imgencode64?>')"><i class="mdi mdi-close	"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                }   
            }
        } 

         if($isEmpty) { ?>
            <div class="no-listcontent">
                No photos exist for review now.
            </div>
        <?php }
        ?>
    </div>
</div>
<?php 
exit;