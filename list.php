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

$c = $excursion->import('c', 'G', 'TXT');

require_once 'core/header.php';

$xtpl = new XTemplate('themes/'.$user['theme'].'/list.xtpl');

if(!empty($c))
{

	$sql = $db->query("SELECT * FROM pages WHERE cat = '$c' AND state > 0 LIMIT 10");
	while ($row = $sql->fetch())
	{

		$xtpl->assign(array(
			'ID' => (int) $row['id'],
			'TITLE' => $row['title'],
			'DESC' => $row['desc'],
			'CAT' => $row['cat'],
			'OWNER' => $excursion->generateUser($row['owner']),
			'DATE' => date($config['date_medium'], $row['date']),
			'TEXT' => $row['text']
		));
		$xtpl->parse('MAIN.LIST');	
		
	}

}
else
{

	header("Location: message.php");

}

$sql_cat = $db->query("SELECT * FROM categories WHERE code = '$c' LIMIT 1");
$cat = $sql_cat->fetch();

$xtpl->assign(array(
	'ID' => (int) $cat['id'],
	'TITLE' => $cat['title'],
	'DESC' => $cat['desc'],
	'CODE' => $cat['code']
));

$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';
 
?>