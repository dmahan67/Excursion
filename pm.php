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

$ex['location'] = 'pm';
$excursion->checkAuth($user['group'], $ex['location']);

$title = $excursion->import('title', 'G', 'TXT');

require_once 'core/header.php';

if($m == 'send')
{

	if($action == 'send')
	{
	
		$insert['title'] = $excursion->import('title', 'P', 'TXT');
		$insert['text'] = $excursion->import('text', 'P', 'HTM');
		$insert['date'] = (int)time();
		$insert['touser'] = $excursion->import('touser', 'P', 'INT');
		$insert['fromuser'] = $user['id'];
		
		if (mb_strlen($insert['title']) < 2) $error .= $lang['page_error_title_length'].'<br />';
		if (mb_strlen($insert['text']) < 4) $error .= $lang['page_error_text_length'].'<br />';
		
		if(empty($error))
		{
		
			$db->insert('pm', $insert);
			$id = $db->lastInsertId();
			
			header('Location: pm.php');
			
		}
		if(!empty($error))
		{
		
			$xtpl->assign(array(
				'ERRORS_TEXT' => $error
			));
			$xtpl->parse('MAIN.ERRORS');
			
		}
	
	}

	$xtpl = new XTemplate('themes/'.$user['theme'].'/pm.send.xtpl');
	
	$insert['title'] = (!empty($title) ? $title : $insert['title']);
	
	$xtpl->assign(array(
		'FORM_ACTION' => $excursion->url('pm', 'm=send&action=send'),
		'FORM_TOUSER' => $excursion->inputbox('text', 'touser', $id, array('size' => '64', 'maxlength' => '255')),
		'FORM_TITLE' => $excursion->inputbox('text', 'title', $insert['title'], array('size' => '64', 'maxlength' => '255')),
		'FORM_TEXT' => $excursion->textarea('text', $insert['text'], 24, 120, '', 'input_textarea_minieditor')
	));

}
elseif($m == 'remove')
{

	/* === Hook === */
	foreach ($excursion->Hook('pm.remove') as $pl)
	{
		include $pl;
	}
	/* ===== */
	
	$msg = $excursion->import('msg', 'P', 'ARR');
	
	if (!is_array($msg))
	{
		header('Location: pm.php');
	}

	foreach($msg as $k => $v)
	{
		$msg[] = (int)$excursion->import($k, 'D', 'INT');
	}

	if (count($msg)>0)
	{
		$msg = '('.implode(',', $msg).')';
		$msg = str_replace('on,', '', $msg);
		$sql = $db->query("SELECT * FROM pm WHERE id IN $msg");
		while($row = $sql->fetch())
		{
			$id = $row['id'];
			if (($row['fromuser'] == $user['id'] && ($row['tostate'] == 3 || $row['tostate'] == 0)) ||
					($row['touser'] == $user['id'] && $row['fromstate'] == 3) ||
					($row['fromuser'] == $user['id'] && $row['touser'] == $user['id']))
			{
				$sql2 = $db->delete('pm', "id = $id");
			}
			elseif($row['fromuser'] == $user['id'] && ($row['tostate'] != 3 || $row['tostate'] != 0))
			{
				$sql2 = $db->update('pm', array('fromstate' => '3'), "id = $id");
			}
			elseif($row['touser'] == $user['id'] && $row['fromstate'] != 3)
			{
				$sql2 = $db->update('pm', array('tostate' => '3'), "id = $id");
			}
		}
		$sql->closeCursor();
	}

	header('Location: pm.php');

}
elseif($m == 'details')
{

	$xtpl = new XTemplate('themes/'.$user['theme'].'/pm.message.xtpl');
	
	$sql = $db->query("SELECT * FROM pm WHERE id = $id LIMIT 1");
	$row = $sql->fetch();
	
	$sql2 = $db->query("SELECT * FROM members WHERE id = ".$row['fromuser']." LIMIT 1");
	$row2 = $sql2->fetch();
	
	if($row['fromuser'] == $user['id'] || $row['touser'] == $user['id'])
	{
	
		$xtpl->assign(array(
			'ID' => (int) $row['id'],
			'TITLE' => $row['title'],
			'FROMUSER' => $excursion->generateUser($row['fromuser']),
			'AVATAR' => $excursion->buildAvatar($row['fromuser'], 'avatar photo'),
			'GROUP' => $excursion->generateGroup($row2['groupid']),
			'TOUSER' => $excursion->generateUser($row['touser']),
			'FROMUSER_ID' => $row['fromuser'],
			'TOUSER_ID' => $row['touser'],
			'DATE' => date($config['date_medium'], $row['date']),
			'TEXT' => $row['text'],
			'FROMSTATE' => $row['fromstate'],
			'TOSTATE' => $row['tostate']
		));
		
		if($row['fromuser'] == $user['id'])
		{
			$update['fromstate'] = 1;
		}
		if($row['touser'] == $user['id'])
		{
			$update['tostate'] = 1;
		}
		
		$sql_update_state = $db->update('pm', $update, 'id=?', array($id));
		
	}
	else
	{
	
		header('Location: message.php');
	
	}

}
else
{

	$xtpl = new XTemplate('themes/'.$user['theme'].'/pm.list.xtpl');
	
	$f = (!empty($f) ? $f : 'inbox');
	$page = (!empty($page) ? $page : '1');
	
	if($f == 'inbox'){$total_pm = $db->query("SELECT COUNT(*) FROM pm WHERE touser = '".$user['id']."'")->fetchColumn();}
	if($f == 'sentbox'){$total_pm = $db->query("SELECT COUNT(*) FROM pm WHERE fromuser = '".$user['id']."'")->fetchColumn();}

	$pagination->setLink("pm.php?f=$f&page=%s");
	$pagination->setPage($page);
	$pagination->setSize($config['maxpages']);
	$pagination->setTotalRecords($total_pm);
	
	if($f == 'inbox')
	{
	
		$sql = $db->query("SELECT * FROM pm WHERE touser = '".$user['id']."' AND tostate < 3 ORDER BY date DESC " . $pagination->getLimitSql());
	
	}
	if($f == 'sentbox')
	{
	
		$sql = $db->query("SELECT * FROM pm WHERE fromuser = '".$user['id']."' ORDER BY date DESC " . $pagination->getLimitSql());
	
	}
	
	while ($row = $sql->fetch())
	{

		$xtpl->assign(array(
			'ID' => $row['id'],
			'FROMUSER' => $excursion->generateUser($row['fromuser']),
			'TOUSER' => $excursion->generateUser($row['touser']),
			'TITLE' => $row['title'],
			'TEXT' => $row['text'],
			'DATE' => date($config['date_medium'], $row['date']),
			'FROMSTATE' => $row['fromstate'],
			'TOSTATE' => $row['tostate']
		));
		$xtpl->parse('MAIN.ROW');	
		
	}
	
	$xtpl->assign('PAGINATION', $navigation = $pagination->create_links());

}
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';
 
?>