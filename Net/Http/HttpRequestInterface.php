<?php

namespace Tale\Net\Http;

interface HttpRequestInterface extends HttpMessageInterface {

	public function getMethod();
	public function setMethod( $method );

	public function getPath();
	public function setPath( $path );
}