<?php

namespace Tale\Net\Http\Responses;

use Tale\Net\Http\HttpResponse,
	Tale\Net\Http\HttpBodyInterface,
	Tale\System\StringUtils,
	Tale\Net\Http\HttpMethod;

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
class HttpServerResponse extends HttpResponse {

	public function __construct( $statusCode = null, $reasonPhrase = null, array $headers = null, HttpBodyInterface $body = null ) {
		parent::__construct( $statusCode, $reasonPhrase, $headers, $body );
	}

	public function applyStatusCode() {

		header( $this->getHeadLine() );

		return $this;
	}

	public function applyHeaders() {

		$headers = $this->getHeaderLines();
		foreach( $headers as $line )
			header( $line );

		return $this;
	}

	public function applyBody() {

		$body = $this->getBody();

		if( $body->hasContent() ) {

            if( !$this->hasHeader( 'content-type' ) )
                $parts[] = 'Content-Type: '.$body->getContentType().'; encoding='.$body->getContentEncoding();
            
            if( !$this->hasHeader( 'content-length' ) )
                header( 'Content-Length: '.$body->getContentLength() );

            echo $body->getContent();
        }
		

		return $this;
	}

	public function apply() {

		$this->applyStatusCode();
		$this->applyHeaders();
		$this->applyBody();

		return $this;
	}
}