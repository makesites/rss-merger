<?php
namespace Taophp;
/**
 * This file defile the RsscacheInt interface
 *
 * This interface defines the minimal requirement expected for a cache object to work with the rssMerger class
 * @package Rss-merger
 * @license GPLv2
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @copyright 2014 Stéphane Mourey <stephane.mourey@impossible-exil.info>
 * @author Stéphane Mourey <stephane.mourey@impossible-exil.info>
 * @version 2.3.0-beta Asynchronous
 * */

interface rssCacheInt {
	/**
	* Store a string (RSS XML) in the cache for a feed
	*
	* @param string $feedId a unique id for the feed to store
	* @param string $toStore the string to store in the cache (RSS XML)
	*
	* @return bool
	* */
	public function feedRSSCache($feedId,$toStore);

	/**
	* Retrive a string (RSS XML) from the cache for a feed
	*
	* @param string $feedId a unique id for the feed to store
	*
	* @return string the stored string (RSS XML)
	* */
	public function getRSSCache($feedId);

	/**
	* Check if the cache is usable (not too old)
	*
	* @param string $feedId a unique id for the feed to store
	*
	* @return bool true if usable, false if not
	* */
	public function checkRSSCache($feedId);

}
