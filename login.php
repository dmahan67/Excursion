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

$action = $_GET['m'];
$un = $_POST['username'];
$pwd = $_POST['password'];

require_once 'core/header.php';

$xtpl = new XTemplate('themes/bootstrap/login.xtpl');

if($action == 'send'){

	$member->Login($un, $pwd);
	
}

$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>