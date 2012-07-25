<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=user.details.tags
[END_PLUGIN]
==================== */

$xtpl->assign('GRAVATAR', getGravatar($row['id']));

?>