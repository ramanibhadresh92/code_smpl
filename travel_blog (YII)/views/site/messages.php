<?php   
use yii\helpers\Url;
use frontend\assets\AppAsset; 
use frontend\models\Connect;
use frontend\models\SecuritySetting;
use frontend\models\SuggestConnect;
use frontend\models\PostForm;
use frontend\models\MessageBlock;
use frontend\models\Messages;
use frontend\models\Userform;
use backend\models\Googlekey; 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string)$session->get('user_id'); 
$email = $this->context->getuserdata($user_id,'email');
$fullname = $this->context->getuserdata($user_id,'fullname');
$data = array('id' => $user_id, 'email'=> $email, 'fullname' => $fullname);

$con_ids = MessageBlock::find()->where(['from_id' => (string)$user_id])->orwhere(['to_id' => (string)$user_id])->asarray()->all();

$extradata = array();
foreach ($con_ids as $key => $value) {
	$con_id = $value['con_id'];
	$info = Messages::find()->select(['_id'])->where(['con_id' => $con_id])->andwhere(['category' => 'inbox'])->orderBy(['_id' => SORT_DESC])->asarray()->one();
	if($info) {
		$extradata[] = (string)$info['_id'];
	}
}

$info = Messages::find()->where(['in', '_id', $extradata])->orderBy(['created_at' => SORT_DESC])->asarray()->all();

$newArray = array();
foreach ($info as $key => $value) {
	$id = $value['to_id'];
	if($id == $user_id) {
		$id = $value['from_id'];
	}

	$user = Userform::find()->select(['fname', 'lname', 'thumb','email', 'thumbnail', 'status', 'gender'])->where([(string)'_id' => $id])->asarray()->one();
	if($user) {
		$newArray[] = $user;
	}
}

$thumb = $this->context->getimage($user_id, 'thumb');
$country = $this->context->getuserdata($user_id,'country');
$posts = PostForm::getUserPost($user_id); 
$pending_requests = Connect::friendPendingRequests();
$suggetion_requests = SuggestConnect::find()->where(['suggest_to' => "$user_id",'status' => '0'])->all();
$userinfo = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname, 'thumb' => $thumb, 'country' => $country);
$this->title = 'Messages';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
    <link href="<?=$baseUrl?>/css/emoticons.css" rel="stylesheet">
    <link href="<?=$baseUrl?>/css/emostickers.css" rel="stylesheet">
    <div class="page-wrapper messages-wrapper full-wrapper noopened-search hidemenu-wrapper whitebg">
        <div class="header-section">
            <?php include('../views/layouts/header.php'); ?>
        </div>
        <div class="floating-icon">   
            <div class="scrollup-btnbox anim-side btnbox scrollup-float">
                <div class="scrollup-button float-icon"><span class="icon-holder ispan"><i class="mdi mdi-arrow-up-bold-circle"></i></span></div> 
            </div>           
        </div>
        <div class="clear"></div>
		<div class="container page_container">
			<?php include('../views/layouts/leftmenu.php'); ?>

			<span class="search-xs-btn" onclick="personSearchSlide()">
				<i class="zmdi zmdi-search"></i>
			</span>
			<span class="messagesearch-xs-btn" onclick="messageSearchSlide()">
				<i class="zmdi zmdi-search mdi-17px"></i>
			</span>
			<div class="settings-icon globel_setting">
				<a class='dropdown-button more_btn' href='javascript:void(0)' data-activates='globel_setting_xs'>
					<i class="zmdi zmdi-more-vert zmdi-hc-lg"></i>
				</a>
				<ul id='globel_setting_xs' class='dropdown-content custom_dropdown' onclick=''>
					<li> 
						<a href="javascript:void(0)" id="" onclick="new_group_action()" class="i_mute">New group</a>
					</li>
					<li>
						<a href="javascript:void(0)" onclick="MessageRequest()" class="i_mute">New requests</a>
					</li>
					<li>
						<a href="javascript:void(0)" id="" onclick="getallarchivedusers()">Archived</a>
					</li>
					<li>
						<a href="javascript:void(0)" id="" onclick="getallsavedmsg()">Saved messages</a>
					</li>
					<li>
						<a href="javascript:void(0)" id="" onclick="getmessageleftsetting()">Settings</a>
					</li>
					<li>
						<a href="javascript:void(0)" id="" onclick="getallblockusers()">Block</a>
					</li>
				</ul>
			</div>
			<div class="settings-icon person_dropdown xs_dropdown">
				<a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="setting_messages_xs">
					<i class="zmdi zmdi-more-vert zmdi-hc-lg"></i>
				</a>
				<ul id="setting_messages_xs" class="dropdown-content custom_dropdown">
				</ul>
			</div>
			<div class="fixed-layout messages-page-fixed mt-0">
				<div class="main-content with-lmenu messages-page main-page">
					<div class="combined-column">
						<div class="content-box messages-list">
							<div class="cbox-desc">
								<div class="left-section">
									<div class="side-user">
										<div class="side_user_container">
											<span class="img-holder"><a href="<?php echo Url::to(['userwall/index', 'id' => $user_id]); ?>"><img src="<?=$thumb?>"></a></span>
											<a href="javascript:void(0)"><span class="desc-holder"><?=$fullname?></span></a>
										</div>

										<div class="settings-icon globel_setting">

												<a class='dropdown-button more_btn' href='javascript:void(0)' data-activates='globel_setting'>
													<i class="zmdi zmdi-more-vert"></i>
												</a>
												<ul id='globel_setting' class='dropdown-content custom_dropdown'>
													<li>
														<a href="javascript:void(0)" id="" onclick="new_group_action()" class="i_mute">New group</a>
													</li>
													<li>
														<a href="javascript:void(0)" onclick="MessageRequest()" class="i_mute">New requests</a>
													</li>
													<li>
														<a href="javascript:void(0)" id="" onclick="getallarchivedusers()">Archived</a>
													</li>
													<li>
														<a href="javascript:void(0)" id="" onclick="getallsavedmsg()">Saved messages</a>
													</li>
													<li>
														<a href="javascript:void(0)" id="" onclick="getmessageleftsetting()">Settings</a>
													</li>
													<li>
														<a href="javascript:void(0)" id="" onclick="getallblockusers()">Block</a>
													</li>
												</ul>
											</div>

									</div>
									<div class="friends-search search_lg">
										<div class="fsearch-form">
											<nav class="message_search">
												<div class="nav-wrapper">
													<form>
														<div class="input-field">
														<input id="msg_search" class="messagewallsearch message_user_search custom_msg_search" type="search" placeholder="search" required="" />
															<a href="javascript:void(0)" class="label-icon" for="search">
																<i class="zmdi zmdi-search"></i>
															</a>
														</div>
													</form>
												</div>
											</nav> 
										</div>
									</div>
									<div class="clear"></div>
									<div class="tab-content">   
										<div class="tab-pane fade main-pane active in" id="messages-inbox">
											<div class="gloader-holder doneloading">									
												<div class="gloader-content">
													<div class="message-userlist nice-scroll">
														<ul class="users-display">
														</ul>
													</div>
												</div>
											</div>
										</div>
										<div class='clear'></div>
									</div>
									<!-- <a href="javascript:void(0)" onclick="openNewMessagemobile(this)" id="addnewchat" class="addnewchat mobile-btn"><i class="zmdi zmdi-edit"></i></a> -->
									<a href="javascript:void(0)" onclick="openpersonSlide()" id="addnewchat" class="addnewchat mobile-btn">
				                     	<i class="zmdi zmdi-edit"></i>
									</a>
								</div>
								<div class="right-section">
									<div id="detailbox">
										<div class="topstuff">
											<div class="msgwindow-name curtusrbasicinfo">
											</div>

											<div class="msgwindow-group">
												<div class="imgholder fourpersongroup">
													<div class="group-person">
														<img src="<?=$baseUrl?>/images/whoisaround-img.png"/>
													</div>
													<div class="group-person">
														<img src="<?=$baseUrl?>/images/demo-profile.jpg">
													</div>
													<div class="group-person">
														<img src="<?=$baseUrl?>/images/demo-profile.jpg">
													</div>
													<div class="group-person">
														<img src="<?=$baseUrl?>/images/whoisaround-img.png"/>
													</div>
													
												</div>
												
												<span class="desc-holder">
													<span class="group-name-default">
														<span class="group-name"> Vipul Patel </span>
														<a href="javascript:void(0)" id="edit-group-name" class="edit-group-name"><i class="mdi mdi-pencil" ></i></a> 
													</span>
													<span class="group-name-editor">
														<input type="text" class="group-name-input" />
														<a href="javascript:void(0)" id="edit-group-done" class="edit-group-done"><i class="zmdi zmdi-check" ></i></a>
														<a href="javascript:void(0)" id="edit-group-cancle" class="edit-group-cancle"><i class="mdi mdi-close" ></i></a>
													</span>
												</span> 
												<span class="group-status"><i class="mdi mdi-chevron-right" ></i>&nbsp; 2 participants</span>
												
											</div>

											<div class="friends-search"">				
												<div class="header_right_icon" id="videocamcall">
													<i class="zmdi zmdi-hc-2x zmdi-videocam i_mute"></i>
												</div>
												<div class="header_right_icon">
													<i class="zmdi zmdi-hc-2x zmdi-phone i_mute"></i>
												</div>
												<div class="header_right_icon"  onclick="add_person_group()">
													<i class="zmdi zmdi-hc-2x zmdi-account-add i_mute"></i>
												</div>
												<div class="header_right_icon msg_search_icon" id="search_messages_Action">
													<i class="zmdi zmdi-hc-2x zmdi-search"></i>
												</div>

												<div class="header_right_icon settings-icon person_dropdown dropdown-friendlock"> 
												   <a class='dropdown-button more_btn' href='javascript:void(0)' data-activates='setting_messages'>
														<i class="zmdi zmdi-hc-2x zmdi-more-vert"></i>
													</a>
													<ul id='setting_messages' class='dropdown-content custom_dropdown messageOptionsClkoptionshtml'>
													</ul>
												</div>
											</div>
											<div class="add-chat-search">
												<label>To: </label>
												<select class="add-multi-friends userselect2">
												</select>
												<a href="javascript:void(0)" id="canceladdchat" class="canceladdchat"><i class="mdi mdi-close	"></i></a>
												<a href="javascript:void(0)" id="doneaddchat" class="btn btn-sm btn-primary doneaddchat right">Done</a>
											</div>
										</div>
										<div class="main-msgwindow">
											<input class="customsearchmain input" type="text" placeholder="Search message text" autocomplete="off">
				                           <a href="javascript:void(0)" class="customsearchmain searchicon label-icon" for="search">
				                              <i class="zmdi zmdi-search"></i>
				                           </a>
				                           <div>
											<h4 class="dateshower"></h4>
											<div class="photos-thread">
												<a href="javascript:void(0)" onclick="hideMsgPhotos()" class="backlink"><i class="mdi mdi-menu-left"></i> Back to conversation</a>
												<div class="albums-grid images-container">
													<div class="row">												
																						
													</div>
												</div>

											</div>
											<div class="allmsgs-holder">
												<div class="msg-notice">
													<div class="mute-notice">
														This conversation has been muted. All the push notifications will be turned off. <a href="javascript:void(0)" onclick="manageMuteConverasion()">Unmute</a>
													</div>
													<div class="block-notice">
														This conversation is blocked. <a href="javascript:void(0)" onclick="manageBlockConverasion(this)">Unblock</a>
													</div>
												</div>
												<ul class="current-messages"> 
												</ul>
												<div class="newmessage" id="li-user-blank">
													<div class="msgdetail-list nice-scroll" style="overflow-y: hidden;" tabindex="6"></div>
												</div>

												<div class="hidden-attachment-box hidden">
					                              <div class="innerhidden-attachment-box">
					                                 <div class="up">
					                                    <div class="wp-location attachmentdiv">
					                                       <a href="javascript:void(0)">
					                                          <div class="selfdiv">
					                                             <center>
					                                                <img src="<?=$baseUrl?>/images/wp-simily.png">
					                                                <div>Video</div>
					                                             </center>
					                                          </div>
					                                       </a>
					                                    </div>
					                                    <div class="wp-photo attachmentdiv">
					                                       <a href="javascript:void(0)">
					                                          <div class="selfdiv">
					                                             <center>
					                                                <img src="<?=$baseUrl?>/images/wp-camera.png">
					                                                <div>Camera</div>
					                                             </center>
					                                          </div>
					                                       </a>
					                                    </div>
					                                    <div class="wp-video attachmentdiv">
					                                       <a href="javascript:void(0)">
					                                          <div class="selfdiv">
					                                             <center>
					                                             	<img src="<?=$baseUrl?>/images/wp-photovideo.png" onclick="attach_file_add()">
					                                                <div>Photo</div>
					                                                <input type="file" id="attach_file_add" class="dis-none" />
					                                             </center>
					                                          </div>
					                                       </a>
					                                    </div>
					                                 </div>
					                                 <div class="bottom">
					                                    <div class="wp-gift attachmentdiv">
					                                       <a href="javascript:void(0)" onclick="giftModalAction()">
					                                          <div class="selfdiv">
					                                             <center>
					                                                <img src="<?=$baseUrl?>/images/wp-gift.png">
					                                                <div>Gift</div>
					                                             </center>
					                                          </div>
					                                       </a>
					                                    </div>
					                                    <div class="wp-location attachmentdiv">
					                                       <a href="javascript:void(0)">
					                                          <div class="selfdiv">
					                                             <center>
					                                                <img src="<?=$baseUrl?>/images/wp-location.png">
					                                                <div>Location</div>
					                                             </center>
					                                          </div>
					                                       </a>
					                                    </div>
					                                    <div class="wp-location attachmentdiv">
					                                       <a href="javascript:void(0)">
					                                          <div class="selfdiv">
					                                             <center>
					                                                <img src="<?=$baseUrl?>/images/wp-contact.png">
					                                                <div>Contact</div>
					                                             </center>
					                                          </div>
					                                       </a>
					                                    </div>
					                                    
					                                 </div>
					                              </div>
					                            </div>

												<div class="addnew-msg">
												   <div class="write-msg input-field">
												      <div class="fixed-action-btn horizontal click-to-toggle attachment_add_icon docustomize">
												         <a href="javascript:void(0)"><i class="mdi mdi-attachment mdi-rotate-135 prefix"></i></a>
												         <ul>
															<li>	
												         		<a href="javascript:void(0)">
												                  <img src="<?=$baseUrl?>/images/wp-location.png">
												               </a>
												            </li>
												            <li>	
												         		<a href="javascript:void(0)">
												                  <img src="<?=$baseUrl?>/images/wp-contact.png">
												               </a>
												            </li>
												            <li>
												               <a href="javascript:void(0)">
												                  <img src="<?=$baseUrl?>/images/wp-photovideo.png" onclick="attach_file_add()">
												                  <input type="file" id="attach_file_add" class="dis-none" />
												               </a>
												            </li>
												            <li>
												               <a href="javascript:void(0)" onclick="giftModalAction()">
												                  <img src="<?=$baseUrl?>/images/wp-gift.png">
												               </a>
												            </li>
												         </ul>
												      </div>
												      <div class="emotion-holder gifticonclick">
												          <i class="zmdi zmdi-mood msgemojisbtn" onclick="manageEmotionBox(this,'messages')"></i>
												          <div class="emotion-box dis-none">
												            <div class="nice-scroll emotions">
												               <ul class="emotion-list">
												               </ul>
												            </div>
												          </div>
												      </div>
												      <textarea id="inputMessageWall" class="inputMessageWall materialize-textarea" placeholder="Type your message"></textarea>
												   </div>   
												   <div class="msg-stuff">
												      <div class="send-msg">
												         <button class="btn btn-primary btn-xxs btn-msg-send" onclick="messageSendFromMessage();"><i class="mdi mdi-telegram"></i></button>
												      </div>
												   </div>
												</div>
												
												<div class="bottom-stuff">
													<h6>Select messages to delete</h6>
													<div class="btn-holder">
														<a href="javascript:void(0)" class="btn btn-primary btn-sm" onclick="deleteselectedmessage()">Delete</a>
														<a href="javascript:void(0)" class="btn btn-primary btn-sm" onclick="hideMsgCheckbox()">Cancel</a>
													</div>
												</div>
												<div class="selected_messages_box">
													<a class="close_selected_messages_box waves-effect" onclick="closeSelectedMessage()">
														<i class="mdi mdi-close mdi-20px	"></i>
													</a>
													<p class="selected_msg_number">
														<span>0</span>  selected
													</p>
													<div class="selected_msg_functions">
														<a onclick="selectedmessagesaved()">
															<i class="zmdi zmdi-star"></i>
														</a>
														<a onclick="deleteselectedmessage()">
															<i class="zmdi zmdi-delete"></i>
														</a> 
														<a>
															<i class="zmdi zmdi-forward"></i>
														</a>
														<a class="downloadicon">
															<i class="zmdi zmdi-upload"></i>
														</a>
													</div>
												</div>
											</div>
											</div>
										</div>
									</div>
									<div id="callbox" class="dis-none" style="height: 500px; width: 98%; background: black; padding-left: 36%;">
										<div class="callboxinnerbox" id="box1" style="width: 200px; height: 200px; ">
											<img style="width: 100%; height: 100%" src="">
										</div>
										<div class="callboxinnerbox" id="box2" style="margin: 20px 0; height: 55px;  width: 200px; padding-left: 57px;"><img style="transform: rotate(90deg); padding-left: 0px;" src="<?=$baseUrl?>/images/msgvideoprocess.gif"></div>

										<div class="callboxinnerbox" id="box3" style="width: 200px; height: 200px; ">
											<video id='minivideo' autoplay='autoplay' style="width: 100%; height: 100%;"></video>
      										<div id='ownvolume'></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div> 
			</div>
        </div>
    </div>  
		
    <!--side modal-->
	<!--new group modal-->
	<div class="new_group_modal side_modal left_side_modal">
		<div class="custom_side_header">
			<span class="slide_out_btn slide_out_right_btn close_side_slider waves-effect">
				<i class="zmdi zmdi-arrow-left"></i>
			</span>
			<h3>New Group</h3>
		</div>
		<div class="side_modal_container side_modal_content new_group_content">
			<span class="big_profile_image">
				<img src="<?=$baseUrl?>/images/whoisaround-img1.png" />
			</span>
			<div class="group_name_form">
				<input placeholder="Group Name" id="group_name" type="text"  />
			</div>
			<span class="btn-floating btn-large bottom_newgroup_btn" onclick="addPerson()">
				<i class="zmdi zmdi-arrow-right"></i>
			</span>
		</div>
	</div>

	<!--Message request modal-->
	<div class="message_request_modal side_modal left_side_modal">
		<div class="custom_side_header">
			<span class="slide_out_btn slide_out_right_btn close_side_slider waves-effect">
				<i class="zmdi zmdi-arrow-left"></i>
			</span>
			<h3>Message Requests</h3>
		</div>
		<div class="side_modal_container side_modal_content">
			<div class="message_request_msg">
				<p>Open a request to get more info about who's messaging you.They won't know you've seen it until you accept.</p>
			</div>
			<div class="message_requst_info">
				<span class="participants_profile">
					<img src="<?=$baseUrl?>/images/whoisaround-img.png" />
				</span>
				<span class="participants_name">
					<span class="day_time">
						Tuesday
					</span>
					<span class="msg_name">Vipul Patel</span>
					<span class="requsted_msg">Hi</span>
					<span class="request_option">
						<a class="request_approve" href="javascript:void(0)">Accept</a>
						<a class="request_reject" href="javascript:void(0)">Decline</a>
					</span>

				</span>

			</div>
		</div>
	</div>

	<!--create group modal-->
	<div class="create_group_modal side_modal right_side_modal">
		<div class="custom_side_header">
			<span class="slide_out_btn slide_out_right_btn close_side_slider waves-effect"  onclick="add_to_Creategroup_close()">
				<i class="mdi mdi-close mdi-20px	"></i>
			</span>
			<h3>Create Group</h3>
			<a class="waves-effect waves-light btn add_to_group_done" onclick="add_to_Creategroup_close()">Done</a>
		</div>
		<div class="side_modal_container side_modal_content create_group_content three_person">
			<div class="group_info_profile">
				<span class="big_profile_image">
					<div>
						<img src="<?=$baseUrl?>/images/whoisaround-img.png" />
						<img src="<?=$baseUrl?>/images/whoisaround-img1.png" />
						<img src="<?=$baseUrl?>/images/demo-profile.jpg" />
					</div>
					<input type="file" id="upload_creategroup_img dis-none" />
					<label for="upload_creategroup_img" class="edit_profile_pic">
						<i class="zmdi zmdi-edit"></i>
					</label>
				</span>
			</div>
			<div class="creategroup_name_form">
				<div class="contact_user_container">
					<p class="contact_user_name">Vipul Patel, Bhadresh Ramani</p>
					<div class="group_name_text dis-none">
						<input placeholder="" id="create_group_name_text" type="text" class="validate" />
					</div>

					<p class="contact_user_lastseen">Created by 12345 67890, 06/nov/2017</p>

					<span class="group_name_edit" onclick="group_name_edit()" >
						<i class="zmdi zmdi-edit"></i>
					</span>
					<span class="group_name_remove" onclick="group_name_remove()" >
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>




			</div>
			<div class="participants_container">
				<p class="participants_number">2 participants</p>
				<a class="participants_info" onclick="CreateGroupaddPerson()">
					<span class="participants_profile">
						<span class="add_participents">
							<i class="zmdi zmdi-account-add"></i>
						</span>
					</span>
					<span class="participants_name add_participants_name">Add Participants</span>
				</a>
				<div class="participants_info">
					<span class="participants_profile">
						<img src="<?=$baseUrl?>/images/whoisaround-img.png" />
					</span>
					<span class="participants_name">Vipul Patel</span>
					<span class="group_admin">
						Group Admin
					</span>
				</div>
				<div class="participants_info">
					<span class="participants_profile">
						<img src="<?=$baseUrl?>/images/whoisaround-img1.png" />
					</span>
					<span class="participants_name">Bhadresh Ramani</span>
					<span class="settings-icon group_participants_info">
						<a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="group_person_info">
							<i class="zmdi zmdi-more"></i>
						</a>
						<ul id="group_person_info" class="dropdown-content custom_dropdown">
							<li>
								<a>Make group admin</a>
							</li>
							<li>
								<a>Remove</a>
							</li>
						</ul>
					</span>
				</div>
			</div>
		</div>
	</div>

	<!--add person to new group modal-->
	<div class="add_person_newgroup side_modal left_side_modal">
		<div class="custom_side_header">
			<span class="close_side_slider waves-effect" onclick="closeInnerSlide()">
				<i class="zmdi zmdi-arrow-left"></i>
			</span>
			<h3>Add to Group</h3>
			<a class="waves-effect waves-light btn add_to_group_done" onclick="closeInnerSlide()">Done</a>
		</div>
		<div class="side_modal_container side_modal_content">
			<div  class="custom_search group_modal_search">
					<button class="close_message_search arrow_back_icon" >
						<i class="zmdi zmdi-arrow-left"></i>
					</button>
					<button class="close_message_search search_messages_icon" >
						<i class="zmdi zmdi-hc-2x zmdi-search"></i>
					</button>
					<input id="add_to_group_search"  class="custom_search_input" type="text" placeholder="Search People" autocomplete="off" />
					<span class="remove_focus_text_icon">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
			</div>
			<div class="added_to_group_person">
				<div class="added_person">
					<img src="<?=$baseUrl?>/images/whoisaround-img1.png" />
					<span class="remove_added_person">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
				<div class="added_person">
					<img src="<?=$baseUrl?>/images/whoisaround-img.png" />
					<span class="remove_added_person">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
				<div class="added_person">
					<img src="<?=$baseUrl?>/images/demo-profile.jpg" />
					<span class="remove_added_person">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
			</div>
			<p class="suggested_label">Suggested</p>
			<div class="suggested_person_addto_group">
				<label class="add_to_group_container" for="filled_for_person_00">
					<span class="add_to_group_personprofile">
						<img src="<?=$baseUrl?>/images/demo-profile.jpg"/>
					</span>
					<div class="add_to_group__personlabel">
						<p class="group_person_name" id="checkPerson0">Adel Google1</p>
					</div>
					<p class="addgroup_user_checkbox">
						<input type="checkbox" id="filled_for_person_00"  class="chk_person" />
						<label for="filled_for_person_00"></label>
					</p>
				</label>
				<label class="add_to_group_container" for="filled_for_person_11">
					<span class="add_to_group_personprofile">
						<img src="<?=$baseUrl?>/images/demo-profile.jpg"/>
					</span>
					<div class="add_to_group__personlabel">
						<p class="group_person_name" id="checkPerson0">Adel Google1</p>
					</div>
					<p class="addgroup_user_checkbox">
						<input type="checkbox" id="filled_for_person_11"  class="chk_person" />
						<label for="filled_for_person_11"></label>
					</p>
				</label>
				<label class="add_to_group_container" for="filled_for_person_22">
					<span class="add_to_group_personprofile">
						<img src="<?=$baseUrl?>/images/demo-profile.jpg"/>
					</span>
					<div class="add_to_group__personlabel">
						<p class="group_person_name" id="checkPerson0">Adel Google1</p>
					</div>
					<p class="addgroup_user_checkbox">
						<input type="checkbox" id="filled_for_person_22"  class="chk_person" />
						<label for="filled_for_person_22"></label>
					</p>
				</label>
			</div>
		</div>
	</div>

	<!--add person to create group modal-->
	<div class="add_person_creategroup side_modal right_side_modal">
		<div class="custom_side_header">
			<span class="close_side_slider waves-effect" onclick="add_to_createdgroup_done()">
				<i class="zmdi zmdi-arrow-right"></i>
			</span>
			<h3>Add to Group</h3>
			<a class="waves-effect waves-light btn add_to_group_done" onclick="add_to_createdgroup_done()">Done</a>
		</div>
		<div class="side_modal_container side_modal_content">
			<div  class="custom_search group_modal_search">
				<div>
					<button class="close_message_search arrow_back_icon" >
						<i class="zmdi zmdi-arrow-left"></i>
					</button>
					<button class="close_message_search search_messages_icon" >
						<i class="zmdi zmdi-hc-2x zmdi-search"></i>
					</button>
					<input id="add_to_group_search"  class="custom_search_input" type="text" placeholder="Search People" autocomplete="off" />
					<span class="remove_focus_text_icon">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
			</div>
		</div>
		<div class="added_to_group_person">
			<div class="added_person">
				<img src="<?=$baseUrl?>/images/whoisaround-img1.png" />
				<span class="remove_added_person">
					<i class="mdi mdi-close mdi-20px	"></i>
				</span>
			</div>
			<div class="added_person">
				<img src="<?=$baseUrl?>/images/whoisaround-img.png" />
				<span class="remove_added_person">
					<i class="mdi mdi-close mdi-20px	"></i>
				</span>
			</div>
		</div>
		<p class="suggested_label">Suggested</p>
		<div class="suggested_person_addto_group">
			<label class="add_to_group_container" for="filled_for_person_0" >
				<span class="add_to_group_personprofile">
					<img src="<?=$baseUrl?>/images/demo-profile.jpg"/>
				</span>
				<div class="add_to_group__personlabel">
					<p class="group_person_name" id="checkPerson0">Bhadresh Ramani</p>
				</div>
				<p class="addgroup_user_checkbox">
					<input type="checkbox" id="filled_for_person_0"  class="chk_person" checked="checked"/>
					<label for="filled_for_person_0"></label>
				</p>
			</label>
			<label class="add_to_group_container" for="filled_for_person_1">
				<span class="add_to_group_personprofile">
					<img src="<?=$baseUrl?>/images/demo-profile.jpg"/>
				</span>
				<div class="add_to_group__personlabel">
					<p class="group_person_name" id="checkPerson0">Vipul Patel</p>
				</div>
				<p class="addgroup_user_checkbox">
					<input type="checkbox" id="filled_for_person_1"  class="chk_person" checked="checked"/>
					<label for="filled_for_person_1"></label>
				</p>
			</label>
			<label class="add_to_group_container" for="filled_for_person_4">
				<span class="add_to_group_personprofile">
					<img src="<?=$baseUrl?>/images/demo-profile.jpg"/>
				</span>
				<div class="add_to_group__personlabel">
					<p class="group_person_name" id="checkPerson0">User Name</p>
				</div>
				<p class="addgroup_user_checkbox">
					<input type="checkbox" id="filled_for_person_4"  class="chk_person"/>
					<label for="filled_for_person_4"></label>
				</p>
			</label>
			<label class="add_to_group_container" for="filled_for_person_5">
				<span class="add_to_group_personprofile">
					<img src="<?=$baseUrl?>/images/demo-profile.jpg"/>
				</span>
				<div class="add_to_group__personlabel">
					<p class="group_person_name" id="checkPerson0">User Name</p>
				</div>
				<p class="addgroup_user_checkbox">
					<input type="checkbox" id="filled_for_person_5"  class="chk_person"/>
					<label for="filled_for_person_5"></label>
				</p>
			</label>
			<label class="add_to_group_container" for="filled_for_person_6">
				<span class="add_to_group_personprofile">
					<img src="<?=$baseUrl?>/images/demo-profile.jpg"/>
				</span>
				<div class="add_to_group__personlabel">
					<p class="group_person_name" id="checkPerson0">User Name</p>
				</div>
				<p class="addgroup_user_checkbox">
					<input type="checkbox" id="filled_for_person_6"  class="chk_person"/>
					<label for="filled_for_person_6"></label>
				</p>
			</label>
		</div>
	</div>

	<!--add to group modal-->
	<div class="add_to_group_modal side_modal right_side_modal inner_slide_modal">
		<div class="custom_side_header">
			<span class="add_to_group_close close_side_slider waves-effect" onclick="add_to_group_close()">
				<i class="mdi mdi-close mdi-20px	"></i>
			</span>
			<h3>Add to Group</h3>
			<a class="waves-effect waves-light btn add_to_group_done" onclick="add_to_group_done()">Done</a>
		</div>
		<div class="side_modal_container side_modal_content">
			<div  class="custom_search group_modal_search">
				<div>
					<button class="close_message_search arrow_back_icon" >
						<i class="zmdi zmdi-arrow-left"></i>
					</button>
					<button class="close_message_search search_messages_icon" >
						<i class="zmdi zmdi-hc-2x zmdi-search"></i>
					</button>
					<input id="add_to_group_search"  class="custom_search_input" type="text" placeholder="Search People" autocomplete="off" />
					<span class="remove_focus_text_icon">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
			</div>
			<div class="added_to_group_person">
				<div class="added_person">
					<img src="<?=$baseUrl?>/images/whoisaround-img1.png" />
					<span class="remove_added_person">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
				<div class="added_person">
					<img src="<?=$baseUrl?>/images/whoisaround-img.png" />
					<span class="remove_added_person">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
				<div class="added_person">
					<img src="<?=$baseUrl?>/images/demo-profile.jpg" />
					<span class="remove_added_person">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
			</div>
			<p class="suggested_label">Suggested</p>
			<div class="suggested_person_addto_group">
				<label class="add_to_group_container" for="filled_for_person_2">
					<span class="add_to_group_personprofile">
						<img src="<?=$baseUrl?>/images/demo-profile.jpg"/>
					</span>
					<div class="add_to_group__personlabel">
						<p class="group_person_name" id="checkPerson0">Adel Google1</p>
					</div>
					<p class="addgroup_user_checkbox">
						<input type="checkbox" id="filled_for_person_2"  class="chk_person" />
						<label for="filled_for_person_2"></label>
					</p>
				</label>
				<label class="add_to_group_container" for="filled_for_person_3">
					<span class="add_to_group_personprofile">
						<img src="<?=$baseUrl?>/images/demo-profile.jpg"/>
					</span>
					<div class="add_to_group__personlabel">
						<p class="group_person_name" id="checkPerson0">Adel Google1</p>
					</div>
					<p class="addgroup_user_checkbox">
						<input type="checkbox" id="filled_for_person_3"  class="chk_person" />
						<label for="filled_for_person_3"></label>
					</p>
				</label>
				<label class="add_to_group_container" for="filled_for_person_4">
					<span class="add_to_group_personprofile">
						<img src="<?=$baseUrl?>/images/demo-profile.jpg"/>
					</span>
					<div class="add_to_group__personlabel">
						<p class="group_person_name" id="checkPerson0">Adel Google1</p>
					</div>
					<p class="addgroup_user_checkbox">
						<input type="checkbox" id="filled_for_person_4"  class="chk_person" />
						<label for="filled_for_person_4"></label>
					</p>
				</label>
			</div>
		</div>
	</div>

	<!--add to block modal-->
	<div class="add_to_block_modal side_modal left_side_modal inner_slide_modal">
		<div class="custom_side_header">
			<span class="add_to_group_close close_side_slider waves-effect" onclick="BlockCancel()">
				<i class="zmdi zmdi-arrow-left"></i>
			</span>
			<h3>Add to Block</h3>
			<a class="waves-effect waves-light btn add_to_group_done" onclick="BlockCancel()">Done</a>
		</div>
		<div class="side_modal_container side_modal_content">
			<div  class="custom_search group_modal_search">
				<div>
					<button class="close_message_search arrow_back_icon" >
						<i class="zmdi zmdi-arrow-left"></i>
					</button>
					<button class="close_message_search search_messages_icon" >
						<i class="zmdi zmdi-hc-2x zmdi-search"></i>
					</button>
					<input id="add_to_group_search"  class="custom_search_input" type="text" placeholder="Search People" autocomplete="off" />
					<span class="remove_focus_text_icon">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
			</div>
			<div class="added_to_group_person">
				<div class="added_person">
					<img src="<?=$baseUrl?>/images/whoisaround-img1.png" />
					<span class="remove_added_person">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
				<div class="added_person">
					<img src="<?=$baseUrl?>/images/whoisaround-img.png" />
					<span class="remove_added_person">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
				<div class="added_person">
					<img src="<?=$baseUrl?>/images/demo-profile.jpg" />
					<span class="remove_added_person">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
			</div>
			<p class="suggested_label">Suggested</p>
			<div class="suggested_person_addto_group">
				<label class="add_to_group_container" for="block_person_1">
					<span class="add_to_group_personprofile">
						<img src="<?=$baseUrl?>/images/demo-profile.jpg"/>
					</span>
					<div class="add_to_group__personlabel">
						<p class="group_person_name" id="checkPerson0">Adel Google1</p>
					</div>
					<p class="addgroup_user_checkbox">
						<input type="checkbox" id="block_person_1"  class="chk_person" />
						<label for="block_person_1"></label>
					</p>
				</label>
				<label class="add_to_group_container" for="block_person_2">
					<span class="add_to_group_personprofile">
						<img src="<?=$baseUrl?>/images/demo-profile.jpg"/>
					</span>
					<div class="add_to_group__personlabel">
						<p class="group_person_name" id="checkPerson0">Adel Google1</p>
					</div>
					<p class="addgroup_user_checkbox">
						<input type="checkbox" id="block_person_2"  class="chk_person" />
						<label for="block_person_2"></label>
					</p>
				</label>
				<label class="add_to_group_container" for="block_person_3">
					<span class="add_to_group_personprofile">
						<img src="<?=$baseUrl?>/images/demo-profile.jpg"/>
					</span>
					<div class="add_to_group__personlabel">
						<p class="group_person_name" id="checkPerson0">Adel Google1</p>
					</div>
					<p class="addgroup_user_checkbox">
						<input type="checkbox" id="block_person_3"  class="chk_person" />
						<label for="block_person_3"></label>
					</p>
				</label>
			</div>
		</div>
	</div>

	<!--messages saved modal-->
	<div class="message_save_modal side_modal left_side_modal">
		<div class="custom_side_header">
			<span class="slide_out_btn slide_out_right_btn close_side_slider waves-effect">
				<i class="zmdi zmdi-arrow-left"></i>
			</span>
			<h3>Saved messages</h3>
		</div>
		<div class="side_modal_container saved_message_container">
		</div>
		<div class="side_modal_content">
		</div>
	</div>

	<!--person messages saved modal-->
	<div class="person_message_save_modal side_modal right_side_modal right_innermodal">
		<div class="custom_side_header">
			<a class="close_side_slider waves-effect" onclick="closeInnerRightSlide()">
				<i class="zmdi zmdi-arrow-right"></i>
			</a>
			<h3>Saved messages</h3>
		</div>
		<div class="side_modal_container saved_message_container">
		</div>

		<div class="side_modal_content">
		</div>
	</div>

	<!--Archieve modal-->
	<div class="archieved_chat_modal side_modal left_side_modal">
		<div class="custom_side_header">
			<span class="slide_out_btn slide_out_right_btn close_side_slider waves-effect">
				<i class="zmdi zmdi-arrow-left"></i>
			</span>
			<h3>Archieved chats</h3>
		</div>
		<div class="side_modal_container archeive_container">
		</div>
	</div>

	<!--Block contact modal-->
	<div class="blocked_contact_modal side_modal left_side_modal">
		<div class="custom_side_header">
			<span class="slide_out_btn slide_out_right_btn close_side_slider waves-effect">
				<i class="zmdi zmdi-arrow-left"></i>
			</span>
			<h3>Blocked Contacts</h3>
		</div>
		
		<div class="side_modal_container">
			<a class="blocked_addperson_box" onclick="AddBlockModal()">
				<span class="add_to_block_icon">
					<i class="zmdi zmdi-account-add"></i>
				</span>
				<p class="add_to_block_name">Add blocked contact</p>
			</a>
			<div class="block_contact_container">
				<div class="blocked_person_box">
					<span class="blocked_person_img">
						<img src="<?=$baseUrl?>/images/demo-profile.jpg" />
					</span>
					<div class="number_blocked">
						<p class="blocked_number">+967736870060</p>
						<p class="blocked_name">Hey there! i am using Arabiaface</p>
					</div>
					<span class="blocked_remove waves-effect">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
				<div class="blocked_person_box">
					<span class="blocked_person_img">
						<img src="<?=$baseUrl?>/images/demo-profile.jpg" />
					</span>
					<div class="number_blocked">
						<p class="blocked_number">Adel Ahasanat</p>
						<p class="blocked_name">Hey there! i am using Arabiaface</p>
					</div>
					<span class="blocked_remove waves-effect">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
				<p class="blocked_msg">
					Blocked Contacts will no longer be able to call you or send you messages
				</p>
			</div>

		</div>
	</div>

	<!--setting modal-->
	<div class="msg_setting_modal side_modal left_side_modal">
		<div class="custom_side_header">
			<span class="slide_out_btn slide_out_right_btn close_side_slider waves-effect">
				<i class="zmdi zmdi-arrow-left"></i>
			</span>
			<h3>Settings</h3>
		</div>
		<div class="side_modal_container">
		</div>
	</div>

	<!--Search Messages modal-->
	<div class="search_messages_modal side_modal right_side_modal">
		<div class="custom_side_header">
			<span class="slide_out_btn slide_out_right_btn close_side_slider waves-effect" onclick="closeMessageSearchSlide()">
				<i class="mdi mdi-close mdi-20px	"></i>
			</span>
			<h3>Search Messages</h3>
		</div>
		<div class="side_modal_container">
			<div class="custom_search msg_search_box">
				<div>
					<button class="close_message_search arrow_back_icon">
						<i class="zmdi zmdi-arrow-left"></i>
					</button>
					<button class="close_message_search search_messages_icon">
						<i class="zmdi zmdi-hc-2x zmdi-search"></i>
					</button>
					<input id="messages_search" class="custom_search_input" type="text" placeholder="Search message text" autocomplete="off" />
					<span class="remove_focus_text_icon dis-none">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
			</div>
		</div>
		<div class="side_modal_content">

		</div>
	</div>

	<!--contact info modal-->
	<div class="contact_info_modal side_modal right_side_modal"></div>

	<!--group info modal-->
	<div class="group_info_modal side_modal right_side_modal">
		<div class="custom_side_header">
			<span class="close_side_slider waves-effect" onclick="CloseGroupInfo()">
				<i class="mdi mdi-close mdi-20px	"></i>
			</span>
			<h3>Group info</h3>
			<a type="button" class="waves-effect waves-light btn add_to_group_done" onclick="CloseGroupInfo()">Done</a>
		</div>
		<div class="side_modal_container group_info_container">
			<div class="group_info_profile three_person">
				<span class="big_profile_image">
					<div>
						<img src="<?=$baseUrl?>/images/whoisaround-img1.png" />
						<img src="<?=$baseUrl?>/images/whoisaround-img1.png" />
						<img src="<?=$baseUrl?>/images/whoisaround-img1.png" />
					</div>
					<input type="file" id="upload_group_img" class="dis-none" />
					<label for="upload_group_img" class="edit_profile_pic">
						<i class="mdi mdi-camera" ></i>
					</label>
				</span>
			</div>
			<div class="contact_user_container">
				<p class="contact_user_name">Group Name</p>
				<div class="group_name_text">
					<input placeholder="" id="group_name_text" type="text" class="validate" />
				</div>

				<p class="contact_user_lastseen">Created by 12345 67890, 06/nov/2017</p>
				<span class="group_name_edit" onclick="group_name_edit()" >
					<i class="zmdi zmdi-edit"></i>
				</span>
				<span class="group_name_remove" onclick="group_name_remove()" >
					<i class="mdi mdi-close mdi-20px	"></i>
				</span>
			</div>
			<ul class="contact_info_ul">
				<li>
					<a>
						<span class="contact_list_options">Mute</span>
						<span class="contact_list_icon">
							<div class="switch contact_info_switch">
								<label>
									<input type="checkbox"/>
									<span class="lever"></span>
								</label>
							</div>
						</span>
					</a>
				</li>
			</ul>
			<div class="participants_container">
				<p class="participants_number">3 participants</p>
				<a class="participants_info" onclick="add_person_group()">
					<span class="participants_profile">
						<span class="add_participents">
							<i class="zmdi zmdi-account-add"></i>
						</span>
					</span>
					<span class="participants_name add_participants_name">Add Participants</span>
				</a>
				<div class="participants_info">
					<span class="participants_profile">
						<img src="<?=$baseUrl?>/images/whoisaround-img.png" />
					</span>
					<span class="participants_name">Vipul Patel</span>
					<span class="settings-icon group_participants_info">
						<a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="group_person_info1">
							<i class="zmdi zmdi-more"></i>
						</a>
						<ul id="group_person_info1" class="dropdown-content custom_dropdown">
							<li>
								<a>Make group admin</a>
							</li>
							<li>
								<a>Remove</a>
							</li>
						</ul>

					</span>
				</div>
				<div class="participants_info">
					<span class="participants_profile">
						<img src="<?=$baseUrl?>/images/whoisaround-img1.png" />
					</span>
					<span class="participants_name">Bhadresh Ramani</span>
					<span class="settings-icon group_participants_info">
						<a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="group_person_info2">
							<i class="zmdi zmdi-more"></i>
						</a>
						<ul id="group_person_info2" class="dropdown-content custom_dropdown">
							<li>
								<a>Make group admin</a>
							</li>
							<li>
								<a>Remove</a>
							</li>
						</ul>
					</span>
				</div>
				<div class="participants_info">
					<span class="participants_profile">
						<img src="<?=$baseUrl?>/images/demo-profile.jpg" />
					</span>
					<span class="participants_name">Nimish Parekh</span>
					<span class="settings-icon group_participants_info">
						<a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="group_person_info3">
							<i class="zmdi zmdi-more"></i>
						</a>
						<ul id="group_person_info3" class="dropdown-content custom_dropdown">
							<li>
								<a>Make group admin</a>
							</li>
							<li>
								<a>Remove</a>
							</li>
						</ul>
					</span>
				</div>
			</div>
			<div class="exit_group">
				<ul>
					<li>
						<a>
							<span class="exit_group_icon">
								<i class="mdi mdi-logout" ></i>
							</span>
							<span class="exit_group_label">
								Exit group
							</span>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>

	<!--group info modal-->
	<div class="person_search_slide_xs side_modal left_side_modal">
		<div class="custom_side_header">
			<span class="slide_out_btn slide_out_right_btn close_side_slider waves-effect">
			<i class="zmdi zmdi-arrow-left"></i>
			</span>
			<h3>New Chat</h3>
		</div>
		<div class="side_modal_container side_modal_content">
			<div  class="custom_search group_modal_search">
				<div>
					<button class="close_message_search arrow_back_icon" >
						<i class="zmdi zmdi-arrow-left"></i>
					</button>
					<button class="close_message_search search_messages_icon" >
						<i class="zmdi zmdi-hc-2x zmdi-search"></i>
					</button>
					<input id="new_chat_search"  class="custom_search_input" type="text" placeholder="Type name..." autocomplete="off" />
					<span class="remove_focus_text_icon">
						<i class="mdi mdi-close mdi-20px"></i>
					</span>
				</div>
			</div>
			<div class="suggested_person_addto_group">
			</div>
		</div>
	</div>

	<!--xs Search Messages modal -->
	<div class="msgsearch_messages_modal side_modal left_side_modal">
		<div class="custom_side_header">
			<span class="slide_out_btn slide_out_right_btn close_side_slider waves-effect" onclick="closeMessageSearchSlide()">
				<i class="mdi mdi-close mdi-20px"></i>
			</span>
			<h3>Search Messages</h3>
		</div>
		<div>
			<div class="custom_search msg_search_box">
				<div>
					<button class="close_message_search arrow_back_icon">
						<i class="zmdi zmdi-arrow-left"></i>
					</button>
					<button class="close_message_search search_messages_icon">
						<i class="zmdi zmdi-hc-2x zmdi-search"></i>
					</button>
					<input id="messages_search" class="custom_search_input" type="text" placeholder="Search message text" autocomplete="off" />
					<span class="remove_focus_text_icon dis-none">
						<i class="mdi mdi-close mdi-20px	"></i>
					</span>
				</div>
			</div>
		</div>
		<div class="side_modal_content">

		</div>
	</div>

	<!--gift modal-->
	<div id="giftlist_popup" class="modal gift_modal  giftlist-popup">
		<div class="custom_message_modal_header">
			<p>Send gift</p>
			<button class="close_modal_icon waves-effect" onclick="gift_modal()">
				<i class="mdi mdi-close mdi-20px	"></i>
			</button>
		</div>
		<div class="gift_content">
			<div class="popup-content">
				<div class="emostickers-holder">
					<div class="emostickers-box">
						<div class="emostickers">
							<ul class="emostickers-list">
								<li>
									<a href="javascript:void(0)" data-class="(wine)" onclick="useGift('(wine)','popular','editmode', 'message');">
										<span class="emosticker emosticker-wine" title="wine">(wine)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(icecream)" onclick="useGift('(icecream)','popular','editmode', 'message');">
										<span class="emosticker emosticker-icecream" title="icecream">(icecream)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(coffee)" onclick="useGift('(coffee)','popular','editmode', 'message');">
										<span class="emosticker emosticker-coffee" title="coffee">(coffee)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(heart)" onclick="useGift('(heart)','popular','editmode', 'message');">
										<span class="emosticker emosticker-heart" title="heart">(heart)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(flower)" onclick="useGift('(flower)','popular','editmode', 'message');">
										<span class="emosticker emosticker-flower" title="flower">(flower)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(cake)" onclick="useGift('(cake)','popular','editmode', 'message');">
										<span class="emosticker emosticker-cake" title="cake">(cake)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(handshake)" onclick="useGift('(handshake)','popular','editmode', 'message');">
										<span class="emosticker emosticker-handshake" title="handshake">(handshake)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(gift)" onclick="useGift('(gift)','popular','editmode', 'message');">
										<span class="emosticker emosticker-gift" title="gift">(gift)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(goodmorning)" onclick="useGift('(goodmorning)','popular','editmode', 'message');">
										<span class="emosticker emosticker-goodmorning" title="goodmorning">(goodmorning)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(goodnight)" onclick="useGift('(goodnight)','popular','editmode', 'message');">
										<span class="emosticker emosticker-goodnight" title="goodnight">(goodnight)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(backpack)" onclick="useGift('(backpack)','popular','editmode', 'message');">
										<span class="emosticker emosticker-backpack" title="backpack">(backpack)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(parasailing)" onclick="useGift('(parasailing)','popular','editmode', 'message');">
										<span class="emosticker emosticker-parasailing" title="parasailing">(parasailing)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(train)" onclick="useGift('(train)','popular','editmode', 'message');">
										<span class="emosticker emosticker-train" title="train">(train)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(flipflop)" onclick="useGift('(flipflop)','popular','editmode', 'message');">
										<span class="emosticker emosticker-flipflop" title="flipflop">(flipflop)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(airplane)" onclick="useGift('(airplane)','popular','editmode', 'message');">
										<span class="emosticker emosticker-airplane" title="airplane">(airplane)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(sunbed)" onclick="useGift('(sunbed)','popular','editmode', 'message');">
										<span class="emosticker emosticker-sunbed" title="sunbed">(sunbed)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(happyhalloween)" onclick="useGift('(happyhalloween)','popular','editmode', 'message');">
										<span class="emosticker emosticker-happyhalloween" title="happyhalloween">(happyhalloween)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(merrychristmas)" onclick="useGift('(merrychristmas)','popular','editmode', 'message');">
										<span class="emosticker emosticker-merrychristmas" title="merrychristmas">(merrychristmas)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(eidmubarak)" onclick="useGift('(eidmubarak)','popular','editmode', 'message');">
										<span class="emosticker emosticker-eidmubarak" title="eidmubarak">(eidmubarak)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(happyyear)" onclick="useGift('(happyyear)','popular','editmode', 'message');">
										<span class="emosticker emosticker-happyyear" title="happyyear">(happyyear)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(happymothers)" onclick="useGift('(happymothers)','popular','editmode', 'message');">
										<span class="emosticker emosticker-happymothers" title="happymothers">(happymothers)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(happyfathers)" onclick="useGift('(happyfathers)','popular','editmode', 'message');">
										<span class="emosticker emosticker-happyfathers" title="happyfathers">(happyfathers)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(happyaniversary)" onclick="useGift('(happyaniversary)','popular','editmode', 'message');">
										<span class="emosticker emosticker-happyaniversary" title="happyaniversary">(happyaniversary)</span>
									</a>
								</li>
								<li>
									<a href="javascript:void(0)" data-class="(happybirthday)" onclick="useGift('(happybirthday)','popular','editmode', 'message');">
										<span class="emosticker emosticker-happybirthday" title="happybirthday">(happybirthday)</span>
									</a>
								</li>
							</ul>
						</div>
					</div>
					<span class="credits">100 Credits</span>
				</div>
			</div>
		</div>

	</div>

	<!--user gift modal-->
	<div id="usegift-popup" class="modal usegift_modal popup-area giftlist-popup giftslider-popup">
		<div class="edit-emosticker"></div>
		<div class="preview-emosticker"></div>
	</div>
		
	<?php include('../views/layouts/addpersonmodal.php'); ?>
	
	
	<!--mute discard-->
	<div id="mute_modal" class="modal compose_discard_modal custom_modal ">
		<div class="modal-content">
			<p class="discard_modal_msg">Mute Chat ?</p>
		</div>
		<div class="modal-footer">
			<a class="modal_keep btngen-center-align " >Done</a>
			<a type="button" class="modal_discard btngen-center-align " onclick="clearPost()">Cancel</a>
		</div>
	</div>

	<!--Archieve discard-->
	<div id="Archieve_modal" class="modal compose_discard_modal custom_modal">
		<div class="modal-content">
			<p class="discard_modal_msg">Archieve Chat ?</p>
		</div>
		<div class="modal-footer">
			<a class="modal_keep btngen-center-align " >Done</a>
			<a type="button" class="modal_discard btngen-center-align " onclick="clearPost()">Cancel</a>
		</div>
	</div>

	<!--Unread message discard-->
	<div id="UnreadMessages_modal" class="modal compose_discard_modal custom_modal">
		<div class="modal-content">
			<p class="discard_modal_msg">Archieve Chat ?</p>
		</div>
		<div class="modal-footer">
			<a class="modal_keep btngen-center-align " >Done</a>
			<a type="button" class="modal_discard btngen-center-align " onclick="clearPost()">Cancel</a>
		</div>
	</div>

	<!--Delete chat discard-->
	<div id="DeteletChat_modal" class="modal compose_discard_modal custom_modal">
		<div class="modal-content">
			<p class="discard_modal_msg">Delete Chat ?</p>
		</div>
		<div class="modal-footer">
			<a class="modal_keep btngen-center-align " >Done</a>
			<a type="button" class="modal_discard btngen-center-align " onclick="clearPost()">Cancel</a>
		</div>
	</div>
<?php $this->endBody() ?> 
<script>
document.body.style.overflowY = "hidden";
var data2 = <?php echo json_encode($data);?>;
var data1 = <?php echo json_encode($usrfrdlist); ?>;	
</script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
   
<!--- START strophe loaded --------
<script src="<?=$baseUrl?>/js/strophe.js"></script>
<script src="<?=$baseUrl?>/js/strophe.jingle.js"></script>
<script src="<?=$baseUrl?>/js/strophe.jingle.session.js"></script>
<script src="<?=$baseUrl?>/js/strophe.jingle.sdp.js"></script>
<script src="<?=$baseUrl?>/js/strophe.jingle.adapter.js"></script>
<script src="<?=$baseUrl?>/js/hark.bundle.js"></script>
<script src="<?=$baseUrl?>/js/muc.js"></script>
<script src="<?=$baseUrl?>/js/vkbeautify.0.99.00.beta.js"></script>
<script src="<?=$baseUrl?>/js/custom_strophe.js"></script>
------ END strophe loaded -------->

<script type="text/javascript" src="<?=$baseUrl?>/js/markjs/jquery.mark.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/markjs/jquery.mark.es6.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/markjs/custom_mark.js"></script>