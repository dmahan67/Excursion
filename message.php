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

$ex['location'] = 'message';

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
if($id == 105){
	$title = $lang['message_105_title'];
	$subtitle =  $lang['message_105_subtitle'];
	$message =  $lang['message_105_text'];
}
if($id == 106){
	$title = $lang['message_106_title'];
	$subtitle =  $lang['message_106_subtitle'];
	$message =  $lang['message_106_text'];
}
if($id == 107){
	$title = $lang['message_107_title'];
	$subtitle =  $lang['message_107_subtitle'];
	$message =  $lang['message_107_text'];
}
if($id == 108){
	$title = $lang['message_108_title'];
	$subtitle =  $lang['message_108_subtitle'];
	$message =  $lang['message_108_text'];
}
if($id == 109){
	$title = $lang['message_109_title'];
	$subtitle =  $lang['message_109_subtitle'];
	$message =  $lang['message_109_text'];
}

/* === Hook === */
foreach ($excursion->Hook('message.case') as $pl)
{
	include $pl;
}
/* ===== */

require_once 'core/header.php';

$xtpl = new XTemplate('themes/'.$user['theme'].'/message.xtpl');

$xtpl->assign(array(
	'MESSAGE_TITLE' => $title,
	'MESSAGE_SUBTITLE' => $subtitle,
	'MESSAGE_TEXT' => $message,
));

/* === Hook === */
foreach ($excursion->Hook('message.tags') as $pl)
{
	include $pl;
}
/* ===== */

$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>