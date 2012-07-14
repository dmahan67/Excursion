<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=comments.send
[END_PLUGIN]
==================== */

$akismet = new Akismet($config['main_url'], $config['plugin']['akismet']['apikey'], $comment);

if (!$akismet->errorsExist()) 
{

	$akismet->submitSpam();
	
}

if($akismet->errorsExist()) {

	if($akismet->isError('AKISMET_INVALID_KEY')) {
	
		$error .= $lang['system_error'] . '<br />';

	} 
	elseif($akismet->isError('AKISMET_RESPONSE_FAILED')) 
	{

		$error .= $lang['system_error'] . '<br />';
		
	} 
	elseif($akismet->isError('AKISMET_SERVER_NOT_FOUND')) 
	{

		$error .= $lang['system_error'] . '<br />';
		
	}

} 
else 
{

	if ($akismet->isSpam()) 
	{

		$error .= $lang['spam_error'] . '<br />';
		
	}
	
}

?>