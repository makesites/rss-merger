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
 * @version 2.4.1-beta Atoms welcome
 * */

class rssMerger {
	const SCRIPT_VERSION = '2.4.2-beta';
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
	/** @type string the file name URL of the image of the feed */
	public $imgURL;
	/** @type string description for the image */
	public $imgDesc;
	/** @type int defines the width of the image. Default is 88. Maximum value is 144 */
	public $imgWidth;
	/** @type int defines the height of the image. Default is 31. Maximum value is 400 */
	public $imgHeight;
	/** @type int Max number of items to gather from each feed, 0 for all */
	protected $nbItems2Gather = 0;
	/** @type int Max number of items to produce in the result, 0 for all */
	protected $nbItems2Produce = 0;
	/** @type object the cache object to store data */
	protected $cache;
	/** @type bool set to true if you want the feed output to be formatted (i.e. with tabulations and linebreaks) */
	public $formatted = false;
	/** @type bool set true if you want download the feeds asynchroniouly */
	public $asynchronious = true;
	/** @type int the maximum number of seconds to wait for a feed to merge */
	public $curlTimeOut = 10;
	/** @ type array list of RSS Urls and short names */
	public $RSSfeedList;
	
	
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
	public function addFeeds($feeds) {
		$args = func_get_args();
		if (count($args)>2 || ( count($args)==2 && $args[1]!==0)) $feeds = $args;
		if (is_string($feeds) && strpos($feeds,',')) $feeds = explode(',',$feeds);
		if (is_array($feeds))
			foreach ($feeds as $feed)
				$this->addFeeds($feed);
		else {
			$this->rssList[] = $feeds;
		}
		return $this;
	}
	/**
	 *	Add feeds to the list of the feeds to grab
	 *
	 * @deprecated use addFeeds instead
	 *
	 * @param string|array $feeds the list of the feeds to add, in comma separated string or in an array
	 *
	 * @return rssMerger $this*
	 *
	 * */
	public function addRssFeeds() {
		return $this->addFeeds(func_get_args());
	}


	/**
	 * The main method of the class, doing the merge
	 *
	 * @return string the new RSS feed
	 * */
	public function getMerged(){
		if ($this->checkRSSCache()) {
			return $this->cache->getRSSCache($this->getFeedId());
		}
		return $this->gatherNews();
	}

	/**
	 * Check if the cache should be use and is usable
	 *
	 * @return bool
	 * */
	protected function checkRSSCache() {
		if(!$this->cache) return false;
		return $this->cache->checkRSSCache($this->getFeedId());
	}

	/**
	 * Loop through the rss URLs and gather the items
	 *
	 * @return string the new RSS feed
	 * */
	protected function gatherNews() {
		$rssItems = array();
		$this->checkCurlMulti();

		if ($this->asynchronious)
		{
			/** Using CURL for asynchronious download of feeds (should must faster) */
			$mh = curl_multi_init();
			foreach ($this->rssList as $rssUrl)
			{
				$curlHls[$rssUrl] = curl_init();
				curl_setopt($curlHls[$rssUrl], CURLOPT_URL, $rssUrl);
				curl_setopt($curlHls[$rssUrl], CURLOPT_HEADER, 0);
				curl_setopt($curlHls[$rssUrl], CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curlHls[$rssUrl], CURLOPT_TIMEOUT, $this->curlTimeOut);
				curl_multi_add_handle($mh,$curlHls[$rssUrl]);
			}
			$active = false;
			do {
				$mrc = curl_multi_exec($mh, $active);
				curl_multi_select($mh);
			} while ($active && $mrc == CURLM_OK);
			$aXml = array();
			foreach ($curlHls as $ch)
			{
				/** Get RSS URL from curl request */
				$rss_org_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
				$tXml = false;
				if (self::isRss($ch)){
					/** rss+xml content detected */
					$tXml = simplexml_load_string(curl_multi_getcontent($ch));
					$tXml->channel->addChild('sourceURL', $rss_org_url);
				}
				if (self::isAtom($ch)) {
					/** atom+xml content detected */
					$tXml = simplexml_load_string(self::convertFromAtom(curl_multi_getcontent($ch)));
					$tXml->channel->addChild('sourceURL', $rss_org_url);
				}
				
				if (curl_getinfo($ch, CURLINFO_CONTENT_TYPE) == 'text/xml' || curl_getinfo($ch, CURLINFO_CONTENT_TYPE) == 'application/xml') {
					/** text/xml or application/xml content detected */
					$tXml = simplexml_load_string(self::convertFromAtom(curl_multi_getcontent($ch)));
					$tXml->channel->addChild('sourceURL', $rss_org_url);
				}			
				if (!$tXml) {
					/** not supported content detected */
					$tXml = simplexml_load_string(curl_multi_getcontent($ch));
					$tXml->channel->addChild('sourceURL', htmlspecialchars($rss_org_url));
				}
				if ($tXml) $aXml[] = $tXml;
			}
		}else{
			/** Synchronious download of feeds */
			foreach ($this->rssList as $rssUrl)
			{
				$headers = get_headers($rssUrl);
				$tXml = strpos($headers['Content-Type'],'atom+xml') ?
										simplexml_load_string(self::convertFromAtom(file_get_contents($rssUrl))) :
										simplexml_load_file($rssUrl);
				if($tXml)
				if ($tXml) {
					// Add Feed source URL to RSS channel element
					$tXml->channel->addChild('sourceURL', $rssUrl);
					$aXml[] = $tXml;
				}
			}
		}
		/** Feed are loaded, parsing them */
		foreach ($aXml as $xml)
		{
			$feedNbItems = count($xml->channel->item);
			$maxNum = $this->nbItems2Gather ? min($feedNbItems, $this->nbItems2Gather) : $feedNbItems;
			for ($i=0; $i< $maxNum; $i++) {
				$item = $xml->channel->item[$i];
				$new['title'] = self::getFeedIdentifier($xml->channel->sourceURL) . $item->title;
				$new['link'] = $item->link;
				$new['description'] = $item->description;
				$new['pubDate'] = $item->pubDate;
				$new['guid'] = $item->guid?$item->guid:$item->link; // not a real GUID if not provided, but unique and enought to validate the RSS feed
				$new['date'] = strtotime($item->pubDate);
				foreach ($new as $k=>$v)
					$encOri = function_exists('mb_detect_encoding')?mb_detect_encoding($v):'UTF-8';
					if ($encOri=='ASCII') $encOri = 'UTF-8';
					$new[$k] = '<![CDATA['.html_entity_decode($v,ENT_COMPAT | ENT_HTML401,$encOri).']]>';
					
				// Adding source URL html_entity_decode to stay W3C Validator compatible
				$new['source'] = htmlspecialchars($xml->channel->sourceURL);
				array_push($rssItems, $new);
			}
		}
		//sort the items according to date
		usort($rssItems, array(__CLASS__, 'sortByDate'));
		if ($this->nbItems2Produce > 0)
			$rssItems = array_slice($rssItems,0,$this->nbItems2Produce);
		return $this->outputXML($rssItems);
	}
	
	/**
	 *	Match RSS feed URL to a user friendly name
	 *
	 * @param string $sourceURL
	 *
	 * @return string with user friendly name if match was found. otherwise empty string $this
	 *
	 * */
	function getFeedIdentifier($sourceURL){
		if (!empty($this->RSSfeedList)) {
			$key = array_search($sourceURL, array_column($this->RSSfeedList, 'url'));
			if ($key !== false) {
				return '[' . $this->RSSfeedList[$key]['name'] . '] ';
			} else {
				return '';
			}
		} else {
			return '';
		}
	}
	
	
	
	/**
	 *	Check if the required curl functions are available
	 *
	 * @return bool true if yes
	 * */
	function checkCurlMulti(){
		if (!$this->asynchronious) return false;
		$curlFunctions2Check = array('curl_multi_init','curl_multi_exec');
		foreach ($curlFunctions2Check as $f)
		{
			if (!function_exists($f))
				return $this->asynchronious = false;
		}
		return true;
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
		/** if there no item, and there is data in cache, even old, we use them instead */
		if (!count($rssItems)
			&& $this->cache
			&& $this->cache->checkRSSCacheExists($this->getFeedId()))
				return $this->cache->getRSSCache($this->getFeedId());

		/** Back to normal use */
		$t=$n='';
		if ($this->formatted) {
			$t="\t";
			$n="\n";
		}
		$output = '<?xml version="1.0" encoding="' . $this->xmlEncoding . '"?>' . $n;
		$output .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . $n;
		$output .= $t . '<channel>' . $n;
		$output .= $t.$t . '<title>' . $this->siteName . '</title>' . $n;
		$output .= $t.$t . '<link>' . $this->siteUrl . '</link>' . $n;
		
		if (!empty($this->imgURL)) {
			$output .= $t.$t . '<image>' . $n;
				$output .= $t.$t . '<url>' . $this->imgURL . '</url>' . $n;
				$output .= $t.$t . '<title>' . $this->siteName . '</title>' . $n;
				$output .= $t.$t . '<link>' . $this->siteUrl . '</link>' . $n;
				if (!empty($this->imgWidth) && !empty($this->imgHeight)) {
					$output .= $t.$t . '<width>' . $this->imgWidth . '</width>' . $n;
					$output .= $t.$t . '<height>' . $this->imgHeight . '</height>' . $n;
				}
				if (!empty($this->imgDesc)) {
					$output .= $t.$t . '<description>' . $this->imgDesc . '</description>' . $n;
				}
			$output .= $t.$t . '</image>' . $n;
		}
		$output .= $t.$t . '<description>' . $this->feedDesc . '</description>' . $n;
		$output .= $t.$t . '<pubDate>' . date('r') . '</pubDate>' . $n;
		$output .= $t.$t . '<generator>'.self::SCRIPT_NAME.' v' . self::SCRIPT_VERSION . ' : '.self::SCRIPT_URL.' </generator>' . $n;
		$output .= $t.$t . '<language>'.$this->lang.'</language>' . $n;
		
		/** Check if https is used. If so, return https for atom link */
		if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
			$secProtocol = 'https';
		} else {
			$secProtocol = strtolower(substr($_SERVER['SERVER_PROTOCOL'],0,strpos($_SERVER[SERVER_PROTOCOL],'/')));
		}
		
		$output .= $t.$t . '<atom:link href="'. $secProtocol .'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'" rel="self" type="application/rss+xml" />' . $n;

		foreach ($rssItems as $item) {
			$output .= $t.$t . '<item>' . $n;
			$output .= $t.$t.$t . '<title>' . $item['title'] . '</title>' . $n;
			$output .= $t.$t.$t . '<link>' . $item['link']  . '</link>' . $n;
			$output .= $t.$t.$t . '<guid>' . $item['guid']  . '</guid>' . $n;
			$output .= $t.$t.$t . '<description>' . $item['description'] . '</description>' . $n;
			$output .= $t.$t.$t . '<pubDate>' . $item['pubDate'] . '</pubDate>' . $n;
			/** Add source RSS feed URL to feed item if a valid one was found */
			if ($item['source'] != '') {
				$output .= $t.$t.$t . '<source url="' . $item['source'] . '">' . $item['source'] . '</source>' . $n;
			}
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
	public function setCache($cache)
	{
		if (!in_array('Taophp\rssCacheInt',class_implements($cache)))
			throw new \Exception('rssMerger::setCache expects an objet that implements the rssCacheInt interface. Implemented interfaces: '.print_r(class_implements($cache),true));
		$this->cache = $cache;
		return $this;
	}

	/**
	 *	Convert an Atom string to an RSS string
	 *
	 * @param string $source the Atom string to convert
	 *
	 * @return string the converted string
	 * */
	static public function convertFromAtom($source){
		$chan = new \DOMDocument();
		$chan->loadXML($source);

		$sheet = new \DOMDocument();
		$sheet->load(__DIR__.'/atom2rss/atom2rss.xsl');

		$processor = new \XSLTProcessor();
		$processor->registerPHPFunctions();
		$processor->importStylesheet($sheet);
		return $processor->transformToXML($chan);
	}

	/**
	 *	test if a feed is RSS
	 *
	 * @param ressource $ch
	 *
	 * @return true or false
	 * */
	static public function isRss(&$ch) {
		return strpos(curl_getinfo($ch, CURLINFO_CONTENT_TYPE),'rss+xml')?true:false;
	}

	/**
	 *	test if a feed is Atom
	 *
	 * @param ressource $ch the feed to test
	 *
	 * @return true or false
	 * */
	static public function isAtom(&$ch) {
		return strpos(curl_getinfo($ch, CURLINFO_CONTENT_TYPE),'atom+xml')?true:false;
	}


}
