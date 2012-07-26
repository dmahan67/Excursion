<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=user.details.tags
[END_PLUGIN]
==================== */

list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('plugin', 'useractivity');

if($user['auth_read'])
{

	$sql_activity = $db->query("SELECT * FROM comments ORDER BY id ASC LIMIT ". $config['plugin']['useractivity']['limit']);
	while ($act = $sql_activity->fetch())
	{
		$search_area = ($act['area'] == 'page' ? 'pages' : '');
		
		$sql_area = $db->query("SELECT * FROM ".$search_area." WHERE id = ".$act['area_id']." LIMIT 1");
		$area = $sql_area->fetch();
		
		$xtpl->assign(array(
			'ID' => $act['id'],
			'AREA' => $act['area'],
			'OWNER' => $excursion->generateUser($act['userid']),
			'TITLE' => $area['title'],
			'PAGE_ID' => $area['id'],
			'DATE' => date($config['date_medium'], $act['date'])
		));
		$xtpl->parse('MAIN.ACTIVITY_ROW');
		
	}
	
}

?>