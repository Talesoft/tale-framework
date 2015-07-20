<?php

namespace Tale\Net\Dns\Records;

use Tale\Net\Dns\DnsRecord,
    Tale\Net\Dns\DnsRecordType;

class DnsSoaRecord extends DnsRecord {

    private $_sourceHostName;
    private $_mailAddress;
    private $_serial;
    private $_refreshInterval;
    private $_retryDelay;
    private $_expireTime;
    private $_minTtl;

    public function __construct( $hostName, $sourceHostName, $mailAddress, $serial, $refreshInterval, $retryDelay, $expireTime, $minTtl, $ttl ) {
        parent::__construct( $hostName, DnsRecordType::SOA, $ttl );

        $this->_sourceHostName = $sourceHostName;
        $this->_mailAddress = $mailAddress;
        $this->_serial = $serial;
        $this->_refreshInterval = $refreshInterval;
        $this->_retryDelay = $retryDelay;
        $this->_expireTime = $expireTime;
        $this->_minTtl = $minTtl;
    }

    public function getSourceHostName() {

        return $this->_sourceHostName;
    }

    public function getMailAddress() {

        return $this->_mailAddress;
    }

    public function getSerial() {

        return $this->_serial;
    }

    public function getRefreshInterval() {

        return $this->_refreshInterval;
    }

    public function getRetryDelay() {

        return $this->_retryDelay;
    }

    public function getExpireTime() {

        return $this->_expireTime;
    }

    public function getMinTtl() {

        return $this->_minTtl;
    }

    public function __toString() {

        $str = implode( ' ', [
            $this->_sourceHostName,
            $this->_mailAddress,
            $this->_serial,
            $this->_refreshInterval,
            $this->_retryDelay,
            $this->_expireTime,
            $this->_minTtl
        ] );

        return parent::__toString()." $str";
    }
}