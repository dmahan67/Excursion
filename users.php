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

$ex['location'] = 'users';

$token = $excursion->import('token','G','TXT');
$email = $excursion->import('email','P','TXT',64, TRUE);

if($action == 'validate')
{

	/* === Hook === */
	foreach ($excursion->Hook('user.validate.action') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$member->validate($token);

}
if($action == 'remove')
{

	/* === Hook === */
	foreach ($excursion->Hook('user.remove.action.first') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$group_id = $db->query("SELECT groupid FROM members WHERE token='$token' LIMIT 1")->fetchColumn();

	if($group_id == 1)
	{
	
		/* === Hook === */
		foreach ($excursion->Hook('user.remove.action.loop') as $pl)
		{
			include $pl;
		}
		/* ===== */
	
		$member->remove($token);
	}
	else
	{
	
		header('Location: message.php');
		
	}

}

require_once 'core/header.php';

if(isset($id) && $id > 0 && empty($m))
{

	$xtpl = new XTemplate('themes/'.$user['theme'].'/users.details.xtpl');
	
	$sql = $db->query("SELECT * FROM members WHERE id = $id LIMIT 1");
	$row = $sql->fetch();
	
	$xtpl->assign(array(
		'ID' => $row['id'],
		'USERNAME' => $row['username'],
		'GROUP' => $excursion->generateGroup($row['groupid']),
		'EMAIL' => $row['email'],
		'AVATAR' => $row['avatar'],
		'REGDATE' => date($config['date_medium'], $row['regdate']),
		'BIRTHDATE' => date($config['date_medium'], $excursion->datetostamp($row['birthdate'])),
		'GENDER' => $lang['gender_' . $row['gender']]
	));
	
	/* === Hook === */
	foreach ($excursion->Hook('user.details.tags') as $pl)
	{
		include $pl;
	}
	/* ===== */
	
}
elseif($m == 'profile')
{

	$xtpl = new XTemplate('themes/'.$user['theme'].'/users.profile.xtpl');
	
	if($action == 'send')
	{
	
		$insert['theme'] = $excursion->import('themes','P','TXT');
		$insert['gender'] = $excursion->import('gender','P','TXT');
		$insert['birthdate'] = (int) $excursion->import_date('birthdate', false);
		$old_pass = $excursion->import('curr_password','P','TXT');
		$new_pass1 = $excursion->import('new_password1','P','TXT',16);
		$new_pass2 = $excursion->import('new_password2','P','TXT',16);
			
		$insert['birthdate'] = ($insert['birthdate'] > $sys['now_offset']) ? ($sys['now_offset'] - 31536000) : $insert['birthdate'];
		$insert['birthdate'] = ($insert['birthdate'] == '0') ? '0000-00-00' : $excursion->stamptodate($insert['birthdate']);
		
		if($_FILES['avatar'])
		{
		
			$file = $_FILES['avatar'];
		
			$gd_supported = array('jpg', 'jpeg', 'png', 'gif');
			$file_ext = strtolower(end(explode(".", $file['name'])));
			$fcheck = $excursion->file_check($file['tmp_name'], $file['name'], $file_ext);
			if(in_array($file_ext, $gd_supported) && $fcheck == 1)
			{
			    $file['name']= $excursion->safename($file['name'], true);
				$filename_full = $user['id'].'-'.strtolower($file['name']);
				$filepath = 'assets/avatars/'.$filename_full;

				if(file_exists($filepath))
				{
					unlink($filepath);
				}

				move_uploaded_file($file['tmp_name'], $filepath);
				$excursion->imageresize($filepath, $filepath, 100, 100, 'fit', '', 100);
				@chmod($filepath, $config['file_perms']);
				$sql = $db->update('members', array("avatar" => $filepath), "id='".$user['id']."'");
			}
			
		}
		
		if(!empty($old_pass))
		{
		
			if ($new_pass1 != $new_pass2) $error = $lang['profile_error_nosame'].'<br />';
			if (mb_strlen($new_pass1) < 4) $error .= $lang['reg_pwd_length'].'<br />';
			if (md5($old_pass) != $user['password']) $error = $lang['profile_error_nomatch'].'<br />';
			
		}
		
		if(empty($error))
		{
		
			if(!empty($old_pass) && !empty($new_pass1) && !empty($new_pass2))
			{
			
				$db->update('members', array('password' => md5($new_pass1)), "id='".$user['id']."'");
			
			}
		
			$db->update('members', array(
				'theme' => $insert['theme'],
				'gender' => $insert['gender'],
				'birthdate' => $insert['birthdate'],
				'avatar' => $insert['avatar']
				
			), "id='".$user['id']."'");
			
			header('Location: users.php?id='.$user['id']);
		
		}
		else
		{
		
			$xtpl->assign(array(
				'ERRORS_TEXT' => $error
			));
			
			$xtpl->parse('MAIN.ERRORS');
		
		}
	
	}	
	
	$xtpl->assign(array(
		'FORM_ACTION' => $excursion->url('users', 'm=profile&action=send'),
		'FORM_THEMES' => $excursion->selectbox_theme($user['theme'], 'themes'),
		'FORM_GENDER' => $excursion->selectbox_gender($user['gender'] ,'gender'),
		'FORM_PASSWORD' => $excursion->inputbox('password', 'curr_password', '', array('size' => 12, 'maxlength' => 32)),
		'FORM_NEWPASSWORD' => $excursion->inputbox('password', 'new_password1', '', array('size' => 12, 'maxlength' => 32)),
		'FORM_REPEAT_NEWPASSWORD' => $excursion->inputbox('password', 'new_password2', '', array('size' => 12, 'maxlength' => 32)),
		'FORM_AVATAR' => $excursion->inputbox('file', 'avatar', '', array('size' => 24)),
		'FORM_BIRTHDATE' => $excursion->selectbox_date($excursion->datetostamp($user['birthdate']), 'short', 'birthdate', $excursion->date('Y', $sys['now_offset']), $excursion->date('Y', $sys['now_offset']) - 77, false),
	));

}
elseif($m == 'edit' && $user['group'] == '4')
{

	$xtpl = new XTemplate('themes/'.$user['theme'].'/users.edit.xtpl');
	
	$sql = $db->query("SELECT * FROM members WHERE id = ".$id);
	$row = $sql->fetch();
	
	if($action == 'send')
	{
	
		$insert['theme'] = $excursion->import('themes','P','TXT');
		$insert['username'] = $excursion->import('username','P','TXT');
		$insert['gender'] = $excursion->import('gender','P','TXT');
		$insert['groupid'] = $excursion->import('groupid','P','INT');
		$insert['email'] = $excursion->import('email','P','TXT');
		$insert['birthdate'] = (int) $excursion->import_date('birthdate', false);
		$new_pass1 = $excursion->import('new_password1','P','TXT',16);
		$new_pass2 = $excursion->import('new_password2','P','TXT',16);
			
		$insert['birthdate'] = ($insert['birthdate'] > $sys['now_offset']) ? ($sys['now_offset'] - 31536000) : $insert['birthdate'];
		$insert['birthdate'] = ($insert['birthdate'] == '0') ? '0000-00-00' : $excursion->stamptodate($insert['birthdate']);
		
		if($_FILES['avatar'])
		{
		
			$file = $_FILES['avatar'];
		
			$gd_supported = array('jpg', 'jpeg', 'png', 'gif');
			$file_ext = strtolower(end(explode(".", $file['name'])));
			$fcheck = $excursion->file_check($file['tmp_name'], $file['name'], $file_ext);
			if(in_array($file_ext, $gd_supported) && $fcheck == 1)
			{
			    $file['name']= $excursion->safename($file['name'], true);
				$filename_full = $row['id'].'-'.strtolower($file['name']);
				$filepath = 'assets/avatars/'.$filename_full;

				if(file_exists($filepath))
				{
					unlink($filepath);
				}

				move_uploaded_file($file['tmp_name'], $filepath);
				$excursion->imageresize($filepath, $filepath, 100, 100, 'fit', '', 100);
				@chmod($filepath, $config['file_perms']);
				$sql = $db->update('members', array("avatar" => $filepath), "id='".$row['id']."'");
			}
			
		}
		
		if(!empty($new_pass1) || !empty($new_pass2))
		{
		
			if ($new_pass1 != $new_pass2) $error = $lang['profile_error_nosame'].'<br />';
			if (mb_strlen($new_pass1) < 4) $error .= $lang['reg_pwd_length'].'<br />';
			
		}
		
		if(empty($error))
		{
		
			if(!empty($new_pass1) && !empty($new_pass2))
			{
			
				$db->update('members', array('password' => md5($new_pass1)), "id='".$row['id']."'");
			
			}
		
			$db->update('members', array(
				'theme' => $insert['theme'],
				'gender' => $insert['gender'],
				'birthdate' => $insert['birthdate'],
				'avatar' => $insert['avatar'],
				'email' => $insert['email'],
				'username' => $insert['username'],
				'groupid' => $insert['groupid']
			), "id='".$row['id']."'");
			
			header('Location: users.php?id='.$row['id']);
		
		}
		else
		{
		
			$xtpl->assign(array(
				'ERRORS_TEXT' => $error
			));
			
			$xtpl->parse('MAIN.ERRORS');
		
		}
	
	}	
	
	$xtpl->assign(array(
		'FORM_ACTION' => $excursion->url('users', 'm=edit&id='.$row['id'].'&action=send'),
		'FORM_THEMES' => $excursion->selectbox_theme($row['theme'], 'themes'),
		'FORM_USERNAME' => $excursion->inputbox('text', 'username', $row['username'], array('size' => 24, 'maxlength' => 64)),
		'FORM_GENDER' => $excursion->selectbox_gender($row['gender'] ,'gender'),
		'FORM_GROUP' => $excursion->selectbox_groups($row['groupid'],'groupid'),
		'FORM_EMAIL' => $excursion->inputbox('text', 'email', $row['email'], array('size' => 24, 'maxlength' => 64)),
		'FORM_NEWPASSWORD' => $excursion->inputbox('password', 'new_password1', '', array('size' => 12, 'maxlength' => 32)),
		'FORM_REPEAT_NEWPASSWORD' => $excursion->inputbox('password', 'new_password2', '', array('size' => 12, 'maxlength' => 32)),
		'FORM_AVATAR' => $excursion->inputbox('file', 'avatar', '', array('size' => 24)),
		'FORM_BIRTHDATE' => $excursion->selectbox_date($excursion->datetostamp($row['birthdate']), 'short', 'birthdate', $excursion->date('Y', $sys['now_offset']), $excursion->date('Y', $sys['now_offset']) - 77, false),
	));

}
elseif(isset($action) && $action == 'recover')
{

	$xtpl = new XTemplate('themes/'.$user['theme'].'/users.recover.xtpl');
 
	if($m == 'lostpass')
	{
 
		if($step == 2)
		{
		
			$answer = $db->query("SELECT SQ_Answer FROM members WHERE email='" .$email. "'")->fetchColumn();
			
			if(strtoupper(trim($answer)) == strtoupper(trim($excursion->import('answer','P','TXT'))))
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

	$sql = $db->query("SELECT * FROM members ORDER BY id ASC LIMIT 25");
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