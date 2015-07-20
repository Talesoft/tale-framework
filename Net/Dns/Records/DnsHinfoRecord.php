<?php

namespace Tale\Net\Dns\Records;

use Tale\Net\Dns\DnsRecord,
    Tale\Net\Dns\DnsRecordType;

class DnsHinfoRecord extends DnsRecord {

    private $_cpu;
    private $_os;

    public function __construct( $hostName, $cpu, $os, $ttl ) {
        parent::__construct( $hostName, DnsRecordType::HINFO, $ttl );

        $this->_cpu = $cpu;
        $this->_os = $os;
    }

    public function getCpu() {

        return $this->_cpu;
    }

    public function getOs() {

        return $this->_os;
    }

    public function __toString() {

        return parent::__toString().' '.$this->_cpu.' '.$this->_os;
    }
}