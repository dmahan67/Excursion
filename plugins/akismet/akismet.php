<?php
/* ====================
[BEGIN_PLUGIN]
Hooks=global
[END_PLUGIN]
==================== */

define("AKISMET_SERVER_NOT_FOUND",	0);
define("AKISMET_RESPONSE_FAILED",	1);
define("AKISMET_INVALID_KEY",		2);

class Akismet extends AkismetObject {
	var $apiPort = 80;
	var $akismetServer = 'rest.akismet.com';
	var $akismetVersion = '1.1';
	var $http;
	
	var $ignore = array(
			'HTTP_COOKIE',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED_HOST',
			'HTTP_MAX_FORWARDS',
			'HTTP_X_FORWARDED_SERVER',
			'REDIRECT_STATUS',
			'SERVER_PORT',
			'PATH',
			'DOCUMENT_ROOT',
			'SERVER_ADMIN',
			'QUERY_STRING',
			'PHP_SELF',
			'argv'
		);
	
	var $blogUrl = "";
	var $apiKey  = "";
	var $comment = array();

	function Akismet($blogUrl, $apiKey, $comment = array()) {
		$this->blogUrl = $blogUrl;
		$this->apiKey  = $apiKey;
		$this->setComment($comment);
		
		$this->http = new AkismetHttpClient($this->akismetServer, $blogUrl, $apiKey);
		if($this->http->errorsExist()) {
			$this->errors = array_merge($this->errors, $this->http->getErrors());
		}
		
		if(!$this->_isValidApiKey($apiKey)) {
			$this->setError(AKISMET_INVALID_KEY, "Your Akismet API key is not valid.");
		}
	}
	
	function isSpam() {
		$response = $this->http->getResponse($this->_getQueryString(), 'comment-check');
		
		return ($response == "true");
	}
	
	function submitSpam() {
		$this->http->getResponse($this->_getQueryString(), 'submit-spam');
	}
	
	function submitHam() {
		$this->http->getResponse($this->_getQueryString(), 'submit-ham');
	}
	
	function setComment($comment) {
		$this->comment = $comment;
		if(!empty($comment)) {
			$this->_formatCommentArray();
			$this->_fillCommentValues();
		}
	}
	
	function getComment() {
		return $this->comment;
	}
	
	function _isValidApiKey($key) {
		$keyCheck = $this->http->getResponse("key=".$this->apiKey."&blog=".$this->blogUrl, 'verify-key');
			
		return ($keyCheck == "valid");
	}
	
	function _formatCommentArray() {
		$format = array(
				'type' => 'comment_type',
				'author' => 'comment_author',
				'email' => 'comment_author_email',
				'website' => 'comment_author_url',
				'body' => 'comment_content'
			);
		
		foreach($format as $short => $long) {
			if(isset($this->comment[$short])) {
				$this->comment[$long] = $this->comment[$short];
				unset($this->comment[$short]);
			}
		}
	}
	
	function _fillCommentValues() {
		if(!isset($this->comment['user_ip'])) {
			$this->comment['user_ip'] = ($_SERVER['REMOTE_ADDR'] != getenv('SERVER_ADDR')) ? $_SERVER['REMOTE_ADDR'] : getenv('HTTP_X_FORWARDED_FOR');
		}
		if(!isset($this->comment['user_agent'])) {
			$this->comment['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		}
		if(!isset($this->comment['referrer'])) {
			$this->comment['referrer'] = $_SERVER['HTTP_REFERER'];
		}
		if(!isset($this->comment['blog'])) {
			$this->comment['blog'] = $this->blogUrl;
		}
	}
	
	function _getQueryString() {
		foreach($_SERVER as $key => $value) {
			if(!in_array($key, $this->ignore)) {
				if($key == 'REMOTE_ADDR') {
					$this->comment[$key] = $this->comment['user_ip'];
				} else {
					$this->comment[$key] = $value;
				}
			}
		}

		$query_string = '';

		foreach($this->comment as $key => $data) {
			$query_string .= $key . '=' . urlencode(stripslashes($data)) . '&';
		}

		return $query_string;
	}
	
}

class AkismetObject {
	var $errors = array();
	
	function setError($name, $message) {
		$this->errors[$name] = $message;
	}
	
	function getError($name) {
		if($this->isError($name)) {
			return $this->errors[$name];
		} else {
			return false;
		}
	}
	
	function getErrors() {
		return (array)$this->errors;
	}
	
	function isError($name) {
		return isset($this->errors[$name]);
	}

	function errorsExist() {
		return (count($this->errors) > 0);
	}
	
	
}

class AkismetHttpClient extends AkismetObject {
	var $akismetVersion = '1.1';
	var $con;
	var $host;
	var $port;
	var $apiKey;
	var $blogUrl;
	var $errors = array();
	
	function AkismetHttpClient($host, $blogUrl, $apiKey, $port = 80) {
		$this->host = $host;
		$this->port = $port;
		$this->blogUrl = $blogUrl;
		$this->apiKey = $apiKey;
	}
	
	function getResponse($request, $path, $type = "post", $responseLength = 1160) {
		$this->_connect();
		
		if($this->con && !$this->isError(AKISMET_SERVER_NOT_FOUND)) {
			$request  = 
					strToUpper($type)." /{$this->akismetVersion}/$path HTTP/1.0\r\n" .
					"Host: ".((!empty($this->apiKey)) ? $this->apiKey."." : null)."{$this->host}\r\n" .
					"Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n" .
					"Content-Length: ".strlen($request)."\r\n" .
					"User-Agent: Akismet PHP4 Class\r\n" .
					"\r\n" .
					$request
				;
			$response = "";

			@fwrite($this->con, $request);

			while(!feof($this->con)) {
				$response .= @fgets($this->con, $responseLength);
			}

			$response = explode("\r\n\r\n", $response, 2);
			return $response[1];
		} else {
			$this->setError(AKISMET_RESPONSE_FAILED, "The response could not be retrieved.");
		}
		
		$this->_disconnect();
	}
	
	function _connect() {
		if(!($this->con = @fsockopen($this->host, $this->port))) {
			$this->setError(AKISMET_SERVER_NOT_FOUND, "Could not connect to akismet server.");
		}
	}
	
	function _disconnect() {
		@fclose($this->con);
	}
	
	
}

?>