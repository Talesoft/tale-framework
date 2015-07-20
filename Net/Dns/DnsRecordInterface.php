<?php

namespace Tale\Net\Dns;

interface DnsRecordInterface {

    public function getHostName();
    public function getType();
    public function getTtl();
}