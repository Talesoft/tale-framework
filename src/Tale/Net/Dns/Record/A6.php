<?php

namespace Tale\Net\Dns\Record;

use Tale\Net\Dns\RecordType;
use Tale\Net\Ip\Address;

class A6 extends A
{

    private $_chainedHostName;
    private $_maskLength;

    public function __construct($hostName, $chainedHostName, Address $address, $maskLength, $ttl)
    {
        parent::__construct($hostName, $address, $ttl, RecordType::A6);

        $this->_chainedHostName = $chainedHostName;
        $this->_maskLength = $maskLength;
    }

    public function getChainedHostName()
    {

        return $this->_chainedHostName;
    }

    public function getMaskLength()
    {

        return $this->_maskLength;
    }

    public function __toString()
    {

        return parent::__toString().' '.$this->getChainedHostName().' '.$this->getMaskLength();
    }
}