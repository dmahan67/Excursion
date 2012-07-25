<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=page.tags
[END_PLUGIN]
==================== */

list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('plugin', 'comments');
if($user['auth_read'])
{

	$totalcom = $db->query("SELECT COUNT(*) FROM comments WHERE area = 'page' AND area_id = '".$row['id']."'")->fetchColumn();
	$page = (!empty($page) ? $page : '1');
	$pagination->setLink("page.php?id=$id&page=%s");
	$pagination->setPage($page);
	$pagination->setSize($config['plugin']['comments']['maxcomments']);
	$pagination->setTotalRecords($totalcom);

	$sql_com = $db->query("SELECT * FROM comments WHERE area = 'page' AND area_id = '".$row['id']."' ORDER BY date DESC " . $pagination->getLimitSql());
	while ($com = $sql_com->fetch())
	{

		if($user['auth_admin'] || $user['id'] == $com['userid'])
		{
		
			$delete_url = $excursion->url('page', 'id='.$row['id'].'&action=delcom&com='.$com['id'].'');
			$com_admin = $excursion->rc('link_deletecom', array('url' => $delete_url));
		
		}
		
		/* === Hook === */
		foreach ($excursion->Hook('comments.row') as $pl)
		{
			include $pl;
		}
		/* ===== */

		$xtpl->assign(array(
			'COM_ID' => (int) $com['id'],
			'COM_OWNER' => $excursion->generateUser($com['userid']),
			'COM_AVATAR' => $excursion->buildAvatar($com['userid'], 'avatar avatar-60 photo'),
			'COM_AVATAR_URL' => $db->query("SELECT avatar FROM members WHERE id='".$com['userid']."' LIMIT 1")->fetchColumn(),
			'COM_DATE' => date($config['date_medium'], $com['date']),
			'COM_TEXT' => $com['text'],
			'COM_ADMIN' => $com_admin
		));
		$xtpl->parse('MAIN.COMMENTS.ROW');	
		
	}

	if($user['auth_write'])
	{

		$xtpl->assign(array(
			'FORM_ACTION' => $excursion->url('page', 'id='.$row['id'].'&action=send'),
			'FORM_TEXT' => $excursion->textarea('text', $insert['text'], 24, 120, '', 'input_textarea_minieditor')
		));
		
		$xtpl->parse('MAIN.COMMENTS.REPLY');

	}

	if($action == 'delcom')
	{

		$com = $excursion->import('com','G','INT');

		$sql_delcom = $db->query("SELECT * FROM comments WHERE id = ".$com." LIMIT 1");
		$row_delcom = $sql_delcom->fetch();
		
		if($user['auth_admin'] || $user['id'] == $row_delcom['userid'])
		{
		
			$db->delete('comments', "id=$com");
			header('Location: page.php?id='.$row['id']);
		
		}
		else
		{
		
			$excursion->reportError('error_insufficient_rights');
		
		}

	}
	if($action == 'send' && $user['auth_write'])
	{

		$insert['area'] = 'page';
		$insert['area_id'] = $row['id'];
		$insert['userid'] = (int)$user['id'];
		$insert['date'] = (int)time();
		$insert['text'] = $excursion->import('text', 'P', 'HTM');
		
		if (mb_strlen($insert['text']) < 4) $excursion->reportError('error_text_length');
		
		/* === Hook === */
		foreach ($excursion->Hook('comments.send') as $pl)
		{
			include $pl;
		}
		/* ===== */
				
		if(!$excursion->error_found())
		{

			$db->insert('comments', $insert);
			$id = $db->lastInsertId();
			
			header('Location: page.php?id='.$row['id'].'#com-'.$id.'');
			
		}
		
	}

	$navigation = $pagination->create_links();

	$xtpl->assign(array(
		'COM_COUNT' => (int) $totalcom,
		'PAGINATION' => $navigation
	));

	$xtpl->parse('MAIN.COMMENTS');

	$xtpl->assign('COMMENTS', (int) $totalcom);

}

?>