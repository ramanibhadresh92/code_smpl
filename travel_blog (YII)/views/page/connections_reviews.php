<?php 
use frontend\models\LoginForm;
use frontend\models\Connect;
use frontend\models\Notification;
use frontend\models\PostForm;

$i = 0;
if(count($eml_id) > 0){ ?>
	<div class="sresult-list nice-scroll">
	<ul>
		<?php $start = 0;
		foreach($eml_id as $invitedConnections){
			if (empty($_GET['key']))
			{
				$connectid = (string)$invitedConnections['to_id'];
			}
			else
			{
				$connectid = (string)$invitedConnections['_id'];
			}
			$result = LoginForm::find()->where(['_id' => $connectid])->one();
			$frndimg = $this->context->getimage($connectid,'thumb');
			$pagereviewexist = $pagelikeexist = PostForm::find()->where(['page_id' => "$page_id", 'post_user_id' => "$connectid", 'is_deleted' => '0', 'is_page_review' => '1'])->one();
			$invitaionsent = Notification::find()->where(['post_id' => "$page_id", 'status' => '1', 'from_connect_id' => "$connectid", 'user_id' => "$user_id", 'notification_type' => 'pageinvitereview'])->one();
			$is_connect = Connect::find()->where(['from_id' => "$user_id",'to_id' => "$connectid",'status' => '1'])->one();
			if($is_connect){
		?>
		<li class="invite_<?=$connectid?>">
			<div class="invitelike-friend invitelike-friend invitelike-connect">
				<div class="imgholder"><img src="<?=$frndimg?>"/></div>
				<div class="descholder">
					<h6><?=$result['fullname']?></h6>
					<div class="btn-holder events_<?=$connectid?>">
						<?php if($pagereviewexist)
						{
							echo '<label class="infolabel"><i class="zmdi zmdi-check"></i> Reviewed</label>';
						}
						else if($invitaionsent)
						{
							echo '<label class="infolabel"><i class="zmdi zmdi-check"></i> Invited</label>';
						}
						else
						{ ?>
							<a href="javascript:void(0)" onclick="sendinvitereview('<?=$connectid?>','<?=$page_id?>')" class="btn-invite">Invite</a>
							<a href="javascript:void(0)" onclick="cancelinvitereview('<?=$connectid?>')" class="btn-invite-close"><i class="mdi mdi-close"></i></a>
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