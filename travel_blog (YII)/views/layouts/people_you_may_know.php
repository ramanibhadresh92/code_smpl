<?php

use frontend\models\Connect;
use frontend\models\SecuritySetting;
use frontend\assets\AppAsset;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

if(isset($_GET['r']) && $_GET['r'] == 'userwall/saved-content')
{
    $baseUrl = $_POST['baseUrl'];
}
else
{
    $baseUrl = AppAsset::register($this)->baseUrl;
}

$session = Yii::$app->session;
$uid = (string)$session->get('user_id');
$model_connect = new Connect();
$init_connections = $model_connect->userlistFirstfive($uid);
?>

<div class="content-box bshadow peopleyoumayknow">
    <div class="cbox-title">
        People you may know
    </div>
    <div class="cbox-desc">
        <ul class="people-list">
            <input type="hidden" name="login_id" id="login_id" value="<?php echo $session->get('user_id');?>">
            <?php  
                $counter = 0;
                foreach($init_connections as $connect){ 
				$connect_id = (string) $connect['_id'];
                $requestexists = $model_connect->requestexists($connect['id']);
                $alreadysend = $model_connect->requestalreadysend($connect['id']);
                {
                    $counter++;
                    $ctr = $model_connect->mutualconnectcount($connect['id']);
                    if(isset($_GET['r']) && $_GET['r'] == 'userwall/saved-content')
                    {
                        $dpimg = $this->getimage($connect['_id'],'photo');
                    }
                    else
                    {
                        $dpimg = $this->context->getimage($connect['_id'],'photo');
                    }
                    $form = ActiveForm::begin(
                    [
                        'id' => 'add_connect',
                        'options'=>[
                        'onsubmit'=>'return false;',
                        ],
                    ]
                );
				$result_security = SecuritySetting::find()->where(['user_id' => $connect_id])->one();
												
				if($result_security)
				{
					$lookup_settings = $result_security['my_view_status'];
				}
				else
				{
					$lookup_settings = 'Public';
				}
				$is_connect = Connect::find()->where(['from_id' => $connect_id,'to_id' => (string) $user_id,'status' => '1'])->one();
				if(($lookup_settings == 'Public') || ($lookup_settings == 'Connections' && $is_connect)) 
				{
            ?>
            <li id='remove_<?php echo $connect['id'];?>'>
                <div class="people-box">
                    <div class="img-holder"><img src="<?= $dpimg?>"/></div>
                    <input type="hidden" name="to_id" id="to_id" value="<?php echo $connect['id'];?>">
                    <div class="desc-holder">
                        <a href="<?php $id = $connect['id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>" class="userlink"><?php echo $connect['fname']." ".$connect['lname'];?></a>
                        <span class="info"><?php if($ctr > 0){?><?php echo $ctr;?> Mutual Connections<?php }else{echo "No Mutual Connect";} ?></span>								
                        <?php 
                        $result_security = SecuritySetting::find()->where(['user_id' => "$id"])->one();
                        if ($result_security)
                        {
                            $request_setting = $result_security['connect_request'];
                        }
                        else
                        {
                            $request_setting = 'Public';
                        }
                        if(($request_setting == 'Public') || ($request_setting == 'Connections of Connections' && $ctr > 0)){ ?>
                        <a href="javascript:void(0)" class="btn btn-default title_<?php echo $connect['id'];?>" title="Add connect">
                            <i class="mdi mdi-account-plus people_<?php echo $connect['id'];?>" onclick="addConnect('<?=$connect['id']?>')"></i>
                            <i class="mdi mdi-account-minus dis-none sendmsg_<?php echo $connect['id'];?>" onclick="removeConnect('<?=(string)$connect['_id']?>','<?=$uid?>','<?=(string)$connect['_id']?>', 'cancle_connect_request')"></i>  
                        </a>
                        <a href="javascript:void(0)" class="tb-pyk-remove"></a>
                        <?php } else { ?>
                            <a href="javascript:void(0)" class="btn btn-default">
                            <img onclick="privateMessage()" src="<?=$baseUrl?>/images/private-connect.png"/>
                            </a>
                        <?php } ?>
                        <a href="javascript:void(0)" onclick="removeConnectFromListing('<?php echo $connect['id'];?>')" class="close-btn"><i class="mdi mdi-close	"></i></a>
                    </div>
                </div>
            </li>
            <?php ActiveForm::end() ?>
				<?php } } } ?>
            <li class="viewall">
                <div class="right"><a href="<?php echo Url::to(['site/travpeople']); ?>">View All</a></div>
            </li>                       
        </ul>
    </div>
</div>
<script type="text/javascript" src="<?=$baseUrl?>/js/connect.js"></script>
