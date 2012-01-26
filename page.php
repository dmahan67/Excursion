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

$id = (int)$_GET['id'];
$action = $_GET['action'];
$m = $_GET['m'];
$c = $_GET['c'];

require_once 'core/header.php';

if($m == 'add' && $user['group'] == 4)
{

	if($action == 'send' && $user['group'] == 4)
	{
	
		$insert['title'] = $_POST['title'];
		$insert['desc'] = $_POST['desc'];
		$insert['cat'] = $_POST['category'];
		$insert['page_file'] = (int)$_POST['pagefile'];
		$insert['page_url'] = $_POST['pageurl'];
		$insert['text'] = $_POST['text'];
		$insert['owner'] = (int)$user['id'];
		$insert['date'] = (int)time();
		$insert['state'] = (int)1;
		
		$db->insert('pages', $insert);
		$id = $db->lastInsertId();
		
		header('Location: page.php?id='.$id.'');
	
	}

	$xtpl = new XTemplate('themes/bootstrap/page.add.xtpl');
	
	
	
	$category .= "<select name='category' id='category'>";
	$sql = $db->query("SELECT * FROM categories ORDER BY title ASC");
	while ($row = $sql->fetch())
	{
	
		$category .= "<option name='".$row['code']."' value='".$row['code']."'>".$row['title']."</option>";
		
	}
	$category .= "</select>";
	
	$xtpl->assign(array('CATEGORY' => $category));

}

if(isset($id) && $id > 0 && empty($m))
{

	$xtpl = new XTemplate('themes/bootstrap/page.xtpl');
	
	$sql = $db->query("SELECT * FROM pages WHERE id = $id LIMIT 1");
	$row = $sql->fetch();
	
	$xtpl->assign(array(
		'ID' => (int) $row['id'],
		'TITLE' => $row['title'],
		'DESC' => $row['desc'],
		'CAT' => $db->query("SELECT title FROM categories WHERE code='".$row['cat']."' LIMIT 1")->fetchColumn(),
		'OWNER' => $excursion->generateUser($row['id']),
		'DATE' => date($config['date_medium'], $row['date']),
		'TEXT' => $row['text']
	));
	
	if($row['page_file'] > 0)
	{
		$xtpl->assign(array(
			'FILE_URL' => $row['page_url']
		));
		$xtpl->parse('MAIN.PAGE_FILE');
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