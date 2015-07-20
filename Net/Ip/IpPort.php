<?php

namespace Tale\Net\Ip;

use Tale\System\Enum;

class IpPort extends Enum {

    const TCPMUX = 1;
    const COMPRESSNET = 3;
    const RJE = 5;
    const INET_ECHO = 7;
    const DISCARD = 9;
    const SYSTAT = 11;
    const DAYTIME = 13;
    const QOTD = 17;
    const CHARGEN = 19;
    const FTP_DATA = 20;
    const FTP = 21;
    const SSH = 22;
    const TELNET = 23;
    const SMTP = 25;
    const NSW_FE = 27;
    const MSG_ICP = 29;
    const MSG_AUTH = 31;
    const DSP = 33;
    const TIME = 37;

    const DOMAIN = 53;

    const HTTP = 80;

    const LDAP = 389;

    const HTTPS = 443;

    const FTPS_DATA = 989;
    const FTPS = 990;
    const TELNETS = 992;
    const IMAPS = 993;
    const POP3S = 995;
}