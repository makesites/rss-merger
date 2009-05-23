##############################################################

RSS Merger v1.0

Created by: Makis Tracend (makis@makesites.cc)
URL: http://www.makesites.cc/author/makis/

##############################################################


Description
===========

There are many RSS parsers out there and probably I could hack one to do my job but I like creating things from the ground up (even if it's done a thousand times before), just so that in the end I will have something that will accommodate my needs.


Instructions
============

Put in the RSS feeds you want to parse in the "rss_list.txt" (one URL per line) and the "rss_merger.php" script will output a new rss feed with a selection of items from all the RSS feeds. 

It works fine right out of the box but you may want to edit some of the global variables defined in the beginning of the script. Just open it in a text editor and fire away... 

$site_name 		: Replace this with your website's title
$site_url 		: Replace this with your website's URL
$rss_list 		: A text file with all your feed URLs listed, one per line
$xml_encoding 	: The encoding you want the final XML file to have
$num_of_items 	: The number of items it will collect from each feed, counted off the top of the RSS feed. 


Changelog
=========

27-01-2008 	Initial release. Basic parsing & caching functionality created. 


Copyright
=========

This work is released under the terms of the GNU General Public License:
http://www.gnu.org/licenses/gpl-2.0.txt
