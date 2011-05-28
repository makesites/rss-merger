<?php
// KML to JSON converter
//////////////////////////
// by Makis Tracend (makis@makesit.es)
// Currently used to export plolygons  and markers (labels) in a ECMA-script friendly form
// NOTE: there is a known issue with the kml namespace on the exported file. 
// 		 Manually removing the namespaces should let the parsing work properly


// Configutation
define("FILE", "example.kml"); // the name of the kml file - altertively you can pass it in the URL with the "u" variable name
define("SHOW_LABELS", true); // define if you want to show the point text as labels on the map 

// Parse XML
$xmlUrl = ( isset($_REQUEST["u"]) ) ? $_REQUEST["u"] : FILE; 
$xml = simplexml_load_file($xmlUrl, 'SimpleXMLElement',LIBXML_NSCLEAN); 

// Get the basic data set we want to use
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
	} else if( !empty($placemark->Point)&& SHOW_LABELS ) {
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