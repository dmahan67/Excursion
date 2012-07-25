<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=comments.row
[END_PLUGIN]
==================== */

$grav = $db->query("SELECT * FROM members WHERE id = ".$com['userid'])->fetchColumn();
$xtpl->assign('COM_GRAVATAR', getGravatar($grav, 80));
		
?>