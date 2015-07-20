<?php

namespace Tale\Net\Http;

use Tale\System\StringUtils;

class HttpResponse extends HttpMessage implements HttpResponseInterface {

    private $_statusCode;
    private $_reasonPhrase;

    public function __construct( $statusCode = null, $reasonPhrase = null, array $headers = null, HttpBodyInterface $body = null ) {
        parent::__construct( $headers, $body );

        $this->_statusCode = $statusCode ? $statusCode : HttpStatusCode::OK;
        $this->_reasonPhrase = $reasonPhrase;
    }

    public function getStatusCode() {

        return $this->_statusCode;
    }

    public function setStatusCode( $statusCode ) {
        
        $this->_statusCode = $statusCode;

        return $this;
    }

    public function hasReasonPhrase( $key ) {

        return !is_null( $this->_reasonPhrase );
    }

    public function getReasonPhrase() {

        return $this->_reasonPhrase;
    }

    public function setReasonPhrase( $reasonPhrase ) {

        $this->_reasonPhrase = $reasonPhrase;

        return $this;
    }

    public function setLocation( $location ) {

        return $this->setHeader( 'Location', (string)$location );
    }

    public function getHeadLine() {

        $rp = $this->_reasonPhrase;

        if( !$rp )
            $rp = HttpStatusCode::getReasonPhrase( $this->_statusCode );

        return implode( ' ', [
            $this->getProtocol().'/'.$this->getProtocolVersion(), 
            $this->_statusCode, 
            $rp
        ] );
    }

    public function getString() {

        return $this->getHeadLine()."\r\n".parent::getString();
    }
}
