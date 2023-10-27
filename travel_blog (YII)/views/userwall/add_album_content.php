<div class="modal_content_container">
	<div class="modal_content_child modal-content">
		<div class="popup-title ">
			<button class="hidden_close_span close_span waves-effect">
				<i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
			</button>			
			<h3>Create new album</h3>
			<a type="button" class="item_done crop_done waves-effect hidden_close_span custom_close" href="javascript:void(0)" >Done</a>
		</div>
	
		<div class="custom_modal_content modal_content" id="createpopup">
			<div class="ablum-yours profile-tab">
				<div class="ablum-box detail-box">															
					<div class="content-holder main-holder">
						<div class="summery">																	
							<div class="dsection bborder expandable-holder expanded">	
								<div class="form-area expandable-area">
									<form class="ablum-form">
										<div class="form-box">
											<div class="fulldiv">
												<div class="half">
													<div class="frow">
														<div class="caption-holder">
															<label>Album title</label>
														</div>
														<div class="detail-holder">
															<div class="input-field">
																<input type="text" class="fullwidth locinput" placeholder="Album title" name="album_title" id="album_title">
															</div>
														</div>
													</div>
												</div>
											</div>
											
											<div class="fulldiv">
												<div class="half">
													<div class="frow">
														<div class="caption-holder">
															<label>Say something about it</label>
														</div>
														<div class="detail-holder">
															<div class="input-field">
																<input type="text" placeholder="Tell people about the album" name="album_description" class="fullwidth locinput" id="album_description"/>
															</div>
														</div>
													</div>
												</div>
											</div>
											
											<div class="fulldiv">
												<div class="half">
													<div class="frow">
														<div class="caption-holder">
															<label>Where was it taken?</label>
														</div>
														<div class="detail-holder">
															<div class="input-field">
																<input type="text" name="album_place" placeholder="Where was it taken?" class="fullwidth locinput getplacelocation" data-query="all"  onfocus="filderMapLocationModal(this)" id="album_place"/>
															</div>
														</div>
													</div>
												</div>
											</div>
											
											<div class="frow nomargin new-post">
												<div class="caption-holder">
													<label>Add photos to album</label>
												</div>
												<div class="detail-holder">
													<div class="input-field ">					
														<div class="post-photos new_pic_add">
															<div class="img-row">		
																<div class="img-box">
																	<div class="custom-file addimg-box add-photo ablum-add">
																	<span class="icont">+</span><br><span class="">Upload photo</span>
																	<div class="addimg-icon">
																	</div>

																	<input type="file" name="imageFile1[]" class="upload custom-upload remove-custom-upload custom-upload-new" title="Choose a file to upload xx" required="true" data-class="#addAlbumContentPopup .post-photos .img-row" multiple="true"/>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>											
									</form>
								</div>
							</div>													
						</div>																
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="valign-wrapper additem_modal_footer modal-footer">		
	<a href="javascript:void(0)" class="btngen-center-align  close_modal open_discard_modal waves-effect">Cancel</a>
	<a href="javascript:void(0)" class="btngen-center-align waves-effect" data-class="addbtn" onclick="addAlbum()">Create</a>
</div>
<?php exit; ?>