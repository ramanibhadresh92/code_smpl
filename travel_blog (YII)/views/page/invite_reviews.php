<?php 
use frontend\models\LoginForm;
use frontend\models\Connect;
use frontend\models\Notification;
use frontend\models\Like;

$i = 0;
if(count($eml_id) > 0){ ?>
	<div class="sresult-list nice-scroll">
	<ul>
		<?php $start = 0;
		foreach($eml_id as $invitedconnections){
			if (!(isset($_GET['key']) && $_GET['key'] != '')) {
				$connectid = (string)$invitedconnections['to_id'];
			} else {
				$connectid = (string)$invitedconnections['_id'];
			}

			$result = LoginForm::find()->where(['_id' => $connectid])->one();
			$frndimg = $this->context->getimage($connectid,'thumb');
			$pagelikeexist = Like::find()->where(['post_id' => "$page_id", 'user_id' => "$connectid", 'status' => '1', 'like_type' => 'page'])->all();
			$invitaionsent = Notification::find()->where(['post_id' => "$page_id", 'status' => '1', 'from_connect_id' => "$connectid", 'user_id' => "$user_id"])->one();
			$is_connect = Connect::find()->where(['from_id' => "$user_id",'to_id' => "$connectid",'status' => '1'])->one();
			if($is_connect) {
				if($_GET['key'] != '') {
                    $fname = isset($result['fname']) ? $result['fname'] : '';
                    $lname = isset($result['lname']) ? $result['lname'] : '';
                    $name = isset($result['fullname']) ? $result['fullname'] : '';
                    if (stripos($fname, $_GET['key']) === 0 || stripos($lname, $_GET['key']) === 0 || stripos($name, $_GET['key']) === 0) {
                    } else {
                        continue;
                    }
                }
		?>
		<li class="invite_<?=$connectid?>">
			<div class="invitelike-friend invitelike-connect">
				<div class="imgholder"><img src="<?=$frndimg?>"/></div>
				<div class="descholder">
					<h6><?=$result['fullname']?></h6>
					<div class="btn-holder events_<?=$connectid?>">
						<?php if($pagelikeexist)
						{
							echo '<label class="infolabel"><i class="zmdi zmdi-check"></i> Liked</label>';
						}
						else if($invitaionsent)
						{
							echo '<label class="infolabel"><i class="zmdi zmdi-check"></i> Invited</label>';
						}
						else
						{ ?>
							<a href="javascript:void(0)" onclick="sendinvite('<?=$connectid?>','<?=$page_id?>')" class="btn-invite">Invite</a>
							<a href="javascript:void(0)" onclick="cancelinvite('<?=$connectid?>')" class="btn-invite-close"><i class="mdi mdi-close"></i></a>
						<?php } ?>
					</div>
					<div class="dis-none btn-holder sendinvitation_<?=$connectid?>">
						<label class="infolabel"><i class="zmdi zmdi-check"></i> Invitation sent</label>
					</div>
				</div>														
			</div>
		</li>
		<?php $start++; } } ?>
		<?php if($start == 0){ ?>
		<?php $this->context->getnolistfound('nomoreconnectfound'); ?>
		<?php } ?>
		</ul>
	</div>
	<?php } else { ?>
	<?php $this->context->getnolistfound('noconnectfound'); ?>
	<?php } ?>
<?php exit;?>