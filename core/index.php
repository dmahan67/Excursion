<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
require_once 'core/header.php';

$xtpl = new XTemplate('themes/bootstrap/index.xtpl');

$xtpl->assign('VARIABLE', 'TEST');

$xtpl->parse('MAIN.block1');

$row = array('ID'=>'38',
			'NAME'=>'cocomp',
			'AGE'=>'33'
		 );

$xtpl->assign('DATA',$row);

$xtpl->parse('MAIN.block3');

$xtpl->parse('MAIN');
$xtpl->out('MAIN');

require_once 'core/footer.php';
 
?>