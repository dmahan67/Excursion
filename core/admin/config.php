<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
$adm['location'] = 'config';

if($action == 'save')
{

	$insert['title'] = $excursion->import('title','P','TXT');
	$insert['subtitle'] = $excursion->import('subtitle','P','TXT');
	$insert['keywords'] = $excursion->import('keywords','P','TXT');
	$insert['forcetheme'] = $excursion->import('forcetheme','P','TXT');
	$insert['disablereg'] = $excursion->import('disablereg','P','TXT');
	$insert['disableval'] = $excursion->import('disableval','P','TXT');
	$insert['valnew'] = $excursion->import('valnew','P','TXT');
	$insert['maintenance'] = $excursion->import('maintenance','P','TXT');
	$insert['reason'] = $excursion->import('reason','P','TXT');
	$insert['maxpages'] = $excursion->import('maxpages','P','INT');
	$insert['admin_email'] = $excursion->import('admin_email','P','TXT');
	
	if (!$user['auth_write']) $excursion->reportError('error_insufficient_rights');

	if(!$excursion->error_found())
	{
		$db->update('config', array('value' => $insert['title']), "title='title'");
		$db->update('config', array('value' => $insert['subtitle']), "title='subtitle'");
		$db->update('config', array('value' => $insert['keywords']), "title='keywords'");
		$db->update('config', array('value' => $insert['forcetheme']), "title='forcetheme'");
		$db->update('config', array('value' => $insert['disablereg']), "title='disablereg'");
		$db->update('config', array('value' => $insert['disableval']), "title='disableval'");
		$db->update('config', array('value' => $insert['valnew']), "title='valnew'");
		$db->update('config', array('value' => $insert['maintenance']), "title='maintenance'");
		$db->update('config', array('text' => $insert['reason']), "title='maintenance'");
		$db->update('config', array('value' => $insert['maxpages']), "title='maxpages'");
		$db->update('config', array('value' => $insert['admin_email']), "title='admin_email'");
		
		header('Location: admin.php?m=config');
	}

}
 
$xtpl = new XTemplate('themes/admin/config.xtpl');

$xtpl->assign(array(
	'FORM_ACTION' => $excursion->url('admin', 'm=config&action=save'),
	'FORM_TITLE' => $excursion->inputbox('text', 'title', $config['title'], array('size' => 24, 'maxlength' => 64)),
	'FORM_SUBTITLE' => $excursion->inputbox('text', 'subtitle', $config['subtitle'], array('size' => 24, 'maxlength' => 64)),
	'FORM_KEYWORDS' => $excursion->inputbox('text', 'keywords', $config['keywords'], array('size' => 24, 'maxlength' => 64)),
	'FORM_FORCETHEME' => $excursion->radiobox($config['forcetheme'], 'forcetheme', array('yes', 'no'), array($lang['yes'], $lang['no'])),
	'FORM_DISABLEREG' => $excursion->radiobox($config['disablereg'], 'disablereg', array('yes', 'no'), array($lang['yes'], $lang['no'])),
	'FORM_DISABLEVAL' => $excursion->radiobox($config['disableval'], 'disableval', array('yes', 'no'), array($lang['yes'], $lang['no'])),
	'FORM_VALNEW' => $excursion->radiobox($config['valnew'], 'valnew', array('yes', 'no'), array($lang['yes'], $lang['no'])),
	'FORM_MAINTENANCE' => $excursion->radiobox($config['maintenance'], 'maintenance', array('yes', 'no'), array($lang['yes'], $lang['no'])),
	'FORM_REASON' => $excursion->inputbox('text', 'reason', $config['reason'], array('size' => 24, 'maxlength' => 64)),
	'FORM_MAXPAGES' => $excursion->inputbox('text', 'maxpages', $config['maxpages'], array('size' => 24, 'maxlength' => 64)),
	'FORM_ADMIN_EMAIL' => $excursion->inputbox('text', 'admin_email', $config['admin_email'], array('size' => 24, 'maxlength' => 64))
));

$excursion->display_messages($xtpl);

$xtpl->parse('MAIN');
$xtpl->out('MAIN');
 
?>