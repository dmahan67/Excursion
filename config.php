<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
error_reporting (E_ALL ^ E_NOTICE);

/* ========== Main Settings ========== */

$config['main_url'] = 'localhost';									// Full URL without ending in a slash
$config['title'] = 'Excursion';										// Title of your website
$config['admin_email'] = 'donotreply@excursion-powered.com';		// Administration email

/* ========== Time / Date ========== */

$config['date_short'] = 'm/d/y';									// 01/19/10
$config['date_medium'] = 'F j, Y';									// January 19, 2010
$config['date_long'] = 'F j, Y, g:i a';								// January 19, 2010 at 5:30 pm

/* ========== Database Settings ========== */

$config['mysqlhost'] = 'localhost';
$config['mysqlport'] = '';	
$config['mysqluser'] = 'root';	
$config['mysqlpassword'] = '';
$config['mysqldb'] = 'excursion';

$config['mysqlcharset'] = 'utf8';
$config['mysqlcollate'] = 'utf8_unicode_ci';
 
?>