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

$ex['location'] = 'list';

require_once 'core/header.php';

$xtpl = new XTemplate('themes/'.$user['theme'].'/list.xtpl');

$page = (!empty($page) ? $page : '1');
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
		
		$DateTime = new DateTime(date($config['date_medium'], $row['date']));
		$shortMonth = array('JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC');
		$numberMonth = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

		$xtpl->assign(array(
			'ID' => (int) $row['id'],
			'TITLE' => $row['title'],
			'DESC' => $row['desc'],
			'CAT' => $db->query("SELECT title FROM categories WHERE code='".$row['cat']."' LIMIT 1")->fetchColumn(),
			'CAT_CODE' => $row['cat'],
			'OWNER' => $excursion->generateUser($row['owner']),
			'DATE' => date($config['date_medium'], $row['date']),
			'NEWS_DATE_MONTH' => $DateTime->format( 'F' ),
			'DATE_MONTH_SHORT' => str_replace($numberMonth, $shortMonth, $DateTime->format( 'm' )),
			'DATE_DAY' => $DateTime->format( 'd' ),
			'TEXT' => $row['text'],
			'TEXT_CUT' => $excursion->truncate($row['text'], 200)
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