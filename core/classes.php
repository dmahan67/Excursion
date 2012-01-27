<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
$import_filters = array();
 
class Members {

	/**
	 * Members Information
	 *
	 * @ GROUP 1 - Inactive
	 * @ GROUP 2 - Banned
	 * @ GROUP 3 - Member
	 * @ GROUP 4 - Administrator
	 */

	function Register($un, $pwd, $pwd2, $email, $sq, $sq_answer){
	
		global $db, $xtpl, $lang, $excursion, $member;
		
		$user_exists = (bool)$db->query("SELECT id FROM members WHERE username = ? LIMIT 1", array($un))->fetch();
		$email_exists = (bool)$db->query("SELECT id FROM members WHERE email = ? LIMIT 1", array($email))->fetch();
		
		if ($user_exists) $error .= $lang['reg_un_exists'].'<br />';
		if (preg_match('/&#\d+;/', $u) || preg_match('/[<>#\'"\/]/', $un)) $error .= $lang['reg_un_format'].'<br />';
		if (mb_strlen($un) < 2) $error .= $lang['reg_un_length'].'<br />';
		if (mb_strlen($pwd) < 4) $error .= $lang['reg_pwd_length'].'<br />';
		if ($pwd != $pwd2) $error .= $lang['reg_pwd_nomatch'].'<br />';
		if (mb_strlen($email) < 10) $error .= $lang['reg_email_length'].'<br />';		
		if ($email_exists) $error .= $lang['reg_email_exists'].'<br />';
		if (!filter_var($email, FILTER_VALIDATE_EMAIL )) $error .= $lang['reg_email_format'].'<br />';
		if (mb_strlen($sq_answer) < 2) $error .= $lang['reg_sq_length'].'<br />';
		
		if(empty($error))
		{
			$pwd = md5($pwd);
			
			$insert['username'] = $un;
			$insert['password'] = $pwd;
			$insert['email'] = $email;
			$insert['regdate'] = (int)time();
			$insert['token'] = $excursion->generateToken(16);
			$insert['SQ_Index'] = $sq;
			$insert['SQ_Answer'] = $sq_answer;

			
			$db->insert('members', $insert);
			
			$member->sendValidationEmail($insert['email']);
			
			header('Location: message.php?id=101');
			
		}
		else
		{
		
			$xtpl->assign(array(
				'ERRORS_TEXT' => $error
			));
			
			$xtpl->parse('MAIN.ERRORS');
			
		}
		
	}
	
	function Login($un, $pwd){
		
		global $db, $xtpl, $lang;
		
		$md5pwd = md5($pwd);
		$check_credentials = (bool)$db->query("SELECT id FROM members WHERE username = ? AND password = ? LIMIT 1", array($un, $md5pwd))->fetch();
		
		if (empty($un)) $error .= $lang['login_un_empty'].'<br />';
		if (empty($pwd)) $error .= $lang['login_pwd_empty'].'<br />';
		if (!$check_credentials) $error .= $lang['login_invalid'].'<br />';
		
		$u_id = $db->query("SELECT id FROM members WHERE username='$un'")->fetchColumn();
		$group = $db->query("SELECT groupid FROM members WHERE username='$un'")->fetchColumn();
		
		if ($group == 1) $error = $lang['login_inactive'].'<br />';
		if ($group == 2) $error = $lang['login_banned'].'<br />';
		
		
		if(empty($error))
		{
		
			$_SESSION['user_id'] = $u_id;
			header('Location: index.php');
		
		}
		else
		{
		
			$xtpl->assign(array(
				'ERRORS_TEXT' => $error
			));
			
			$xtpl->parse('MAIN.ERRORS');
			
		}
		
	}
	
	function Logout() {
	
		global $user;
	
		if (isset($_SESSION['user_id']) && $user['id'] > 0){
		
			session_destroy();
			header('Location: login.php');
			
		}
	
	}
	
	function sendValidationEmail($email){
	
		global $lang, $db, $config, $xtpl;
		
		$token = $db->query("SELECT token FROM members WHERE email='$email' LIMIT 1")->fetchColumn();
		$username = $db->query("SELECT username FROM members WHERE email='$email' LIMIT 1")->fetchColumn();
		$user_group = $db->query("SELECT groupid FROM members WHERE email='$email' LIMIT 1")->fetchColumn();
		$email_exists = (bool)$db->query("SELECT id FROM members WHERE email = ? LIMIT 1", array($email))->fetch();
		$is_inactive = (bool)$db->query("SELECT groupid FROM members WHERE token = ? AND groupid = ? LIMIT 1", array($token, '1'))->fetch();
		
		if (!$email_exists) $error .= $lang['reg_email_exists'].'<br />';
		if (!filter_var($email, FILTER_VALIDATE_EMAIL )) $error .= $lang['reg_email_format'].'<br />';
		if ($user_group > 1) $error .= $lang['validation_active'].'<br />';
		if (!strlen($token) == 16) $error = $lang['token_not_exist'].'<br />';	
		
		if(empty($error))
		{
		
			$activate_url = $config['main_url'].'/users.php?action=validate&token='.$token;
			$deactivate_url = $config['main_url'].'/users.php?action=remove&token='.$token;
			
			$subject = $config['title'].' - '.$lang['validation_reg'];
			$activate = '<a href="'.$activate_url.'">'.$lang['validation_activate'].'</a>';
			$deactivate = '<a href="'.$deactivate_url.'">'.$lang['validation_deactivate'].'</a>';
			$body = sprintf($lang['validation_email'], $username, $activate, $deactivate);
			$body .= $lang['validation_admin'];
			$headers = 'From: '. $config['admin_email'] . "\r\n" 
					   .'MIME-Version: 1.0' . "\r\n"
			           .'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			mail($email, $subject, $body, $headers);
			
			header('Location: message.php?id=103');
	
		}
		else
		{
		
			$xtpl->assign(array(
				'ERRORS_TEXT' => $error
			));
			
			$xtpl->parse('MAIN.RECOVERY_OPTIONS.VALIDATION_ERRORS');
		
		}
		
	}
	
	function validate($token){
	
		global $db;
		
		$is_member_token = (bool)$db->query("SELECT token FROM members WHERE token = ? LIMIT 1", array($token))->fetch();
		
		if(strlen($token) == 16 && $is_member_token)
		{
		
			$db->update('members', array('groupid' => '3'), 'token="'.$token.'"');
			header('Location: login.php');
		
		}
		
	}
	
	function remove($token){
	
		global $db;
		
		$is_member_token = (bool)$db->query("SELECT token FROM members WHERE token = ? LIMIT 1", array($token))->fetch();
		$group_id = $db->query("SELECT groupid FROM members WHERE token='$token' LIMIT 1")->fetchColumn();
		
		if(strlen($token) == 16 && $is_member_token)
		{
		
			$db->delete('members', "token='".$token."'");
			header('Location: index.php.php');
		
		}
		
	}
	
	function lostPassword($email){
	
		global $lang, $db, $config, $excursion;
		
		$password = $excursion->generateToken();
		
		$db->update('members', array('password' => md5($password)), 'email="'.$email.'"');
		$username = $db->query("SELECT username FROM members WHERE email='$email' LIMIT 1")->fetchColumn();
		
		$subject = $config['title'].' - '.$lang['validation_reg'];
		
		$body = sprintf($lang['reset_email'], $username, $password);
		$headers = 'From: '. $config['admin_email'] . "\r\n" 
					   .'MIME-Version: 1.0' . "\r\n"
			           .'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		mail($email, $subject, $body, $headers);
	
	}
	
}

class Excursion {

	function import_buffered($name, $value, $null = '')
	{
		if ($value === '' || $value === null)
		{
			if (isset($_SESSION['buffer'][$name]) && !is_array($_SESSION['buffer'][$name]))
			{
				return $_SESSION['buffer'][$name];
			}
			else
			{
				return $null;
			}
		}
		else
		{
			return $value;
		}
	}
	
	function alphaonly($text)
	{
		return(preg_replace('/[^a-zA-Z0-9\-_]/', '', $text));
	}

	function import($name, $source, $filter, $maxlen = 0, $dieonerror = false, $buffer = false)
	{
		global $import_filters;

		switch($source)
		{
			case 'G':
				$v = (isset($_GET[$name])) ? $_GET[$name] : NULL;
				$log = TRUE;
				break;

			case 'P':
				$v = (isset($_POST[$name])) ? $_POST[$name] : NULL;
				$log = TRUE;
				if ($filter=='ARR')
				{
					if ($buffer)
					{
						$v = $this->import_buffered($name, $v, null);
					}
					return($v);
				}
				break;

			case 'R':
				$v = (isset($_REQUEST[$name])) ? $_REQUEST[$name] : NULL;
				$log = TRUE;
				break;

			case 'C':
				$v = (isset($_COOKIE[$name])) ? $_COOKIE[$name] : NULL;
				$log = TRUE;
				break;

			case 'D':
				$v = $name;
				$log = FALSE;
				break;

			default:
				die('Unknown source for a variable : <br />Name = '.$name.'<br />Source = '.$source.' ? (must be G, P, C or D)');
				break;
		}

		if (MQGPC && ($source=='G' || $source=='P' || $source=='C') && $v != NULL && $filter != 'ARR')
		{
			$v = stripslashes($v);
		}

		if (($v === '' || $v === NULL) && $buffer)
		{
			$v = $this->import_buffered($name, $v, null);
			return $v;
		}
		
		if ($v === null)
		{
			return null;
		}

		if ($maxlen>0)
		{
			$v = mb_substr($v, 0, $maxlen);
		}

		$pass = FALSE;
		$defret = NULL;

		if (is_array($import_filters[$filter]))
		{
			foreach ($import_filters[$filter] as $func)
			{
				$v = $func($v, $name);
			}
			return $v;
		}

		switch($filter)
		{
			case 'INT':
				if (is_numeric($v) && floor($v)==$v)
				{
					$pass = TRUE;
					$v = (int) $v;
				}
				break;

			case 'NUM':
				if (is_numeric($v))
				{
					$pass = TRUE;
					$v = (float) $v;
				}
				break;

			case 'TXT':
				$v = trim($v);
				if (mb_strpos($v, '<')===FALSE)
				{
					$pass = TRUE;
				}
				else
				{
					$defret = str_replace('<', '&lt;', $v);
				}
				break;

			case 'ALP':
				$v = trim($v);
				$f = $this->alphaonly($v);
				if ($v == $f)
				{
					$pass = TRUE;
				}
				else
				{
					$defret = $f;
				}
				break;

			case 'PSW':
				$v = trim($v);
				$f = preg_replace('#[\'"&<>]#', '', $v);
				$f = mb_substr($f, 0 ,32);

				if ($v == $f)
				{
					$pass = TRUE;
				}
				else
				{
					$defret = $f;
				}
				break;

			case 'HTM':
				$v = trim($v);
				$pass = TRUE;
				break;

			case 'ARR':
				$pass = TRUE;
				break;

			case 'BOL':
				if ($v == '1' || $v == 'on')
				{
					$pass = TRUE;
					$v = TRUE;
				}
				elseif ($v=='0' || $v=='off')
				{
					$pass = TRUE;
					$v = FALSE;
				}
				else
				{
					$defret = FALSE;
				}
				break;

			case 'NOC':
				$pass = TRUE;
				break;

			default:
				die('Unknown filter for a variable : <br />Var = '.$cv_v.'<br />Filter = &quot;'.$filter.'&quot; ?');
				break;
		}

		if (!$pass || !in_array($filter, array('INT', 'NUM', 'BOL', 'ARR')))
		{
			$v = preg_replace('/(&#\d+)(?![\d;])/', '$1;', $v);
		}
		if ($pass)
		{
			return $v;
		}
		else
		{
			if ($log)
			{
				
			}
			if ($dieonerror)
			{
				die('Wrong input.');
			}
			else
			{
				return $defret;
			}
		}
	}

	function generateToken($length = 16)
	{
		$string = sha1(mt_rand());
		if ($length > 40)
		{
			for ($i=0; $i < floor($length / 40); $i++)
			{
				$string .= sha1(mt_rand());
			}
		}
		return(substr($string, 0, $length));
	}
	
	function generateGroup($id)
	{
	
		global $db;
		
		$is_group = (bool)$db->query("SELECT id FROM groups WHERE id = ? LIMIT 1", array($id))->fetch();
		$group = $db->query("SELECT title FROM groups WHERE id='$id' LIMIT 1")->fetchColumn();
		
		if($is_group)
		{
		
			return $group;
			
		}
	
	}
	
	function generateUser($id)
	{
	
		global $db;
		
		$is_member = (bool)$db->query("SELECT id FROM members WHERE id = ? LIMIT 1", array($id))->fetch();
		$member_username = $db->query("SELECT username FROM members WHERE id='$id' LIMIT 1")->fetchColumn();
		
		$member = '<a href="users.php?id=' . $id . '">' . $member_username . '</a>';
		
		if($is_member)
		{
		
			return $member;
			
		}
	
	}
	
	function Hook($hook)
	{
		global $plugins;

		$extplugins = array();

		if (isset($plugins[$hook]) && is_array($plugins[$hook]))
		{
			foreach($plugins[$hook] as $k)
			{
				
				$cat = 'plug';
				$opt = $k['code'];
			
				$extplugins[] = 'plugins/' . $k['file'];
				
			}
		}

		return $extplugins;
	}
	
}

?>