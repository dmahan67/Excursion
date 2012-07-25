<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=user.edit.tags
[END_PLUGIN]
==================== */

$xtpl->assign('FORM_GRAVATAR', $excursion->inputbox('text', 'gravatar', $row['gravatar'], array('size' => 24, 'maxlength' => 64)));

?>