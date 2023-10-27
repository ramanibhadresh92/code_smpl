<h4><i class="zmdi zmdi-account"></i> Page Admins</h4>
<div class="content-box">
	<p>You can add other people to help you manage your page with different roles based on their required work.</p>
	<div class="manage-role">
		<div class="pagerole-holder">
			<h5>Add another person</h5>
			<div class="pagerole-box addrole-box">
				<div class="imgholder"></div>
				<div class="descholder">
					<div class="frow">
						<div class="sliding-middle-custom anim-area underlined">
							<input type="text" id="roleid" class="addrole-name" placeholder="Type a name or email..."/>
						</div>
					
					</div>
					<div class="dropdown dropdown-custom setDropVal left leftDrop">
						<div class="public_dropdown_container"						>
						<a href="javascript:void(0)" class="dropdown_text dropdown-button-left" data-activates="post_privacy_composeren"><span>Admin</span><i class="zmdi zmdi-caret-down"></i></a>
						<ul id="post_privacy_composeren" class="dropdown-privacy dropdown-content custom_dropdown">
							<li><a href="javascript:void(0)" onclick="resetInfoText(this, 'Admin')">Admin</a></li>
							<li><a href="javascript:void(0)" onclick="resetInfoText(this, 'Editor')">Editor</a></li>
							<li><a href="javascript:void(0)" onclick="resetInfoText(this, 'Supporter')">Supporter</a></li>
						</ul>
						<input type="hidden" value="Admin" id="roletype">
					</div>													
					<div class="clear"></div>
					<div class="frow">															
						<p class="infotext">can edit the page, send messages, settings expect assigning role, publish the page, create ads, create a post or comment and view insights.</p>
					</div>															
				</div>
			</div>													
		</div>
		<div class="pull-right">
			<a href="javascript:void(0)" class="add-pagerole btngen-center-align waves-effect" onclick="addPageRole()">Add Role</a>
		</div>
	</div>
</div>
<h4>Existing Page Admins</h4>
<div class="content-box">
	<h6>Admin</h6>
	<p>Admin can manage all aspects of the page including sending messages and publishing as the page, creating ads, seeing which admin created a post or comment, viewing insights and assigning page roles.</p>
	<?php if(empty($pageadmins)){ ?>
	<div class="no-listcontent">
		No admin for <?=$page_details['page_name']?> page
	</div>
	<?php } foreach($pageadmins as $pagerole){
		$fullname = $this->context->getuserdata($pagerole['user_id'],'fullname');
		$img = $this->context->getimage($pagerole['user_id'],'photo');
	?>
	<div class="admin-holder pagerole-holder adminrole">
		<div class="pagerole-box">
			<div class="imgholder"><img src="<?=$img?>"/></div>
			<div class="descholder">
				<h5><?=$fullname?></h5>
				<div class="frow">
					<p>Admin <a href="javascript:void(0)" class="inline-tooltip" title="info goes here">[?]</a></p>
				</div>
				<?php if($pagerole['user_id'] != $page_details['created_by']){ ?>
				<a href='javascript:void(0)' class='closebtn' onclick='removePageRole(this,"<?=$pagerole['_id']?>")'><i class='mdi mdi-close	'></i></a>
				<?php } ?>
			</div>
		</div>													
	</div>
	<?php } ?>
	<div class="pagerole-holder adminrole"></div>    
	<h6>Editor</h6>
	<p>Editors can edit the page, send messages, settings expect assigning role, publish the page, create ads, create a post or comment and view insights.</p>
	<?php if(empty($pageeditors)){ ?>
	<div class="no-listcontent">
		No editor for <?=$page_details['page_name']?> page
	</div>
	<?php } foreach($pageeditors as $pagerole){
		$fullname = $this->context->getuserdata($pagerole['user_id'],'fullname');
		$img = $this->context->getimage($pagerole['user_id'],'photo');
	?>
	<div class="admin-holder pagerole-holder adminrole">
		<div class="pagerole-box">
			<div class="imgholder"><img src="<?=$img?>"/></div>
			<div class="descholder">
				<h5><?=$fullname?></h5>
				<div class="frow">
					<p>Editor <a href="javascript:void(0)" class="inline-tooltip" title="info goes here">[?]</a></p>
				</div>
				<a href='javascript:void(0)' class='closebtn' onclick='removePageRole(this,"<?=$pagerole['_id']?>")'><i class='mdi mdi-close	'></i></a>
			</div>
		</div>
	</div>
	<?php } ?>
	<div class="pagerole-holder editorrole"></div>
	<h6>Supporter</h6>
	<p>Supporter can respond to and delete comments on the page, send messages as the page and respond to the messages, create ads and view insights.</p>
	<?php if(empty($pagesupporters)){ ?>
	<div class="no-listcontent">
		No supporter for <?=$page_details['page_name']?> page
	</div>
	<?php } foreach($pagesupporters as $pagerole){
		$fullname = $this->context->getuserdata($pagerole['user_id'],'fullname');
		$img = $this->context->getimage($pagerole['user_id'],'photo');
	?>
	<div class="admin-holder pagerole-holder adminrole">
		<div class="pagerole-box">
			<div class="imgholder"><img src="<?=$img?>"/></div>
			<div class="descholder">
				<h5><?=$fullname?></h5>
				<div class="frow">
					<p>Supporter <a href="javascript:void(0)" class="inline-tooltip" title="info goes here">[?]</a></p>
				</div>
				<a href='javascript:void(0)' class='closebtn' onclick='removePageRole(this,"<?=$pagerole['_id']?>")'><i class='mdi mdi-close	'></i></a>
			</div>
		</div>													
	</div>
	<?php } ?>
	<div class="pagerole-holder supporterrole">
	</div>
</div>
<?php exit;?>