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
elseif($m == 'profile')
{

	$xtpl = new XTemplate('themes/'.$user['theme'].'/users.profile.xtpl');
	
	if($action == 'send')
	{
	
		$insert['theme'] = $_POST['themes'];
		$old_pass = $_POST['current_password'];
		$new_pass1 = $_POST['new_password1'];
		$new_pass2 = $_POST['new_password2'];
		
		$md5_pass = md5($old_pass);
		$pw = $db->query("SELECT password FROM members WHERE id='".$user['id']."' LIMIT 1")->fetchColumn();
		
		if (!empty($md5_pass) && $md5_pass != $pw) $error = $lang['profile_error_nomatch'].'<br />';
		if ($new_pass1 != $new_pass2) $error = $lang['profile_error_nosame'].'<br />';
		if (mb_strlen($new_pass1) < 4) $error .= $lang['reg_pwd_length'].'<br />';
		
		if(empty($error))
		{
		
			echo "worked";
		
		}
		else
		{
		
			$xtpl->assign(array(
				'ERRORS_TEXT' => $error
			));
			
			$xtpl->parse('MAIN.ERRORS');
		
		}
		
	
	}
	
	$handle = opendir('themes');
	while ($f = readdir($handle))
	{
		if (mb_strpos($f, '.') === FALSE && is_dir("themes/$f") && $f != "admin")
		{
			$themelist[] = $f;
		}
	}
	closedir($handle);
	sort($themelist);

	$values = array();
	$titles = array();
	
	$themes .= "<select class='xlarge' name='themes' id='themes'>";
	
	foreach ($themelist as $i => $x)
	{
	
		if($x == $user['theme'])
		{
		
			$selected = "selected='selected'";
			
		}
		else
		{
		
			$selected = "";
			
		}
	
		$themes .= "<option name='$x' value='$x' ".$selected.">$x</option>";
		
	}
	
	$themes .= "</select>";
	
	$xtpl->assign(array('THEMES' => $themes));

}
elseif(isset($action) && $action == 'recover')
{

	$xtpl = new XTemplate('themes/'.$user['theme'].'/users.recover.xtpl');
 
	if($m == 'lostpass')
	{
 
		if($step == 2)
		{
		
			$answer = $db->query("SELECT SQ_Answer FROM members WHERE email='" .$email. "'")->fetchColumn();
			
			if(strtoupper(trim($answer)) == strtoupper(trim($_POST['answer'])))
			{
			
				$member->lostPassword($email);
				header("Location: message.php?id=104");
				
			}
			
		}
		else
		{
		
			$email_exists = (bool)$db->query("SELECT id FROM members WHERE email='" .$email. "' LIMIT 1")->fetch();
			
			if($email_exists)
			{
			
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
	if(empty($m) || $m == 'validation')
	{
	
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