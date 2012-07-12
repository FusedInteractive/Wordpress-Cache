Simple Wordpress Cache
=============

If you have parts of your Wordpress theme that run slow it may work to cache them. Automatically creates refresh records in a database table so 
you can create caches dynamically.

Created by Fused Interactive

[Our Website](http://fusedinteractive.com/) | [Follow us on Twitter](http://twitter.com/fusedinc) | [Like us on Facebook](http://www.facebook.com/fusedinteractive)

Installation
-----------

Run the following SQL query on your Wordpress database:

    CREATE TABLE `cache` (
    	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    	  `name` varchar(100) NOT NULL DEFAULT '',
    	  `lastrun` varchar(100) DEFAULT NULL,
    	  `runevery_min` int(11) NOT NULL DEFAULT '30',
    	  `runevery_hour` int(11) NOT NULL DEFAULT '0',
    	  `runevery_day` int(11) NOT NULL DEFAULT '0',
    	  PRIMARY KEY (`id`)
    );

Upload the `cache.php` file to either your theme folder or a sub-directory in your theme folder.

Make sure to CHMOD the `files` directory to 777.

Usage
-----------

Make sure you include the `cache.php` file and create the class. We recommend doing this in `functions.php`.

    require_once 'cache.php';
    $cache = new Simple_WP_Cache();

You can either add your caches to the database manually so you can specify a refresh period (minutes, hours, or days) or the class will do it automatically and set them to refresh every 30 minutes. (Note: If you want them to for sure refresh every 30 minutes you'll need to setup a cron job. No documentation from us for that, so good luck.)

Example
-----------

    if(!$mydata = $cache->load('mydata')) {
    		$mydata = array('a', 'b', 'c');
    		$cache->build('mydata', $mydata);
    }
