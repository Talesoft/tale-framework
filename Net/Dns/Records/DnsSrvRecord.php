<?php

namespace Tale\Net\Dns\Records;

use Tale\Net\Dns\DnsRecordType;

class DnsSrvRecord extends DnsMxRecord {

    private $_port;
    private $_weight;
    
    public function __construct( $hostName, $targetHostName, $port, $priority, $weight, $ttl, $type = null ) {
        parent::__construct( $hostName, $targetHostName, $priority, $ttl, DnsRecordType::SRV );

        $this->_port = $port;
        $this->_weight = $weight;
    }

    public function getPort() {

        return $this->_port;
    }

    public function getWeight() {

        return $this->_weight;
    }

    public function __toString() {

        return parent::__toString().' '.$this->_port.' '.$this->_weight;
    }
}