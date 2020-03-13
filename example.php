<?php
/**
 * This is an example on how to use rssMerger
 *
 * @package Rss-merger
 * @license GPLv2
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @copyright 2017 Stefan Bautz <stefan.bautz@gmail.com>
 * @author Stefan Bautz <stefan.bautz@gmail.com>
 * @version 1.0
 * */
	
	// Include required files
	require_once('rssMerger.php');
	require_once('rssCacheInt.php');
	require_once('rssFileCache.php');
	
	// create new merger
	$mymerger = new Taophp\rssMerger();

	// create the cache from a directory path
	try {
		$cache = new Taophp\rssFileCache('tmp');
	} catch (Exception $e) {
		error_log($e->getMessage());
	}

	// Optional - if $cache creation is successfull, associate it to the merger
	if ($cache) $mymerger->setCache($cache);
	
	// Optional - Set the total number of items to produce (default is all)
	$mymerger->setNumberOfItems2Produce(0);

	// Optional - Set the number of items to gather from each feed (default is all)
	$mymerger->setNumberOfItems2Gather(0);
	
	// Add feed URLs
	$mymerger->addFeeds(
		'https://owncloud.org/blogfeed/',
		'http://forum.teamspeak.com/external.php?type=RSS2&forumids=91',
		'https://about.gitlab.com/atom.xml',
		'https://roundcube.net/feeds/atom.xml',
		'https://wordpress.org/news/feed/'
	);
	
	// Optional - Give a short name for each RSS feed URL which will be displayed in front of each entry in the summary
	
	$RSSfeedList = array(
		0 => array(
			'name' => 'GitLab',
			'url' => 'https://about.gitlab.com/atom.xml'
		),
		1 => array(
			'name' => 'OwnCloud',
			'url' => 'https://owncloud.org/blogfeed/'
		),
		2 => array(
			'name' => 'TeamSpeak',
			'url' => 'http://forum.teamspeak.com/external.php?type=RSS2&forumids=91'
		),
		3 => array(
			'name' => 'RoundCube',
			'url' => 'https://roundcube.net/feeds/atom.xml'
		),
		4 => array(
			'name' => 'WordPress',
			'url' => 'https://wordpress.org/news/feed/'
		),
	);
	
	// Optional - Define RSS Name
	$mymerger->siteName = 'Software updates';
	
	// Optional - Give a short Feed description
	$mymerger->feedDesc = 'Merged RSS feed for software updates';
	
	// Optional - URL of the feed
	$mymerger->siteUrl = 'https://www.example.com/rss/example.php';
	
	// Optional - Image name for the feed image (Not used if empty)
	$mymerger->imgURL = 'https://www.example.com/rss/update-feed.png';
	
	// Optional - Description for the feed image (Not used if empty)
	$mymerger->imgDesc = 'Software updates feed image';
	
	// Optional - Feed image width (default is 88. Maximum value is 144. Not used if empty)
	$mymerger->imgWidth = 88;
	
	// Optional - Feed image height (default is 31. Maximum value is 400. Not used if empty)
	$mymerger->imgHeight = 31;
	
	// Get the resulting RSS feed in a string
	$rssString = $mymerger->getMerged();

	// Set header
	header('Content-Type: application/rss+xml; charset=UTF-8');
	
	// Output merged feed
	echo $rssString;

?>
