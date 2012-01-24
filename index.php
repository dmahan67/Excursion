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

require_once 'core/header.php';

$xtpl = new XTemplate('themes/bootstrap/index.xtpl');

/* === Hook === */
foreach ($excursion->Hook('index.tags') as $pl)
{
<<<<<<< HEAD
	include $pl;
=======
 include $pl;
>>>>>>> 56c531a8a91f95df5879ee85180c9897a890b045
}
/* ===== */

$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';
 
?>