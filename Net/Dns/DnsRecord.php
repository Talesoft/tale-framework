<?php

namespace Tale\Net\Dns;

//TODO: Implement Records\DnsNaptrRecord

class DnsRecord implements DnsRecordInterface {

    private $_hostName;
    private $_type;
    private $_ttl;

    public function __construct( $hostName, $type, $ttl ) {

        $this->_hostName = $hostName;
        $this->_type = $type;
        $this->_ttl = $ttl;
    }

    public function getHostName() {

        return $this->_hostName;
    }

    public function getType() {

        return $this->_type;
    }

    public function getTypeName() {

        return DnsRecordType::getName( $this->_type );
    }

    public function getTtl() {

        return $this->_ttl;
    }

    public function __toString() {

        return $this->getTypeName().' '.$this->_hostName;
    }
}