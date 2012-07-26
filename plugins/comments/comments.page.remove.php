<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=news.row
[END_PLUGIN]
==================== */

$sql_comments_delete = $db->delete(comments, "area='page' AND area_id=$id");

?>