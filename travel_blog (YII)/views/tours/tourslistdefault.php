<?php $i = 1; 
    foreach($tourslist as $key => $tours) { 
        if(empty($tours)) {
            continue;
        } ?>
		<?php if(isset($lazyhelpcount) && ($lazyhelpcount == 0 || $lazyhelpcount == '')) { ?>
        <div class="tours-section">
        <h4><?=$key?></h4>
        <div class="row">
		<?php } ?> 
        <?php 
        foreach ($tours as $key => $tour) { ?> 
        <div class="col s12 m4 l4 tour-holder tourbox">
            <a class="tour-box" href="<?=$tour['ProductURL']?>" target="_new">
                <span class="imgholder"><img src="<?=str_replace('/graphicslib','/graphicslib/thumbs674x446/',$tour['ProductImage'])?>"/></span>
                <span class="descholder">
                    <span class="head6"><?=$tour['Group1']?></span>
                    <span class="head5"><?=$tour['ProductName']?></span>
                    <span class="info">
                        <span class="ratings">
                            <span class="clear"></span>
                            <?php for($j=0;$j<$tour['AvgRating'];$j++){ ?>
                            <i class="mdi mdi-star"></i>
                            <?php } ?>
                        </span>
                        <span class="pricing">
                            <span class="currency">From USD</span>
                            <span class="amount">$<?=$tour['PriceUSD']?></span>
                        </span>
                    </span>
                </span>
            </a>
        </div>
        <?php } ?>
<?php if(isset($lazyhelpcount) && ($lazyhelpcount == 0 || $lazyhelpcount == '')) { ?>
    </div>
</div>
<?php } ?>
<?php if($i%3 == 0) { ?>
    &nbsp;<br/>
<?php } 
$i++; 
} 
exit;?>