<?php

namespace Tale\Net\Http\Request;

use Tale\Net\Http\Request,
	Tale\Net\Http\Body,
	Tale\StringUtils,
	Tale\Net\Url,
	Tale\Net\Ip\EndPoint,
	Tale\Net\Ip\Address,
	Tale\Net\Http\Response\Server as ServerResponse;

//http://tools.ietf.org/html/draft-ietf-httpbis-p2-semantics-14#section-7.2
class Server extends Request {

	private $_args;
	private $_url;
	private $_urlArgs;
	private $_bodyArgs;

	public function __construct( $method = null, $path = null, array $headers = null, Body $body = null ) {
		parent::__construct( $method, $path, $headers, $body );

		$this->_args = [];
		$this->_url = new Url();
		$this->_urlArgs = [];
		$this->_bodyArgs = [];

		//Iterate, filter and map server vars (Mapping to _args and _headers, respective)
		foreach( $_SERVER as $name => $value ) {

			if( strncmp( $name, 'HTTP_', 5 ) === 0 ) {

				$name = StringUtils::dasherize( StringUtils::humanize( strtolower( substr( $name, 5 ) ) ) );
				$this->setHeader( $name, $value );
				continue;
			}

			$this->_args[ StringUtils::variablize( strtolower( $name ) ) ] = $value;
		}
		ksort( $this->_args );

		//Message/Request stuff
		if( $this->hasArg( 'serverProtocol' ) ) {

			$parts = StringUtils::map( $this->getArg( 'serverProtocol' ), '/', [ 'proto', 'version' ] );

			$this->setProtocol( $parts[ 'proto' ] );
			$this->setProtocolVersion( $parts[ 'version' ] );
		}

		if( $this->hasArg( 'requestMethod' ) )
			$this->setMethod( $this->getArg( 'requestMethod' ) );


		//INFO: The following parts put respective server data into this instance for later ease-of-access
		//e.g. it fills the $_url property will all important values etc.
		//It basically takes everything in the $_SUPERGLOBALS and puts it into PHP arrays and objects.
		//Notice that we access $_SERVER via $this->getArg with variablized indices (we converted those just above)

		//Put the URL together
		$this->_url->setScheme( $this->getArg( 'https', '' ) == 'on' ? 'https' : 'http' );

		if( $this->hasHeader( 'host' ) )
			$this->_url->setDomain( $this->getHeader( 'host' ) );
		else if( $this->hasArg( 'serverName' ) )
			$this->_url->setDomain( $this->getArg( 'serverName' ) );

		$this->_url->setPort( $this->getArg( 'serverPort' ) );

        $path = $this->getArg( 'pathInfo' );
        if( empty( $path ) ) {

        	$path = $this->getArg( 
                'redirectRequestUri', 
                $this->getArg( 'requestUri', '/' )
            );
        }

        if( empty( $path ) )
        	$path = '/';

        $this->setPath( $path );

        $pos = null;
        if( ( $pos = strpos( $path, '?' ) ) !== false )
        	$path = substr( $path, 0, $pos );

        $this->_url->setPath( $path );

        $queryString = $this->getArg( 'redirectQueryString', $this->getArg( 'queryString' ) );
        if( !empty( $queryString ) ) {

        	$this->_url->setQueryString( $queryString );
        	$this->_urlArgs = $this->_url->getQueryArray();
        }

        if( $this->isPost() || $this->isPut() ) {

        	//TODO: Validate if it would make more sense to use the HTTP header "Content-Type" instead of the server var CONTENT_TYPE here (would require ;-splitting ("; encoding=xx; otherarg=yy;"))
        	$contentType = $this->getArg( 'contentType' );

        	//We handle multipart requests with a special body type!
        	if( strncmp( $contentType, 'multipart/', 10 ) === 0 ) {

        		//See 2nd comment above, here we need to do it anyways
        		//TODO: This one will be a MultiPart-Body and cant be simply converted to an array.
				//TODO: The classes exist, the function doesnt
        	} else {

	        	$body = $this->getBody();
	        	$body->setContentType( $this->getArg( 'contentType' ) );
	        	$body->setContent( file_get_contents( 'php://input' ) );

	        	//PUT wont use QueryString, it will probably be plain data!
	        	if( $this->isPost() ) {

	        		$this->_bodyArgs = $this->getBody()->getContentArray();
	        	}
	        }
        }
	}

	public function getArgs() {

		return $this->_args;
	}

	public function hasArg( $name ) {

		return array_key_exists( $name, $this->_args );
	}

	public function getArg( $name, $default = null ) {

		if( !$this->hasArg( $name ) )
			return $default;

		return $this->_args[ $name ];
	}

	public function getUrl() {

		return $this->_url;
	}

	public function getUrlArgs() {

		return $this->_urlArgs;
	}

	public function hasUrlArg( $name ) {

		return array_key_exists( $name, $this->_urlArgs );
	}

	public function getUrlArg( $name, $default = null ) {

		if( !$this->hasUrlArg( $name ) )
			return $default;

		return $this->_urlArgs[ $name ];
	}

	public function getBodyArgs() {

		return $this->_bodyArgs;
	}

	public function hasBodyArg( $name ) {

		return array_key_exists( $name, $this->_bodyArgs );
	}

	public function getBodyArg( $name, $default = null ) {

		if( !$this->hasUrlArg( $name ) )
			return $default;

		return $this->_bodyArgs[ $name ];
	}

	public function getAddress() {

		return Address::fromString( $this->getArg( 'serverAddr' ) );
	}

	public function getEndPoint() {

		return new EndPoint( $this->getAddress(), $this->getArg( 'serverPort' ) );
	}

	public function getRemoteAddress() {

		return Address::fromString( $this->getArg( 'remoteAddr' ) );
	}

	public function getRemoteEndPoint() {

		return new EndPoint( $this->getRemoteAddress(), $this->getArg( 'remotePort' ) );
	}

	public function createResponse() {

		$res = new ServerResponse();
		$res->setProtocol( $this->getProtocol() );
		$res->setProtocolVersion( $this->getProtocolVersion() );

		return $res; 
	}
}