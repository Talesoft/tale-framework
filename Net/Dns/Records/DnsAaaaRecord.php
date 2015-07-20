<?php

namespace Tale\Net\Dns\Records;

use Tale\Net\Dns\DnsRecord,
    Tale\Net\Dns\DnsRecordType,
    Tale\Net\Ip\IpAddressInterface;

class DnsAaaaRecord extends DnsRecord {

    public function __construct( $hostName, IpAddressInterface $ipAddress, $ttl ) {
        parent::__construct( $hostName, $ipAddress, $ttl, DnsRecordType::AAAA );
    }
}