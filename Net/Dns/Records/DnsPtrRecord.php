<?php

namespace Tale\Net\Dns\Records;

use Tale\Net\Dns\DnsRecordType;

class DnsPtrRecord extends DnsCnameRecord {
    
    public function __construct( $hostName, $targetHostName, $ttl ) {
        parent::__construct( $hostName, $targetHostName, $ttl, DnsRecordType::PTR );
    }
}