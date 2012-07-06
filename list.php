<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
require_once 'config.php';
require_once 'core/xtemplate.php';
require_once 'core/common.php';

$ex['location'] = 'list';

require_once 'core/header.php';

$xtpl = new XTemplate('themes/'.$user['theme'].'/list.xtpl');

$total_pages = $db->query("SELECT COUNT(*) FROM pages WHERE cat = '$c' AND state > 0")->fetchColumn();
$pagination->setLink("list.php?c=$c&page=%s");
$pagination->setPage($page);
$pagination->setSize($config['maxpages']);
$pagination->setTotalRecords($total_pages);

if(!empty($c))
{

	/* === Hook === */
	foreach ($excursion->Hook('list.first') as $pl)
	{
		include $pl;
	}
	/* ===== */
	
	$sql = $db->query("SELECT * FROM pages WHERE cat = '$c' AND state > 0 ORDER BY date DESC " . $pagination->getLimitSql());
	while ($row = $sql->fetch())
	{
	
		/* === Hook === */
		foreach ($excursion->Hook('list.loop.tags') as $pl)
		{
			include $pl;
		}
		/* ===== */

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
	
	/* === Hook === */
	foreach ($excursion->Hook('list.last') as $pl)
	{
		include $pl;
	}
	/* ===== */

}
else
{

	header("Location: message.php");

}

$sql_cat = $db->query("SELECT * FROM categories WHERE code = '$c' LIMIT 1");
$cat = $sql_cat->fetch();
$navigation = $pagination->create_links();

$xtpl->assign(array(
	'ID' => (int) $cat['id'],
	'TITLE' => $cat['title'],
	'DESC' => $cat['desc'],
	'CODE' => $cat['code'],
	'PAGINATION' => $navigation
));

/* === Hook === */
foreach ($excursion->Hook('list.tags') as $pl)
{
	include $pl;
}
/* ===== */

$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';
 
?>