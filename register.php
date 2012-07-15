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

if($config['disablereg']=='yes')
{

	header('Location: message.php?id=107');

}

$insert['username'] = $excursion->import('username', 'P', 'TXT');
$pwd = $excursion->import('password', 'P', 'TXT');
$pwd2 = $excursion->import('password2', 'P', 'TXT');
$insert['email'] = $excursion->import('email', 'P', 'TXT');
$insert['SQ_Index'] = $excursion->import('sq', 'P', 'INT');
$insert['SQ_Answer'] = $excursion->import('sq_answer', 'P', 'TXT');

$ex['location'] = 'register';

require_once 'core/header.php';

$xtpl = new XTemplate('themes/'.$user['theme'].'/register.xtpl');

if($action == 'send')
{

	/* === Hook === */
	foreach ($excursion->Hook('register.send.action') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$member->Register($insert['username'], $pwd, $pwd2, $insert['email'], $insert['SQ_Index'], $insert['SQ_Answer']);
	
}

$xtpl->assign(array(
	'FORM_ACTION' => $excursion->url('register', 'action=send'),
	'FORM_USERNAME' => $excursion->inputbox('text', 'username', $insert['username'], array('size' => 24, 'maxlength' => 100)),
	'FORM_PASSWORD' => $excursion->inputbox('password', 'password', '', array('size' => 8, 'maxlength' => 32)),
	'FORM_REPEAT_PASSWORD' => $excursion->inputbox('password', 'password2', '', array('size' => 8, 'maxlength' => 32)),
	'FORM_EMAIL' => $excursion->inputbox('text', 'email', $insert['email'], array('size' => 24, 'maxlength' => 64)),
	'QUESTIONS' => $excursion->selectbox_security_questions($insert['SQ_Index'], 'sq'),
	'FORM_SQ_ANSWER' => $excursion->inputbox('text', 'sq_answer', $insert['SQ_Answer'], array('size' => 24, 'maxlength' => 64))
));

/* === Hook === */
foreach ($excursion->Hook('register.tags') as $pl)
{
	include $pl;
}
/* ===== */

$excursion->display_messages($xtpl);

$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';
 
?>