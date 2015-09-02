<?php

namespace Tale\Net\Dns\Record;

use Tale\Net\Dns\Record;
use Tale\Net\Dns\RecordType;

class Cname extends Record {

    private $_targetHostName;
    
    public function __construct( $hostName, $targetHostName, $ttl, $type = null ) {
        parent::__construct( $hostName, $type ? $type : RecordType::CNAME, $ttl );

        $this->_targetHostName = $targetHostName;
    }

    public function getTargetHostName() {

        return $this->_targetHostName;
    }

    public function __toString() {

        return parent::__toString().' '.$this->_targetHostName;
    }
}