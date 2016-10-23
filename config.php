<?php
/**
 * @package       Watchful.li siteoverview with Excel export
 * @author        Rene Kreijveld
 * @authorUrl https://about.me/renekreijveld
 * @copyright (c) 2016, Rene Kreijveld
 */

// Config
define('API_KEY', 'add-your-watchful.li-api-key-here');
define('BASE_URL', 'https://app.watchful.li/api/v1');

// Show only published websites? Then set SHOW_ONLY_PUBLISHED to true.
// Show all sites? Then set SHOW_ONLY_PUBLISHED to false.
define('SHOW_ONLY_PUBLISHED', true);
define('SHOW_DEMO_DATA', false);

// Demo data with fake URLs
$demoData = array(
	(object) array("siteid" => "00001", "name" => "CNN", "access_url" => "http://edition.cnn.com/", "admin_url" => "http://edition.cnn.com/", "up" => 2, "j_version" => "3.6.2", "nbUpdates" => 0, "ip" => "151.101.36.73", "server_version" => "Apache/2", "php_version" => "5.6.25", "mysql_version" => "5.5.5-10.0.26-MariaDB"),
	(object) array("siteid" => "00002", "name" => "Apple", "access_url" => "http://www.apple.com/", "admin_url" => "http://www.apple.com/", "up" => 2, "j_version" => "3.6.2", "nbUpdates" => 0, "ip" => "23.62.140.52", "server_version" => "Apache/2", "php_version" => "7.1", "mysql_version" => "5.6"),
	(object) array("siteid" => "00003", "name" => "NOS", "access_url" => "http://nos.nl/", "admin_url" => "http://nos.nl/", "up" => 2, "j_version" => "3.6.2", "nbUpdates" => 2, "ip" => "145.58.29.114", "server_version" => "Apache/2", "php_version" => "7.0.10", "mysql_version" => "5.5.5-10.0.26-MariaDB"),
	(object) array("siteid" => "00004", "name" => "Google", "access_url" => "https://www.google.com/", "admin_url" => "https://www.google.com/", "up" => 2, "j_version" => "3.5.1", "nbUpdates" => 0, "ip" => "216.58.210.36", "server_version" => "Google/NginX", "php_version" => "7.0.10", "mysql_version" => "5.5.5-10.0.26-MariaDB"),
	(object) array("siteid" => "00005", "name" => "Microsoft", "access_url" => "https://www.microsoft.com/", "admin_url" => "https://www.microsoft.com/", "up" => 2, "j_version" => "3.6.2", "nbUpdates" => 0, "ip" => "88.221.184.151", "server_version" => "Microsoft-IIS/7.5", "php_version" => "5.6.25", "mysql_version" => "5.6"),
	(object) array("siteid" => "00006", "name" => "Joomla", "access_url" => "https://www.joomla.org/", "admin_url" => "https://www.joomla.org/", "up" => 2, "j_version" => "4.0-beta", "nbUpdates" => 0, "ip" => "72.29.124.146", "server_version" => "Apache/2", "php_version" => "7.0.10", "mysql_version" => "6.0"),
	(object) array("siteid" => "00007", "name" => "GitHub", "access_url" => "https://github.com/", "admin_url" => "https://github.com/", "up" => 2, "j_version" => "3.6.2", "nbUpdates" => 1, "ip" => "192.30.253.112", "server_version" => "Apache/2", "php_version" => "7.0.10", "mysql_version" => "5.5.5-10.0.26-MariaDB"),
	(object) array("siteid" => "00008", "name" => "WordPress", "access_url" => "https://wordpress.org/", "admin_url" => "https://wordpress.org/", "up" => 2, "j_version" => "1.5.15", "nbUpdates" => 12, "ip" => "66.155.40.250", "server_version" => "Apache/2", "php_version" => "5.3.29", "mysql_version" => "5.0"),
);