<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
require_once 'config.php';
require_once 'core/classes.php';
require_once 'core/xtemplate.php';
$ex['location'] = 'login';
require_once 'core/common.php';

$un = $excursion->import('username', 'P', 'TXT');
$pwd = $excursion->import('password', 'P', 'TXT');

require_once 'core/header.php';

$xtpl = new XTemplate('themes/'.$user['theme'].'/login.xtpl');

if($action == 'send')
{

	/* === Hook === */
	foreach ($excursion->Hook('login.send.action') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$member->Login($un, $pwd);
	
}

$xtpl->assign(array(
	'FORM_ACTION' => $excursion->url('login', 'action=send'),
	'FORM_USERNAME' => $excursion->inputbox('text', 'username', $insert['username'], array('size' => 24, 'maxlength' => 100)),
	'FORM_PASSWORD' => $excursion->inputbox('password', 'password', '', array('size' => 8, 'maxlength' => 32))
));

/* === Hook === */
foreach ($excursion->Hook('login.tags') as $pl)
{
	include $pl;
}
/* ===== */

if($config['maintenance']=='yes')
{

	$xtpl->parse('MAIN.MAINTENANCE');

}

$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>