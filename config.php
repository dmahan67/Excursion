<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
error_reporting (E_ALL ^ E_NOTICE);

/* ========== Main Settings ========== */

$config['main_url'] = 'http://localhost';								// Full URL without ending in a slash
$config['default_theme'] = 'bootstrap';									// Default theme unless set by user
$config['default_language'] = 'en';										// Default language

$config['new_install'] = 1;

/* ========== Time / Date ========== */

date_default_timezone_set('America/New_York');							// Default time zone, check www.php.net/manual/en/timezones.php for a list
$config['date_short'] = 'm/d/y';										// 01/19/10
$config['date_medium'] = 'F j, Y';										// January 19, 2010
$config['date_long'] = 'F j, Y, g:i a';									// January 19, 2010 at 5:30 pm
$sys['day'] = @date('Y-m-d');
$sys['now'] = time();
$sys['now_offset'] = $sys['now'];

/* ========== Database Settings ========== */

$config['mysqlhost'] = 'localhost';
$config['mysqlport'] = '';	
$config['mysqluser'] = 'root';	
$config['mysqlpassword'] = '';
$config['mysqldb'] = 'excursion';

$config['mysqlcharset'] = 'utf8';
$config['mysqlcollate'] = 'utf8_unicode_ci';

/* ========== Images ========== */

$config['file_perms'] = 0664;
 
?>