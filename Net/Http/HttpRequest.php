<?php

namespace Tale\Net\Http;

use Tale\System\StringUtils,
    Tale\System\Exception;

class HttpRequest extends HttpMessage implements HttpRequestInterface {

    private $_method;
    private $_path;

    public function __construct( $method = null, $path = null, array $headers = null, HttpBodyInterface $body = null ) {
        parent::__construct( $headers, $body );

        $this->_method = $method ? strtoupper( $method ) : HttpMethod::GET;
        $this->_path = $path ? $path : '/';
    }

    public function getMethod() {

        return $this->_method;
    }

    public function setMethod( $method ) {

        $this->_method = strtoupper( $method );

        return $this;
    }

    public function isMethod( $method ) {

        return $this->_method === strtoupper( $method );
    }

    public function isPost() {

        return $this->isMethod( HttpMethod::POST );
    }

    public function isGet() {

        return $this->isMethod( HttpMethod::GET );
    }

    public function isPut() {

        return $this->isMethod( HttpMethod::PUT );
    }

    public function isHead() {

        return $this->isMethod( HttpMethod::HEAD );
    }

    public function isDelete() {

        return $this->isMethod( HttpMethod::DELETE );
    }

    public function getPath() {

        return $this->_path;
    }

    public function setPath( $path ) {

        $this->_path = $path;

        return $this;
    }

    public function isAjaxRequest() {

        if( !$this->hasHeader( 'x-requested-with' ) )
            return false;

        return $this->getHeader( 'x-requested-with' ) == 'XMLHttpRequest';
    }

    public function hasUserAgent() {

        return $this->hasHeader( 'user-agent' );
    }

    public function getUserAgent() {

        return $this->getHeader( 'user-agent' );
    }

    public function setUserAgent( $userAgent ) {

        return $this->setHeader( 'User-Agent', $userAgent );
    }

    public function getString() {

        $header = implode( ' ', [
            $this->_method,
            $this->_path,
            $this->getProtocol().'/'.$this->getProtocolVersion()
        ] )."\r\n";

        return $header.parent::getString();
    }
}
