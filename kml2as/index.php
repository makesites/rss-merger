<?php
$xmlUrl = "example.kml"; // XML feed file/URL
$xmlStr = file_get_contents($xmlUrl);
$xml = simplexml_load_string($xmlStr);

//$placemarks = $xml->xpath("/kml/Document/Folder/Placemark");
//$styles = $xml->xpath("/kml/Document/StyleMap");
$polygons = $xml->xpath("/kml/Document/Folder/Placemark/Polygon");


// what's presented on the screen
$output = "<pre>";

foreach( $polygons as $polycount => $polygon ){
	$output .= "{ coords:[";
	
	$coordinates = $polygon->outerBoundaryIs->LinearRing->coordinates;
	$coords = explode(",0 " , $coordinates);
	// FIX: to pop the last element which is most likely blank
	if( trim($coords[count($coords)-1]) == "" ){ 
		array_pop($coords);
	}
	
	foreach( $coords as $num => $str ){
		
		// split the coordinates
		$LatLng = explode(",", $str);
		// revese order while removing whiteaspace
		$output .= "new LatLng(" . trim($LatLng[1]) .", ". trim($LatLng[0]) .")";
		
		if( $num+1 < count($coords) ){
			$output .= ",";
		}
	}
	
	$output .= "], " . 'colour:"lightblue" }';
	
	if( $polycount+1 < count($polygons) ){
		$output .= "," . "\n";
	} else {
		$output .= "" . "\n";
	}
	
}

$output .= "</pre>";

echo $output;


?>