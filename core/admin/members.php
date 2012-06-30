<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
$adm['location'] = 'members';

$xtpl = new XTemplate('themes/admin/members.xtpl');

if($action == 'update')
{

	$rstructuretitle = $excursion->import('rstructuretitle', 'P', 'ARR');
	$rstructuredesc = $excursion->import('rstructuredesc', 'P', 'ARR');
	$rstructureicon = $excursion->import('rstructureicon', 'P', 'ARR');

	foreach ($rstructuretitle as $i => $k)
	{
		$oldrow = $db->query("SELECT id FROM groups WHERE id=".(int)$i)->fetch();
		$rstructure['title'] = $excursion->import($rstructuretitle[$i], 'D', 'TXT');
		$rstructure['desc'] = $excursion->import($rstructuredesc[$i], 'D', 'TXT');
		$rstructure['icon'] = $excursion->import($rstructureicon[$i], 'D', 'TXT');

		$db->update(groups, $rstructure, "id=".(int)$i);
	}

	header('Location: admin.php?m=members');

}
if($action == 'save')
{

	$insert['title'] = $excursion->import('title','P','TXT');
	$insert['desc'] = $excursion->import('desc','P','TXT');
	$insert['icon'] = $excursion->import('icon','P','TXT');

	if (empty($insert['title'])) $error .= $lang['admin_error_title_missing'].'<br />';

	if(empty($error))
	{
	
		$db->insert('groups', $insert);
		$id = $db->lastInsertId();
		
		header('Location: admin.php?m=members');
		
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

	if($id <= 4)
	{
	
		die("here");
		$error = 'true'; // cannot delete required groups (inactive, banned, members, administrators)
		
	}

	if(!empty($id) && empty($error)){

		$db->delete(groups, "id='".$db->prep($id)."'");

		header('Location: admin.php?m=members');

	}

}

$sql = $db->query("SELECT * FROM groups ORDER BY id ASC");
$jj = 0;
foreach ($sql->fetchAll() as $row)
	{
	$structure_id = $row['id'];
	
		if($row['id'] == '1' || $row['id'] == '2' || $row['id'] == '4')
		{
		
			$xtpl->assign(array('FORM_TITLE' => $excursion->inputbox('text', 'rstructuretitle['.$structure_id.']', $row['title'], 'maxlength="64"', 'input_text_disabled')));
		
		}
		else
		{
		
			$xtpl->assign(array('FORM_TITLE' => $excursion->inputbox('text', 'rstructuretitle['.$structure_id.']', $row['title'], 'maxlength="64"', 'input_text_medium')));
		
		}
	
		$xtpl->assign(array(
			'FORM_DESC' => $excursion->inputbox('text', 'rstructuredesc['.$structure_id.']', $row['desc'], 'maxlength="64"'),
			'FORM_ICON' => $excursion->inputbox('text', 'rstructureicon['.$structure_id.']', $row['icon'], 'maxlength="64"', 'input_text_medium'),
			'ID' => $row['id'],
			'TITLE' => $row['title'],
			'DESC' => $row['desc'],
			'ICON' => $row['icon']
		));
		
		$xtpl->parse('MAIN.ROW');
		
	}

$xtpl->assign(array(
	'FORM_ACTION_SAVE' => $excursion->url('admin', 'm=members&action=save'),
	'FORM_ACTION_UPDATE' => $excursion->url('admin', 'm=members&action=update'),
	'FORM_ICON' => $excursion->inputbox('text', 'icon', '', array('size' => 24, 'maxlength' => 64)),
	'FORM_TITLE' => $excursion->inputbox('text', 'title', '', array('size' => 24, 'maxlength' => 64)),
	'FORM_DESC' => $excursion->inputbox('text', 'desc', '', array('size' => 24, 'maxlength' => 64)),
));

$xtpl->parse('MAIN');
$xtpl->out('MAIN');
 
?>