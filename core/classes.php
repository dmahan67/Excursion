<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
$import_filters = array();
$textarea_count = 0;
$url_appendix = array();

$config['type_text'] = '0';
$config['type_string'] = '1';
$config['type_select'] = '2';
$config['type_radio'] = '3';
$config['type_range'] = '4';
 
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
		$cfg['disablereg'] = $db->query("SELECT value FROM config WHERE title='disablereg'")->fetchColumn();
		$cfg['disableval'] = $db->query("SELECT value FROM config WHERE title='disableval'")->fetchColumn();
		$cfg['valnew'] = $db->query("SELECT value FROM config WHERE title='valnew'")->fetchColumn();
		
		if($cfg['disablereg']=='no')
		{
			
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
				if($cfg['disableval']=='yes' && $cfg['valnew']=='no'){$insert['groupid'] = 3;}
				$insert['email'] = $email;
				$insert['regdate'] = (int)time();
				$insert['token'] = $excursion->generateToken(16);
				$insert['SQ_Index'] = $sq;
				$insert['SQ_Answer'] = $sq_answer;

				$db->insert('members', $insert);
				
				if($cfg['disableval']=='no' && $cfg['valnew']=='no'){
				
					$member->sendValidationEmail($insert['email']);
					header('Location: message.php?id=101');
					
				}
				
				if($cfg['valnew']=='yes')
				{
				
					header('Location: message.php?id=109');
				
				}
				
				if($cfg['disableval']=='yes' && $cfg['valnew']=='no'){
				
					header('Location: message.php?id=108');
					
				}		
				
			}
			else
			{
			
				$xtpl->assign(array(
					'ERRORS_TEXT' => $error
				));
				
				$xtpl->parse('MAIN.ERRORS');
				
			}
			
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
	
	function mktime($hour = false, $minute = false, $second = false, $month = false, $date = false, $year = false)
	{
		if ($hour === false)  $hour  = date ('G');
		if ($minute === false) $minute = date ('i');
		if ($second === false) $second = date ('s');
		if ($month === false)  $month  = date ('n');
		if ($date === false)  $date  = date ('j');
		if ($year === false)  $year  = date ('Y');

		return mktime ((int) $hour, (int) $minute, (int) $second, (int) $month, (int) $date, (int) $year);
	}
	
	function datetostrftime($format) {

		$chars = array(
			'd' => '%d', 'D' => '%a', 'j' => '%e', 'l' => '%A',
			'N' => '%u', 'w' => '%w', 'z' => '%j', 'W' => '%V',
			'F' => '%B', 'm' => '%m', 'M' => '%b', 'o' => '%G',
			'Y' => '%Y', 'y' => '%y', 'a' => '%P', 'A' => '%p',
			'g' => '%l', 'h' => '%I', 'H' => '%H', 'i' => '%M',
			's' => '%S', 'O' => '%z', 'T' => '%Z', 'U' => '%s'
		);
		return strtr((string)$format, $chars);
	}
	
	function datetostamp($date, $format = null)
	{
		if ($date == '0000-00-00') return 0;
		if (!$format)
		{
			preg_match('#(\d{4})-(\d{2})-(\d{2})#', $date, $m);
			return mktime(0, 0, 0, (int) $m[2], (int) $m[3], (int) $m[1]);
		}
		if ($format == 'auto')
		{
			return strtotime($date);
		}
		$format = $this->datetostrftime($format);
		$m = strptime($date, $format);
		return mktime(
			(int)$m['tm_hour'], (int)$m['tm_min'], (int)$m['tm_sec'],
			(int)$m['tm_mon']+1, (int)$m['tm_mday'], (int)$m['tm_year']+1900
		);
	}
	
	function import_date($name, $usertimezone = true, $returnarray = false, $source = 'P')
	{
		global $lang, $R, $user;

		$date = $this->import($name, $source, 'ARR');

		$year = $this->import($date['year'], 'D', 'INT');
		$month = $this->import($date['month'], 'D', 'INT');
		$day = $this->import($date['day'], 'D', 'INT');
		$hour = $this->import($date['hour'], 'D', 'INT');
		$minute = $this->import($date['minute'], 'D', 'INT');

		if (($month && $day && $year) || ($day && $minute))
		{
			$timestamp = $this->mktime($hour, $minute, 0, $month, $day, $year);
		}
		else
		{
			$string = $this->import($date['string'], 'D', 'TXT');
			$format = $this->import($date['format'], 'D', 'TXT');
			if ($string && $format)
			{
				$timestamp = $this->datetostamp($string, $format);
			}
			else
			{
				return NULL;
			}
		}
		if ($usertimezone)
		{
			$timestamp -= $user['timezone'] * 3600;
		}
		if ($returnarray)
		{
			$result = array();
			$result['stamp'] = $timestamp;
			$result['year'] = (int)date('Y', $timestamp);
			$result['month'] = (int)date('m', $timestamp);
			$result['day'] = (int)date('d', $timestamp);
			$result['hour'] = (int)date('H', $timestamp);
			$result['minute'] = (int)date('i', $timestamp);
			return $result;
		}
		return $timestamp;
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
	
	function parse_str($str)
	{
		$res = array();
		$str = str_replace('&amp;', '&', $str);
		foreach (explode('&', $str) as $item)
		{
			if (!empty($item))
			{
				list($key, $val) = explode('=', $item);
				$res[$key] = $val;
			}
		}
		return $res;
	}
	
	function rc($name, $params = array())
	{
		global $R, $L, $theme_reload;
		if (isset($R[$name]) && is_array($theme_reload))
		{
			$R[$name] = (!empty($theme_reload['R'][$name]) && $theme_reload['R'][$name] != $R[$name]) ? $theme_reload['R'][$name] : $R[$name];
		}
		elseif (isset($L[$name]) && is_array($theme_reload))
		{
			$L[$name] = (!empty($theme_reload['L'][$name]) && $theme_reload['L'][$name] != $L[$name]) ? $theme_reload['L'][$name] : $L[$name];
		}
		
		$res = isset($R[$name]) ? $R[$name]
			: (isset($L[$name]) ? $L[$name] : $name);
		is_array($params) ? $args = $params : parse_str($params, $args);
		if (preg_match_all('#\{\$(\w+)\}#', $res, $matches, PREG_SET_ORDER))
		{
			foreach($matches as $m)
			{
				$var = $m[1];
				$res = str_replace($m[0], (isset($args[$var]) ? $args[$var] : $GLOBALS[$var]), $res);
			}
		}
		return $res;
	}
	
	function rc_attr_string($attrs)
	{
		$attr_str = '';
		if (is_array($attrs))
		{
			foreach ($attrs as $key => $val)
			{
				$attr_str .= ' ' . $key . '="' . htmlspecialchars($val) . '"';
			}
		}
		elseif ($attrs)
		{
			$attr_str = ' ' . $attrs;
		}
		return $attr_str;
	}
	
	function get_messages($src = 'default', $class = '')
	{
		$messages = array();
		if (empty($src) && empty($class))
		{
			return $_SESSION['messages'];
		}

		if (!is_array($_SESSION['messages']))
		{
			return $messages;
		}

		if (empty($src))
		{
			foreach ($_SESSION['messages'] as $src => $grp)
			{
				foreach ($grp as $msg)
				{
					if (!empty($class) && $msg['class'] != $class)
					{
						continue;
					}
					$messages[] = $msg;
				}
			}
		}
		elseif (is_array($_SESSION['messages'][$src]))
		{
			if (empty($class))
			{
				return $_SESSION['messages'][$src];
			}
			else
			{
				foreach ($_SESSION['messages'][$src] as $msg)
				{
					if ($msg['class'] != $class)
					{
						continue;
					}
					$messages[] = $msg;
				}
			}
		}
		return $messages;
	}
	
	function implode_messages($src = 'default', $class = '')
	{
		global $R, $L, $error_string;
		$res = '';

		if (!is_array($_SESSION['messages']))
		{
			return;
		}

		$messages = get_messages($src, $class);
		foreach ($messages as $msg)
		{
			$text = isset($L[$msg['text']]) ? $L[$msg['text']] : $msg['text'];
			$res .= $this->rc('code_msg_line', array('class' => $msg['class'], 'text' => $text));
		}

		if (!empty($error_string) && (empty($class) || $class == 'error'))
		{
			$res .= $this->rc('code_msg_line', array('class' => 'error', 'text' => $error_string));
		}
		return empty($res) ? '' : $this->rc('code_msg_begin', array('class' => empty($class) ? 'message' : $class))
			. $res . $R['code_msg_end'];
	}
	
	function createTags($type, $name, $value = '', $custom_rc)
	{
		global $R;
		
		$rc_name = preg_match('#^(\w+)\[(.*?)\]$#', $name, $mt) ? $mt[1] : $name;
		$rc = empty($custom_rc)
			? (empty($R["{$type}_{$rc_name}"]) ? "$type" : "{$type}_{$rc_name}")
			: $custom_rc;
		if (!isset($R[$rc]))
		{
			$rc = 'default';
		}

		return $this->rc($rc, array(
			'type' => $type,
			'name' => $name,
			'value' => htmlspecialchars($this->import_buffered($name, $value))
		));
	}
	
	function inputbox($type, $name, $value = '', $attrs = '', $custom_rc = '')
	{
		global $R, $config;
		
		$input_attrs = $this->rc_attr_string($attrs);
		$rc_name = preg_match('#^(\w+)\[(.*?)\]$#', $name, $mt) ? $mt[1] : $name;
		$rc = empty($custom_rc)
			? (empty($R["input_{$type}_{$rc_name}"]) ? "input_$type" : "input_{$type}_{$rc_name}")
			: $custom_rc;
		if (!isset($R[$rc]))
		{
			$rc = 'input_default';
		}
		$error = $cfg['msg_separate'] ? $this->implode_messages($name, 'error') : '';
		return $this->rc($rc, array(
			'type' => $type,
			'name' => $name,
			'value' => htmlspecialchars($this->import_buffered($name, $value)),
			'attrs' => $input_attrs,
			'error' => $error
		));
	}
	
	function selectbox($chosen, $name, $values, $titles = array(), $add_empty = true, $attrs = '', $custom_rc = '', $htmlspecialchars_bypass = false)
	{
		global $R, $config;

		if (!is_array($values))
		{
			$values = explode(',', $values);
		}
		if (!is_array($titles))
		{
			$titles = explode(',', $titles);
		}
		$use_titles = count($values) == count($titles);
		$input_attrs = $this->rc_attr_string($attrs);
		$chosen = $this->import_buffered($name, $chosen);
		$multi = is_array($chosen) && isset($input_attrs['multiple']);
		$error = $cfg['msg_separate'] ? $this->implode_messages($name, 'error') : '';
		$rc_name = preg_match('#^(\w+)\[(.*?)\]$#', $name, $mt) ? $mt[1] : $name;

		$selected = (is_null($chosen) || $chosen === '' || $chosen == '00') ? ' selected="selected"' : '';
		$rc = empty($R["input_option_{$rc_name}"]) ? 'input_option' : "input_option_{$rc_name}";
		if ($add_empty)
		{
			$options .= $this->rc($rc, array(
				'value' => '',
				'selected' => $selected,
				'title' => $R['code_option_empty']
			));
		}
		foreach ($values as $k => $x)
		{
			$x = trim($x);
			$selected = ($multi && in_array($x, $chosen)) || (!$multi && $x == $chosen) ? ' selected="selected"' : '';
			$title = $use_titles ? htmlspecialchars($titles[$k]) : htmlspecialchars($x);
			$options .= $this->rc($rc, array(
				'value' => $htmlspecialchars_bypass ? $x : htmlspecialchars($x),
				'selected' => $selected,
				'title' => $title
			));
		}
		$rc = empty($custom_rc) 
			? empty($R["input_select_{$rc_name}"]) ? 'input_select' : "input_select_{$rc_name}"
			: $custom_rc;
		$result .= $this->rc($rc, array(
			'name' => $name,
			'attrs' => $input_attrs,
			'error' => $error,
			'options' => $options
		));
		return $result;
	}
	
	function selectbox_gender($check, $name)
	{
		global $lang;

		$genlist = array('U', 'M', 'F');
		$titlelist = array();
		foreach ($genlist as $i)
		{
			$titlelist[] = $lang['gender_' . $i];
		}
		return $this->selectbox($check, $name, $genlist, $titlelist, false);
	}
	
	function infoget($file, $limiter = 'EXT', $maxsize = 32768)
	{
		global $lang;
		$result = array();

		$fp = @fopen($file, 'r');
		if ($fp)
		{
			$limiter_begin = '[BEGIN_' . $limiter . ']';
			$limiter_end = '[END_' . $limiter . ']';
			$data = fread($fp, $maxsize);
			$begin = mb_strpos($data, $limiter_begin);
			$end = mb_strpos($data, $limiter_end);

			if ($end > $begin && $begin > 0)
			{
				$lines = mb_substr($data, $begin + 8 + mb_strlen($limiter),
					$end - $begin - mb_strlen($limiter) - 8);
				$lines = explode("\n", $lines);

				foreach ($lines as $k => $line)
				{
					$line = ltrim($line, " */");
					$linex = explode('=', $line);
					$ii = 1;
					while (!empty($linex[$ii]))
					{
						$result[$linex[0]] .= trim($linex[$ii]);
						$ii++;
					}
				}
			}
			else
			{
				$result = false;
			}
		}
		else
		{
			$result = false;
		}
		@fclose($fp);
		return $result;
	}
	
	function selectbox_theme($selected_theme, $input_name)
	{
		global $config;
		
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
		foreach ($themelist as $i => $x)
		{
			$themeinfo = "themes/$x/$x.php";
			if (file_exists($themeinfo))
			{
				$info = $this->infoget($themeinfo, 'THEME');
				if ($info)
				{
					$values[] = "$x";
					$titles[] = $info['Name'];
					
				}
				else
				{
					$values[] = "$x";
					$titles[] = $x;
				}
			}
			else
			{
				$values[] = "$x";
				$titles[] = $x;
			}
		}

		return $this->selectbox($selected_theme, $input_name, $values, $titles, false);
	}
	
	function checkbox($chosen, $name, $title = '', $attrs = '', $value = '1', $custom_rc = '')
	{
		global $R;
		
		$input_attrs = $this->rc_attr_string($attrs);
		$checked = $chosen ? ' checked="checked"' : '';
		$rc_name = preg_match('#^(\w+)\[(.*?)\]$#', $name, $mt) ? $mt[1] : $name;
		$rc = empty($custom_rc) 
			? empty($R["input_checkbox_{$rc_name}"]) ? 'input_checkbox' : "input_checkbox_{$rc_name}" 
			: $custom_rc;
		return $this->rc($rc, array(
			'value' => htmlspecialchars($this->import_buffered($name, $value)),
			'name' => $name,
			'checked' => $checked,
			'title' => $title,
			'attrs' => $input_attrs
		));
	}
	
	function radiobox($chosen, $name, $values, $titles = array(), $attrs = '', $separator = '', $custom_rc = '')
	{
		global $R;
		
		if (!is_array($values))
		{
			$values = explode(',', $values);
		}
		if (!is_array($titles))
		{
			$titles = explode(',', $titles);
		}
		$use_titles = count($values) == count($titles);
		$input_attrs = $this->rc_attr_string($attrs);
		$chosen = $this->import_buffered($name, $chosen);
		if (empty($separator))
		{
			$separator = $R['input_radio_separator'];
		}
		$i = 0;
		$result = '';
		$rc_name = preg_match('#^(\w+)\[(.*?)\]$#', $name, $mt) ? $mt[1] : $name;
		$rc = empty($custom_rc) 
			? empty($R["input_radio_{$rc_name}"]) ? 'input_radio' : "input_radio_{$rc_name}"
			: $custom_rc;
		foreach ($values as $k => $x)
		{
			$checked = ($x == $chosen) ? ' checked="checked"' : '';
			$title = $use_titles ? htmlspecialchars($titles[$k]) : htmlspecialchars($x);
			if ($i > 0)
			{
				$result .= $separator;
			}
			$result .= $this->rc($rc, array(
				'value' => htmlspecialchars($x),
				'name' => $name,
				'checked' => $checked,
				'title' => $title,
				'attrs' => $input_attrs
			));
			$i++;
		}
		return $result;
	}
	
	function selectbox_date($utime, $mode = 'long', $name = '', $max_year = 2030, $min_year = 2000, $usertimezone = true, $custom_rc = '')
	{
		global $lang, $R, $user;
		$rc_name = preg_match('#^(\w+)\[(.*?)\]$#', $name, $mt) ? $mt[1] : $name;

		$utime = ($usertimezone && $utime > 0) ? ($utime + $user['timezone'] * 3600) : $utime;

		if ($utime == 0)
		{
			list($s_year, $s_month, $s_day, $s_hour, $s_minute) = array(null, null, null, null, null);
		}
		else
		{
			list($s_year, $s_month, $s_day, $s_hour, $s_minute) = explode('-', @date('Y-m-d-H-i', $utime));
		}
		$months = array();
		$months[1] = $lang['January'];
		$months[2] = $lang['February'];
		$months[3] = $lang['March'];
		$months[4] = $lang['April'];
		$months[5] = $lang['May'];
		$months[6] = $lang['June'];
		$months[7] = $lang['July'];
		$months[8] = $lang['August'];
		$months[9] = $lang['September'];
		$months[10] = $lang['October'];
		$months[11] = $lang['November'];
		$months[12] = $lang['December'];

		$year = $this->selectbox($s_year, $name.'[year]', range($min_year, $max_year));
		$month = $this->selectbox($s_month, $name.'[month]', array_keys($months), array_values($months));
		$day = $this->selectbox($s_day, $name.'[day]', range(1, 31));

		$range = array();
		for ($i = 0; $i < 24; $i++)
		{
			$range[] = sprintf('%02d', $i);
		}
		$hour = $this->selectbox($s_hour, $name.'[hour]', $range);

		$range = array();
		for ($i = 0; $i < 60; $i++)
		{
			$range[] = sprintf('%02d', $i);
		}

		$minute = $this->selectbox($s_minute, $name.'[minute]', $range);

		$rc = empty($R["input_date_{$mode}"]) ? 'input_date' : "input_date_{$mode}";
		$rc = empty($R["input_date_{$rc_name}"]) ? $rc : "input_date_{$rc_name}";
		$rc = empty($custom_rc) ? $rc : $custom_rc;

		$result = $this->rc($rc, array(
			'day' => $day,
			'month' => $month,
			'year' => $year,
			'hour' => $hour,
			'minute' => $minute
		));

		return $result;
	}
	
	function textarea($name, $value, $rows, $cols, $attrs = '', $custom_rc = '')
	{
		global $textarea_count, $R;
		
		$textarea_count++;
		
		$input_attrs = $this->rc_attr_string($attrs);
		$rc_name = preg_match('#^(\w+)\[(.*?)\]$#', $name, $mt) ? $mt[1] : $name;
		$rc = empty($custom_rc)
			? (empty($R["input_textarea_{$rc_name}"]) ? 'input_textarea' : "input_textarea_{$rc_name}")
			: $custom_rc;
		$error = $cfg['msg_separate'] ? $this->implode_messages($name, 'error') : '';
		return $this->rc($rc, array(
			'name' => $name,
			'value' => htmlspecialchars($this->import_buffered($name, $value)),
			'rows' => $rows,
			'cols' => $cols,
			'attrs' => $input_attrs,
			'error' => $error
		));
	}
	
	function checklistbox($chosen, $name, $values, $titles = array(), $attrs = '', $separator = '', $addnull = true, $custom_rc = '')
	{
		global $R;
		
		if (!is_array($values))
		{
			$values = explode(',', $values);
		}
		if (!is_array($titles))
		{
			$titles = explode(',', $titles);
		}
		$use_titles = count($values) == count($titles);
		$input_attrs = $this->rc_attr_string($attrs);
		
		$chosen = $this->import_buffered($name, $chosen);

		if (empty($separator))
		{
			$separator = $R['input_radio_separator'];
		}
		
		$i = 0;
		$result = '';
		if ($addnull)
		{
			$result .= $this->inputbox('hidden', $name.'[nullval]', 'nullval');
		}
		$rc_name = preg_match('#^(\w+)\[(.*?)\]$#', $name, $mt) ? $mt[1] : $name;
		$name = $name.'[]';
		$rc = empty($custom_rc) 
			? empty($R["input_checkbox_{$rc_name}"]) ? 'input_checkbox' : "input_checkbox_{$rc_name}"
			: $custom_rc;
		foreach ($values as $k => $x)
		{
			$i++;
			$x = trim($x);
			$checked = (is_array($chosen) && in_array($x, $chosen)) || (!is_array($chosen) && $x == $chosen) ? ' checked="checked"' : '';
			$title = $use_titles ? htmlspecialchars($titles[$k]) : htmlspecialchars($x);
			if ($i > 1)
			{
				$result .= $separator;
			}
			$result .= $this->rc($rc, array(
				'value' => htmlspecialchars($x),
				'name' => $name.'['.$i.']',
				'checked' => $checked,
				'title' => $title,
				'attrs' => $input_attrs
			));

		}
		return $result;

	}
	
	function filebox($name, $value = '', $filepath = '', $delname ='', $attrs = '', $custom_rc = '')
	{
		global $R, $config, $lang;
		
		$input_attrs = $this->rc_attr_string($attrs);
		$rc_name = preg_match('#^(\w+)\[(.*?)\]$#', $name, $mt) ? $mt[1] : $name;
		
		$custom_rc = explode('|', $custom_rc, 2);
		if(empty($value))
		{
			$rc = empty($custom_rc[1])
				? (empty($R["input_file_{$rc_name}_empty"]) ? "input_file_empty" : "input_file_{$rc_name}_empty")
				: $custom_rc[1];
		}
		else
		{
			$rc = empty($custom_rc[0])
				? (empty($R["input_file_{$rc_name}"]) ? "input_file" : "input_file_{$rc_name}")
				: $custom_rc[0];
		}
		
		$filepath = empty($filepath) ? $value : $filepath;
		$delname = empty($delname) ? 'del'.$name : $delname;
		$error = $cfg['msg_separate'] ? $this->implode_messages($name, 'error') : '';
		return $this->rc($rc, array(
			'name' => $name,
			'filepath' => $filepath,
			'delname' => $delname,
			'value' => $value,
			'attrs' => $input_attrs,
			'error' => $error
		));
	}
	
	function selectbox_categories($check, $name, $subcat = '', $hideprivate = true)
	{
		global $db;

		$sql = $db->query("SELECT code, title FROM categories ORDER BY title ASC");
		$count = $db->countRows('categories');
		$jj = 0;
		while ($row = $sql->fetch())
		{
		
			$jj++;
			
			if($jj > ($count - 1)){
			
				$result_value .= $row['code'];
				$result_title .= $row['title'];
				
			}
			else
			{
			
				$result_value .= $row['code'].',';
				$result_title .= $row['title'].',';
				
			}
			
		}
		$result = $this->selectbox($check, $name, $result_value, $result_title, false);

		return($result);
	}
	
	function selectbox_groups($check, $name)
	{
		global $db;

		$sql = $db->query("SELECT id, title FROM groups ORDER BY title ASC");
		$count = $db->countRows('groups');
		$jj = 0;
		while ($row = $sql->fetch())
		{
		
			$jj++;
			
			if($jj > ($count - 1)){
			
				$result_value .= $row['id'];
				$result_title .= $row['title'];
				
			}
			else
			{
			
				$result_value .= $row['id'].',';
				$result_title .= $row['title'].',';
				
			}
			
		}
		$result = $this->selectbox($check, $name, $result_value, $result_title, false);

		return($result);
	}
	
	function selectbox_security_questions($check, $name)
	{
		global $db;

		$sql = $db->query("SELECT id, question FROM security_questions ORDER BY id ASC");
		$count = $db->countRows('security_questions');
		$jj = 0;
		while ($row = $sql->fetch())
		{
		
			$jj++;
			
			if($jj > ($count - 1)){
			
				$result_value .= $row['id'];
				$result_title .= $row['question'];
				
			}
			else
			{
			
				$result_value .= $row['id'].',';
				$result_title .= $row['question'].',';
				
			}
			
		}
		$result = $this->selectbox($check, $name, $result_value, $result_title, false);

		return($result);
	}
	
	function url($name, $params = '', $tail = '', $htmlspecialchars_bypass = false, $ignore_appendix = false)
	{
		global $config, $url_appendix;
		
		if (is_string($params))
		{
			$params = $this->parse_str($params);
		}
		elseif (!is_array($params))
		{
			$params = array();
		}
		if (!$ignore_appendix && count($url_appendix) > 0)
		{
			$params = array_merge($params, $url_appendix);
		}
		$params = array_filter((array)$params);
		
		$url = $name.".php";

		if (count($params) > 0)
		{
			$sep = $htmlspecialchars_bypass ? '&' : '&amp;';
			$url .= '?' . http_build_query($params, '', $sep);
		}
		$url .= $tail;

		return $url;
	}
	
	function stamptodate($stamp)
	{
		return date('Y-m-d', $stamp);
	}
	
	function date($format, $timestamp = null, $usertimezone = true)
	{
		global $lang, $Ldt, $user;
		
		if ($usertimezone)
		{
			$timestamp += $user['timezone'] * 3600;
		}
		
		$datetime = ($Ldt[$format]) ? @date($Ldt[$format], $timestamp) : @date($format, $timestamp);
		$search = array(
			'/Monday/', '/Tuesday/', '/Wednesday/', '/Thursday/',
			'/Friday/', '/Saturday/', '/Sunday/',
			'/Mon([^a-z])/', '/Tue([^a-z])/', '/Wed([^a-z])/', '/Thu([^a-z])/',
			'/Fri([^a-z])/', '/Sat([^a-z])/', '/Sun([^a-z])/',
			'/January/', '/February/', '/March/',
			'/April/', '/May/', '/June/',
			'/July/', '/August/', '/September/',
			'/October/', '/November/', '/December/',
			'/Jan([^a-z])/', '/Feb([^a-z])/', '/Mar([^a-z])/',
			'/Apr([^a-z])/', '/May([^a-z])/', '/Jun([^a-z])/',
			'/Jul([^a-z])/', '/Aug([^a-z])/', '/Sep([^a-z])/',
			'/Oct([^a-z])/', '/Nov([^a-z])/', '/Dec([^a-z])/'
		);
		$replace = array(
			$L['Monday'], $L['Tuesday'], $L['Wednesday'], $L['Thursday'],
			$L['Friday'], $L['Saturday'], $L['Sunday'],
			$L['Monday_s'], $L['Tuesday_s'], $L['Wednesday_s'], $L['Thursday_s'],
			$L['Friday_s'], $L['Saturday_s'], $L['Sunday_s'],
			$L['January'], $L['February'], $L['March'],
			$L['April'], $L['May'], $L['June'],
			$L['July'], $L['August'], $L['September'],
			$L['October'], $L['November'], $L['December'],
			$L['January_s'], $L['February_s'], $L['March_s'],
			$L['April_s'], $L['May_s'], $L['June_s'],
			$L['July_s'], $L['August_s'], $L['September_s'],
			$L['October_s'], $L['November_s'], $L['December_s']
		);
		return ($lang == 'en') ? $datetime : preg_replace($search, $replace, $datetime);
	}
	
	function file_check($path, $name, $ext)
	{
	
		global $lang, $config;
		

		require 'core/mimetype.php';
		
		$fcheck = FALSE;
		if (in_array($ext, array('jpg', 'jpeg', 'png', 'gif')))
		{
			switch($ext)
			{
				case 'gif':
					$fcheck = @imagecreatefromgif($path);
				break;

				case 'png':
					$fcheck = @imagecreatefrompng($path);
				break;

				default:
					$fcheck = @imagecreatefromjpeg($path);
				break;
			}
			$fcheck = $fcheck !== FALSE;
		}
		else
		{
			if (!empty($mime_type[$ext]))
			{
				foreach ($mime_type[$ext] as $mime)
				{
					$content = file_get_contents($path, 0, NULL, $mime[3], $mime[4]);
					$content = ($mime[2]) ? bin2hex($content) : $content;
					$mime[1] = ($mime[2]) ? strtolower($mime[1]) : $mime[1];
					$i++;
					if ($content == $mime[1])
					{
						$fcheck = TRUE;
						break;
					}
				}
			}
			else
			{
				$fcheck = ($config['pfsnomimepass']) ? 1 : 2;
			}
		}
		
		return($fcheck);
	}
	
	function safename($basename, $underscore = true, $postfix = '')
	{
		global $lang;

		$fname = mb_substr($basename, 0, mb_strrpos($basename, '.'));
		$ext = mb_substr($basename, mb_strrpos($basename, '.') + 1);
		if($underscore) $fname = str_replace(' ', '_', $fname);
		$fname = preg_replace('#[^a-zA-Z0-9\-_\.\ \+]#', '', $fname);
		$fname = str_replace('..', '.', $fname);
		if(empty($fname)) $fname = $this->unique();
		return $fname . $postfix . '.' . mb_strtolower($ext);
	}
	
	function unique($length = 16)
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

	function imagesharpen($imgdata, $source_width, $target_width)
	{
		$s = $target_width * (750.0 / $source_width);
		$a = 52;
		$b = -0.27810650887573124;
		$c = .00047337278106508946;
		$sharpness = max(round($a+$b*$s+$c*$s*$s), 0);
		$sharpenmatrix = array(
			array(-1, -2, -1),
			array(-2, $sharpness + 12, -2),
			array(-1, -2, -1)
		);
		imageconvolution($imgdata, $sharpenmatrix, $sharpness, 0);
		return $imgdata;
	}
	
	function imageresize($source, $target='return', $target_width=99999, $target_height=99999, $crop='', $fillcolor='', $quality=90, $sharpen=true)
	{
		if (!file_exists($source)) return;
		$source_size = getimagesize($source);
		if(!$source_size) return;
		$mimetype = $source_size['mime'];
		if (substr($mimetype, 0, 6) != 'image/') return;

		$source_width = $source_size[0];
		$source_height = $source_size[1];
		if($target_width > $source_width) $target_width = $source_width; $noscaling_x = true;
		if($target_height > $source_height) $target_height = $source_height; $noscaling_y = true;

		$fillcolor = preg_replace('/[^0-9a-fA-F]/', '', (string)$fillcolor);
		if (!$fillcolor && $noscaling_x && $noscaling_y)
		{
			$data = file_get_contents($source);
			if($target == 'return') return $data;
		}

		$offsetX = 0;
		$offsetY = 0;

		if($crop)
		{
			$crop = ($crop == 'fit') ? array($target_width, $target_height) : explode(':', (string)$crop);
			if(count($crop) == 2)
			{
				$source_ratio = $source_width / $source_height;
				$target_ratio = (float)$crop[0] / (float)$crop[1];

				if ($source_ratio < $target_ratio)
				{
					$temp = $source_height;
					$source_height = $source_width / $target_ratio;
					$offsetY = ($temp - $source_height) / 2;
				}
				if ($source_ratio > $target_ratio)
				{
					$temp = $source_width;
					$source_width = $source_height * $target_ratio;
					$offsetX = ($temp - $source_width) / 2;
				}
			}
		}

		$width_ratio = $target_width / $source_width;
		$height_ratio = $target_height / $source_height;
		if ($width_ratio * $source_height < $target_height)
		{
			$target_height = ceil($width_ratio * $source_height);
		}
		else
		{
			$target_width = ceil($height_ratio * $source_width);
		}

		ini_set('memory_limit', '100M');
		$canvas = imagecreatetruecolor($target_width, $target_height);

		switch($mimetype)
		{
			case 'image/gif':
				$fn_create = 'imagecreatefromgif';
				$fn_output = 'imagegif';
				$mimetype = 'image/gif';
				$sharpen = false;
			break;

			case 'image/x-png':
			case 'image/png':
				$fn_create = 'imagecreatefrompng';
				$fn_output = 'imagepng';
				$quality = round(10 - ($quality / 10));
				$sharpen = false;
			break;

			default:
				$fn_create = 'imagecreatefromjpeg';
				$fn_output = 'imagejpeg';
				$sharpen = ($target_width < 75 || $target_height < 75) ? false : $sharpen;
			break;
		}
		$source_data = $fn_create($source);

		if (in_array($size['mime'], array('image/gif', 'image/png')))
		{
			if (!$fillcolor)
			{
				imagealphablending($canvas, false);
				imagesavealpha($canvas, true);
			}
			elseif(strlen($fillcolor) == 6 || strlen($fillcolor) == 3)
			{
				$background	= (strlen($fillcolor) == 6) ?
					imagecolorallocate($canvas, hexdec($fillcolor[0].$fillcolor[1]), hexdec($fillcolor[2].$fillcolor[3]), hexdec($fillcolor[4].$fillcolor[5])):
					imagecolorallocate($canvas, hexdec($fillcolor[0].$fillcolor[0]), hexdec($fillcolor[1].$fillcolor[1]), hexdec($fillcolor[2].$fillcolor[2]));
				imagefill($canvas, 0, 0, $background);
			}
		}
		imagecopyresampled($canvas, $source_data, 0, 0, $offsetX, $offsetY, $target_width, $target_height, $source_width, $source_height);
		imagedestroy($source_data);
		$canvas = ($sharpen) ? $this->imagesharpen($canvas, $source_width, $target_width) : $canvas;

		if($target == 'return')
		{
			ob_start();
			$fn_output($canvas, null, $quality);
			$data = ob_get_contents();
			ob_end_clean();
			imagedestroy($canvas);
			return $data;
		}
		else
		{
			$result = $fn_output($canvas, $target, $quality);
			imagedestroy($canvas);
			return $result;
		}
	}
	
	function buildAvatar($userid, $code){
	
		global $db;
	
		$src = $db->query("SELECT avatar FROM members WHERE id='$userid' LIMIT 1")->fetchColumn();
		
		if(empty($code)){ $class = 'avatar'; }else{ $class = $code; }
	
		return $this->rc("member_image", array('src' => $src, 'class' => $class));
	
	}
	
	function compile_plugin_info($dir)
	{
		$ext_list = array();

		$dp = opendir($dir);
		while ($f = readdir($dp))
		{
			$path = $dir . '/' . $f;
			if ($f[0] != '.' && is_dir($path) && file_exists("$path/$f.config.php"))
			{
				$info = $this->infoget("$path/$f.config.php", 'PLUGIN_CONFIG');

				$ext_list[$f] = $info;
			}
		}
		closedir($dp);
		return $ext_list;
	}
	
	function parseConfig($info_cfg)
	{
	
		global $config;
		
		$options = array();
		if (is_array($info_cfg))
		{
			foreach ($info_cfg as $i => $x)
			{
				$line = explode(':', $x);
				if (is_array($line) && !empty($line[1]) && !empty($i))
				{
					switch ($line[1])
					{
						case 'string':
							$line['Type'] = $config['type_string'];
							break;
						case 'select':
							$line['Type'] = $config['type_select'];
							break;
						case 'radio':
							$line['Type'] = $config['type_radio'];
							break;
						case 'range':
							$line['Type'] = $config['type_range'];
							break;
						default:
							$line['Type'] = $config['type_text'];
							break;
					}
					$options[] = array(
						'name' => $i,
						'order' => $line[0],
						'type' => $line['Type'],
						'variants' => $line[2],
						'default' => $line[3],
						'text' => $line[4]
					);
				}
			}
		}
		return $options;
	}

}

?>