<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=user.profile.tags
[END_PLUGIN]
==================== */

$xtpl->assign('FORM_GRAVATAR', $excursion->inputbox('text', 'gravatar', $user['gravatar'], array('size' => 12, 'maxlength' => 32)));

?>