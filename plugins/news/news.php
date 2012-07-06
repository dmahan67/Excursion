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

$sql = $db->query("SELECT * FROM pages WHERE $sql_stmt AND state > 0 ORDER BY date DESC LIMIT " . $config['plugin']['news']['maxpages']);
while ($row = $sql->fetch())
{

	$xtpl->assign(array(
		'NEWS_ID' => (int) $row['id'],
		'NEWS_TITLE' => $row['title'],
		'NEWS_OWNER' => $excursion->generateUser($row['owner']),
		'NEWS_DATE' => date($config['date_medium'], $row['date']),
		'NEWS_TEXT' => $row['text']
	));
	$xtpl->parse('MAIN.NEWS');	
	
}

?>