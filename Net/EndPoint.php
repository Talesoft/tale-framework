<?php

namespace Tale\Net;

class EndPoint {

	private $_addressFamily;

	public function __construct( $addressFamily ) {

		$this->_addressFamily = $addressFamily;
	}

	public function getAddressFamily() {

		return $this->_addressFamily;
	}
}