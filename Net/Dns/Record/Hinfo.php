<?php

namespace Tale\Net\Dns\Record;

use Tale\Net\Dns\Record;
use Tale\Net\Dns\RecordType;

class Hinfo extends Record {

    private $_cpu;
    private $_os;

    public function __construct( $hostName, $cpu, $os, $ttl ) {
        parent::__construct( $hostName, RecordType::HINFO, $ttl );

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