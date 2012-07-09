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
require_once 'core/common.php';

$ex['location'] = 'logout';

/* === Hook === */
foreach ($excursion->Hook('logout') as $pl)
{
	include $pl;
}
/* ===== */

$member->Logout();

?>