<?php   
use frontend\models\Trip;
$stops = Trip::getTripDetails($place);
$getstartpoints = $this->context->getlatlng($stops['start_from']);
$stop = explode('**',$stops['end_to']);
$stop = array_filter($stop);
$count = count($stop);
for($i=0;$i<$count;$i++)
{
	if($i+1 < $count)
	{
		$place = $stop[$i];
		$plltlng = $this->context->getlatlng($place);
		$start .= "'$plltlng',";
	}
	$placee = $stop[$i];
	$plltlngg = $this->context->getlatlng($placee);
	$end .= "'$plltlngg',";
}
$from = "'".$getstartpoints."'";
$first = $from.','.$start;
$start = substr($first,0,-1);
$end = substr($end,0,-1);
?>
<script>
function calcRoute(source,destination) {
	var polyline = new google.maps.Polyline({
		path: [source, destination],
		strokeColor: '<?=str_replace('dot','',$stops['tripcolor'])?>',
		strokeWeight: 5,
		<?php if($stops['mapline'] == 'curves'){ ?>
		geodesic: true,
		<?php } ?>
		strokeOpacity: 5
	});
	polyline.setMap(map);
}
</script>
<?php exit;?>