<?php

namespace Tale\Net\Dns\Record;

use Tale\Net\Dns\RecordType;

class Ptr extends Cname
{

    public function __construct($hostName, $targetHostName, $ttl)
    {
        parent::__construct($hostName, $targetHostName, $ttl, RecordType::PTR);
    }
}