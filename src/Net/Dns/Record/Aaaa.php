<?php

namespace Tale\Net\Dns\Record;

use Tale\Net\Dns\RecordType;
use Tale\Net\Ip\Address;

class Aaaa extends A {

    public function __construct( $hostName, Address $address, $ttl ) {
        parent::__construct( $hostName, $address, $ttl, RecordType::AAAA );
    }
}