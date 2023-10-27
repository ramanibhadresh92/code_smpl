<div class="section-holder nice-scroll">
	<div class="sectitle">
		Notes
		<span class="count"><?=count($notes)?> notes</span>
		<a href="javascript:void(0)" class="right iconlink redicon" onclick="hideSubLayer(this)"><i class="mdi mdi-close	"></i> Close</a>
	</div>
	<div class="addnote-section">
		<div class="note-holder">
			<div class="detail-mode">
				<div class="editable">
					<form>
						<div class="notetitle">
							<div class="sliding-middle-out anim-area underlined fullwidth">
								<input type="text" placeholder="Note Title" id="notetitle">
							</div>
						</div>
						<div class="note-tt fullwidth">
							<div class="sliding-middle-out anim-area underlined fullwidth tt-holder">
								<textarea placeholder="Your trip note" class="materialize-textarea" id="notetext"></textarea>
							</div>
						</div>
						<a href="javascript:void(0)" class="waves-effect waves-light btn btn-xs modal-trigger btn-trip right mt-10" onclick="addNewStop('note')">Add Note</a>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div class="secdesc">
		<div class="notes-list">
			<ul>
				<?php if(!empty($notes)){
					foreach($notes as $note){
						$noteid = $note['_id'];
						$tripid = $note['tripid'];
						$notetitle = $note['notetitle'];
						$notetext = $note['notetext'];
						$created_date = $note['created_date'];
				?>
				<li>
					<div class="note-holder mode-holder">
						<div class="normal-mode">
							<div class="editable">
								<h6 class="ttitle_<?=$noteid?>" onclick="open_detail(this)"><?=$notetitle?></h6>
								<span class="sub" onclick="open_detail(this)">added on <?=date('d-m-Y',$created_date)?></span>
								<div class="actionbtns">
									<a href="javascript:void(0)" onclick="open_detail(this)" class="editlink">
										<i class="zmdi zmdi-edit mdi-16px"></i>
									</a>
									<a href="javascript:void(0)" onclick="deletenote('<?=$tripid?>','<?=$noteid?>')" class="deletelink">
										<i class="zmdi zmdi-delete mdi-16px"></i>
									</a>
								</div>
							</div>
							<div class="viewable">
								<h6><a href="javascript:void(0)" onclick="open_detail(this)"><span class="ttitle_<?=$noteid?>"><?=$notetitle?></span></a></h6>
								<span class="sub"><?=$created_date?></span>
							</div>
						</div>
						<div class="detail-mode">
							<div class="editable">
								<form>
									<div class="notetitle">
										<div class="row mb-0">
											<div class="input-field col s12">
										    	<input type="text" placeholder="Note Title" value="<?=$notetitle?>" id="ntitle_<?=$noteid?>">
										      	<label for="ntitle_<?=$noteid?>" class="">Note Title</label>
										    </div>
										</div>
										<a href="javascript:void(0)" class="right iconlink  redicon" onclick="close_detail(this)"><i class="mdi mdi-close	"></i> Cancel</a>
									</div>
									<div class="note-tt fullwidth">
										<div class="sliding-middle-out anim-area underlined fullwidth tt-holder">
											<textarea placeholder="Your trip note" id="ntext_<?=$noteid?>" class="materialize-textarea"><?=$notetext?></textarea>
										</div>
									</div>
									<a href="javascript:void(0)" data-noteid="<?=$noteid?>" class="btn btn-primary btn-sm right bottombtn redicon" onclick="saveEditNote(this)">Save Note</a>
								</form>
							</div>
							<div class="viewable">
								<form>
									<div class="notetitle">
										<span class="ttitle_<?=$noteid?>"><?=$notetitle?></span>
										<a href="javascript:void(0)" class="right iconlink  redicon" onclick="close_detail(this)"><i class="mdi mdi-close	"></i> Close</a>
									</div>
									<div class="note-tt fullwidth">
										<p class="ttext_<?=$noteid?>">
											<?=$notetext?>
										</p>
									</div>
								</form>
							</div>
						</div>
					</div>
				</li>
				<?php } } ?>
			</ul>
		</div>
	</div>
</div>
<?php exit;?>