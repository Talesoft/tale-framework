<?php

namespace Tale\Net\Ip;

use Tale\Net\EndPointInterface;

interface IpEndPointInterface extends EndPointInterface {

	public function getAddress();
	public function getPort();

    public function getString();
    public function __toString();
    
    public static function fromString( $string );
}