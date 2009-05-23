##############################################################

RSS Merger v1.2

Created by: Makis Tracend (makis@makesites.cc)
URL: http://www.makesites.cc/projects/rss_merger

##############################################################


Description
===========
This script will load a number of RSS feeds and compile them into one RSS 2.0 file. You can use it to present a mash-up of your news from different places (blogs, twitter etc.) or just present news from other sources around the Net.


Instructions
============
Put in the RSS feeds you want to parse in the "rss_list.txt" (one URL per line) and the "rss_merger.php" script will output a new rss feed with a selection of items from all the RSS feeds. 

It works fine right out of the box but you may want to edit some of the global variables defined in the beginning of the script. Just open it in a text editor and fire away... 

$site_name 		: Replace this with your website's title
$site_url 		: Replace this with your website's URL
$site_info 		: A small description of your site or this feed's purpose
$rss_list 		: A text file with all your feed URLs listed, one per line
$cached_rss 	: The cache RSS file that will be created to improve performance
$xml_encoding 	: The encoding you want the final XML file to have
$num_of_items 	: The number of items it will collect from each feed, counted off the top of the RSS feed. 


Changelog
=========
14-06-2008 	(v1.2) 	Re-written parsing function for PHP 5 using the SimpleXML extension. Items now sorted by date.

01-02-2008 	(v1.1) 	Bug fix: Absence of a description or title for the XML file could break structure of the final XML output.
					Improvement: Made the description of the final XML output a variable.

27-01-2008 	(v1.0) 	Initial release: Basic parsing & caching functionality created. 


Copyright
=========
This work is released under the terms of the GNU General Public License:
http://www.gnu.org/licenses/gpl-2.0.txt
