<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
$adm['location'] = 'home';
 
$xtpl = new XTemplate('themes/admin/home.xtpl');

$totalusers = $db->countRows(members);
$totalplugins = count(glob("plugins/*",GLOB_ONLYDIR));

$sql = $db->query("SELECT * FROM members ORDER BY id DESC LIMIT 5");
while ($row = $sql->fetch())
{

	$xtpl->assign(array(
		'ID' => (int) $row['id'],
		'USERNAME' => $excursion->generateUser($row['id']),
		'DATE' => date($config['date_medium'], $row['regdate']),
	));
	$xtpl->parse('MAIN.NEW_USERS');	
	
}

$sql_config = $db->query("SELECT * FROM config WHERE part = 'core' AND title='version'");
$config = $sql_config->fetch();

$cfg['version'] = $config['value'];

$xtpl->parse('MAIN');
$xtpl->out('MAIN');
 
?>