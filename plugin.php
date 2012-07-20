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
$ex['location'] = 'plugin';
require_once 'core/common.php';

$p = $excursion->import('p', 'G', 'ALP', 24);

if (!empty($p) && $plugins[$p]['installed'] && $plugins[$p]['standalone'])
{
	$extname = $p;
    $path_skin = 'themes/'.$user['theme'].'/tpl/'.$extname.'.xtpl';
	$path_file = 'plugins/'.$extname.'/'.$extname.'.php';
    if (!file_exists($path_skin))
    {
        $path_skin = 'plugins/'.$extname.'/tpl/'.$extname.'.xtpl';
    }
}
else
{

	header('Location: message.php');
	
}

require_once 'core/header.php';

if (!empty($path_skin))
{
	
	$xtpl = new XTemplate($path_skin);
	
}
if (!empty($path_file))
{

	require_once $path_file;
	
}

$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';

?>