<?php

namespace Tale\Net;

use Tale\System\StringUtils;

class Uri implements UriInterface {

	private $_scheme;
	private $_path;

	public function __construct( array $items = null ) {

		$items = array_replace( [
			'scheme' => null,
			'path' => null
		], $items );

		$this->_scheme = $items[ 'scheme' ];
		$this->_path = $items[ 'path' ];
	}

	public function hasScheme() {

		return !is_null( $this->_scheme );
	}

	public function getScheme() {

		return $this->_scheme;
	}

	public function setScheme( $scheme ) {

		$this->_scheme = $scheme;

		return $this;
	}

	public function hasPath() {

		return !is_null( $this->_path );
	}

	public function getPath() {

		return $this->_path;
	}

	public function setPath( $path ) {

		$this->_path = $path;

		return $this;
	}

	public function getString() {

		return $this->_scheme;
	}

	public function __toString() {

		return $this->getString();
	}

	public static function fromString( $uriString ) {

		return new static( StringUtils::map( $uriString, ':', [ 'scheme', 'path' ] ) );
	}
}