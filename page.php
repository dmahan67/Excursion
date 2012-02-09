<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
require_once 'config.php';
require_once 'core/xtemplate.class.php';
require_once 'core/common.php';

$c = $excursion->import('c','G','TXT');

if($action == 'remove' && $user['group'] == 4)
{

	/* === Hook === */
	foreach ($excursion->Hook('page.remove.action') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$page_cat = $db->query("SELECT cat FROM pages WHERE id='$id' LIMIT 1")->fetchColumn();
	$sql_page_delete = $db->delete('pages', "id=$id");
	header('Location: list.php?c='.$page_cat.'');

}
if($action == 'queue' && $user['group'] == 4)
{

	/* === Hook === */
	foreach ($excursion->Hook('page.queue.action.first') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$page_state = $db->query("SELECT state FROM pages WHERE id='$id' LIMIT 1")->fetchColumn();
	$page_cat = $db->query("SELECT cat FROM pages WHERE id='$id' LIMIT 1")->fetchColumn();

	if($page_state > 0)
	{
	
		/* === Hook === */
		foreach ($excursion->Hook('page.queue.action.loop') as $pl)
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
	
		header('Location: message.php');
	
	}

}

require_once 'core/header.php';

if($m == 'edit' && $user['group'] == 4)
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
		foreach ($excursion->Hook('page.edit.action.loop') as $pl)
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
		
		if (mb_strlen($insert['title']) < 4) $error .= $lang['page_error_title_length'].'<br />';
		if (mb_strlen($insert['cat']) < 2) $error .= $lang['page_error_cat_missing'].'<br />';
		if (mb_strlen($insert['text']) < 4) $error .= $lang['page_error_text_length'].'<br />';
		
		if(empty($error))
		{
		
			$sql_update_page = $db->update('pages', $insert, 'id=?', array($id));
			
			header('Location: page.php?id='.$id.'');
			
		}
		if(!empty($error))
		{
		
			$xtpl->assign(array(
				'ERRORS_TEXT' => $error
			));
			$xtpl->parse('MAIN.ERRORS');
			
		}
	
	}
	if($action == 'send' && $user['group'] != 4)
	{
	
		header('Location: message.php?id=105');
		
	}
	
	$sql = $db->query("SELECT * FROM pages WHERE id = $id LIMIT 1");
	$row = $sql->fetch();
	
	$category .= "<select name='category' id='category'>";
	$category_sql = $db->query("SELECT * FROM categories ORDER BY title ASC");
	while ($cat = $category_sql->fetch())
	{
	
		if($cat['code'] == $row['cat']){ $selected = "selected='selected'"; }else{$selected = "";}
	
		$category .= "<option name='".$cat['code']."' value='".$cat['code']."' ".$selected.">".$cat['title']."</option>";
		
	}
	$category .= "</select>";
	
	$select_file .= "<select name='pagefile'>";
	if($row['page_file'] == 0){ $select_file0 = "<option value='0' selected='selected'>No</option>"; } else { $select_file0 = "<option value='0'>No</option>";}
	if($row['page_file'] == 1){ $select_file1 = "<option value='1' selected='selected'>Yes</option>"; } else { $select_file1 = "<option value='1'>Yes</option>";}
	if($row['page_file'] == 2){ $select_file2 = "<option value='2' selected='selected'>Members only</option>"; } else { $select_file2 = "<option value='2'>Members only</option>";}
	$select_file .= "".$select_file0."".$select_file1."".$select_file2."";
	$select_file .= "</select>";
	
	$xtpl->assign(array(
		'ID' => $row['id'],
		'TITLE' => $row['title'],
		'CAT' => $row['cat'],
		'DESC' => $row['desc'],
		'TEXT' => $row['text'],
		'PAGE_FILE' => $row['page_file'],
		'PAGE_URL' => $row['page_url'],
		'CATEGORY' => $category,
		'SELECT_FILE' => $select_file
	));
	
	/* === Hook === */
	foreach ($excursion->Hook('page.edit.action.tags') as $pl)
	{
		include $pl;
	}
	/* ===== */

}
if($m == 'add' && $user['group'] == 4)
{

	$xtpl = new XTemplate('themes/'.$user['theme'].'/page.add.xtpl');

	if($action == 'send' && $user['group'] == 4)
	{
	
		$insert['title'] = $excursion->import('title', 'P', 'TXT');
		$insert['desc'] = $excursion->import('desc', 'P', 'TXT');
		$insert['cat'] = $excursion->import('category', 'P', 'TXT');
		$insert['page_file'] = intval($excursion->import('pagefile', 'P', 'INT'));
		$insert['page_url'] = $excursion->import('pageurl', 'P', 'TXT');
		$insert['text'] = $excursion->import('text', 'P', 'HTM');
		$insert['owner'] = (int)$user['id'];
		$insert['date'] = (int)time();
		$insert['state'] = (int)1;
		
		if (mb_strlen($insert['title']) < 4) $error .= $lang['page_error_title_length'].'<br />';
		if (mb_strlen($insert['cat']) < 2) $error .= $lang['page_error_cat_missing'].'<br />';
		if (mb_strlen($insert['text']) < 4) $error .= $lang['page_error_text_length'].'<br />';
		
		if(empty($error))
		{
		
			$db->insert('pages', $insert);
			$id = $db->lastInsertId();
			
			header('Location: page.php?id='.$id.'');
			
		}
		if(!empty($error))
		{
		
			$xtpl->assign(array(
				'ERRORS_TEXT' => $error
			));
			$xtpl->parse('MAIN.ERRORS');
			
		}
	
	}
	if($action == 'send' && $user['group'] != 4)
	{
	
		header('Location: message.php?id=105');
		
	}	
	
	$category .= "<select name='category' id='category'>";
	$sql = $db->query("SELECT * FROM categories ORDER BY title ASC");
	while ($row = $sql->fetch())
	{
	
		if($row['code'] == $c){ $selected = "selected='selected'"; }else{$selected = "";}
	
		$category .= "<option name='".$row['code']."' value='".$row['code']."' ".$selected.">".$row['title']."</option>";
		
	}
	$category .= "</select>";
	
	$xtpl->assign(array('CATEGORY' => $category));

}
if($m == 'add' && $user['group'] != 4)
{
	
	header('Location: message.php?id=105');
	
}

if((isset($id) && $id > 0) && empty($m))
{

	$xtpl = new XTemplate('themes/'.$user['theme'].'/page.xtpl');
	
	$sql = $db->query("SELECT * FROM pages WHERE id = $id LIMIT 1");
	$row = $sql->fetch();
	
	$xtpl->assign(array(
		'ID' => (int) $row['id'],
		'TITLE' => $row['title'],
		'DESC' => $row['desc'],
		'CAT' => $db->query("SELECT title FROM categories WHERE code='".$row['cat']."' LIMIT 1")->fetchColumn(),
		'OWNER' => $excursion->generateUser($row['owner']),
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
	
}
if((empty($id) || $id == 0) && empty($m))
{

	header('Location: message.php');
	
}
	
$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';
 
?>