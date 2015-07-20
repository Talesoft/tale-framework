<?php

namespace Tale\Net\Http;

interface HttpResponseInterface extends HttpMessageInterface {

	public function getStatusCode();
	public function setStatusCode( $statusCode );
	
	public function getReasonPhrase();
	public function setReasonPhrase( $reasonPhrase );
}