<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=global
[END_PLUGIN]
==================== */
 
function getGravatar($userid, $s = 100, $d = 'mm', $r = 'g', $img = false, $atts = array()) 
{
	global $db;
	
	$email = $db->query("SELECT gravatar FROM members WHERE id = ".$userid)->fetchColumn();
	$url = 'http://www.gravatar.com/avatar/';
	$url .= md5( strtolower( trim( $email ) ) );
	$url .= "?s=$s&d=$d&r=$r";
	if ( $img ) {
		$url = '<img src="' . $url . '"';
		foreach ( $atts as $key => $val )
		{
			$url .= ' ' . $key . '="' . $val . '"';
			$url .= ' />';
		}
	}
	return $url;
}

$user['gravatar'] = $db->query("SELECT gravatar FROM members WHERE id = ".$user['id'])->fetchColumn();

?>