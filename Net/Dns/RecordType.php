<?php

namespace Tale\Net\Dns;

use Tale\Enum;

class RecordType extends Enum {

    const A = \DNS_A;
    const MX = \DNS_MX;
    const NS = \DNS_NS;
    const SOA = \DNS_SOA;
    const PTR = \DNS_PTR;
    const CNAME = \DNS_CNAME;
    const AAAA = \DNS_AAAA;
    const A6 = \DNS_A6;
    const SRV = \DNS_SRV;
    const NAPTR = \DNS_NAPTR;
    const HINFO = \DNS_HINFO;
    const TXT = \DNS_TXT;
    const ALL = \DNS_ALL;
    const ANY = \DNS_ANY;
}