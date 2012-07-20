<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
session_start();

require_once 'core/database.php';

/* ========== CONNECT TO DATABASE ========== */

try
{
	$dbc_port = empty($config['mysqlport']) ? '' : ';port='.$config['mysqlport'];
	$db = new DB('mysql:host='.$config['mysqlhost'].$dbc_port.';dbname='.$config['mysqldb'], $config['mysqluser'], $config['mysqlpassword']);
}
catch (PDOException $e)
{
	die('Could not connect to database !<br />
		Please check your settings in the file config.php<br />
		MySQL error : '.$e->getMessage());
}

/* ========== Instance Classes ========== */

$excursion = new Excursion();
$member = new Members();
$pagination = new Pagination();

/* ======== Common Variables ======== */

$a = $excursion->import('a', 'G', 'ALP', 24);
$c = $excursion->import('c', 'G', 'ALP', 24);
$m = $excursion->import('m', 'G', 'ALP', 24);
$f = $excursion->import('f', 'G', 'ALP', 24);
$action = $excursion->import('action', 'G', 'ALP', 24);
$id = $excursion->import('id','G','INT');
$step = $excursion->import('step','G','INT');
$page = $excursion->import('page', 'G', 'INT');

/* ========== Guest/User ========== */

$user['id'] = 0;
$user['theme'] = $config['default_theme'];
$user['lang'] = $config['default_language'];
$user['timezone'] = 0;

if (isset($_SESSION['user_id']))
{

	$sql = $db->query("SELECT * FROM members WHERE id = ".$_SESSION['user_id']);

	if ($row = $sql->fetch())
	{

		$user['id'] = $row['id'];
		$user['name'] = $row['username'];
		$user['password'] = $row['password'];
		$user['email'] = $row['email'];
		$user['group'] = $row['groupid'];
		$user['lang'] = $row['lang'];
		$user['group_built'] = $excursion->generateGroup($row['groupid']);
		$user['pm'] = $db->query("SELECT COUNT(*) FROM pm WHERE touser = ".$user['id']." AND tostate = 0")->fetchColumn();
		$user['theme'] = ($config['forcetheme'] == 'yes' ? $config['default_theme'] : $row['theme']);
		$user['gender'] = $row['gender'];
		$user['birthdate'] = $row['birthdate'];
		$user['avatar'] = $row['avatar'];
		$user['avatar_built'] = $excursion->buildAvatar($row['id'], 'avatar');
		
	}
	
}

require_once $excursion->import_langfile('main', 'core', $user['lang']);
require_once $excursion->import_langfile($user['theme'], 'theme', $user['lang']);

require_once 'core/resources.php';

/* ========== Plugins & Configuration ========== */

if (!$plugins)
{

	$sql = $db->query("SELECT code, file, hook FROM plugins WHERE active = 1 ORDER BY hook ASC");
	$plugins_active = array();
	
	if ($sql->rowCount() > 0)
	{
	
		while ($row = $sql->fetch())
		{
	
			$plugins[$row['hook']][] = $row;
			$plugins_active[$row['code']] = true;
			$plugins[$row['code']]['installed'] = true;
			$plugins[$row['code']]['standalone'] = ($row['hook'] == 'standalone' ?  true : false);
	
		}
	
		$sql->closeCursor();
	
	}
	
}

$sql_config = $db->query("SELECT * FROM plugins");
while ($row = $sql_config->fetch())
{

	if(@file_exists("plugins/".$row['code']."/lang/".$row['code'].".lang.".$user['lang'].".php"))
	{
	
		require_once $excursion->import_langfile($row['code'], 'plug', $user['lang']);
		
	}

}

$sql_config = $db->query("SELECT * FROM config");
while ($row = $sql_config->fetch())
{
	if ($row['part'] == 'core')
	{
	
		$config[$row['title']] = $row['value'];
		
	}
	else
	{
	
		$config['plugin'][$row['part']][$row['title']] = $row['value'];
		
	}
	
}
$sql_config->closeCursor();
mb_internal_encoding('UTF-8');
$excursion->load_pageStructure();

/* ========== MAINTENANCE REDIRECT ========== */

if($config['maintenance']=='yes' && $ex['location']!='login' && $user['group']!=4)
{
	header('Location: login.php');
	exit;
}

/* ========== CHECKPOINTS ========== */

if($user['id'] > 0 && $user['group'] == 1)
{

	if (isset($_SESSION['user_id'])){
	
		session_destroy();
		header('Location: index.php');
		
	}
	
}

if($user['id'] > 0 && $user['group'] == 2)
{

	if (isset($_SESSION['user_id'])){
	
		session_destroy();
		header('Location: login.php');
		
	}
	
}

/* ========== Global Tags ========== */

/* === Hook === */
foreach ($excursion->Hook('global') as $pl)
{
	include $pl;
}
/* ===== */

$config['footer_tags'] .= $excursion->createTags('javascript', '', 'core/plugins/ckeditor/ckeditor.js', '');
$config['footer_tags'] .= $excursion->createTags('javascript', '', 'core/plugins/ckeditor/editor_themes.js', '');

$theme['dir'] = 'themes/'.$user['theme'];

?>