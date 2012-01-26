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
$action = $_GET['action'];
$token = $_GET['token'];
$m = $_GET['m'];
$email = $_POST['email'];
$step = $_GET['step'];

if($action == 'validate')
{

	$member->validate($token);

}
if($action == 'remove')
{

	$group_id = $db->query("SELECT groupid FROM members WHERE token='$token' LIMIT 1")->fetchColumn();

	if($group_id == 1)
	{
		$member->remove($token);
	}
	else
	{
	
		header('Location: message.php');
		
	}

}

require_once 'core/header.php';

if(isset($id) && $id > 0)
{

	$xtpl = new XTemplate('themes/'.$user['theme'].'/users.details.xtpl');
	
	$sql = $db->query("SELECT * FROM members WHERE id = $id LIMIT 1");
	$row = $sql->fetch();
	
	$xtpl->assign(array(
		'ID' => $row['id'],
		'USERNAME' => $row['username'],
		'GROUP' => $excursion->generateGroup($row['groupid']),
		'EMAIL' => $row['email'],
		'REGDATE' => date($config['date_medium'], $row['regdate'])
	));
	
}
elseif(isset($action) && $action == 'recover')
{

	$xtpl = new XTemplate('themes/'.$user['theme'].'/users.recover.xtpl');
 

	if($m == 'lostpass')
	{
 
		if($step == 2) // proccess security question, and do function to update password
		{
			$answer = $db->query("SELECT SQ_Answer FROM members WHERE email='" .$email. "'")->fetchColumn();
			if(strtoupper(trim($answer)) == strtoupper(trim($_POST['answer']))){
				$member->lostPassword($email);
				header("Location: message.php?id=104");
			}
		}
		else
		{
			// display security question
			$email_exists = (bool)$db->query("SELECT id FROM members WHERE email='" .$email. "' LIMIT 1")->fetch();
			if($email_exists){
				$SQ_index = $db->query("SELECT SQ_Index FROM members WHERE email='" .$email. "'")->fetchColumn();
				$SQ = $db->query("SELECT question FROM security_questions WHERE id='" .$SQ_index. "'")->fetchColumn();
				
				$xtpl->assign(array(
					'SECURITY_QUESTION' => $SQ,
					'EMAIL' => $email)
				);
				$xtpl->parse('MAIN.SECURITY_QUESTION');
			}
			
		}
 
	}
	if($m == 'validation')
	{
 
		$member->sendValidationEmail($email);
 
	}
	 if(empty($m)){
		$xtpl->parse('MAIN.RECOVERY_OPTIONS');
	}
 
}
else
{

	$xtpl = new XTemplate('themes/'.$user['theme'].'/users.xtpl');

	$sql = $db->query("SELECT * FROM members ORDER BY id DESC LIMIT 10");
	while ($row = $sql->fetch())
	{

		$xtpl->assign(array(
			'ID' => $row['id'],
			'USERNAME' => $row['username'],
			'GROUP' => $excursion->generateGroup($row['groupid']),
			'EMAIL' => $row['email'],
			'REGDATE' => date($config['date_medium'], $row['regdate'])
		));
		$xtpl->parse('MAIN.USERS_LIST');	
		
	}
	
}
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';
 
?>