<?php

namespace Tale\Net\Dns\Record;

use Tale\Net\Dns\RecordType;

class Ns extends Cname {
    
    public function __construct( $hostName, $targetHostName, $ttl ) {
        parent::__construct( $hostName, $targetHostName, $ttl, RecordType::NS );
    }
}