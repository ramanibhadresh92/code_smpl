<?php 
use yii\helpers\Url;
use frontend\models\Like;
use frontend\models\Page;

if(count($promopages) > 0){?>
<div class="pages-list all-list">
	<ul>	
		<?php foreach($promopages as $promopage){
			$pageid = (string)$promopage['adid'];
			$pagelink = Url::to(['page/index', 'id' => "$pageid"]);
			$like_count = Like::getLikeCount($pageid);
			$like_names = Like::getLikeUserNames($pageid);
			$like_buddies = Like::getLikeUser($pageid);
			$newlike_buddies = array();
			$start = 0;
			foreach($like_buddies as $like_buddy) {
				if($start < 3){
				$lid = $like_buddy['user']['_id'];
				$id = Url::to(['userwall/index', 'id' => "$lid"]);
				if($user_id == (string)$lid)
				{
					$name = 'You';
				}
				else
				{
					$name = ucfirst($like_buddy['user']['fname']). ' '.ucfirst($like_buddy['user']['lname']);
				}
				$newlike_buddies[] = "<a href='$id'>".$name."</a>";
				}
				$start++;
			}
			$newlike_buddies = implode(', ', $newlike_buddies);
			$likeexist = Like::getPageLike($pageid);
			if($likeexist){$likestatus = 'Liked';}
			else{$likestatus = 'Like';}
			$pagedetail = Page::find()->where(['page_id' => $pageid])->one();
			$page_img = $this->context->getpageimage($pageid);
			$pagelikeids = Page::getpagenameLikes($pageid);
		?>
		<li>
			<div class="lcontent-holder">
				<div class="photo-holder">
					<a href="<?=$pagelink?>"><img src="<?=$page_img?>"/></a>
				</div>
				<div class="content-holder">
					<h4><a href="<?=$pagelink?>"><?=$pagedetail['page_name']?></a><span> | <?=$pagedetail['category']?></span></h4>
					<div class="icon-line">
						<i class="zmdi zmdi-thumb-up"></i>
						<span class="liketitle_<?=$pageid?>">
						<?php if($like_count > 0){
							if($like_count > 3 )
							{
								$val = $like_count - 3; 
								$counter = $val.' others'; 
								$counter = ' and <a href="javascript:void(0)">'.$counter.'</a>';
							}
							else {$counter = '';}
						?>
						<?php echo $newlike_buddies . $counter .' liked this page'; ?>
						<?php } else { ?>Become a first to like this page<?php } ?>
						</span>
					</div>
					<div class="icon-line">
						<i class="zmdi zmdi-accounts"></i>
						<?=$pagelikeids?>
					</div>
					<div class="icon-line">
						<i class="zmdi zmdi-thumb-up"></i>
						<span class="likecount_<?=$pageid?>">
							<?php if($like_count > 0){ ?><?=$like_count?> liked this page
							<?php } else { ?>Become a first to like this page<?php } ?>
						</span>
					</div>
					<div class="action-btns">														
						<a class="btn btn-primary waves-effect" href="javascript:void(0)" onclick="pageLike('<?=$pageid?>');"><i class="zmdi zmdi-thumb-up"></i> <span class="likestatus_<?=$pageid?>"><?=$likestatus?></span></a>
					</div>
				</div>
			</div>
		</li>
		<?php } ?>
	</ul>
</div>
<?php } else { ?>
<?php $this->context->getnolistfound('nopromotepagefound'); ?>
<?php } ?>
<?php exit;?>