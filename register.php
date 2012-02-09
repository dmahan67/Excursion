<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
require_once 'config.php';
require_once 'core/xtemplate.php';
require_once 'core/common.php';

$un = $excursion->import('username', 'P', 'TXT');
$pwd = $excursion->import('password', 'P', 'TXT');
$pwd2 = $excursion->import('password2', 'P', 'TXT');
$email = $excursion->import('email', 'P', 'TXT');
$sq = $excursion->import('sq', 'P', 'INT');
$sq_answer = $excursion->import('sq_answer', 'P', 'TXT');

require_once 'core/header.php';

$xtpl = new XTemplate('themes/'.$user['theme'].'/register.xtpl');

if($action == 'send'){

	/* === Hook === */
	foreach ($excursion->Hook('register.send.action') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$member->Register($un, $pwd, $pwd2, $email, $sq, $sq_answer);
	
}

$questions .= "<select class='xlarge' name='sq' id='sq'>";
$sql = $db->query("SELECT * FROM security_questions ORDER BY id ASC");
while ($row = $sql->fetch())
{

	$questions .= "<option name='".$row['id']."' value='".$row['id']."'>".$row['question']."</option>";
	
}
$questions .= "</select>";

$xtpl->assign(array(
	'FORM_ACTION' => $excursion->url('register', 'action=send'),
	'FORM_USERNAME' => $excursion->inputbox('text', 'username', $insert['username'], array('size' => 24, 'maxlength' => 100)),
	'FORM_PASSWORD' => $excursion->inputbox('password', 'password', '', array('size' => 8, 'maxlength' => 32)),
	'FORM_REPEAT_PASSWORD' => $excursion->inputbox('password', 'password2', '', array('size' => 8, 'maxlength' => 32)),
	'FORM_EMAIL' => $excursion->inputbox('text', 'email', $insert['email'], array('size' => 24, 'maxlength' => 64)),
	'QUESTIONS' => $questions,
	'FORM_SQ_ANSWER' => $excursion->inputbox('text', 'sq_answer', $insert['sq_answer'], array('size' => 24, 'maxlength' => 64))
));

/* === Hook === */
foreach ($excursion->Hook('register.tags') as $pl)
{
	include $pl;
}
/* ===== */

$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';
 
?>