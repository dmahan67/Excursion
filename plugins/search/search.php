<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=standalone
[END_PLUGIN]
==================== */

$query = $excursion->import('query', 'R', 'TXT');
$query = $db->prep($query);

$page = (!empty($page) ? $page : '1');
$pagination->setLink("plugin.php?p=search&page=%s");
$pagination->setPage($page);
$pagination->setSize($config['maxpages']);

if(!empty($query))
{

	$words = explode(' ', $query);
	$sqlsearch = '%'.implode('%', $words).'%';
	if (mb_strlen($query) < $config['plugin']['search']['minsigns'])
	{
		$excursion->reportError('search_error_querytooshort');
	}
	if (count($words) > $config['plugin']['search']['maxwords'])
	{
		$excursion->reportError('search_error_toomanywords');
	}
	
	$where_and['state'] = "state = '1'";
	$where_or['title'] = "title LIKE '".$db->prep($sqlsearch)."'";
	$where_or['text'] = "text LIKE '".$db->prep($sqlsearch)."'";
	$where_or = array_diff($where_or, array(''));
	count($where_or) || $where_or['title'] = "title LIKE '".$db->prep($sqlsearch)."'";
	$where_and['or'] = '('.implode(' OR ', $where_or).')';
	$where_and = array_diff($where_and, array(''));
	$where = implode(' AND ', $where_and);
		
	$sql = $db->query("
		SELECT *
		FROM pages
		WHERE $where
		ORDER BY date ASC " . $pagination->getLimitSql()
	);
		
	$items = $sql->rowCount();
	$totalitems[] = $db->query('SELECT FOUND_ROWS()')->fetchColumn();
	$jj = 0;

	foreach ($sql->fetchAll() as $row)
	{
		$DateTime = new DateTime(date($config['date_medium'], $row['date']));
		
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

		$xtpl->parse('MAIN.RESULTS.ROW');
		$jj++;
	}
	if (!empty($query))
	{
		$xtpl->assign('COUNT', $jj);
		$xtpl->parse('MAIN.RESULTS');
	}
	unset($where_and, $where_or, $where);	
}

$pagination->setTotalRecords($items);

$navigation = $pagination->create_links();

$xtpl->assign(array(
	'FORM_ACTION' => $excursion->url('plugin', 'p=search'),
	'FORM_TEXT' => $excursion->inputbox('text', 'query', htmlspecialchars($query), 'size="32"'),
	'PAGINATION' => $navigation
));

$excursion->display_messages($xtpl);

?>