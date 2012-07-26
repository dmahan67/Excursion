<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=news.row
[END_PLUGIN]
==================== */

$xtpl->assign('RATINGS', pullRating($row['id'], true, false, false));

?>