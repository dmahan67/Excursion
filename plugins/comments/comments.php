<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=page.tags
[END_PLUGIN]
==================== */

$totalcom = $db->query("SELECT COUNT(*) FROM comments WHERE area = 'page' AND area_id = '".$row['id']."'")->fetchColumn();

$sql_com = $db->query("SELECT * FROM comments WHERE area = 'page' AND area_id = '".$row['id']."' ORDER BY id ASC LIMIT 10");
while ($com = $sql_com->fetch())
{

	if($user['group'] == '4' || $user['id'] == $com['userid'])
	{
	
		$delete_url = $excursion->url('page', 'id='.$row['id'].'&action=delcom&com='.$com['id'].'');
		$com_admin = $excursion->rc('link_deletecom', array('url' => $delete_url));
	
	}

	$xtpl->assign(array(
		'COM_ID' => (int) $com['id'],
		'COM_OWNER' => $excursion->generateUser($com['userid']),
		'COM_AVATAR' => $excursion->buildAvatar($com['userid'], 'avatar avatar-60 photo'),
		'COM_DATE' => date($config['date_medium'], $com['date']),
		'COM_TEXT' => $com['text'],
		'COM_ADMIN' => $com_admin
	));
	$xtpl->parse('MAIN.COMMENTS.ROW');	
	
}

if($user['group'] >= 3)
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
	
	if($user['group'] == '4' || $user['id'] == $row_delcom['userid'])
	{
	
		$db->delete('comments', "id=$com");
		header('Location: page.php?id='.$row['id']);
	
	}
	else
	{
	
		header('Location: message.php?id=105');
	
	}

}
if($action == 'send' && $user['group'] >= 3)
{

	$insert['area'] = 'page';
	$insert['area_id'] = $row['id'];
	$insert['userid'] = (int)$user['id'];
	$insert['date'] = (int)time();
	$insert['text'] = $excursion->import('text', 'P', 'HTM');
	
	if (mb_strlen($insert['text']) < 4) $error .= $lang['page_error_text_length'].'<br />';

	if(empty($error))
	{
	
		$db->insert('comments', $insert);
		$id = $db->lastInsertId();
		
		header('Location: page.php?id='.$row['id'].'#com-'.$id.'');
		
	}
	if(!empty($error))
	{
	
		$xtpl->assign(array(
			'ERRORS_TEXT' => $error
		));
		$xtpl->parse('MAIN.COMMENTS.ERRORS');
		
	}
	
}

$xtpl->assign(array('COM_COUNT' => (int) $totalcom));

$xtpl->parse('MAIN.COMMENTS');

$xtpl->assign(array('COMMENTS' => (int) $totalcom));

?>