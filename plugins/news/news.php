<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=index.tags
[END_PLUGIN]
==================== */

$categories = explode(',', $config['plugin']['news']['category']);
$count = 0;
foreach ($categories as $v)
{

	$v = explode('|', trim($v));
	
	if($count == 0)
	{
	
		$sql_stmt .= "cat='$v[0]'";
		
	}
	else
	{
	
		$sql_stmt .= " OR cat='$v[0]'";
	
	}
	
	$count++;
	
}

$totalpage = $db->query("SELECT COUNT(*) FROM pages WHERE $sql_stmt AND state > 0")->fetchColumn();
if(empty($page)){$page = 1;}
$pagination->setLink("index.php?page=%s");
$pagination->setPage($page);
$pagination->setSize($config['plugin']['news']['maxpages']);
$pagination->setTotalRecords($totalpage);

$sql = $db->query("SELECT * FROM pages WHERE $sql_stmt AND state > 0 ORDER BY date DESC " . $pagination->getLimitSql());
while ($row = $sql->fetch())
{

	if ((int)$config['plugin']['news']['text_length'] > 0 && mb_strlen($row['text']) > $config['plugin']['news']['text_length'])
	{
		$row['text'] = ($excursion->truncate($row['text'], $config['plugin']['news']['text_length'], true)) . "";
	}

	$xtpl->assign(array(
		'NEWS_ID' => (int) $row['id'],
		'NEWS_TITLE' => $row['title'],
		'NEWS_OWNER' => $excursion->generateUser($row['owner']),
		'NEWS_DATE' => date($config['date_medium'], $row['date']),
		'NEWS_TEXT' => $row['text']
	));
	$xtpl->parse('MAIN.NEWS.ROW');	
	
}

$navigation = $pagination->create_links();
$xtpl->assign('PAGINATION', $navigation);

$xtpl->parse('MAIN.NEWS');

?>