<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=page.tags
[END_PLUGIN]
==================== */

$xtpl->assign('RATINGS', pullRating($row['id'], true, false, false));

?>