<?php

namespace Tale\Net\Http;

interface HttpMessageInterface {

    public function getHeaders();
    public function setHeaders( array $headers );

    public function hasHeader( $name );
    public function getHeader( $name );
    public function getHeaderLine( $name );
    public function setHeader( $name, $value );
    public function setHeaderLine( $line );
    public function removeHeader( $name );

    public function getBody();
    public function setBody( HttpBodyInterface $body );

	public function getProtocol();
	public function setProtocol( $protocolName );
	public function getProtocolVersion();
	public function setProtocolVersion( $protocolVersion );
}