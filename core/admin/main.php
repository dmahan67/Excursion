<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
require_once 'core/admin/header.php';
 
switch ($m)
	{
	case 'plugins':
	require('core/admin/plugins.php');
	break;
	
	case 'config':
	require('core/admin/config.php');
	break;
	
	case 'pages':
	require('core/admin/pages.php');
	break;
	
	case 'members':
	require('core/admin/members.php');
	break;

	default:
	require('core/admin/home.php');
	break;
}

require_once 'core/admin/footer.php';

?>