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
$pwd2 = $_POST['password2'];
$email = $_POST['email'];

require_once 'core/header.php';

$xtpl = new XTemplate('themes/bootstrap/register.xtpl');

if($action == 'send'){

	$member->Register($un, $pwd, $pwd2, $email);
	
}

$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';
 
?>