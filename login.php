<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
require_once 'config.php';
require_once 'core/xtemplate.class.php';
require_once 'core/common.php';

$un = $excursion->import('username', 'P', 'TXT');
$pwd = $excursion->import('password', 'P', 'TXT');

require_once 'core/header.php';

$xtpl = new XTemplate('themes/'.$user['theme'].'/login.xtpl');

if($action == 'send'){

	/* === Hook === */
	foreach ($excursion->Hook('login.send.action') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$member->Login($un, $pwd);
	
}

/* === Hook === */
foreach ($excursion->Hook('login.tags') as $pl)
{
	include $pl;
}
/* ===== */

$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>