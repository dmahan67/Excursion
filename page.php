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

$c = $excursion->import('c','G','TXT');

$ex['location'] = 'page';

/* === Hook === */
foreach ($excursion->Hook('page.actions') as $pl)
{
	include $pl;
}
/* ===== */

if($action == 'remove')
{

	/* === Hook === */
	foreach ($excursion->Hook('page.remove') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$page_cat = $db->query("SELECT cat FROM pages WHERE id='$id' LIMIT 1")->fetchColumn();
	
	list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('page', $page_cat);
	$excursion->blockAuth($user['auth_admin']);
	
	$sql_page_delete = $db->delete('pages', "id=$id");
	header('Location: list.php?c='.$page_cat.'');

}
if($action == 'queue')
{
	
	/* === Hook === */
	foreach ($excursion->Hook('page.queue.first') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$page_state = $db->query("SELECT state FROM pages WHERE id='$id' LIMIT 1")->fetchColumn();
	$page_cat = $db->query("SELECT cat FROM pages WHERE id='$id' LIMIT 1")->fetchColumn();
	
	list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('page', $page_cat);
	$excursion->blockAuth($user['auth_admin']);

	if($page_state > 0 && $user['auth_admin'])
	{
	
		/* === Hook === */
		foreach ($excursion->Hook('page.queue') as $pl)
		{
			include $pl;
		}
		/* ===== */
	
		$insert['state'] = 0;
	
		$sql_update_page_state = $db->update('pages', $insert, 'id=?', array($id));
		header('Location: list.php?c='.$page_cat.'');
		
	}
	else
	{
	
		$excursion->reportError('error_page_inactive');
	
	}

}

require_once 'core/header.php';

if($m == 'edit')
{

	$xtpl = new XTemplate('themes/'.$user['theme'].'/page.edit.xtpl');
	
	/* === Hook === */
	foreach ($excursion->Hook('page.edit.action.first') as $pl)
	{
		include $pl;
	}
	/* ===== */
	
	if($action == 'send' && $user['group'] == 4)
	{
	
		/* === Hook === */
		foreach ($excursion->Hook('page.edit.send') as $pl)
		{
			include $pl;
		}
		/* ===== */
	
		$insert['title'] = $excursion->import('title', 'P', 'TXT');
		$insert['desc'] = $excursion->import('desc', 'P', 'TXT');
		$insert['cat'] = $excursion->import('category', 'P', 'TXT');
		$insert['page_file'] = intval($excursion->import('pagefile', 'P', 'INT'));
		$insert['page_url'] = $excursion->import('pageurl', 'P', 'TXT');
		$insert['text'] = $excursion->import('text', 'P', 'HTM');
		
		if (mb_strlen($insert['title']) < 4) $excursion->reportError('error_title_length');
		if (mb_strlen($insert['cat']) < 2) $excursion->reportError('page_error_cat_missing');
		if (mb_strlen($insert['text']) < 4) $excursion->reportError('error_text_length');
		
		if(!$excursion->error_found())
		{
		
			$db->update('pages', $insert, 'id=?', array($id));
			header('Location: page.php?id='.$id.'');
			
		}
	
	}
	if($action == 'send' && $user['group'] != 4)
	{
	
		header('Location: message.php?id=105');
		
	}
	
	$sql = $db->query("SELECT * FROM pages WHERE id = $id LIMIT 1");
	$row = $sql->fetch();
	
	list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('page', $row['cat']);
	$excursion->blockAuth($user['auth_write']);
	
	$xtpl->assign(array(
		'FORM_ACTION' => $excursion->url('page', 'id='.$id.'&m=edit&action=send'),
		'FORM_TITLE' => $excursion->inputbox('text', 'title', $row['title'], array('size' => '64', 'maxlength' => '255')),
		'FORM_DESC' => $excursion->inputbox('text', 'desc', $row['desc'], array('size' => '64', 'maxlength' => '255')),
		'FORM_CAT' => $excursion->selectbox_categories($row['cat'], 'category'),
		'FORM_PAGEFILE' => $excursion->selectbox($row['page_file'], 'pagefile', range(0, 2), array($lang['no'], $lang['yes'], $lang['members_only']), false),
		'FORM_PAGEURL' => $excursion->inputbox('text', 'pageurl', $row['page_url'], array('size' => '64', 'maxlength' => '255')),
		'FORM_TEXT' => $excursion->textarea('text', $row['text'], 24, 120, '', 'input_textarea_editor')
	));
	
	/* === Hook === */
	foreach ($excursion->Hook('page.edit.tags') as $pl)
	{
		include $pl;
	}
	/* ===== */

}
if($m == 'add')
{

	list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('page', $c);
	$excursion->blockAuth($user['auth_write']);

	$ex['location'] = 'page.add';

	$xtpl = new XTemplate('themes/'.$user['theme'].'/page.add.xtpl');

	if($action == 'send')
	{
		
		/* === Hook === */
		foreach ($excursion->Hook('page.add.send') as $pl)
		{
			include $pl;
		}
		/* ===== */
		
		$insert['title'] = $excursion->import('title', 'P', 'TXT');
		$insert['desc'] = $excursion->import('desc', 'P', 'TXT');
		$insert['cat'] = $excursion->import('category', 'P', 'TXT');
		$insert['page_file'] = intval($excursion->import('pagefile', 'P', 'INT'));
		$insert['page_url'] = $excursion->import('pageurl', 'P', 'TXT');
		$insert['text'] = $excursion->import('text', 'P', 'HTM');
		$insert['owner'] = (int)$user['id'];
		$insert['date'] = (int)time();
		$insert['state'] = (int)1;
		
		if (mb_strlen($insert['title']) < 4) $excursion->reportError('error_title_length');
		if (mb_strlen($insert['cat']) < 2) $excursion->reportError('page_error_cat_missing');
		if (mb_strlen($insert['text']) < 4) $excursion->reportError('error_text_length');
		
		if(!$excursion->error_found())
		{
		
			$db->insert('pages', $insert);
			$id = $db->lastInsertId();
			
			header('Location: page.php?id='.$id.'');
			
		}
	
	}
	
	$xtpl->assign(array(
		'FORM_ACTION' => $excursion->url('page', 'm=add&action=send'),
		'FORM_TITLE' => $excursion->inputbox('text', 'title', $insert['title'], array('size' => '64', 'maxlength' => '255')),
		'FORM_DESC' => $excursion->inputbox('text', 'desc', $insert['desc'], array('size' => '64', 'maxlength' => '255')),
		'FORM_CAT' => $excursion->selectbox_categories($c, 'category'),
		'FORM_PAGEFILE' => $excursion->selectbox($insert['pagefile'], 'pagefile', range(0, 2), array($lang['no'], $lang['yes'], $lang['members_only']), false),
		'FORM_PAGEURL' => $excursion->inputbox('text', 'pageurl', $insert['pageurl'], array('size' => '64', 'maxlength' => '255')),
		'FORM_TEXT' => $excursion->textarea('text', $insert['text'], 24, 120, '', 'input_textarea_editor'),
	));
	
	/* === Hook === */
	foreach ($excursion->Hook('page.add.tags') as $pl)
	{
		include $pl;
	}
	/* ===== */

}
if((isset($id) && $id > 0) && empty($m))
{

	$xtpl = new XTemplate('themes/'.$user['theme'].'/page.xtpl');
	
	$sql = $db->query("SELECT * FROM pages WHERE id = $id LIMIT 1");
	$row = $sql->fetch();
	
	list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('page', $row['cat']);
	$excursion->blockAuth($user['auth_read']);
	
	if($row['state'] == '0')
	{
	
		if($user['group'] != '4' || $row['owner'] != $user['id'])
		{
	
			header('Location: message.php');
			
		}
	
	}
	
	$xtpl->assign(array(
		'ID' => (int) $row['id'],
		'TITLE' => $row['title'],
		'DESC' => $row['desc'],
		'CAT' => $db->query("SELECT title FROM categories WHERE code='".$row['cat']."' LIMIT 1")->fetchColumn(),
		'CAT_CODE' => $row['cat'],
		'OWNER' => $excursion->generateUser($row['owner']),
		'AVATAR' => $excursion->buildAvatar($row['owner'], 'avatar'),
		'DATE' => date($config['date_medium'], $row['date']),
		'TEXT' => $row['text']
	));
	
	if($row['page_file'] > 0)
	{
		if($row['page_file'] == 1)
		{
			$xtpl->assign(array(
				'FILE_URL' => $row['page_url']
			));
			$xtpl->parse('MAIN.PAGE_FILE');
		}
		if($row['page_file'] == 2 && $user['id'] != 0)
		{
			$xtpl->assign(array(
				'FILE_URL' => $row['page_url']
			));
			$xtpl->parse('MAIN.PAGE_FILE');
		}
	}
	
	/* === Hook === */
	foreach ($excursion->Hook('page.tags') as $pl)
	{
		include $pl;
	}
	/* ===== */
	
}
if((empty($id) || $id == 0) && empty($m))
{

	header('Location: message.php');
	
}

$excursion->display_messages($xtpl);
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';
 
?>