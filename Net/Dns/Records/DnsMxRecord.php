<?php

namespace Tale\Net\Dns\Records;

use Tale\Net\Dns\DnsRecordType;

class DnsMxRecord extends DnsCnameRecord {

    private $_priority;
    
    public function __construct( $hostName, $targetHostName, $priority, $ttl, $type = null ) {
        parent::__construct( $hostName, $targetHostName, $ttl, $type ? $type : DnsRecordType::MX );

        $this->_priority = $priority;
    }

    public function getPriority() {

        return $this->_priority;
    }

    public function __toString() {

        return parent::__toString().' '.$this->_priority;
    }
}