<?php

namespace Tale\Net\Ip;

use Tale\Net\AddressFamily,
    Tale\Net\Dns\DnsUtils,
    Tale\System\Exception;

class IpAddress implements IpAddressInterface {

	private $_bytes;
	private $_family;

	public function __construct( array $bytes ) {

		$this->_bytes = $bytes;

		switch( count( $this->_bytes ) ) {
			case 4: $this->_family = AddressFamily::INET; break;
			case 16: $this->_family = AddressFamily::INET6; break;
			default:

				throw new Exception( "Failed to construct IP address: Passed bytes dont count for any known AddressFamily. Try 4 (IPv4) or 16 (IPv6) bytes" );
		}
	}

	public function getBytes() {

		return $this->_bytes;
	}

    public function getHexBytes() {

        return array_map( function( $byte ) {

            return str_pad( dechex( $byte ), 2, '0', \STR_PAD_LEFT );
        }, $this->_bytes );
    }

    public function getFamily() {

        return $this->_family;
    }

    public function isIpv4() {

        return $this->_family === AddressFamily::INET;
    }

    public function isIpv6() {

        return $this->_family === AddressFamily::INET6;
    }

    public function getString() {

        return inet_ntop( implode( array_map( 'chr', $this->_bytes ) ) );
    }

    public function getReverseHostName() {

        return DnsUtils::getReverseHostName( $this );
    }

    public function lookUp() {

        return DnsUtils::lookUpReverse( $this );
    }

    public function lookUpArray() {

        return iterator_to_array( $this->lookUp() );
    }

    public function lookUpFirst() {

        return DnsUtils::lookUpReverseFirst( $this );
    }

    public function lookUpHostName() {

        $first = $this->lookUpFirst();

        if( !$first )
            return null;

        return $first->getTargetHostName();
    }

    public function __toString() {

        return $this->getString();
    }

    public static function fromString( $string ) {

        $n = @inet_pton( $string );

        if( $n === false )
            throw new Exception( "Failed to convert string to IPAddress: $string is not a valid ip address of any kind" );

        return new static( array_map( 'ord', str_split( $n ) ) );
    }

    public static function tryFromString( $string ) {

        $ip = null;
        try {

            $ip = self::fromString( $string );
        } catch( \Exception $e ) {

            return null;
        }

        return $ip;
    }

    public static function fromDomain( $string, $v6 = false ) {

        $ip = self::tryFromString( $string );

        if( $ip )
            return $ip;

        $record = $v6 ? DnsUtils::lookUpAaaaRecord( $string ) : DnsUtils::lookUpARecord( $string );

        if( !$record )
            throw new Exception( "Failed to look up IP address for host $string. No matching records found" );

        return $record->getIpAddress();
    }
}