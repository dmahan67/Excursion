<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
session_start();

require_once 'core/database.php';
require_once 'lang/'.$config['default_language'].'/lang.'.$config['default_language'].'.php';
require_once 'core/resources.php';
require_once 'core/classes.php';


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

/* ======== Common Variables ======== */

$m = $excursion->import('m', 'G', 'ALP', 24);
$a = $excursion->import('a', 'G', 'ALP', 24);
$action = $excursion->import('action', 'G', 'ALP', 24);
$id = $excursion->import('id','G','INT');
$step = $excursion->import('step','G','INT');

/* ========== Config Tags ========== */

$config['title'] = $db->query("SELECT value FROM config WHERE title='title'")->fetchColumn();
$config['subtitle'] = $db->query("SELECT value FROM config WHERE title='subtitle'")->fetchColumn();
$config['keywords'] = $db->query("SELECT value FROM config WHERE title='keywords'")->fetchColumn();
$config['forcetheme'] = $db->query("SELECT value FROM config WHERE title='forcetheme'")->fetchColumn();
$config['disablereg'] = $db->query("SELECT value FROM config WHERE title='disablereg'")->fetchColumn();
$config['disableval'] = $db->query("SELECT value FROM config WHERE title='disableval'")->fetchColumn();
$config['valnew'] = $db->query("SELECT value FROM config WHERE title='valnew'")->fetchColumn();
$config['maintenance'] = $db->query("SELECT value FROM config WHERE title='maintenance'")->fetchColumn();
$config['reason'] = $db->query("SELECT text FROM config WHERE title='maintenance'")->fetchColumn();

/* ========== Guest/User ========== */

$user['id'] = 0;
$user['theme'] = $config['default_theme'];
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
		$user['group_built'] = $excursion->generateGroup($row['groupid']);
		if($config['forcetheme'] == 'yes'){$user['theme'] = $config['default_theme'];}else{$user['theme'] = $row['theme'];}
		$user['gender'] = $row['gender'];
		$user['birthdate'] = $row['birthdate'];
		$user['avatar'] = $row['avatar'];
		$user['avatar_built'] = $excursion->buildAvatar($row['id'], 'avatar');
		
	}
	
}

require_once 'themes/'.$user['theme'].'/'.$user['theme'].'.lang.php';

/* ========== Plugins ========== */

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
	
		}
	
		$sql->closeCursor();
	
	}
	
}

/* ========== Global Tags ========== */

$theme['dir'] = 'themes/'.$user['theme'];

if($config['maintenance']=='yes' && $ex['location']!='login' && $user['group']!=4)
{

	header('Location: login.php');

}

$header_tags .= $excursion->createTags('css', 'validate', 'core/plugins/validate/jquery.validate.css', '');
$header_tags .= $excursion->createTags('javascript', 'validate', 'core/plugins/validate/jquery.validate.js', '');
$header_tags .= $excursion->createTags('javascript', 'validate.functions', 'core/plugins/validate/jquery.validation.functions.js', '');
$header_tags .= $excursion->createTags('javascript', 'validate.forms', 'core/plugins/validate/jquery.validate.forms.js', '');

$config['header_tags'] = $header_tags;

$footer_tags .= $excursion->createTags('javascript', '', 'core/plugins/ckeditor/ckeditor.js', '');
$footer_tags .= $excursion->createTags('javascript', '', 'core/plugins/ckeditor/editor_themes.js', '');

$config['footer_tags'] = $footer_tags;

?>