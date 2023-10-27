<?php
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<div class="tcontent-holder">
	<div class="cbox-title nborder" id="todo-title">
		<img src="<?=$baseUrl?>/images/todoicon-sm.png"/>
		Popular Attractions in <?=$placefirst?>
		<div class="top-stuff right">										
			<a href="javascript:void(0)" class="viewall-link allthings" onclick="openDirectTab('places-todo');">View All</a>
			<div class="more-actions">
				<ul class="tabs nav-custom-tabs text-right">
					<li class="tab"><a href="javascript:void(0)" onclick="openListSection(this)"><i class="zmdi zmdi-view-list-alt zmdi-hc-lg"></i>List</a></li>
				</ul>														
			</div>														
		</div>
	</div>
	<div class="cbox-desc">
		<div class="places-content-holder">
			<div class="list-holder">
				<div class="row">
				<?php if(!empty($todos)) { ?>
				<?php $i= 1; foreach($todos as $todo){ if($i <= $count) { ?>
					<div class="col-sm-4 col-xs-12 pb-holder <?php if($i == 3){ ?>third-col<?php } ?>">
						<div class="placebox">
							<a href="<?=$todo['ProductURL']?>" target="_new">
								<div class="imgholder himg-box"><img src="<?=str_replace('/graphicslib','/graphicslib/thumbs674x446/',$todo['ProductImage'])?>" class="himg"/><div class="overlay"></div></div>
								<div class="descholder">
									<h5 title="<?=$todo['ProductName']?>"><?=$todo['ProductName']?></h5>
									<span class="ratings">
										<?php for($j=0;$j<5;$j++){ ?>
											<i class="mdi mdi-star <?php if($j < $todo['AvgRating']){ ?>active<?php } ?>"></i>
										<?php } ?>
									</span>
									<div class="tags">
										<?php 
										$pieces = explode(", ", str_replace(' & ',', ',$todo['Group1']));
										foreach($pieces as $element) {
											echo "<span>".$element."</span> ";
										} ?>
									</div>
								</div>
							</a>
						</div>
					</div>
					<?php } $i++;} ?>
					<?php } else { ?>
					<div class="col-lg-12">
						<?php $this->context->getnolistfound('nothingsfound');?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php exit;?>