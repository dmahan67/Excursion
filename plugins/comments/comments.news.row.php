<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=news.row
[END_PLUGIN]
==================== */

$totalcom = $db->query("SELECT COUNT(*) FROM comments WHERE area = 'page' AND area_id = '".$row['id']."'")->fetchColumn();
$xtpl->assign('NEWS_COMMENTS', (int) $totalcom);

?>