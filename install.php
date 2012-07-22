<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
require_once 'config.php';
require_once 'core/classes.php';
$excursion = new Excursion();
require_once 'core/xtemplate.php';
require_once 'core/database.php';
require_once $excursion->import_langfile('main', 'core');
require_once 'core/resources.php';

$ex['location'] = 'install';

$step = $config['new_install'];
$step = (!empty($step) ? $step : '1');
$action = $excursion->import('action','G','ALP');

$xtpl = new XTemplate('install/tpl/install.xtpl');

if($step == 1)
{

	if($action == 'send')
	{
	
		$config_contents = file_get_contents('config.php');
		$config_contents = preg_replace("#^\\\$config\['new_install'\]\s*=\s*.*?;#m", "\$config['new_install'] = 2;", $config_contents);
		file_put_contents('config.php', $config_contents);
		
		header('Location: install.php');
	
	}
	
	if (is_dir('assets/avatars'))
	{
		$status['avatars_dir'] = is_writable('assets/avatars')
			? $R['install_code_writable']
			: $excursion->rc('install_code_invalid', array('text' =>
				$excursion->rc('install_chmod_value', array('chmod' =>
					substr(decoct(fileperms('assets/avatars')), -4)))));
	}
	else
	{
		$status['avatars_dir'] = $R['install_code_not_found'];
	}

	if (file_exists('config.php'))
	{
		$status['config'] = is_writable('config.php')
			? $R['install_code_writable']
			: $excursion->rc('install_code_invalid', array('text' =>
				$excursion->rc('install_chmod_value', array('chmod' =>
					substr(decoct(fileperms('config.php')), -4)))));
	}
	else
	{
		$status['config'] = $R['install_code_not_found'];
	}
	if (file_exists('install/install.sql'))
	{
		$status['sql_file'] = $R['install_code_found'];
	}
	else
	{
		$status['sql_file'] = $R['install_code_not_found'];
	}
	$status['php_ver'] = (function_exists('version_compare') && version_compare(PHP_VERSION, '5.2.3', '>='))
		? $excursion->rc('install_code_valid', array('text' =>
			$excursion->rc('install_ver_valid', array('ver' => PHP_VERSION))))
		: $excursion->rc('install_code_invalid', array('text' =>
			$excursion->rc('install_ver_invalid', array('ver' => PHP_VERSION))));
	$status['mbstring'] = (extension_loaded('mbstring'))
		? $R['install_code_available'] : $R['install_code_not_available'];
	$status['hash'] = (extension_loaded('hash') && function_exists('hash_hmac'))
		? $R['install_code_available'] : $R['install_code_not_available'];
	$status['mysql'] = (extension_loaded('pdo_mysql'))
		? $R['install_code_available'] : $R['install_code_not_available'];

	$xtpl->assign(array(
		'INSTALL_AV_DIR' => $status['avatars_dir'],
		'INSTALL_CONFIG' => $status['config'],
		'INSTALL_SQL_FILE' => $status['sql_file'],
		'INSTALL_PHP_VER' => $status['php_ver'],
		'INSTALL_MBSTRING' => $status['mbstring'],
		'INSTALL_HASH' => $status['hash'],
		'INSTALL_MYSQL' => $status['mysql']
	));

	$xtpl->parse("MAIN.STEP1");

}
elseif($step == 2)
{

	$db_host = $excursion->import('db_host', 'P', 'TXT', 0, false, true);
	$db_port = $excursion->import('db_port', 'P', 'TXT', 0, false, true);
	$db_user = $excursion->import('db_user', 'P', 'TXT', 0, false, true);
	$db_pass = $excursion->import('db_pass', 'P', 'TXT', 0, false, true);
	$db_name = $excursion->import('db_name', 'P', 'TXT', 0, false, true);

	if($action == 'send')
	{
	
		try
		{
			$db_port = empty($db_port) ? '' : ';port='.$db_port;
			$db = new DB('mysql:host='.$db_host.$dbc_port.';dbname='.$db_name, $db_user, $db_pass);
		}
		catch (PDOException $e)
		{
			if ($e->getCode() == 1049 || mb_strpos($e->getMessage(), '[1049]') !== false)
			{

				try
				{
					$db = new DB('mysql:host='.$db_host.$dbc_port, $db_user, $db_pass);
					$db->query("CREATE DATABASE `$db_name`");
					$db->query("USE `$db_name`");
				}
				catch (PDOException $e)
				{
					$excursion->reportError('install_error_sql_db', 'db_name');
				}
			}
			else
			{
				$excursion->reportError('install_error_sql', 'db_host');
			}
		}

		if (!$excursion->error_found() && function_exists('version_compare')
			&& !version_compare($db->getAttribute(PDO::ATTR_SERVER_VERSION), '5.0.7', '>='))
		{
			$excursion->reportError($excursion->rc('install_error_sql_ver', array('ver' => $db->getAttribute(PDO::ATTR_SERVER_VERSION))));
		}

		if (!$excursion->error_found())
		{
			$config_contents = file_get_contents('config.php');
			install_config_replace($config_contents, 'mysqlhost', $db_host);
			if (!empty($db_port))
			{
				$excursion->install_config_replace($config_contents, 'mysqlport', $db_port);
			}
			install_config_replace($config_contents, 'mysqluser', $db_user);
			install_config_replace($config_contents, 'mysqlpassword', $db_pass);
			install_config_replace($config_contents, 'mysqldb', $db_name);
			
			$sql_file = file_get_contents('install/install.sql');
			$error = $db->runScript($sql_file);

			if ($error)
			{
				$excursion->reportError($excursion->rc('install_error_sql_script', array('msg' => $error)));
			}
			else
			{
				$config_contents = file_get_contents('config.php');
				$config_contents = preg_replace("#^\\\$config\['new_install'\]\s*=\s*.*?;#m", "\$config['new_install'] = 3;", $config_contents);
				file_put_contents('config.php', $config_contents);
				header('Location: install.php');
			}
		}
	
	}
	
	$xtpl->assign(array(
		'INSTALL_DB_HOST' => is_null($db_host) ? $config['mysqlhost'] : $db_host,
		'INSTALL_DB_PORT' => is_null($db_port) ? $config['mysqlport'] : $db_port,
		'INSTALL_DB_USER' => is_null($db_user) ? $config['mysqluser'] : $db_user,
		'INSTALL_DB_NAME' => is_null($db_name) ? $config['mysqldb'] : $db_name,
		'INSTALL_DB_X' => $db_x,
	));
	
	$xtpl->parse("MAIN.STEP2");
	
}
elseif($step == 3)
{

	$install_plugins = $excursion->import('install_plugins', 'P', 'ARR', 0, false, true);
	
	try
	{
		$config['db_port'] = empty($config['mysqlport']) ? '' : ';port='.$config['mysqlport'];
		$db = new DB('mysql:host='.$config['mysqlhost'].$dbc_port.';dbname='.$config['mysqldb'], $config['mysqluser'], $config['mysqlpassword']);
	}
	catch (PDOException $e)
	{
		$excursion->reportError('install_error_sql', 'db_host');
	}

	if($action == 'send')
	{
	
		$selected_plugins = array();
		if (is_array($install_plugins))
		{
			foreach ($install_plugins as $key => $val)
			{
				if ($val)
				{
					$selected_plugins[] = $key;
				}
			}
		}
		
		if (!$excursion->error_found())
		{

			$selected_plugins = install_extensions($selected_plugins, false);
			foreach ($selected_plugins as $ext)
			{
			
				$ext_config = 'plugins/' . $ext . '/' . $ext . '.config.php';
				$config_exists = file_exists($ext_config);
			
				if ($config_exists)
				{
				
					$info = $excursion->infoget($ext_config, 'PLUGIN_CONFIG');
					
					$insert_rows = array();
					
					$insert_rows[] = array(
						'groupid' => 0,
						'code' => 'plugin',
						'area' => $info['Code'],
						'rights' => $excursion->authValue($info['Auth_guests']),
						'rights_lock' => $excursion->authValue($info['Lock_guests'])
					);
					
					$sql = $db->query("SELECT * FROM groups ORDER BY id ASC");
					foreach ($sql->fetchAll() as $row)
					{
						if($row['id'] == '1'){$ins_auth = 0; $ins_lock = 31;}
						elseif($row['id'] == '2'){$ins_auth = 0; $ins_lock = 31;}
						elseif($row['id'] == '4'){$ins_auth = 31; $ins_lock = 0;}
						else{$ins_auth = $excursion->authValue($info['Auth_members']); $ins_lock = $excursion->authValue($info['Lock_members']);}
						
						$insert_rows[] = array(
							'groupid' => $row['id'],
							'code' => 'plugin',
							'area' => $info['Code'],
							'rights' => $ins_auth,
							'rights_lock' => $ins_lock
						);
					}
						
					$db->insert('auth', $insert_rows);
				
				}
				if (!plugin_install($ext, false))
				{
					$excursion->reportError("Installing $ext plugin has failed");
				}
			}
			
			$config_contents = file_get_contents('config.php');
			$config_contents = preg_replace("#^\\\$config\['new_install'\]\s*=\s*.*?;#m", "\$config['new_install'] = 4;", $config_contents);
			file_put_contents('config.php', $config_contents);
			header('Location: install.php');
						
		}
					
	}
	
	$extensions = $excursion->compile_plugin_info('plugins');
	
	$auto_check = array('news', 'comments', 'latest', 'mobile');
	
	foreach ($extensions as $code => $info)
	{
				
		if (empty($info['Error']))
		{
	
			$xtpl->assign(array(
				'NAME' => $info['Name'],
				'CODE' => $code,
				'DESC' => empty($L['info_desc']) ? $info['Description'] : $L['info_desc']
			));
			if (in_array($code, $auto_check, true)) {$xtpl->assign('CHECKED', 'checked="checked"');}
			$xtpl->parse('MAIN.STEP3.ROW');

		}

	}

	$xtpl->parse("MAIN.STEP3");

}
elseif($step == 4)
{

	try
	{
		$config['db_port'] = empty($config['mysqlport']) ? '' : ';port='.$config['mysqlport'];
		$db = new DB('mysql:host='.$config['mysqlhost'].$dbc_port.';dbname='.$config['mysqldb'], $config['mysqluser'], $config['mysqlpassword']);
	}
	catch (PDOException $e)
	{
		$excursion->reportError('install_error_sql', 'db_host');
	}

	if($action == 'send')
	{
	
		$insert['username'] = $excursion->import('username', 'P', 'TXT');
		$pwd = $excursion->import('password', 'P', 'TXT');
		$pwd2 = $excursion->import('password2', 'P', 'TXT');
		$insert['email'] = $excursion->import('email', 'P', 'TXT');
		$insert['SQ_Index'] = $excursion->import('sq', 'P', 'INT');
		$insert['SQ_Answer'] = $excursion->import('sq_answer', 'P', 'TXT');
		
		$user_exists = (bool)$db->query("SELECT id FROM members WHERE username = ? LIMIT 1", array($insert['username']))->fetch();
		$email_exists = (bool)$db->query("SELECT id FROM members WHERE email = ? LIMIT 1", array($insert['email']))->fetch();
		$config['disablereg'] = $db->query("SELECT value FROM config WHERE title='disablereg'")->fetchColumn();
		$config['disableval'] = $db->query("SELECT value FROM config WHERE title='disableval'")->fetchColumn();
		$config['valnew'] = $db->query("SELECT value FROM config WHERE title='valnew'")->fetchColumn();
		$totalusers = $db->countRows('members');
			
		if ($user_exists) $excursion->reportError('reg_un_exists');
		if (preg_match('/&#\d+;/', $insert['username']) || preg_match('/[<>#\'"\/]/', $insert['username'])) $excursion->reportError('reg_un_format');
		if (mb_strlen($insert['username']) < 2) $excursion->reportError('reg_un_length');
		if (mb_strlen($pwd) < 4) $excursion->reportError('reg_pwd_length');
		if ($pwd != $pwd2) $excursion->reportError('reg_pwd_nomatch');
		if (mb_strlen($insert['email']) < 10) $excursion->reportError('reg_email_length');	
		if ($email_exists) $excursion->reportError('reg_email_exists');
		if (!filter_var($insert['email'], FILTER_VALIDATE_EMAIL )) $excursion->reportError('reg_email_format');
		if (mb_strlen($insert['SQ_Answer']) < 2) $excursion->reportError('reg_sq_length');
		
		if(!$excursion->error_found())
		{
			$pwd = md5($pwd);
			$insert['groupid'] = 4;			
			$insert['password'] = $pwd;
			$insert['regdate'] = (int)time();
			$insert['token'] = $excursion->generateToken(16);

			$db->insert('members', $insert);
			
			$config_contents = file_get_contents('config.php');
			$config_contents = preg_replace("#^\\\$config\['new_install'\]\s*=\s*.*?;#m", "\$config['new_install'] = 5;", $config_contents);
			file_put_contents('config.php', $config_contents);
			header('Location: install.php');
			
		}
		
	}

	$xtpl->assign(array(
		'FORM_USERNAME' => $excursion->inputbox('text', 'username', $insert['username'], array('size' => 24, 'maxlength' => 100)),
		'FORM_PASSWORD' => $excursion->inputbox('password', 'password', '', array('size' => 8, 'maxlength' => 32)),
		'FORM_REPEAT_PASSWORD' => $excursion->inputbox('password', 'password2', '', array('size' => 8, 'maxlength' => 32)),
		'FORM_EMAIL' => $excursion->inputbox('text', 'email', $insert['email'], array('size' => 24, 'maxlength' => 64)),
		'QUESTIONS' => $excursion->selectbox_security_questions($insert['SQ_Index'], 'sq'),
		'FORM_SQ_ANSWER' => $excursion->inputbox('text', 'sq_answer', $insert['SQ_Answer'], array('size' => 24, 'maxlength' => 64))
	));

	$xtpl->parse("MAIN.STEP4");
	
}
elseif($step == 5)
{

	$xtpl->parse("MAIN.STEP5");
	
}
else
{

	// error
	
}

$xtpl->assign('STEP', $step);

$excursion->display_messages($xtpl);

$xtpl->parse('MAIN');
$xtpl->out('MAIN');

function install_extensions($selected_extensions, $is_module = FALSE)
{
	global $config, $excursion;
	$path = 'plugins';
	$ret = array();
	
	$extensions = array();
	foreach ($selected_extensions as $name)
	{
		$info = $excursion->infoget("$path/$name/$name.config.php", 'PLUGIN_CONFIG');
		$order = isset($info['Order']) ? (int) $info['Order'] : $config['plugin_default_order'];
		$extensions[$order][] = $name;
	}
	
	foreach ($extensions as $grp)
	{
		foreach ($grp as $name)
		{
			$ret[] = $name;
		}
	}
	
	return $ret;
}

function plugin_add($hook_bindings, $name, $title)
{

	global $db, $xtpl, $lang, $excursion, $member;

	if (empty($title))
	{
		$title = $name;
	}

	$insert_rows = array();
	foreach ($hook_bindings as $binding)
	{
	
		$insert_rows[] = array(
			'hook' => $binding['hook'],
			'code' => $name,
			'owner' => 'plug',
			'part' => $binding['part'],
			'file' => empty($binding['file']) ? "$name/$name.{$binding['part']}.php" : $name . '/' . $binding['file'],
			'active' => 1
		);
		
	}
	$db->insert('plugins', $insert_rows);
}

function config_add($name, $options, $is_module = false, $category = '', $donor = '')
{
	global $config, $excursion, $db;
	$cnt = count($options);
	$type = 'plug';

	if (!$cnt)
	{
		return false;
	}

	$option_set = array();
	for ($i = 0; $i < $cnt; $i++)
	{
		$opt = $options[$i];
		$option_set[] = array(
			'part' => $name, 
			'title' => $opt['name'],
			'order' => isset($opt['order']) ? $opt['order'] : str_pad($i, 2, 0, STR_PAD_LEFT), 
			'type' => (int) $opt['type'],
			'value' => $opt['default'], 
			'default' => $opt['default'],
			'variants' => $opt['variants'],
			'text' => $opt['text']
		);
	}

	$ins_cnt = $db->insert('config', $option_set);
	return $ins_cnt == $cnt;
}

function plugin_install($name, $is_module = false, $update = false, $force_update = false)
{
	global $config, $lang, $user, $excursion, $db;

	$path = "plugins/$name";
	$ignore_parts = array('options', 'install', 'config', 'uninstall');
	
	$excursion->message($excursion->rc('ext_installing', array(
			'type' => $lang['Plugin'],
			'name' => $name
		)));

	$setup_file = $path . "/$name.config.php";
	$options_file = $path . "/$name.options.php";
	if (!file_exists($setup_file))
	{
		$excursion->reportError($excursion->rc('ext_setup_not_found', array('path' => $setup_file)));
		return false;
	}

	$old_ext_format = false;

	$info = $excursion->infoget($setup_file, 'PLUGIN_CONFIG');
	if ($info === false)
	{
		$excursion->reportError('ext_invalid_format');
		return false;
	}

	$hook_bindings = array();
	$dp = opendir($path);
	while ($f = readdir($dp))
	{
		if (preg_match("#^$name(\.([\w\.]+))?.php$#", $f, $mt)
			&& !in_array($mt[2], $ignore_parts))
		{
			$part_info = $excursion->infoget($path . "/$f", 'PLUGIN');
			if ($part_info)
			{
				if (empty($part_info['Hooks']))
				{
					$hooks = array('standalone');
				}
				else
				{
					$hooks = explode(',', $part_info['Hooks']);
					$hooks = is_array($hooks) ? array_map('trim', $hooks) : array();
				}
				if (empty($part_info['Order']))
				{
					$order = $config['plugin_default_order'];
				}
				else
				{
					$order = array_map('trim', explode(',', $part_info['Order']));
					if (count($order) == 1 || count($order) < count($hooks))
					{
						$order = (int) $order[0];
					}
				}
				$i = 0;
				foreach ($hooks as $hook)
				{
					$hook_bindings[] = array(
						'part' => empty($mt[2]) ? 'main' : $mt[2],
						'file' => $f,
						'hook' => $hook,
						'order' => isset($order[$i]) ? (int) $order[$i] : $order
					);
					++$i;
				}
			}
		}
	}
	closedir($dp);
	$bindings_cnt = plugin_add($hook_bindings, $name, $info['Name']);
	$excursion->message($excursion->rc('ext_bindings_installed', array('cnt' => $bindings_cnt)));

	$info_cfg = $excursion->infoget($options_file, 'PLUGIN_OPTIONS');
	$options = $excursion->parseConfig($info_cfg);

	if (count($options) > 0)
	{
		if (config_add($name, $options, $is_module))
		{
			$excursion->message('ext_config_installed');
		}
		else
		{
			$excursion->reportError('ext_config_error');
			return false;
		}
	}
	
	if (file_exists("plugins/".$name."/setup/install.sql"))
	{

		$sql_err = $db->runScript(
			file_get_contents("plugins/".$name."/setup/install.sql"));
		if (empty($sql_err))
		{
			$excursion->message($excursion->rc('ext_executed_sql', array('ret' => 'OK')));
		}
		else
		{
			$excursion->reportError($excursion->rc('ext_executed_sql', array('ret' => $sql_err)));
			return false;
		}
	}

	return true;
}
function install_config_replace(&$file_contents, $config_name, $config_value)
{
	$file_contents = preg_replace("#^\\\$config\['$config_name'\]\s*=\s*'.*?';#m",
		"\$config['$config_name'] = '$config_value';", $file_contents);
	file_put_contents('config.php', $file_contents);
}
 
?>