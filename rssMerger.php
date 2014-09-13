<?php
namespace Taophp;
/**
 * This file defile the rssMerger class
 *
 * The rssMerger class is the main to use the Rss-merger package
 * @package Rss-merger
 * @license GPLv2
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @copyright 2014 Stéphane Mourey <stephane.mourey@impossible-exil.info>
 * @author Stéphane Mourey <stephane.mourey@impossible-exil.info>
 * @copyright 2009-2011 Makis Tracend <makis@makesites.cc>
 * @author Makis Tracend
 * @version 2.1.0-alpha Fork-Day-One
 * */

class rssMerger {
	const SCRIPT_VERSION = '2.1.0-alpha';
	const SCRIPT_NAME = 'Rss Merger';
	const SCRIPT_URL = 'https://github.com/taophp/rss-merger';

	/** @type string $siteName website title */
	public $siteName;
	/** @type string $siteUrl website url */
	public $siteUrl;
	/** @type string $feedDesc small description of this feed's purpose */
	public $feedDesc;
	/** @type array list of urls of  to gather */
	public $rssList;
	/** @type string Encoding to use for the feed */
	public $xmlEncoding = 'UTF-8';
	/** @type string the language of the producted feed */
	public $lang = 'en';
	/** @type int Max number of items to gather from each feed, 0 for all */
	protected $nbItems2Gather = 0;
	/** @type int Max number of items to produce in the result, 0 for all */
	protected $nbItems2Produce = 0;
	/** @type object the cache object to store data */
	protected $cache;
	/** @type bool set to true if you want the feed output to be formatted (i.e. with tabulations and linebreaks) */
	public $formatted = false;

	/**
	 *	Set the number of items to gather from each feed
	 *
	 * @param int $nbItems Number of items to gather from each feed, 0 for all
	 *
	 * @return rssMerger $this
	 *
	 * */
	public function setNumberOfItems2Gather($nbItems)
	{
		$this->nbItems2Gather = $nbItems;
		return $this;
	}

	/**
	 *	Set the number of items to output in the feed
	 *
	 * @param int $nbItems Number of items to output in the feed, 0 for all
	 *
	 * @return rssMerger $this
	 *
	 * */
	public function setNumberOfItems2Produce($nbItems)
	{
		$this->nbItems2Produce = $nbItems;
		return $this;
	}

	/**
	 *	Add feeds to the list of the feeds to grab
	 *
	 * @param string|array $feeds the list of the feeds to add, in comma separated string or in an array
	 *
	 * @return rssMerger $this
	 *
	 * */
	public function addRssFeeds($feeds) {
		$args = func_get_args();
		if (count($args)>1) $feeds = $args;
		if (is_string($feeds) && strpos($feeds,',')) $feeds = explode(',',$feeds);
		if (is_array($feeds))
			array_walk($feeds,array($this,__METHOD__));
		else {
			$this->rssList[] = $feeds;
		}
		return $this;
	}

	/**
	 * The main method of the class, doing the merge
	 *
	 * @return string the new RSS feed
	 * */
	public function getMerged(){
		if ($this->checkCache()) {
			return $this->cache->getRSSCache($this->getFeedId());
		}
		return $this->gatherNews();
	}

	/**
	 * Check if the cache should be use and is usable
	 *
	 * @return bool
	 * */
	protected function checkCache() {
		if(!$this->cache) return false;
		return $this->cache->checkCache($this->getFeedId());
	}

	/**
	 * Loop through the rss URLs and gather the items
	 *
	 * @return string the new RSS feed
	 * */
	protected function gatherNews() {
		$rssItems = array();

		foreach ($this->rssList as $rssUrl) {
		$xml = simplexml_load_file($rssUrl, null, false);
		if($xml){
			$feedNbItems = count($xml->channel->item);
			$maxNum = $this->nbItems2Gather ? min($feedNbItems, $this->nbItems2Gather) : $feedNbItems;
			for ($i=0; $i< $maxNum; $i++) {
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
		usort($rssItems, array(__CLASS__, 'sortByDate'));
		if ($this->nbItems2Produce > 0)
			$rssItems = array_slice($rssItems,0,$this->nbItems2Produce);
		return $this->outputXML($rssItems);
	}

	/**
	 *	Use to sort items, callback function for usort
	 *
	 * @param array $a rss item
	 * @param array $b rss item
	 *
	 * @return int (1 if $a is older, -1 if $b is older, 0 otherwise)
	 * */
	protected static function sortByDate($a,$b) {
		if ($a['date'] < $b['date']) return 1;
		if ($a['date'] > $b['date']) return -1;
		return 0;
	}

	/**
	 *	Produce the RSS final string
	 *
	 * @param array $rssItems the array containing all the items to output
	 *
	 * @return string the RSS formatted string with all that stuff
	 * */
	protected function outputXML($rssItems) {
		$t=$n='';
		if ($this->formatted) {
			$t="\t";
			$n="\n";
		}
		$output = '<?xml version="1.0" encoding="' . $this->xmlEncoding . '"?>' . $n;
		$output .= '<rss version="2.0">' . $n;
		$output .= $t . '<channel>' . $n;
		$output .= $t.$t . '<title>' . $this->siteName . '</title>' . $n;
		$output .= $t.$t . '<link>' . $this->siteUrl . '</link>' . $n;
		$output .= $t.$t . '<description>' . $this->feedDesc . '</description>' . $n;
		$output .= $t.$t . '<pubDate>' . date(DATE_RFC822) . '</pubDate>' . $n;
		$output .= $t.$t . '<generator>'.self::SCRIPT_NAME.' v' . self::SCRIPT_VERSION . ' : '.self::SCRIPT_URL.' </generator>' . $n;
		$output .= $t.$t . '<language>'.$this->lang.'</language>' . $n;

		foreach ($rssItems as $item) {
			$output .= $t.$t . '<item>' . $n;
			$output .= $t.$t.$t . '<title>' . $item['title'] . '</title>' . $n;
			$output .= $t.$t.$t . '<link>' . $item['link'] . '</link>' . $n;
			$output .= $t.$t.$t . '<description><![CDATA[' . $item['description'] . ']]></description>' . $n;
			$output .= $t.$t.$t . '<pubDate>' . $item['pubDate'] . '</pubDate>' . $n;
			$output .= $t.$t . '</item>' . $n.$n;
		}
		$output .= $t . '</channel>' . $n;
		$output .= '</rss>';

		// create the cache file for later use...
		if ($this->cache) $this->cache->feedRSSCache($this->getFeedId(),$output);

		return $output;
	}

	/**
	 *	Produce a unique Id for $this, mainly to use with the cache
	 *
	 * @return string the Id for $this
	 * */
	public function getFeedId(){
			return md5(serialize($this));
	}

	/**
	 *	Attach a cache objet to use, the object must implements the rssCacheInt interface
	 *
	 * @param object $cache the object to use as cache
	 *
	 * @return rssMerger $this
	 * */
	public function setCache(object $cache)
	{
		if (!in_array('rssCacheInt',class_implements($cache)))
			throw new Exception('rssMerger::setCache expects an objet that implements the rssCaheInt interface.');
		$this->cache = $cache;
		return $this;
	}


}
