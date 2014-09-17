########################################################################

RSS Merger 2.2.0-beta More usable

Forked by Stéphane Mourey (stephane.mourey@impossible-exil.info)
URL: http://impossible-exil.info
Created by: Makis Tracend (makis@makesites.cc)
URL: http://www.makesites.cc/projects/rss_merger

########################################################################

Description
===========
This script will load a number of RSS feeds and compile them into one RSS 2.0 file. You can use it to present a mash-up of your news from different places (blogs, twitter etc.) or just present news from other sources around the Net.

Usage
=====
Without cache :
```php
<?php
require_once('rssMerger.php');
$mymerger = new Taophp\rssMerger();

// Set the total number of items to produce (default is all)
$mymerger->setNumberOfItems2Produce(10);

// Set the number of items to gather from each feed (default is all)
$mymerger->setNumberOfItems2Gather(2);

// Set a "nice" output (with tabulations and line breaks)
$mymerger->formatted = true;

// Adding feeds
// with the fluent interface
$mymerger->addRssFeeds('http://exemple.com/feed.rss')
				 ->addRssFeeds('http://exemple.com/feed2.rss')
// With one argument foreach new feed
$mymerger->addRssFeeds('http://exemple.com/feed3.rss',
											 'http://exemple.com/feed4.rss')
// Or with an array of feeds
$mymerger->addRssFeeds(array(
												'http://exemple.com/feed3.rss',
												'http://exemple.com/feed4.rss'
											));

// Get the resulting RSS feed in a string
$rssString = $mymerger->getMerged();
header('Content-Type: application/rss+xml; charset=UTF-8');
echo $rssString;
```

With file cache :

```php
<?php
require_once('rssMerger.php');
require_once('rssCacheInt.php');
require_once('rssFileCache.php');

$mymerger = new Taophp\rssMerger();

// create the cache from a directory path
try {
	$cache = new Taophp\rssFileCache('cache');
} catch (Exception $e) {
	error_log($e->getMessage());
}

// if $cache creation is successfull, associate it to the merger
if ($cache) $mymerger	->setCache($cache);

$mymerger	->addFeeds('http://exemple.com/feed5.rss,http://exemple.com/feed6.rss');

// Get the resulting RSS feed in a string
$rssString = $mymerger->getMerged();
```

Requirements
============
PHP >= 5.3

Changelog
=========
15-09-2014	(v2.2.0-beta) Removing the ability for rssFileCache to redirect to a file, which seems to have been a bad idea from Makis Tracend

15-09-2014	(v2.1.3-beta) Some tests and fixes. Should work well. Please submit an issue if any.

12-09-2014	(v2.0.1-Pre-alpha) Adding rssCacheInt interface and rssFileCache class, re-enabled the cache use in rssMerger in a new way, some renaming, and documentation, adding a feature to enable or disable the output formatting

11-09-2014	(v2.0.0-Pre-alpha) Move the code to my own pseudo "Coding Standard", inhibate the use of the cache

23-05-2009 	(v1.21) Removed extra information before the <rss> tag to better support feedburner

14-06-2008 	(v1.2) 	Re-written parsing function for PHP 5 using the SimpleXML extension. Items now sorted by date.

01-02-2008 	(v1.1) 	Bug fix: Absence of a description or title for the XML file could break structure of the final XML output.
					Improvement: Made the description of the final XML output a variable.

27-01-2008 	(v1.0) 	Initial release: Basic parsing & caching functionality created.


TODO
====

* be compliant with PSR-0
* use [Magpie RSS](https://packagist.org/packages/kellan/magpierss)

Copyright
=========
This work is released under the terms of the GNU General Public License:
http://www.gnu.org/licenses/gpl-2.0.txt
