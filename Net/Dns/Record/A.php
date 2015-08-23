<?php

namespace Tale\Net\Dns\Record;

use Tale\Net\Dns\Record;
use Tale\Net\Dns\RecordType;
use Tale\Net\Ip\Address;

class A extends Record {

    private $_address;

    public function __construct( $hostName, Address $address, $ttl, $type = null ) {
        parent::__construct( $hostName, $type ? $type : RecordType::A, $ttl );

        $this->_address = $address;
    }

    public function getAddress() {

        return $this->_address;
    }

    public function getAddressString() {

        return $this->_address->getString();
    }

    public function __toString() {

        return parent::__toString().' '.$this->getAddressString();
    }
}