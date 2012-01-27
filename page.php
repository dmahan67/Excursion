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

require_once 'core/header.php';

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
	
		$category .= "<option name='".$row['code']."' value='".$row['code']."'>".$row['title']."</option>";
		
	}
	$category .= "</select>";
	
	$xtpl->assign(array('CATEGORY' => $category));

}
if($m == 'add' && $user['group'] != 4)
{
	
	header('Location: message.php?id=105');
	
}

if(isset($id) && $id > 0 && empty($m))
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