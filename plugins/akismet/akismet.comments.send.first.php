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
	
		$excursion->reportError('system_error');

	} 
	elseif($akismet->isError('AKISMET_RESPONSE_FAILED')) 
	{

		$excursion->reportError('system_error');
		
	} 
	elseif($akismet->isError('AKISMET_SERVER_NOT_FOUND')) 
	{

		$excursion->reportError('system_error');
		
	}

} 
else 
{

	if ($akismet->isSpam()) 
	{

		$excursion->reportError('spam_error');
		
	}
	
}

?>