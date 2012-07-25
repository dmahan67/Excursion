<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=comments.send
[END_PLUGIN]
==================== */

list($user['auth_read'], $user['auth_write'], $user['auth_admin']) = $excursion->checkAuth('plugin', 'comments');

$comment = array(
	'body' => $insert['text'],
	'user_ip' => $_SERVER['REMOTE_ADDR'],
	'user_agent' => $_SERVER['HTTP_USER_AGENT']
);
        
$akismet = new Akismet($config['main_url'], $config['plugin']['akismet']['apikey'], $comment);

if($akismet->errorsExist()) 
{
	if($akismet->isError('AKISMET_INVALID_KEY')) 
	{
		$excursion->reportError('error_invalid_key');
	} 
	elseif($akismet->isError('AKISMET_RESPONSE_FAILED')) 
	{
		$excursion->reportError('error_failed');
	} 
	elseif($akismet->isError('AKISMET_SERVER_NOT_FOUND')) 
	{
		$excursion->reportError('error_failed');
	}
} 
else 
{
	if ($akismet->isSpam()) 
	{ 
		$akismet->submitSpam();
		$excursion->reportError('error_spam');	
	}
}

?>