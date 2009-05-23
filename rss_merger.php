<?php

/* Globals */
$site_name = 'My Website Title';						/* Replace this with your website's title */
$site_url = 'http://my-domain-name.com';				/* Replace this with your website's URL */
$site_info = 'A selection of my RSS collection'; 		/* A small description of your site or this feed's purpose */

$rss_list = 'rss_list.txt';								/* Where the file with all your is located */
$xml_encoding = 'UTF-8';								/* The encoding you want the final XML file to have */
$num_of_items = '2';									/* The number of items it will collect from each feed, counted off the top of the RSS feed. */

$script_version = '1.1';								/* Used for internal purposes */

runScript();

/* this is where the script process is initiated */
function runScript() {
	$is_current = checkCache();
	if ( $is_current ) {
		header('Location: ./rss_merger.xml');
		exit;
	} else {
		gatherItems();
	}
}

/* see if we have a fairly recent xml file already created*/
function checkCache() {
	if (file_exists('rss_merger.xml')) {
		$time_difference = @(time() - filemtime('rss_merger.xml'));
		if( $time_difference < 1000 ) {
			$status = true;
		} else {
			$status = false;
		}
	} else {
		$status = false;
	}
	return $status;
}

/* read the $rss_list file and create an array with its contents */
function rssList() {
	global $rss_list;
	$array = file($rss_list);
	return $array;
}

/* loop through the rss URLs and gather the items */
function gatherItems() {
	$rss_list = rssList();
	$rss_items = array();

	foreach ($rss_list as $rss_url) {
		array_push($rss_items, parseRSS($rss_url) );
	}
	outputXML($rss_items);
}

/* the main parsing process - each rss feed is parsed seperately as the function outputs an array with items */
function parseRSS($rss_url) {
	$array = array();
	$array[title] = array();
	$array[link] = array();
	$array[description] = array();
	// get contents of the RSS into a string
	$file = fopen($rss_url, "r");
	$contents = fread($file, strlen(file_get_contents($rss_url)));
	fclose($file);
	$contents = stristr($contents, '<item');
	preg_match_all("'<title(| .*?)>(.*?)</title>'si", $contents, $array[title], PREG_SET_ORDER);
	preg_match_all("'<link(| .*?)>(.*?)</link>'si", $contents, $array[link], PREG_SET_ORDER);
	preg_match_all("'<description(| .*?)>(.*?)</description>'si", $contents, $array[description], PREG_SET_ORDER);
	return $array;
}

/* the final step in our process - output the final xml file with combined data */
function outputXML($rss_items) {
    global $site_name, $site_url, $site_info, $xml_encoding, $num_of_items, $script_version;
	$output = '<?xml version="1.0" encoding="' . $xml_encoding . '"?>' . "\n";
	$output .= '<rss version="2.0">' . "\n";
	$output .= "\t" . '<channel>' . "\n";
	$output .= "\t\t" . '<title>' . $site_name . '</title>' . "\n";
	$output .= "\t\t" . '<link>' . $site_url . '</link>' . "\n";
	$output .= "\t\t" . '<description>' . $site_info . '</description>' . "\n";
	$output .= "\t\t" . '<pubDate>' . date(DATE_RFC822) . '</pubDate>' . "\n";
	$output .= "\t\t" . '<generator>http://www.makesites.cc/programming/by-makis/rss_merger_v' . str_replace('.', '', $script_version) . '/</generator>' . "\n";
	$output .= "\t\t" . '<language>en</language>' . "\n";

    foreach ($rss_items as $item) {
		for($i=0; $i<$num_of_items; $i++) {
			$output .= "\t\t" . '<item>' . "\n";
			$output .= "\t\t\t" . $item[title][$i][0] . "\n";
			$output .= "\t\t\t" . $item[link][$i][0] . "\n";
			$output .= "\t\t\t" . $item[description][$i][0] . "\n";
			$output .= "\t\t" . '</item>' . "\n\n";
		}
    }
	$output .= "\t" . '</channel>' . "\n";
	$output .= '</rss>';

	createCache($output);
	echo $output;
}

/* create a cache file with the xml content so that the script does not become CPU intensive from repetitive calls */
function createCache($output) {
	$file=fopen('rss_merger.xml',"w");
	fwrite($file,$output);
	fclose($file);
}

?>