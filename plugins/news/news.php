<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=index.tags
[END_PLUGIN]
==================== */

list($user['auth_read'], $user['auth_write'], $user['isadmin']) = $excursion->checkAuth('plugin', 'news');
if($user['auth_read'])
{

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

		list($user['auth_read'], $user['auth_write'], $user['isadmin']) = $excursion->checkAuth('page', $row['cat']);
		
		if($user['auth_read'])
		{

			if ((int)$config['plugin']['news']['text_length'] > 0 && mb_strlen($row['text']) > $config['plugin']['news']['text_length'])
			{
				$row['text'] = ($excursion->truncate($row['text'], $config['plugin']['news']['text_length'], true)) . "";
			}

			/* === Hook === */
			foreach ($excursion->Hook('news.row') as $pl)
			{
				include $pl;
			}
			/* ===== */
			
			$DateTime = new DateTime(date($config['date_medium'], $row['date']));
			$shortMonth = array('JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC');
			$numberMonth = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
			
			$xtpl->assign(array(
				'NEWS_ID' => (int) $row['id'],
				'NEWS_TITLE' => $row['title'],
				'NEWS_CAT' => $db->query("SELECT title FROM categories WHERE code='".$row['cat']."' LIMIT 1")->fetchColumn(),
				'NEWS_CAT_CODE' => $row['cat'],
				'NEWS_OWNER' => $excursion->generateUser($row['owner']),
				'NEWS_DATE' => date($config['date_medium'], $row['date']),
				'NEWS_DATE_MONTH' => $DateTime->format( 'F' ),
				'NEWS_DATE_MONTH_SHORT' => str_replace($numberMonth, $shortMonth, $DateTime->format( 'm' )),
				'NEWS_DATE_DAY' => $DateTime->format( 'd' ),
				'NEWS_TEXT' => $row['text'],
				'NEWS_TEXT_CUT' => $excursion->truncate($row['text'], 200)
			));
			$xtpl->parse('MAIN.NEWS.ROW');	
		}
		
	}

	$navigation = $pagination->create_links();
	$xtpl->assign('PAGINATION', $navigation);

	$xtpl->parse('MAIN.NEWS');
	
}

?>