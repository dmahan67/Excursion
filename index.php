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

$ex['location'] = 'index';

require_once 'core/header.php';

$xtpl = new XTemplate('themes/'.$user['theme'].'/index.xtpl');

/* === Hook === */
foreach ($excursion->Hook('index.tags') as $pl)
{
	include $pl;
}
/* ===== */

$excursion->display_messages($xtpl);

$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';
 
?>