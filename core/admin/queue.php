<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
$adm['location'] = 'queue';

if($action == 'approve')
{
	if (!$user['auth_write']) $excursion->reportError('error_insufficient_rights');
	
	if(!$excursion->error_found())
	{
		if($c == 'page')
		{
			if (!empty($id))
			{
				$insert['state'] = '1';
				$db->update(pages, $insert, "id=".$db->prep($id));
			}
		}
		elseif($c == 'user')
		{
			if (!empty($id))
			{
				$insert['groupid'] = '3';
				$db->update(members, $insert, "id=".$db->prep($id));
			}
		}

		header('Location: admin.php?m=queue');
	}
}

if($action == 'remove')
{
	if (!$user['auth_admin']) $excursion->reportError('error_insufficient_rights');
	if (empty($id)) $excursion->reportError('error_unknown');
	
	if(!$excursion->error_found())
	{
		$db->delete(pages, "id='".$db->prep($id)."'");
		header('Location: admin.php?m=queue');
	}
}

$xtpl = new XTemplate('themes/admin/queue.xtpl');

$queue['total_pages'] = $db->query("SELECT COUNT(*) FROM pages WHERE state='0'")->fetchColumn();
$queue['total_members'] = $db->query("SELECT COUNT(*) FROM members WHERE groupid='1'")->fetchColumn();

$sql = $db->query("SELECT * FROM pages WHERE state='0' ORDER BY date ASC");
foreach ($sql->fetchAll() as $row)
{
	$xtpl->assign(array(
		'ID' => $row['id'],
		'TITLE' => $row['title'],
		'OWNER' => $excursion->generateUser($row['owner']),
		'DATE' => date($config['date_medium'], $row['date']),
		'CAT' => $db->query("SELECT title FROM categories WHERE code='".$row['cat']."' LIMIT 1")->fetchColumn()
	));
	
	$xtpl->parse('MAIN.PAGE_ROW');	
}

$sql = $db->query("SELECT * FROM members WHERE groupid='1' ORDER BY regdate ASC");
foreach ($sql->fetchAll() as $row)
{
	$xtpl->assign(array(
		'ID' => $row['id'],
		'USER' => $excursion->generateUser($row['id']),
		'DATE' => date($config['date_medium'], $row['regdate'])
	));
	
	$xtpl->parse('MAIN.USER_ROW');	
}

$excursion->display_messages($xtpl);

$xtpl->parse('MAIN');
$xtpl->out('MAIN');
 
?>