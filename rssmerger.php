<?php
/**
 * This file defile the Rssmerger class
 *
 * The Rssmerger class is the main to use the Rss-merger package
 * @package Rss-merger
 * @license GPLv2
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @copyright 2014 Stéphane Mourey <stephane.mourey@impossible-exil.info>
 * @author Stéphane Mourey <stephane.mourey@impossible-exil.info>
 * @copyright 2009-2011 Makis Tracend <makis@makesites.cc>
 * @author Makis Tracend
 * @version 2.0.0-pre-alpha (Git: $Id$ ) Fork-Day-Zero
 * */

class Rssmerger {
	const SCRIPT_VERSION = '2.0.0-Pre-alpha-$Id$';
	const SCRIPT_NAME = 'Rss Merger';
	const SCRIPT_URL = "https://github.com/taophp/rss-merger";

	/** @type string $siteName website title */
	$siteName;
	/** @type string $siteUrl website url */
	$siteUrl;
	/** @type string $feedDesc small description of this feed's purpose */
	$feedDesc;
	/** @type array list of urls of  to gather */
	$rssList;
	/*  The cache RSS file that will be created */
	$cachedRss ;
	/** @type string $xmlEncore Encoding to use for the feed */
	$xmlEncoding = 'UTF-8';
	/** @type int $nbItems Number of items to collect from each feed */
	$nbItems = '2';
	/** @type bool $enabledCache enable or not the feed cache */
	$enabledCache = false;

	/**
	 * The main method of the class, doing the merge
	 *
	 * @return void
	 *
	 * */
	function merge(){
		if ($this->checkCache()) {
			header('Location: ./' . $this->cachedRss);
			die;
		}
		$this->gatherNews();
	}

	/**
	 * Check if the cache should be use and is usable
	 *
	 * @return bool
	 *
	 * */
	function checkCache() {
		if(!$this->enabledCache) return false;
		if (file_exists($this->cachedRss) && strlen(file_get_contents($this->cachedRss)) > 0 ) {
			$time_difference = (time() - filemtime( $this->cachedRss ));
			return ($time_difference < 1000);
		} else {
			return false;
		}
	 }

	/**
	 * Loop through the rss URLs and gather the items
	 *
	 * @return void
	 *
	 * */
	function gatherNews() {
		$rssItems = array();

	 foreach ($this->rssList as $rss_url) {
		$xml = simplexml_load_file($rss_url, null, false);
		if( $xml ){
				$max_num = min( count( $xml->channel->item ), $this->nbItems) ;
				for ($i=0; $i< $max_num; $i++) {
				$item = $xml->channel->item[$i];
				$new['title'] = $item->title;
				$new['link'] = $item->link;
				$new['description'] = $item->description;
				$new['pubDate'] = $item->pubDate;
				$new['guid'] = $item->guid;
				$new['date'] = strtotime($item->pubDate);
				array_push($rssItems, $new );
				}
			}
		}
		//sort the items according to date
		usort($rssItems, array(&$this, 'sortByDate'));
		$this->outputXML($rssItems);
	}

	static function sortByDate($a,$b) {
		if ($a['date'] < $b['date']) return 1;
		if ($a['date'] > $b['date']) return -1;
		return 0;
	}

	/* the final step in our process - output the final xml file with combined data */
	function outputXML($rssItems) {
		$output = '<?xml version="1.0" encoding="' . $this->xmlEncoding . '"?>' . "\n";
		$output .= '<rss version="2.0">' . "\n";
		$output .= "\t" . '<channel>' . "\n";
		$output .= "\t\t" . '<title>' . $this->siteName . '</title>' . "\n";
		$output .= "\t\t" . '<link>' . $this->siteUrl . '</link>' . "\n";
		$output .= "\t\t" . '<description>' . $this->feedDesc . '</description>' . "\n";
		$output .= "\t\t" . '<pubDate>' . date(DATE_RFC822) . '</pubDate>' . "\n";
		$output .= "\t\t" . '<generator>'.self::SCRIPT_NAME.' v' . self::SCRIPT_VERSION . ' : '.self::SCRIPT_URL.' </generator>' . "\n";
		$output .= "\t\t" . '<language>en</language>' . "\n";

		foreach ($rssItems as $item) {
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
		if ($this->enabledCache) file_put_contents($this->cachedRss, $output, LOCK_EX);

		echo $output;
	}

}
?>
