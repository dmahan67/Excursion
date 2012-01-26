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

/* ========== Guest/User ========== */

$user['id'] = 0;
$user['theme'] = $config['default_theme'];

if (isset($_SESSION['user_id']))
{

	$sql = $db->query("SELECT * FROM members WHERE id = ".$_SESSION['user_id']);

	if ($row = $sql->fetch())
	{

		$user['id'] = $row['id'];
		$user['name'] = $row['username'];
		$user['email'] = $row['email'];
		$user['group'] = $row['groupid'];
		$user['theme'] = $row['theme'];
		
	}
	
}

/* ========== Plugins ========== */

if (!$plugins)
{
	$sql = $db->query("SELECT code, file, hook FROM plugins
		WHERE active = 1 ORDER BY hook ASC");
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

?>