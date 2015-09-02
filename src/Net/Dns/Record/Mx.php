<?php

namespace Tale\Net\Dns\Record;

use Tale\Net\Dns\RecordType;

class Mx extends Cname {

    private $_priority;
    
    public function __construct( $hostName, $targetHostName, $priority, $ttl, $type = null ) {
        parent::__construct( $hostName, $targetHostName, $ttl, $type ? $type : RecordType::MX );

        $this->_priority = $priority;
    }

    public function getPriority() {

        return $this->_priority;
    }

    public function __toString() {

        return parent::__toString().' '.$this->_priority;
    }
}