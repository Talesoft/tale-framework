<?php

namespace Tale\Net\Dns\Records;

use Tale\Net\Dns\DnsRecord,
    Tale\Net\Dns\DnsRecordType;

class DnsTxtRecord extends DnsRecord {

    private $_text;
    
    public function __construct( $hostName, $text, $ttl ) {
        parent::__construct( $hostName, DnsRecordType::TXT, $ttl );

        $this->_text = $text;
    }

    public function getText() {

        return $this->_text;
    }

    public function __toString() {

        return parent::__toString().' '.$this->_text;
    }
}