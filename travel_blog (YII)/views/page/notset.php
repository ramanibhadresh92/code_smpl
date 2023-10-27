<h4><i class="zmdi zmdi-notifications"></i> Notifications</h4>
<div class="content-box">
	<?php if($page_details['not_add_post'] == 'on'){$pagegen = 'on';$pageche = 'checked';}
	else{$pagegen = 'off';$pageche = '';}
	?>
	<div class="settings-group">													
		<div class="row">
			<div class="col l10 m10 s12">
				<div class="info">
					<p>Get a notification when there is a new post added to the page</p>
				</div>
			</div>																		
			<div class="col l2 m2 s12 pull-right">
				<div class="pull-right">	
					<div class="switch">
						<label>
						<input id="not_add_post" class="cmn-toggle cmn-toggle-round" <?=$pageche?> type="checkbox">
						<span class="lever"></span>
						</label>
					</div>
					<input type="hidden" id="not_add_post_value" value="<?=$pagegen?>" />
				</div>
			</div>
		</div>														
	</div>
	<?php if($page_details['not_add_comment'] == 'on'){$pagegen = 'on';$pageche = 'checked';}
	else{$pagegen = 'off';$pageche = '';}
	?>
	<div class="settings-group">													
		<div class="row">
			<div class="col l10 m10 s12">
				<div class="info">
					<p>Get a notification when there is a new comment to the page post</p>
				</div>
			</div>																		
			<div class="col l2 m2 s12 pull-right">
				<div class="pull-right">	
					<div class="switch">
						<label>
						<input id="not_add_comment" class="cmn-toggle cmn-toggle-round" <?=$pageche?> type="checkbox">
						<span class="lever"></span>
						</label>
					</div>
					<input type="hidden" id="not_add_comment_value" value="<?=$pagegen?>" />
				</div>
			</div>
		</div>														
	</div>
	<?php if($page_details['not_like_page'] == 'on'){$pagegen = 'on';$pageche = 'checked';}
	else{$pagegen = 'off';$pageche = '';}
	?>
	<div class="settings-group">													
		<div class="row">
			<div class="col l10 m10 s12">
				<div class="info">
					<p>Get a notification when there is a new like to the page</p>
				</div>
			</div>																		
			<div class="col l2 m2 s12 pull-right">
				<div class="pull-right">	
					<div class="switch">
						<label>
						<input id="not_like_page" class="cmn-toggle cmn-toggle-round" <?=$pageche?> type="checkbox">
						<span class="lever"></span>
						</label>
					</div>
					<input type="hidden" id="not_like_page_value" value="<?=$pagegen?>" />
				</div>
			</div>
		</div>														
	</div>
	<?php if($page_details['not_like_post'] == 'on'){$pagegen = 'on';$pageche = 'checked';}
	else{$pagegen = 'off';$pageche = '';}
	?>
	<div class="settings-group">													
		<div class="row">
			<div class="col l10 m10 s12">
				<div class="info">
					<p>Get a notification when there is a new like on page post</p>
				</div>
			</div>
			<div class="col l2 m2 s12 pull-right">
				<div class="pull-right">	
					<div class="switch">
						<label>
						<input id="not_like_post" class="cmn-toggle cmn-toggle-round" <?=$pageche?> type="checkbox">
						<span class="lever"></span>
						</label>
					</div>
					<input type="hidden" id="not_like_post_value" value="<?=$pagegen?>" />
				</div>
			</div>
		</div>														
	</div>
	<?php if($page_details['not_post_edited'] == 'on'){$pagegen = 'on';$pageche = 'checked';}
	else{$pagegen = 'off';$pageche = '';}
	?>
	<div class="settings-group">													
		<div class="row">
			<div class="col l10 m10 s12">
				<div class="info">
					<p>Get a notification when page post gets edited</p>
				</div>
			</div>																		
			<div class="col l2 m2 s12 pull-right">
				<div class="pull-right">	
					<div class="switch">
						<label>
						<input id="not_post_edited" class="cmn-toggle cmn-toggle-round" <?=$pageche?> type="checkbox">
						<span class="lever"></span>
						</label>
					</div>
					<input type="hidden" id="not_post_edited_value" value="<?=$pagegen?>" />
				</div>
			</div>
		</div>														
	</div>
	<?php if($page_details['not_get_review'] == 'on'){$pagegen = 'on';$pageche = 'checked';}
	else{$pagegen = 'off';$pageche = '';}
	?>
	<div class="settings-group">													
		<div class="row">
			<div class="col l10 m10 s12">
				<div class="info">
					<p>Get a notification each time the page gets a review</p>
				</div>          
			</div>																		
			<div class="col l2 m2 s12 pull-right">
				<div class="pull-right">	
					<div class="switch">
						<label>
						<input id="not_get_review" class="cmn-toggle cmn-toggle-round" <?=$pageche?> type="checkbox">
						<span class="lever"></span>
						</label>
					</div>
					<input type="hidden" id="not_get_review_value" value="<?=$pagegen?>" />
				</div>
			</div>
		</div>														
	</div>
	<?php if($page_details['not_msg_rcv'] == 'on'){$pagegen = 'on';$pageche = 'checked';}
	else{$pagegen = 'off';$pageche = '';}
	?>
	<div class="settings-group">													
		<div class="row">
			<div class="col l10 m10 s12">
				<div class="info">
					<p>Get a notification each time your page receives a message</p>
				</div>
			</div>																		
			<div class="col l2 m2 s12 pull-right">
				<div class="pull-right">	
					<div class="switch">
						<label>
						<input id="not_msg_rcv" class="cmn-toggle cmn-toggle-round" <?=$pageche?> type="checkbox">
						<span class="lever"></span>
						</label>
					</div>
					<input type="hidden" id="not_msg_rcv_value" value="<?=$pagegen?>" />
				</div>
			</div>
		</div>														
	</div>															
</div>
<?php exit;?>