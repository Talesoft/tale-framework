<?php

namespace Tale\Net\Ip;

use Tale\Net\EndPoint,
    Tale\System\StringUtils,
    Tale\System\Exception;

class IpEndPoint extends EndPoint {

    private $_address;
    private $_port;

    public function __construct( IpAddressInterface $address, $port ) {
        parent::__construct( $address->getFamily() );
        
        $this->_address = $address;
        $this->_port = intval( $port );
    }

    public function getAddress() {

        return $this->_address;
    }

    public function getPort() {

        return $this->_port;
    }

    public function getString() {

        $address = $this->_address->getString();
        if( $this->_address->isIpv6() )
            $address = "[$address]";

        return "$address:{$this->_port}";
    }

    public function __toString() {

        return $this->getString();
    }

    public static function fromString( $string ) {

        $parts = StringUtils::mapReverse( $string, ':', [ 'port', 'ip' ] );

        return new static( IpAddress::fromString( trim( $parts[ 'ip' ], '[]' ) ), $parts[ 'port' ] );
    }
}