<?php

/* Globals */
$site_name = 'My Website Title';						/* Replace this with your website's title */
$site_url = 'http://my-domain-name.com';				/* Replace this with your website's URL */
$site_info = 'A selection of my RSS collection'; 		/* A small description of your site or this feed's purpose */

$rss_list = 'rss_list.txt';								/* Where the file with all your is located */
$cached_rss = 'rss_merger.xml'; 						/*  The cache RSS file that will be created */

$xml_encoding = 'UTF-8';								/* The encoding you want the final XML file to have */
$num_of_items = '2';									/* The number of items it will collect from each feed, counted off the top of the RSS feed. */

$script_version = '1.2';								/* This script's version - For internal use, please don't modify */


$rss = new RSS_Merger();
unset($rss);

class RSS_Merger { 

  /* this is where the script process is initiated */
  function __construct() {
    global $cached_rss;

    $is_current = $this->checkCache();
	if ( $is_current ) {
		header('Location: ./' . $cached_rss);
		exit;
	} else {
		$this->gatherNews();
	}
  }

  /* see if we have a fairly recent xml file already created*/
  function checkCache() {
    global $cached_rss;

    if (file_exists( $cached_rss ) && strlen(file_get_contents($cached_rss)) > 0 ) {
      $time_difference = @(time() - filemtime( $cached_rss ));
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

  /* loop through the rss URLs and gather the items */
  function gatherNews() {
    global $rss_list, $num_of_items;

    /* read the $rss_list file and create an array with its contents */
    $rss_urls = file($rss_list);
    $rss_items = array();

   foreach ($rss_urls as $rss_url) {
	  // read the XML file
	  $rss_data = file_get_contents($rss_url);
	  $rss_data = substr($rss_data, strpos($rss_data, "<rss"));
      $xml = simplexml_load_string($rss_data, null, LIBXML_NOWARNING);
	  if( $xml ){
	      // get the first two elements 
		  $max_num = min( count( $xml->channel->item ), $num_of_items) ;
	      for ($i=0; $i< $max_num; $i++) {
		    $item = $xml->channel->item[$i];
            // create a sub-array for each item
		    $new['title'] = $item->title;
		    $new['link'] = $item->link;
		    $new['description'] = $item->description;
		    $new['pubDate'] = $item->pubDate;
		    $new['guid'] = $item->guid;
		    $new['date'] = strtotime($item->pubDate);
            // insert it in the items array
            array_push($rss_items, $new );
	      }
	  }
    }
	//sort the items according to date
	usort($rss_items, array(&$this, 'sortByDate'));  
    $this->outputXML($rss_items);
  }
  
  static function sortByDate( $a , $b ){ 
    if( $a['date'] == $b['date'] ) {  
      return 0;  
    } elseif( $a['date'] > $b['date'] ){
	  return -1;
	} else {
	  return 1;
	}
  }

  /* the final step in our process - output the final xml file with combined data */
  function outputXML($rss_items) {
    global $site_name, $site_url, $site_info, $xml_encoding, $num_of_items, $script_version, $cached_rss;

    $output = '<?xml version="1.0" encoding="' . $xml_encoding . '"?>' . "\n";
    $output .= '<rss version="2.0">' . "\n";
    $output .= "\t" . '<channel>' . "\n";
    $output .= "\t\t" . '<title>' . $site_name . '</title>' . "\n";
    $output .= "\t\t" . '<link>' . $site_url . '</link>' . "\n";
    $output .= "\t\t" . '<description>' . $site_info . '</description>' . "\n";
    $output .= "\t\t" . '<pubDate>' . date(DATE_RFC822) . '</pubDate>' . "\n";
    $output .= "\t\t" . '<generator>RSS Merger v' . $script_version . ' : http://www.makesites.cc/projects/rss_merger </generator>' . "\n";
    $output .= "\t\t" . '<language>en</language>' . "\n";

    foreach ($rss_items as $item) {
      $output .= "\t\t" . '<item>' . "\n";
      $output .= "\t\t\t" . '<title>' . $item['title'] . '</title>' . "\n";
      $output .= "\t\t\t" . '<link>' . $item['link'] . '</link>' . "\n";
      $output .= "\t\t\t" . '<description><![CDATA[' . $item['description'] . ']]></description>' . "\n";
      $output .= "\t\t\t" . '<pubDate>' . $item['pubDate'] . '</pubDate>' . "\n";
      $output .= "\t\t" . '</item>' . "\n\n";
    }
    $output .= "\t" . '</channel>' . "\n";
    $output .= '</rss>';

	// create the cache file for later use...
	file_put_contents($cached_rss, $output, LOCK_EX);

	echo $output;
  }

}

?>