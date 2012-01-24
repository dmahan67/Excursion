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

$id = (int)$_GET['id'];

if(empty($id) || $id == 0){
	$title = $lang['message_blank_title'];
	$subtitle = $lang['message_blank_subtitle'];
	$message = $lang['message_blank_text'];
}
if($id == 101){
	$title = $lang['message_101_title'];
	$subtitle = $lang['message_101_subtitle'];
	$message = $lang['message_101_text'];
}
if($id == 102){
	$title = $lang['message_102_title'];
	$subtitle =  $lang['message_102_subtitle'];
	$message =  $lang['message_102_text'];
}
if($id == 103){
	$title = $lang['message_103_title'];
	$subtitle =  $lang['message_103_subtitle'];
	$message =  $lang['message_103_text'];
}
if($id == 104){
	$title = $lang['message_104_title'];
	$subtitle =  $lang['message_104_subtitle'];
	$message =  $lang['message_104_text'];
}

require_once 'core/header.php';

$xtpl = new XTemplate('themes/bootstrap/message.xtpl');

$xtpl->assign(array(
	'MESSAGE_TITLE' => $title,
	'MESSAGE_SUBTITLE' => $subtitle,
	'MESSAGE_TEXT' => $message,
));

$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>