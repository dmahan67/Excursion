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

	$xtpl = new XTemplate('themes/bootstrap/users.details.xtpl');
	
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

	if($m == 'lostpass')
	{
	
		$member->lostPassword($token);
	
	}
	if($m == 'validation')
	{
	
		$member->sendValidationEmail($email);
	
	}

	$xtpl = new XTemplate('themes/bootstrap/users.recover.xtpl');
	
}
else
{

	$xtpl = new XTemplate('themes/bootstrap/users.xtpl');

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