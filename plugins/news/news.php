<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=index.tags
[END_PLUGIN]
==================== */

$sql = $db->query("SELECT * FROM pages WHERE cat = 'news' ORDER BY id DESC LIMIT 10");
while ($row = $sql->fetch())
{

	$xtpl->assign(array(
		'NEWS_ID' => (int) $row['id'],
		'NEWS_TITLE' => $row['title'],
		'NEWS_OWNER' => $excursion->generateUser($row['id']),
		'NEWS_DATE' => date($config['date_medium'], $row['date']),
		'NEWS_TEXT' => $row['text']
	));
	$xtpl->parse('MAIN.NEWS');	
	
}

?>