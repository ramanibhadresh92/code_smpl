<?php
use yii\helpers\Url;
use frontend\models\LoginForm;
$user_recent_join =  LoginForm::getrecentlyjoined();
?>
<div class="content-box bshadow" id="recently_joined">
    <div class="cbox-title">
        Recently joined
    </div>
    <div class="cbox-desc">
        <div class="recent-list">
            <div class="row">
                <?php
                foreach($user_recent_join as $user)  
				{
                    $uid = (string) $user['_id'];
					if(isset($_GET['r']) && $_GET['r'] == 'userwall/saved-content')
					{
						$recentjoinimg = $this->getimage($uid,'photo');
					}
					else
					{
						$recentjoinimg = $this->context->getimage($uid,'photo');
					}
                ?>
                <div class="recent-col">
                    <div class="recent-box" title="<?=$user['fullname']?>">
                        <a href="<?php echo Url::to(['userwall/index', 'id' => "$uid"]);?>">
                            <img src="<?= $recentjoinimg ?>"/>
                        </a>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>