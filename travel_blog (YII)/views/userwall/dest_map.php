<?php
use frontend\assets\AppAsset;
use frontend\models\PostForm;
use frontend\models\PhotoForm;
use frontend\models\Personalinfo;
$baseUrl = AppAsset::register($this)->baseUrl;
$user_data = Personalinfo::find()->where(['user_id' => (string)$wall_user_id])->one();
$visited_countries = $user_data['visited_countries'];
if($user_data['visited_countries']=='null'){$user_data['visited_countries']='';}
$visited_str = '';
if(isset($visited_countries) && $visited_countries != '') {
	$visited_str = explode(",",$visited_countries);
}

if($user_id == $wall_user_id) {
	$visited_cities = '';

	array_push($destpast,$place);
	if(isset($visited_str) && !empty($visited_str))
	{
		$destpast = array_merge($destpast,$visited_str);
	}

	foreach($destpast as $destination){
		if(!isset($destination['_id']))
		{
			$destinationid = rand(1000,9999);
			$destinationname = str_replace("'","",$destination);
		}
		else
		{
			$destinationid = $destination['_id'];
			$destinationname = str_replace("'","",$destination['place']);
		}
		$count = substr_count($destinationname,",");
		if($count >= 1)
		{
			$placet = (explode(",",$destinationname));
			$placefirst = $placet[0];
			$placesecond = $placet[1];
			if(isset($placet[2]) && !empty($placet[2]))
			{
				$placesecond .=', '.$placet[2];
			}
		}
		else
		{
			$placet = (explode(",",$destinationname));
			$placefirst = $placet[0];
			$placesecond = '&nbsp;';
		}
		$destimage = $this->context->getplaceimage($destinationname);
		$getdestlatlng = $this->context->getlatlng($destinationname);
		$visited_cities .=  "['".$destinationname."',".$getdestlatlng.",'".$destimage."'],";
	} 
	$visited_cities = substr($visited_cities,0,-1);
} else { 
	$visited_cities = '';
	foreach($destpast as $destination)
	{
		$destinationid = $destination['_id'];
		$destinationname = str_replace("'","",$destination['place']);
		$count = substr_count($destinationname,",");
		if($count >= 1)
		{
			$placet = (explode(",",$destinationname));
			$placefirst = $placet[0];
			$placesecond = $placet[1];
			if(isset($placet[2]) && !empty($placet[2]))
			{
				$placesecond .=', '.$placet[2];
			}
		}
		else
		{
			$placet = (explode(",",$destinationname));
			$placefirst = $placet[0];
			$placesecond = '&nbsp;';
		}
		$destimage = $this->context->getplaceimage($destinationname);
		$getdestlatlng = $this->context->getlatlng($destinationname);
		$visited_cities .=  "['".$destinationname."',".$getdestlatlng.",'".$destimage."'],";
	}
	$getcurrent = $this->context->getlatlng($place);
	$destimagee = $this->context->getplaceimage($place);
	$visited_cities .=  "['".str_replace("'","",$place)."',".$getcurrent.",'".$destimagee."'],";
	$visited_cities = substr($visited_cities,0,-1);
}
	$visit_cities = '';
	foreach($destfuture as $destination)
	{
		$destinationid = $destination['_id'];
		$destinationname = str_replace("'","",$destination['place']);
		$count = substr_count($destinationname,",");
		if($count >= 1)
		{
			$placet = (explode(",",$destinationname));
			$placefirst = $placet[0];
			$placesecond = $placet[1];
			if(isset($placet[2]) && !empty($placet[2]))
			{
				$placesecond .=', '.$placet[2];
			}
		}
		else
		{
			$placet = (explode(",",$destinationname));
			$placefirst = $placet[0];
			$placesecond = '&nbsp;';
		}
		$destimage = $this->context->getplaceimage($destinationname);
		$getdestlatlng = $this->context->getlatlng($destinationname);
		$visit_cities .=  "['".$destinationname."',".$getdestlatlng.",'".$destimage."'],";
	}
	$visit_cities = substr($visit_cities,0,-1);
	$getcurrent = $this->context->getlatlng($place);
	$destimagee = $this->context->getplaceimage($place);
	$getjapan = $this->context->getlatlng('Japan');
?>
	<div class="map-holder">
		<div class="mapdiv" id="destmap"></div>
		<div class="mapkeys">
			<ul>
				<li><span class="greenkey"></span> Visited</li>
				<li><span class="orangekey"></span> Want to visit</li>
				<li><span class="redkey"></span> Current</li>
			</ul>
		</div>
		<script>
			var places={
				visited:{
					label:' Visited',
					icon: '<?=$baseUrl?>/images/green-locicon.png',
					items: [<?=$visited_cities?>]
				},
				want:{
					label:' Want to visit',
					icon: '<?=$baseUrl?>/images/orange-locicon.png',
					items: [<?=$visit_cities?>]
				},
				current:{
					label:' Current location',
					icon: '<?=$baseUrl?>/images/red-locicon.png',
					items: [
						['<?=str_replace("'","",$place)?>', <?=$getcurrent?>, '<?=$destimagee?>']
					]
				}
			},
			map = new google.maps.Map(
				document.getElementById('destmap'), 
				{
					zoom: 2,
					center: new google.maps.LatLng(<?=$getjapan?>),
				}
			),
			infowindow = new google.maps.InfoWindow(),
			ctrl=$('<div/>').css({background:'#fff',
				border:'1px solid #000',
				padding:'4px',
				margin:'2px',
				textAlign:'center'
			   });
			ctrl.append($('<input>',{type:'button',id:'showall',value:'Show'})
				.click(function(){
				  $(this).parent().find('input[type="checkbox"]')
					.prop('checked',true).trigger('change');
				}));
			//ctrl.append($('<br/>'));
			ctrl.append($('<input>',{type:'button',value:'Clear'})
				.click(function(){
				  $(this).parent().find('input[type="checkbox"]')
					.prop('checked',false).trigger('change');
				}));
			//ctrl.append($('<hr/>'));
			$.each(places,function(c,category){
				var cat=$('<input>',{type:'checkbox',checked:true}).change(function(){
				   $(this).data('goo').set('map',(this.checked)?map:null);
				})
				.data('goo',new google.maps.MVCObject)
				.prop('checked',category.checked)
				.trigger('change')
				.appendTo($('<div/>').css({whiteSpace:'nowrap',textAlign:'left'}).appendTo(ctrl))
				.after(category.label);
				$.each(category.items,function(m,item){
					/*var image = new google.maps.MarkerImage(
						item[3],
						// This marker is 20 pixels wide by 32 pixels tall.
						new google.maps.Size(20, 32));*/
					var marker=new google.maps.Marker({
						position:new google.maps.LatLng(item[1],item[2]),
						title:item[0],
						icon:category.icon
						//icon:image
					});
					marker.bindTo('map',cat.data('goo'),'map');
					google.maps.event.addListener(marker,'click',function(){
						infowindow.setContent(item[0]);
						infowindow.open(map,this);
					});
				});
			});
			map.controls[google.maps.ControlPosition.TOP_RIGHT].push(ctrl[0]);
		</script>
	</div>
<?php exit();?>