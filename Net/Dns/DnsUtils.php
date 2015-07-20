<?php

namespace Tale\Net\Dns;

use Tale\Net\Ip\IpAddressInterface,
    Tale\Net\Ip\IpAddress,
    Tale\Net\Dns\Records\DnsA6Record,
    Tale\Net\Dns\Records\DnsAaaaRecord,
    Tale\Net\Dns\Records\DnsARecord,
    Tale\Net\Dns\Records\DnsCnameRecord,
    Tale\Net\Dns\Records\DnsHinfoRecord,
    Tale\Net\Dns\Records\DnsMxRecord,
    Tale\Net\Dns\Records\DnsNsRecord,
    Tale\Net\Dns\Records\DnsPtrRecord,
    Tale\Net\Dns\Records\DnsSoaRecord,
    Tale\Net\Dns\Records\DnsSrvRecord,
    Tale\Net\Dns\Records\DnsTxtRecord,
    Tale\System\Exception;

//http://php.net/manual/de/function.dns-get-record.php
class DnsUtils {
    
    public static function getPunyCode( $hostName ) {

        return idn_to_ascii( $hostName );
    }

    public static function getUnicode( $hostName ) {

        return idn_to_utf8( $hostName );
    }

    public static function getReverseHostName( IpAddressInterface $ipAddress ) {

        $str = null;
        if( $ipAddress->isIpv4() )
            $str = implode( '.', array_reverse( $ipAddress->getBytes() ) );
        else if( $ipAddress->isIpv6() ) {
            
            $hexParts = $ipAddress->getHexBytes();
            $bytes = [];
            foreach( $hexParts as $part ) {

                $bytes[] = $part[ 0 ];
                $bytes[] = $part[ 1 ];
            }

            $str = implode( '.', array_reverse( $bytes ) );
        } else
            throw new Exception( "Failed to build reverse host name: Passed ip address needs to be either ipv4 or ipv6" );

        return "$str.in-addr.arpa";
    }

    public static function hasRecordType( $hostName, $type = DnsRecordType::ANY ) {

        return checkdnsrr( $hostName, DnsRecordType::getName( $type ) );
    }

    public static function lookUp( $hostName, $type = DnsRecordType::ANY, $withAuthEntries = false, $withAdditionalLookups = false ) {

        $authNs = null;
        $additionalLookups = null;
        $result = null;

        if( $withAdditionalLookups )
            $result = @dns_get_record( $hostName, $type, $authNs, $additionalLookups );
        else if( $withAuthEntries )
            $result = @dns_get_record( $hostName, $type, $authNs );
        else
            $result = @dns_get_record( $hostName, $type );
    
        if( !$result )
            throw new Exception( "Failed to get DNS records for $hostName, maybe there is no DNS available" );        

        foreach( $result as $record ) {

            $type = DnsRecordType::getValue( $record[ 'type' ] );
            switch( $type ) {
                case DnsRecordType::A6:

                    yield new DnsA6Record( $record[ 'host' ], $record[ 'chain' ], IpAddress::fromString( $record[ 'ipv6' ] ), $record[ 'masklen' ], $record[ 'ttl' ] );
                    break;
                case DnsRecordType::AAAA:

                    yield new DnsAaaaRecord( $record[ 'host' ], IpAddress::fromString( $record[ 'ipv6' ] ), $record[ 'ttl' ] ); 
                    break;
                case DnsRecordType::A:

                    yield new DnsARecord( $record[ 'host' ], IpAddress::fromString( $record[ 'ip' ] ), $record[ 'ttl' ] ); 
                    break;
                case DnsRecordType::CNAME:

                    yield new DnsCnameRecord( $record[ 'host' ], $record[ 'target' ], $record[ 'ttl' ] );
                    break;
                case DnsRecordType::HINFO:

                    yield new DnsCnameRecord( $record[ 'host' ], $record[ 'cpu' ], $record[ 'os' ], $record[ 'ttl' ] );
                    break;
                case DnsRecordType::MX:

                    yield new DnsMxRecord( $record[ 'host' ], $record[ 'target' ], $record[ 'pri' ], $record[ 'ttl' ] ); 
                    break;
                case DnsRecordType::NS:

                    yield new DnsNsRecord( $record[ 'host' ], $record[ 'target' ], $record[ 'ttl' ] ); 
                    break;
                case DnsRecordType::PTR:

                    yield new DnsPtrRecord( $record[ 'host' ], $record[ 'target' ], $record[ 'ttl' ] ); 
                    break;
                case DnsRecordType::SOA:

                    yield new DnsSoaRecord( $record[ 'host' ], $record[ 'mname' ], $record[ 'rname' ], $record[ 'serial' ], $record[ 'refresh' ], $record[ 'retry' ], $record[ 'expire' ], $record[ 'minimum-ttl' ], $record[ 'ttl' ] ); 
                    break;
                case DnsRecordType::TXT:

                    yield new DnsTxtRecord( $record[ 'host' ], $record[ 'txt' ], $record[ 'ttl' ] ); 
                    break;
                default:

                    yield new DnsRecord( $record[ 'host' ], $type, $record[ 'ttl' ] ); 
                    break;
            }
        }
    }

    public static function lookUpArray( $hostName, $type = DnsRecordType::ANY, $withAuthEntries = false, $withAdditionalLookups = false ) {

        return iterator_to_array( self::lookUp( $hostName, $type, $withAuthEntries, $withAdditionalLookups ) );
    }

    public static function lookUpFirst( $hostName, $type = DnsRecordType::ANY ) {

        foreach( self::lookUp( $hostName, $type ) as $record )
            return $record;

        return null;
    }

    public static function lookUpARecord( $hostName ) {

        return self::lookUpFirst( $hostName, DnsRecordType::A );
    }

    public static function lookUpMxRecord( $hostName ) {

        return self::lookUpFirst( $hostName, DnsRecordType::MX );
    }

    public static function lookUpTxtRecord( $hostName ) {

        return self::lookUpFirst( $hostName, DnsRecordType::TXT );
    }

    public static function lookUpReverse( IpAddressInterface $ipAddress ) {

        $reverseHostName = self::getReverseHostName( $ipAddress );
        return self::lookUp( $reverseHostName, DnsRecordType::PTR );
    }

    public static function lookUpReverseArray( IpAddressInterface $ipAddress ) {

        return iterator_to_array( self::lookUpReverse( $ipAddress ) );
    }

    public static function lookUpReverseFirst( IpAddressInterface $ipAddress ) {

        $reverseHostName = self::getReverseHostName( $ipAddress );
        return self::lookUpFirst( $reverseHostName, DnsRecordType::PTR );
    }
}