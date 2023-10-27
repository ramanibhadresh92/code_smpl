<?php   
use frontend\models\Trip;
$stops = Trip::getTripDetails($trip);
$getstartpoints = $this->context->getlatlng($stops['start_from']);
$start = '';
$end = '';
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
var geocoder;
var map;

initialize();

function initialize() {
	var center = new google.maps.LatLng(<?=$getstartpoints?>);
	map = new google.maps.Map(document.getElementById('trip-map-share-<?=$trip?>'), {
		center: center,
		zoom: 10,
		height: 450,
		width: 600,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	});
	var bounds = new google.maps.LatLngBounds();
	var start = [<?=$start?>]
	var end = [<?=$end?>]
	for (var i=0; i < end.length; i++) {
		var startCoords = start[i].split(",");
		var startPt = new google.maps.LatLng(startCoords[0],startCoords[1]);
		var endCoords = end[i].split(",");
		var endPt = new google.maps.LatLng(endCoords[0],endCoords[1]);
		calcRoute(startPt, endPt);
		bounds.extend(startPt);
		bounds.extend(endPt);
	}
	map.fitBounds(bounds);
}

function calcRoute(source,destination) {
	var polyline = new google.maps.Polyline({
		path: [source, destination],
		strokeColor: '<?=str_replace('dot','',$stops['tripcolor'])?>',
		strokeWeight: 5,
		<?php if($stops['mapline'] == 'curves'){?>
		geodesic: true,
		<?php } ?>
		strokeOpacity: 5
	});
	polyline.setMap(map);
}
</script>
<?php exit;?>