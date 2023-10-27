<h4><i class="mdi mdi-email"></i> Messaging</h4>
<div class="content-box">
	<h6>General Settings</h6>
	<?php if($page_details['msg_use_key'] == 'on'){$pagegen = 'on';$pageche = 'checked';}
	else{$pagegen = 'off';$pageche = '';} ?>
	<div class="settings-group">													
		<div class="row">
			<div class="col-lg-10 col-md-10 col-sm-10 col-xs-12">
				<div class="info">
					<label>Use the return key to send messages</label>
				</div>
			</div>																		
			<div class="col-lg-2 col-md-2 col-sm-2 col-xs-12 pull-right">
				<div class="pull-right">	
					<div class="switch">
							<input id="msg_use_key" class="cmn-toggle cmn-toggle-round" <?=$pageche?> type="checkbox">
							<label for="msg_use_key"></label>
							<input type="hidden" id="msg_use_key_value" value="<?=$pagegen?>" />
					</div>												
				</div>
			</div>
		</div>														
	</div>

	<h6>Response Assistant</h6>
	<?php if($page_details['send_instant'] == 'on'){$pagegen = 'on';$pageche = 'checked';}
	else{$pagegen = 'off';$pageche = '';} ?>
	<div class="settings-group">													
		<div class="row">
			<div class="col-lg-10 col-md-10 col-sm-10 col-xs-12">
				<div class="info">
					<label>Send instant replies to anyone who messages your page</label>
				</div>
			</div>																		
			<div class="col-lg-2 col-md-2 col-sm-2 col-xs-12 pull-right">
				<div class="pull-right">	
					<div class="switch">
						<input id="send_instant" class="cmn-toggle cmn-toggle-round" <?=$pageche?> type="checkbox">
						<label for="send_instant"></label>
					</div>
					<input type="hidden" id="send_instant_value" value="<?=$pagegen?>" />
				</div>
			</div>
		</div>
	</div>
	<?php if(!isset($page_details['send_instant_msg']) && empty($page_details['send_instant_msg'])){$send_instant_msg = 'Hi, thank you for contacting us. We\'ll reply back to you as soon as possible.';}
	else{$send_instant_msg = $page_details['send_instant_msg'];} ?>
	<div class="settings-group">
		<div class="normal-mode">									
			<div class="row">
				<div class="col-lg-10 col-md-3 col-sm-12 col-xs-12">
					<div class="response-msg">
						<p class="send_instant_msg_value">
							"<?=$send_instant_msg?>"
						</p>
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-2 col-xs-12 btn-holder">
					<div class="pull-right linkholder">
						<a href="javascript:void(0)" onClick="open_edit(this)">Edit</a>
					</div>
				</div>
			</div>
		</div>
		<div class="edit-mode">
			<div class="row">
				<div class="col-lg-9 col-md-9 col-sm-7 col-xs-12">
					<div class="sliding-middle-out anim-area underlined fullwidth tt-holder">
						<textarea placeholder="Enter your message" id="send_instant_msg"><?=$send_instant_msg?></textarea>
					</div>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-5 col-xs-12">
					<div class="pull-right settings-btn">		
						<a class="btn btn-primary waves-effect btn-sm" onClick="send_instant_msg(this)">Save</a>
						<a class="btn btn-primary waves-effect btn-sm" onClick="close_edit(this)">Cancel</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php if($page_details['show_greeting'] == 'on'){$pagegen = 'on';$pageche = 'checked';}
	else{$pagegen = 'off';$pageche = '';} ?>
	<div class="settings-group">													
		<div class="row">
			<div class="col-lg-10 col-md-10 col-sm-10 col-xs-12">
				<div class="info">
					<label>Show a message greeting when people start a conversation with you on messanger</label>
				</div>
			</div>																		
			<div class="col-lg-2 col-md-2 col-sm-2 col-xs-12 pull-right">
				<div class="pull-right">	
					<div class="switch">
						<input id="show_greeting" class="cmn-toggle cmn-toggle-round" <?=$pageche?> type="checkbox">
						<label for="show_greeting"></label>
					</div>
					<input type="hidden" name="friend_activity" id="show_greeting_value" value="<?=$pagegen?>" />
				</div>
			</div>
		</div>														
	</div>
	<?php if(!isset($page_details['show_greeting_msg']) && empty($page_details['show_greeting_msg'])){$show_greeting_msg = 'Hi, thank you for contacting us on messanger, Please send us any queries you may have.We\'ll be glad to help you with your query.';}
	else{$show_greeting_msg = $page_details['show_greeting_msg'];} ?>
	<div class="settings-group">
		<div class="normal-mode">									
			<div class="row">
				<div class="col-lg-10 col-md-3 col-sm-12 col-xs-12">
					<div class="response-msg">
						<p class="show_greeting_msg_value">
							"<?=$show_greeting_msg?>"
						</p>
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-2 col-xs-12 btn-holder">
					<div class="pull-right linkholder">
						<a href="javascript:void(0)" onClick="open_edit(this)">Edit</a>
					</div>
				</div>
			</div>
		</div>
		<div class="edit-mode">
			<div class="row">															
				<div class="col-lg-9 col-md-9 col-sm-7 col-xs-12">
					<div class="sliding-middle-out anim-area underlined fullwidth tt-holder">
						<textarea placeholder="Enter your message" id="show_greeting_msg"><?=$show_greeting_msg?></textarea>
					</div>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-5 col-xs-12">
					<div class="pull-right settings-btn">		
						<a class="btn btn-primary waves-effect btn-sm" onClick="show_greeting_msg(this)">Save</a>
						<a class="btn btn-primary waves-effect btn-sm" onClick="close_edit(this)">Cancel</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php exit;?>