<?php
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<div class="combined-column messages-page page-portion">
	<div class="content-box messages-list">		
		<div class="cbox-desc">
			<div class="row">
				<div class="left-section">
					<div class="side-user">
						<span class="img-holder"><a href="wall.html"><img src="<?=$baseUrl?>/images/demo-profile.jpg"></a></span>
						<a href="wall.html"><span class="desc-holder">Nimish Parekh</span></a>
						<span class="online-dot"></span><span class="userstatus">Online</span>
					</div>
					<div class="connections-search">
						<div class="fsearch-form">
							<input placeholder="Search" type="text" class="messagewallsearchforpage">
							<a href="javascript:void(0)"><i class="zmdi zmdi-search"></i></a>
							
							<a href="javascript:void(0)" id="addnewchat" class="addnewchat"><i class="mdi mdi-pencil-square-o"></i></a>
						</div>
					</div>
					<div class="clear"></div>					
					<div class="fake-title-area">						
						<ul class="tabs">
							<li class="active tab" data-cls="page"><a href="#messages-pages" data-toggle="tab" aria-expanded="true">Inbox</a></li>
						</ul>
					</div>
					<div class="tab-content">
						<div class="tab-pane fade main-pane active in" id="messages-page">
							<div class="gloader-holder doneloading">									
								<div class="gloader-content">
									<div class="message-userlist nice-scroll" style="overflow-y: hidden;" tabindex="0">
										<ul class="users-display"></ul>
									</div>
								</div>
							</div>
						</div>
					</div>
					<a href="javascript:void(0)" id="addnewchat" class="addnewchat mobile-btn"><i class="mdi mdi-pencil-square-o"></i></a>
				</div>
				<div class="right-section">
					<div class="topstuff">
						<div class="msgwindow-name">
							<div class="imgholder dot_online"><img src="<?=$baseUrl?>/images/whoisaround-img.png"/></div>
							<span class="desc-holder">Vipul Patel</span>
							<span class="online-dot"></span>  
							<span class="userstatus">i like nonsense it wakes up the brain cells</span>
							<span class="usertime"> 12:57 PM </span> 
							<span class="usercountry">| INDIA </span>
						</div>
						<div class="msgwindow-name msgwindow-group">
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
						
						<div class="connections-search">
							<div class="dropdown dropdown-custom dropdown-friendlock">
								<a href="javascript:void(0)" class="dropdown-toggle msg-btns" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
									<img src="<?=$baseUrl?>/images/dot-hori.png" />
								</a>
								<ul class="dropdown-menu">
									<li><a href="javascript:void(0)" onclick="showMsgPhotos()">View in photos in thread</a></li>													
									<li><a href="javascript:void(0)" class="mute-setting" onclick="manageMuteConverasion()">Mute thread</a></li>
									<li class="deleteSocketConversation"><a href="javascript:void(0)" onclick="confirmDeleteConversation()">Delete thread</a></li>
									<li><a href="javascript:void(0)" onclick="showMsgCheckbox()">Delete Messages</a></li>
									<li><a href="javascript:void(0)" class="block-setting" onclick="manageBlockConverasion('block')">Block Messages</a></li>
									<li>
										<div class="clear"></div>
										<hr>
										<div class="clear"></div>
									</li>
									<li>
										<div class="h-checkbox">
											<input id="test1" type="checkbox">
											<label>Sound notification</label>
										</div>
										
									</li>												
								</ul>							
							</div>
						</div>									
						<div class="add-chat-search">
										
							<label>To: </label>
							<select class="select2 add-multi-connections" multiple="multiple">
							  <option value="AL">Bhadresh</option>
							  <option value="WY">Alap</option>
							  <option value="WY">Markand</option>
							  <option value="WY">Hiral</option>
							</select>
							
							<a href="javascript:void(0)" id="canceladdchat" class="canceladdchat"><i class="mdi mdi-close	"></i></a>
							<a href="javascript:void(0)" id="doneaddchat" class="btn btn-sm btn-primary doneaddchat pull-right">Done</a>
						</div>
					</div>
					<div class="main-msgwindow">
						<h4 class="dateshower"></h4>
						<div class="photos-thread">
							<a href="javascript:void(0)" onclick="hideMsgPhotos()" class="backlink">
								<i class="mdi mdi-menu-left"></i> Back to conversation
							</a>
							<div class="photos-area nice-scroll">
								<div class="albums-grid images-container">
									<div class="row">												
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/post-img1.jpg" data-size="1600x1600" data-med="<?=$baseUrl?>/images/post-img1.jpg" data-med-size="1024x1024" data-author="Folkert Gorter" class="himg-box">
														  <img class="himg" src="<?=$baseUrl?>/images/post-img1.jpg">
														</a>																		
													</figure>
												</div>														
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/album2.png" data-size="1600x1600" data-med="<?=$baseUrl?>/images/album2.png" data-med-size="1024x1024" data-author="Folkert Gorter" class="himg-box">
														  <img class="himg" src="<?=$baseUrl?>/images/album2.png">
														</a>																			
													</figure>
												</div>														
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/post-img5.jpg" data-size="1600x1600" data-med="<?=$baseUrl?>/images/post-img5.jpg" data-med-size="1024x1024" data-author="Folkert Gorter" class="vimg-box">
														  <img class="vimg" src="<?=$baseUrl?>/images/post-img5.jpg">
														</a>
													
													</figure>
												</div>														
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/post-img4.jpg" data-size="1600x1600" data-med="<?=$baseUrl?>/images/post-img4.jpg" data-med-size="1024x1024" data-author="Folkert Gorter" class="himg-box">
														  <img class="himg" src="<?=$baseUrl?>/images/post-img4.jpg">
														</a>
													
													</figure>
												</div>														
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/album5.png" data-size="1600x1600" data-med="<?=$baseUrl?>/images/album5.png" data-med-size="1024x1024" data-author="Folkert Gorter" class="himg-box">
														  <img class="himg" src="<?=$baseUrl?>/images/album5.png">
														</a>																			
													</figure>
												</div>														
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/post-img3.jpg" data-size="1600x1600" data-med="<?=$baseUrl?>/images/post-img3.jpg" data-med-size="1024x1024" data-author="Folkert Gorter" class="vimg-box">
														  <img class="vimg" src="<?=$baseUrl?>/images/post-img3.jpg">
														</a>
													
													</figure>
												</div>
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/album7.png" data-size="1600x1600" data-med="<?=$baseUrl?>/images/album7.png" data-med-size="1024x1024" data-author="Folkert Gorter" class="Himg-box">
														  <img class="Himg" src="<?=$baseUrl?>/images/album7.png">
														</a>
													</figure>
												</div>
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/post-img1.jpg" data-size="1600x1600" data-med="<?=$baseUrl?>/images/post-img1.jpg" data-med-size="1024x1024" data-author="Folkert Gorter" class="himg-box">
														  <img class="himg" src="<?=$baseUrl?>/images/post-img1.jpg">
														</a>																		
													</figure>
												</div>														
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/album2.png" data-size="1600x1600" data-med="<?=$baseUrl?>/images/album2.png" data-med-size="1024x1024" data-author="Folkert Gorter" class="himg-box">
														  <img class="himg" src="<?=$baseUrl?>/images/album2.png">
														</a>																			
													</figure>
												</div>														
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/post-img5.jpg" data-size="1600x1600" data-med="<?=$baseUrl?>/images/post-img5.jpg" data-med-size="1024x1024" data-author="Folkert Gorter" class="vimg-box">
														  <img class="vimg" src="<?=$baseUrl?>/images/post-img5.jpg">
														</a>
													
													</figure>
												</div>														
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/post-img4.jpg" data-size="1600x1600" data-med="<?=$baseUrl?>/images/post-img4.jpg" data-med-size="1024x1024" data-author="Folkert Gorter" class="himg-box">
														  <img class="himg" src="<?=$baseUrl?>/images/post-img4.jpg">
														</a>
													</figure>
												</div>														
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/album5.png" data-size="1600x1600" data-med="<?=$baseUrl?>/images/album5.png" data-med-size="1024x1024" data-author="Folkert Gorter" class="himg-box">
														  <img class="himg" src="<?=$baseUrl?>/images/album5.png">
														</a>																			
													</figure>
												</div>														
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/post-img3.jpg" data-size="1600x1600" data-med="<?=$baseUrl?>/images/post-img3.jpg" data-med-size="1024x1024" data-author="Folkert Gorter" class="vimg-box">
														  <img class="vimg" src="<?=$baseUrl?>/images/post-img3.jpg">
														</a>
													
													</figure>
												</div>
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/album7.png" data-size="1600x1600" data-med="<?=$baseUrl?>/images/album7.png" data-med-size="1024x1024" data-author="Folkert Gorter" class="Himg-box">
														  <img class="Himg" src="<?=$baseUrl?>/images/album7.png">
														</a>
													</figure>
												</div>
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/post-img1.jpg" data-size="1600x1600" data-med="<?=$baseUrl?>/images/post-img1.jpg" data-med-size="1024x1024" data-author="Folkert Gorter" class="himg-box">
														  <img class="himg" src="<?=$baseUrl?>/images/post-img1.jpg">
														</a>																		
													</figure>
												</div>														
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/album2.png" data-size="1600x1600" data-med="<?=$baseUrl?>/images/album2.png" data-med-size="1024x1024" data-author="Folkert Gorter" class="himg-box">
														  <img class="himg" src="<?=$baseUrl?>/images/album2.png">
														</a>																			
													</figure>
												</div>														
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/post-img5.jpg" data-size="1600x1600" data-med="<?=$baseUrl?>/images/post-img5.jpg" data-med-size="1024x1024" data-author="Folkert Gorter" class="vimg-box">
														  <img class="vimg" src="<?=$baseUrl?>/images/post-img5.jpg">
														</a>
													
													</figure>
												</div>														
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/post-img4.jpg" data-size="1600x1600" data-med="<?=$baseUrl?>/images/post-img4.jpg" data-med-size="1024x1024" data-author="Folkert Gorter" class="himg-box">
														  <img class="himg" src="<?=$baseUrl?>/images/post-img4.jpg">
														</a>
													</figure>
												</div>														
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/album5.png" data-size="1600x1600" data-med="<?=$baseUrl?>/images/album5.png" data-med-size="1024x1024" data-author="Folkert Gorter" class="himg-box">
														  <img class="himg" src="<?=$baseUrl?>/images/album5.png">
														</a>																			
													</figure>
												</div>														
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/post-img3.jpg" data-size="1600x1600" data-med="<?=$baseUrl?>/images/post-img3.jpg" data-med-size="1024x1024" data-author="Folkert Gorter" class="vimg-box">
														  <img class="vimg" src="<?=$baseUrl?>/images/post-img3.jpg">
														</a>
													</figure>
												</div>
											</div>
										</div>
										<div class="grid-box">
											<div class="photo-box">
												<div class="imgholder">
													<figure>
														<a href="<?=$baseUrl?>/images/album7.png" data-size="1600x1600" data-med="<?=$baseUrl?>/images/album7.png" data-med-size="1024x1024" data-author="Folkert Gorter" class="Himg-box">
														  <img class="Himg" src="<?=$baseUrl?>/images/album7.png">
														</a>
													</figure>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="allmsgs-holder">
							<div class="msg-notice">
								<div class="mute-notice">
									This conversation has been muted. All the push notifications will be turned off. <a href="javascript:void(0)" onclick="manageMuteConverasion()">Unmute</a>
								</div>
								<div class="block-notice">
									This conversation is blocked. <a href="javascript:void(0)" onclick="manageBlockConverasion('unblock')">Unblock</a>
								</div>
							</div>
							<ul class="current-messages">
							</ul>
							<div class="newmessage" id="li-user-blank">
								<div class="msgdetail-list nice-scroll" tabindex="6"></div>
							</div>
							<div class="addnew-msg">
								<div class="write-msg nice-scroll">
									<textarea id="inputPageWallReply" data-ownerid="<?=$page_details['created_by']?>" class="inputMessageWall" placeholder="Enter Message..."></textarea>
								</div>
								<div class="msg-stuff">
									<div class="send-msg">		
										<div class="h-checkbox entertosend">
											<input id="msgpressentertosend" type="checkbox">
											<label>Press enter to send</label>
										</div>							
										<button class="btn btn-primary waves-effect btn-xxs" onclick="messageSendFromMessagePage(this);"><i class="mdi mdi-telegram" ></i></button>
									</div>
									<div class="add-extra">
										<ul>
											<li class="messagegiftselection dis-none"><a href="#giftlist-popup" class="popup-modal giftlink"  onclick="manageEmoStickersBox('popular', 'message')">Gift</a></li>
											<li> 
												<div onmouseover="$(this).find('img').attr('src', '<?=$baseUrl?>/images/upin-icon-hover.png');" onmouseout="$(this).find('img').attr('src','<?=$baseUrl?>/images/upin-icon.png');"> 
													<div class="custom-file"> 
														<div class="title">
															<a href="javascript:void(0)" onmouseover="$(this).find('img').attr('src','<?=$baseUrl?>/images/upin-icon-hover.png');" onmouseout="$(this).find('img').attr('src','<?=$baseUrl?>/images/upin-icon.png');"><img src="<?=$baseUrl?>/images/upin-icon.png"></a>
														</div>
														<input type="file" id="messageWallFileUploadPage" name="upload" class="upload" type="file"/>
													</div>
												</div>
											</li>
											<li>
												<div class="emotion-holder">
													<a href="javascript:void(0)" class="emotion-btn" onclick="manageEmotionBox(this,'messages')"><i class="mdi mdi-emoticon"></i></a>
													<div class="emotion-box dis-none">
														<div class="nice-scroll emotions">
															<ul class="emotion-list">
															</ul>
														</div>
													</div>
												</div>
											</li>
										</ul>																					
									</div>
								</div>
							</div>
							<div class="bottom-stuff">
								<h6>Select messages to delete</h6>
								<div class="btn-holder">
									<a href="javascript:void(0)" class="btn btn-primary waves-effect btn-sm">Delete</a>
									<a href="javascript:void(0)" class="btn btn-primary waves-effect btn-sm" onclick="hideMsgCheckbox()">Cancel</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php exit;?>