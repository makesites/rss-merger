<?php
namespace Taophp;
/**
 * This file defile the rssFileCache class
 *
 * This is a file cache to use with the rssMerger class
 * @package Rss-merger
 * @license GPLv2
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @copyright 2014 Stéphane Mourey <stephane.mourey@impossible-exil.info>
 * @author Stéphane Mourey <stephane.mourey@impossible-exil.info>
 * @copyright 2009-2011 Makis Tracend <makis@makesites.cc>
 * @author Makis Tracend
 * @version 2.3.1-beta Valid RSS Rogers
 * */

class rssFileCache implements rssCacheInt {
	/** @type string the directory used to store files*/
	protected $dir;
	/** @type int the maximum age of a usable cache in milliseconds */
	public $maxAge = 1000; /** default: one second */

	/**
	 *	The constructor
	 *
	 * @param string $dir the directory where to store the files
	 * */
	public function __construct($dir){
		if (!is_dir($dir) || !is_writable($dir))
			throw new \Exception('The directory used to store data must be a writable directory.');
		$this->dir = $dir;
	}

	/**
	 * Store a string (RSS XML) in the cache for a feed
	 *
	 * @param string $feedId a unique id for the feed to store
	 * @param string $toStore the string to store in the cache (RSS XML)
	 *
	 * @return bool
	 * */
	 public function feedRSSCache($feedId,$toStore){
		return file_put_contents($this->getFileFullNameFromFeedId($feedId),$toStore);
	 }

	/**
	 * Retrive a string (RSS XML) from the cache for a feed
	 *
	 * @param string $feedId a unique id for the feed to store
	 *
	 * @return string the stored string (RSS XML)
	 * */
	public function getRSSCache($feedId){
		return file_get_contents($this->getFileFullNameFromFeedId($feedId));
	}

	/**
	 * Check if the cache is usable (not too old)
	 *
	 * @param string $feedId a unique id for the feed to store
	 *
	 * @return bool true if usable, false if not
	 * */
	public function checkRSSCache($feedId){
		$filename = $this->getFileFullNameFromFeedId($feedId);
		return file_exists($filename)
						&& filesize($filename)
						&& (time()-filemtime($filename) < $this->maxAge);
	}

	/**
	 *	Return the complete filename with path of a cached file for a feed Id
	 *
	 * @param string $feedId the feed id
	 *
	 * @return string the complete filename
	 * */
	protected function getFileFullNameFromFeedId($feedId){
		return $this->dir.'/'.$this->getFilenameFromFeedId($feedId);
	}

	/**
	 *	Return the basename of the file used to store a cached feed from its Id
	 *
	 * @param string $feedId the feed id
	 *
	 * @return string the base filename
	 * */
	protected function getFilenameFromFeedId($feedId){
		return $feedId.'.rss';
	}

}
