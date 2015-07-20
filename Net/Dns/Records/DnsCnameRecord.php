<?php

namespace Tale\Net\Dns\Records;

use Tale\Net\Dns\DnsRecord,
    Tale\Net\Dns\DnsRecordType;

class DnsCnameRecord extends DnsRecord {

    private $_targetHostName;
    
    public function __construct( $hostName, $targetHostName, $ttl, $type = null ) {
        parent::__construct( $hostName, $type ? $type : DnsRecordType::CNAME, $ttl );

        $this->_targetHostName = $targetHostName;
    }

    public function getTargetHostName() {

        return $this->_targetHostName;
    }

    public function __toString() {

        return parent::__toString().' '.$this->_targetHostName;
    }
}