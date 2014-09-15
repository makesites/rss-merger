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
 * @version 2.1.3-beta First-usable
 * */

class rssFileCache implements rssCacheInt {
	/** @type string the directory used to store files*/
	protected $dir;
	/** @type int the maximum age of a usable cache in milliseconds */
	public $maxAge = 1000; /** default: one second */
	/** @type string base url to use with redirection */
	protected $baseUrlRedirection;
	/** @type bool true if the client was redirect on the last call of getRSSCache */
	protected $redirected = false;

	/**
	 *	The constructor
	 *
	 * @param string $dir the directory where to store the files
	 * */
	public function __construct($dir){
		if (!is_dir($dir) || !is_writable($dir))
			throw new \Exception('The directory used to store data must be a writable directory');
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
		return file_put_contents($this->getFileStoreFromId($feedId),$toStore);
	 }

	/**
	 * Retrive a string (RSS XML) from the cache for a feed
	 *
	 * @param string $feedId a unique id for the feed to store
	 *
	 * @return string the stored string (RSS XML)
	 * */
	public function getRSSCache($feedId){
		if ($this->baseUrlRedirection)
			$this->redirect($feedId);
		else
			$this->redirected = false;
		return file_get_contents($this->getFileStoreFromId($feedId));
	}

	/**
	 *	Send an header message to redirect the client to the cached file
	 *
	 * @param string the id of the cached feed
	 *
	 * @return rssFileCache $this
	 * */
	public function redirect($feedId){
		if ($this->baseUrlRedirection && !headers_sent())
		{
			header('Location: '.$this->baseUrlRedirection.'/'.$this->getFilenameFromFeedId($feedId));
			$this->redirected = true;
		}else{
			$this->redirected = false;
			throw new \Exception("rssFileCache cannot redirect if its method setBaseUrlRedirection was not used.");
		}
		return $this;
	}

	/**
	 *	Tell if a redirection http header was sent to the client
	 *
	 * @return bool true if a redirection http header was sent
	 * */
	public function wasRedirected()
	{
		return $this->redirected;
	}

	/**
	 * Check if the cache is usable (not too old)
	 *
	 * @param string $feedId a unique id for the feed to store
	 *
	 * @return bool true if usable, false if not
	 * */
	public function checkCache($feedId){
		$filename = $this->getFileStoreFromId($feedId);
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
	protected function getFileStoreFromId($feedId){
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

	/**
	 *	Set the base url to use to redirect the client to cached file
	 *
	 * @param string $url the base url
	 *
	 * @return rssFileCache $this
	 * */
	public function setBaseUrlRedirection($url){
		$this->baseUrlRedirection = $url;
		return $this;
	}

}
