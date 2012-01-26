<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
ob_start();

$xtpl = new XTemplate('themes/'.$user['theme'].'/header.xtpl');

if ($user['id'] > 0)
{

	$xtpl->parse('HEADER.USER');
	
}
else
{

	$xtpl->parse('HEADER.GUEST');
	
}

$xtpl->parse('HEADER');
$xtpl->out('HEADER');
 
?>