<?php

namespace Tale\Net\Http\Response;

use Tale\Net\Http\Response,
	Tale\Net\Http\Body;

/*
TODO: ETAG-Caching-Support

$last_modified_time = filemtime($file); 
$etag = md5_file($file); 

header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT"); 
header("Etag: $etag"); 

if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || 
    trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) { 
    header("HTTP/1.1 304 Not Modified"); 
    exit; 
} 
*/
class Server extends Response
{

	public function __construct($statusCode = null, $reasonPhrase = null, array $headers = null, Body $body = null)
	{
		parent::__construct($statusCode, $reasonPhrase, $headers, $body);
	}

	public function applyStatusCode()
	{

		header($this->getHeadLine());

		return $this;
	}

	public function applyHeaders()
	{

		$body = $this->getBody();
		if ($body->hasContent()) {

			if (!$this->hasHeader('content-type'))
				header('Content-Type: '.$body->getContentType().'; encoding='.$body->getContentEncoding());

			if (!$this->hasHeader('content-length'))
				header('Content-Length: '.$body->getContentLength());
		}

		$headers = $this->getHeaderLines();
		foreach ($headers as $line)
			header($line);

		return $this;
	}

	public function applyBody()
	{

		$body = $this->getBody();
		if ($body->hasContent()) {

			echo $body->getContent();
		}

		return $this;
	}

	public function apply()
	{

		if (function_exists('headers_sent') && headers_sent())
			throw new \RuntimeException(
				"Failed to apply response: The headers have already "
				."been sent out. You made some kind of output "
				."before apply() has been called on the HTTP response"
			);

		$this->applyStatusCode();
		$this->applyHeaders();
		$this->applyBody();

		return $this;
	}
}