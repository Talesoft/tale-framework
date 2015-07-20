<?php

namespace Tale\Net\Dns\Records;

use Tale\Net\Dns\DnsRecord,
    Tale\Net\Dns\DnsRecordType,
    Tale\Net\Ip\IpAddressInterface;

class DnsA6Record extends DnsARecord {

    private $_chainedHostName;
    private $_maskLength;

    public function __construct( $hostName, $chainedHostName, IpAddressInterface $ipAddress, $maskLength, $ttl ) {
        parent::__construct( $hostName, $ipAddress, $ttl, DnsRecordType::A6 );

        $this->_chainedHostName = $chainedHostName;
        $this->_maskLength = $maskLength;
    }

    public function getChainedHostName() {

        return $this->_chainedHostName;
    }

    public function getMaskLength() {

        return $this->_maskLength;
    }

    public function __toString() {

        return parent::__toString().' '.$this->getChainedHostName().' '.$this->getMaskLength();
    }
}