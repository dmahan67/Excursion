<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
$xtpl = new XTemplate('themes/'.$user['theme'].'/footer.xtpl');

$xtpl->parse('FOOTER');
$xtpl->out('FOOTER');

ob_flush();
 
?>