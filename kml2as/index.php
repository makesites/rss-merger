<?php
// there is a known issue with the kml namespace on the exported file - manually removing the namespaces should let the parsing work properly
$xmlUrl = "example.kml"; 
$xmlStr = file_get_contents($xmlUrl);
$xml = simplexml_load_string($xmlStr);

// split the data in the sections we want
$placemarks = $xml->xpath("/kml/Document/Folder/Placemark");

$items = array();

foreach( $placemarks as $polycount => $placemark ){
	
	// case the type of placemark
	
	if( !empty($placemark->Polygon) ) {
		// it's a polygon
		$name = (string) $placemark->name;
		$items[$name] = array();
		$items[$name]["coords"] = generateCoords( $placemark->Polygon->outerBoundaryIs->LinearRing->coordinates );
		$items[$name]["color"] = getStyle( substr( (string)$placemark->styleUrl , 1) );
	} else if( !empty($placemark->Point) ) {
		//it's a point
		$name = (string) $placemark->name;
		$items[$name]["label"] = getLabel( $placemark );
		
	}
	
	
}

// Output
// discart the keys as we will not be needing them any more...
print_r( json_encode( array_values($items) ) );



// Functions

function generateCoords( $coordinates ){
	
	$output = array();
	
	$coords = explode(",0 " , $coordinates);
	// FIX: to pop the last element which is most likely blank
	if( trim($coords[count($coords)-1]) == "" ){ 
		array_pop($coords);
	}
	
	foreach( $coords as $num => $str ){
		// split the coordinates
		$LatLng = explode(",", $str);
		// revese order while removing whiteaspace
		$output[] = array_reverse( array_trim($LatLng) );
		
	}
		
	return $output;
	
}

function getLabel( $placemark ){
	
	$name = str_replace(" ", "\n", (string)$placemark->name );
	// order of commands: 
	// - convert the coordinates to string
	// - remove the last two characters defining 0 for the z-axis
	// - explode the coordinates to array elements
	// - reverse the order
	$coords = array_reverse( explode(",", substr( (string)$placemark->Point->coordinates, 0, -2) ) );
	
	$label = array("title" => $name, "coords" => $coords);
	return $label;
	
}


function getStyle( $id ){
	global $xml;
	// find the stylemaps related with the id
	$stylemaps = $xml->xpath("//StyleMap[@id='". $id ."']/Pair");
	
	foreach( $stylemaps as $stylemap ){
		if( $stylemap->key == "normal" ){ 
			$style = $xml->xpath("//Style[@id='". substr($stylemap->styleUrl , 1) ."']");
			return substr( (string)$style[0]->PolyStyle->color, 2);
		}
	}
}

// Helper functions
function _trim(&$value)
{
    $value = trim($value);    
}

function array_trim($arr)
{
    array_walk($arr,"_trim");
    return $arr;
}


?>