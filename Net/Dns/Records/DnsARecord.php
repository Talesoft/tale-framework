<?php

namespace Tale\Net\Dns\Records;

use Tale\Net\Dns\DnsRecord,
    Tale\Net\Dns\DnsRecordType,
    Tale\Net\Ip\IpAddressInterface;

class DnsARecord extends DnsRecord {

    private $_ipAddress;

    public function __construct( $hostName, IpAddressInterface $ipAddress, $ttl, $type = null ) {
        parent::__construct( $hostName, $type ? $type : DnsRecordType::A, $ttl );

        $this->_ipAddress = $ipAddress;
    }

    public function getIpAddress() {

        return $this->_ipAddress;
    }

    public function getIpAddressString() {

        return $this->_ipAddress->getString();
    }

    public function __toString() {

        return parent::__toString().' '.$this->getIpAddressString();
    }
}