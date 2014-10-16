########################################################################

RSS Merger 2.4.0-beta Atoms welcome

Forked by Stéphane Mourey (stephane.mourey@impossible-exil.info)
URL: http://impossible-exil.info
Created by: Makis Tracend (makis@makesites.cc)
URL: http://www.makesites.cc/projects/rss_merger

########################################################################

Description
===========
This script will load a number of RSS or Atom feeds and compile them into one RSS 2.0 file. You can use it to present a mash-up of your news from different places (blogs, twitter etc.) or just present news from other sources around the Net.
This script is able to load all feeds asyncrhoniously if the [CURL extension](http://php.net/manual/ref.curl.php) is loaded, which provides a *significative* speed improvement.
But you should *really* use it with a cache of some sort. `rssCacheInt` is a interface that will helps you to write your own, if you need, but RSS Merger come with the `rssFileCache` class, which enable the use of a cache directory on the file system.

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

// Adding feeds
// with the fluent interface
$mymerger->addFeeds('http://exemple.com/feed.rss')
				 ->addFeeds('http://exemple.com/feed2.atom')
// With one argument foreach new feed
$mymerger->addFeeds('http://exemple.com/feed3.rss',
											 'http://exemple.com/feed4.rss')
// Or with an array of feeds
$mymerger->addFeeds(array(
												'http://exemple.com/feed3.atom',
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
	$cache = new Taophp\rssFileCache('/path/to/cache/directory');
} catch (Exception $e) {
	error_log($e->getMessage());
}

// if $cache creation is successfull, associate it to the merger
if ($cache) $mymerger	->setCache($cache);

$mymerger	->addFeeds('http://exemple.com/feed5.rss,http://exemple.com/feed6.rss');

// Get the resulting RSS feed in a string
$rssString = $mymerger->getMerged();
```

Options and Important Options
=============================
## **Asynchronious**
By default, we use asynchronious loading of feeds. If you experience troubles like overloaded CPU, try to turn it off this way :

	$mymerger->asynchronious = false;

## **Loading timeout**
To prevent endless loading of slow feeds, we use the cURL timeout option set to 10 seconds. If you experience unloaded feed, you can try a longer one this way :
	$mymerger->curlTimeOut = 15;	//example for 15 seconds

## **Cache**
By default, we do not use any cache, but we *strongly* recommand to use one for *many reasons*. First, make all your settings with none and then, when things work, refer the Usage section to enable one. After that, never disable it except for debuging purposes.

## Formatting
By default, we do not use line breaks or tabulations to format the output feed. If you want to, you can this way :

	$mymerger->formatted = true;

## Language
You can set the language of your feed (default is english) this way:

	$mymerger->lang = 'fr';	//example for french

## Encoding
By default, the output feed is encoded using UTF-8. You can change it this way:

	$mymerger->xmlEncoding('UTF-16');	//example for UTF-16

Dependencies
============
##Required:

* PHP >= 5.3

##Recommanded:
* [mb_string](http://php.net/manual/book.mbstring.php)
* [cURL](http://php.net/manual/book.curl.php)

Changelog
=========
16-10-2014	(v2.4.0-beta) Now accepting Atom feeds

19-09-2014	(v2.3.2-beta) Make the script not to overload the CPU, neither to wait endlessly for a feed, adding the CDATA tag to all data, better solution that XML_Util....

19-09-2014	(v2.3.1-beta) Make the script to provide valid feed in anycase (hope so!)

18-09-2014	(v2.3.0-beta) Adding the ability to download all the feeds asynchroniously.

15-09-2014	(v2.2.0-beta) Removing the ability for rssFileCache to redirect to a file, which seems to have been a *bad* idea from Makis Tracend, because the client should use the url of the cached file to retrieve later news, but it may be overdated or even dead.

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

* be compliant with PSR-4

Copyright
=========
This work is released under the terms of the GNU General Public License:
http://www.gnu.org/licenses/gpl-2.0.txt
