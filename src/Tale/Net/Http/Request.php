<?php

namespace Tale\Net\Http;

use Tale\Environment;
use Tale\Util\StringUtil;

class Request extends Message {

    private $_method;
    private $_path;

    public function __construct( $method = null, $path = null, array $headers = null, Body $body = null ) {
        parent::__construct( $headers, $body );

        $this->_method = $method ? strtoupper( $method ) : Method::GET;
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

        return $this->isMethod( Method::POST );
    }

    public function isGet() {

        return $this->isMethod( Method::GET );
    }

    public function isPut() {

        return $this->isMethod( Method::PUT );
    }

    public function isHead() {

        return $this->isMethod( Method::HEAD );
    }

    public function isDelete() {

        return $this->isMethod( Method::DELETE );
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

    private static function _validateWebEnvironment()
    {

        if (!Environment::isWeb())
            throw new \Exception(
                "Failed to get request data: "
                ."The current environment is not a web environment"
            );
    }

    public static function getProtocolFromEnvironment()
    {

        self::_validateWebEnvironment();

        $proto = Environment::getClientOption('SERVER_PROTOCOL');
        return explode('/', $proto)[0];
    }

    public static function getProtocolVersionFromEnvironment()
    {

        self::_validateWebEnvironment();

        $proto = Environment::getClientOption('SERVER_PROTOCOL');
        return explode('/', $proto)[1];
    }

    public static function getMethodFromEnvironment()
    {

        self::_validateWebEnvironment();

        return Environment::getClientOption('REQUEST_METHOD');
    }

    public static function getHeadersFromEnvironment()
    {

        self::_validateWebEnvironment();

        foreach ($_SERVER as $name => $value) {

            if (strncmp($name, 'HTTP_', 5) === 0) {

                $name = StringUtil::dasherize(StringUtil::humanize(strtolower(substr($name, 5))));
                yield $name => $value;
            }
        }
    }

    public static function getHeaderArrayFromEnvironment()
    {

        return iterator_to_array(self::getHeadersFromEnvironment());
    }
}
