<?php
/**
 * Excursion - Content Management System
 * 
 * @version 0.0.1
 * @author Dyllon Mahan, Brock Burkholder
 */
 
$xtpl = new XTemplate('themes/admin/footer.xtpl');

$xtpl->parse('ADMIN_FOOTER');
$xtpl->out('ADMIN_FOOTER');

ob_flush();
 
?>