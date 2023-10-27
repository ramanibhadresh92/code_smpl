<?php 
use frontend\models\PageVisitor;

$totalpagecount = PageVisitor::getPageVisitors($page_details['page_id']);
$totalweekcount = PageVisitor::getLastYearPageVisitors($page_id,date('Y-m-d h:i:s',strtotime(date('Y-m-d h:i:s') . " -1 week")));
$totalthirtycount = PageVisitor::getLastYearPageVisitors($page_id,date('Y-m-d h:i:s',strtotime(date('Y-m-d h:i:s') . " -30 days")));
$per = '';
$status = '<i class="mdi mdi-menu-down"></i>';
if($totalpagecount >0) {
    $per = ($totalthirtycount*100)/ $totalpagecount;
    if($totalthirtycount >= $totalpagecount) {
        $status = '<i class="mdi mdi-menu-up"></i>';
    } else {
        $status = '<i class="mdi mdi-menu-down"></i>';
        $per = 100 - $per;
    }
}

if($page_details['created_by'] == $user_id)
{
    $vals = $val = 'your '; 
}
else
{
    $val = 'this ';  $vals = 'The';  
}
?>
<div class="content-box bshadow">
    <div class="cbox-title">
        Who viewed <?=$val?> page 
    </div>
    <div class="cbox-desc">
        <div class="view-profile">
            <ul>
                <li>
                    <div class="viewcount"><span><?=$totalpagecount?></span></div>
                    <?=$val?> page has been viewed by <?=$totalweekcount?> people in the past 7 days.
                </li>
                <li>
                    <div class="viewcount"><?=$status?><span><?=$totalthirtycount?></span></div>
                    <?=$vals?> rank for the page views improved by <?=sprintf("%.2f",$per)?>% in the past 30 days.
                </li>
            </ul>
        </div>
    </div>
</div>