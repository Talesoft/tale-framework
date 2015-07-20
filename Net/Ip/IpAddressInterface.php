<?php

namespace Tale\Net\Ip;

interface IpAddressInterface {

    public function getBytes();
    public function getHexBytes();
	public function getFamily();

    public function getString();
    public function __toString();

    public static function fromString( $string );
}