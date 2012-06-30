<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
$adm['location'] = 'pages';

$xtpl = new XTemplate('themes/admin/pages.xtpl');

if($action == 'update')
{

	$rstructuretitle = $excursion->import('rstructuretitle', 'P', 'ARR');
	$rstructuredesc = $excursion->import('rstructuredesc', 'P', 'ARR');
	$rstructurepath = $excursion->import('rstructurepath', 'P', 'ARR');
	$rstructurecode = $excursion->import('rstructurecode', 'P', 'ARR');

	foreach ($rstructuretitle as $i => $k)
	{
		$oldrow = $db->query("SELECT id FROM categories WHERE id=".(int)$i)->fetch();
		$rstructure['title'] = $excursion->import($rstructuretitle[$i], 'D', 'TXT');
		$rstructure['desc'] = $excursion->import($rstructuredesc[$i], 'D', 'TXT');
		$rstructure['path'] = $excursion->import($rstructurepath[$i], 'D', 'TXT');
		$rstructure['code'] = $excursion->import($rstructurecode[$i], 'D', 'TXT');

		$db->update(categories, $rstructure, "id=".(int)$i);
	}

	header('Location: admin.php?m=pages');

}
if($action == 'save')
{

	$insert['title'] = $excursion->import('title','P','TXT');
	$insert['desc'] = $excursion->import('desc','P','TXT');
	$insert['path'] = $excursion->import('path','P','TXT');
	$insert['code'] = $excursion->import('code','P','TXT');

	if (empty($insert['title'])) $error .= $lang['admin_error_title_missing'].'<br />';
	if (empty($insert['path'])) $error .= $lang['admin_error_path_missing'].'<br />';
	if (empty($insert['code'])) $error .= $lang['admin_error_code_missing'].'<br />';

	if(empty($error))
	{
	
		$db->insert('categories', $insert);
		$id = $db->lastInsertId();
		
		header('Location: admin.php?m=pages');
		
	}
	if(!empty($error))
	{
	
		$xtpl->assign(array(
			'ERRORS_TEXT' => $error
		));
		$xtpl->parse('MAIN.ERRORS');
		
	}

}
if($action == 'remove')
{

	if(!empty($id)){

		$db->delete(categories, "id='".$db->prep($id)."'");

		header('Location: admin.php?m=pages');

	}

}

$sql = $db->query("SELECT * FROM categories ORDER BY path ASC");
$jj = 0;
foreach ($sql->fetchAll() as $row)
	{
	$structure_id = $row['id'];
	
		$xtpl->assign(array(
			'FORM_TITLE' => $excursion->inputbox('text', 'rstructuretitle['.$structure_id.']', $row['title'], 'maxlength="64"', 'input_text_medium'),
			'FORM_DESC' => $excursion->inputbox('text', 'rstructuredesc['.$structure_id.']', $row['desc'], 'maxlength="64"', 'input_text_medium'),
			'FORM_CODE' => $excursion->inputbox('text', 'rstructurecode['.$structure_id.']', $row['code'], 'maxlength="20"', 'input_text_custom'),
			'FORM_PATH' => $excursion->inputbox('text', 'rstructurepath['.$structure_id.']', $row['path'], 'maxlength="7"', 'input_text_small'),
			'ID' => $row['id'],
			'TITLE' => $row['title'],
			'DESC' => $row['desc'],
			'CODE' => $row['code'],
			'PATH' => $row['path'],
		));
		
		$xtpl->parse('MAIN.ROW');
		
	}

$xtpl->assign(array(
	'FORM_ACTION_SAVE' => $excursion->url('admin', 'm=pages&action=save'),
	'FORM_ACTION_UPDATE' => $excursion->url('admin', 'm=pages&action=update'),
	'FORM_PATH' => $excursion->inputbox('text', 'path', '', array('size' => 24, 'maxlength' => 7)),
	'FORM_CODE' => $excursion->inputbox('text', 'code', '', array('size' => 24, 'maxlength' => 20)),
	'FORM_TITLE' => $excursion->inputbox('text', 'title', '', array('size' => 24, 'maxlength' => 64)),
	'FORM_DESC' => $excursion->inputbox('text', 'desc', '', array('size' => 24, 'maxlength' => 64)),
));

$xtpl->parse('MAIN');
$xtpl->out('MAIN');
 
?>