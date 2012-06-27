<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
ob_start();

$xtpl = new XTemplate('themes/admin/header.xtpl');

if ($user['id'] <= 0 || $user['group'] < 4)
{

	header('Location: message.php?id=105');
	
}

$xtpl->parse('ADMIN_HEADER');
$xtpl->out('ADMIN_HEADER');
 
?>