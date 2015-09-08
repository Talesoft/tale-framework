<?php

namespace Tale\Net\Credential;

use Tale\Net\Credential;

class Domain extends Credential
{

    private $_domain;

    public function __construct($userName = null, $password = null, $domain = null)
    {
        parent::__construct($userName, $password);

        $this->_domain = $domain;
    }

    public function hasDomain()
    {

        return !is_null($this->_domain);
    }

    public function getDomain()
    {

        return $this->_domain;
    }

    public function setDomain($domain)
    {

        $this->_domain = $domain;

        return $this;
    }

    public function getString()
    {

        $auth = parent::getString();

        return empty($auth) ? $this->_domain : "$auth@{$this->_domain}";
    }

    public static function fromString($string)
    {

        $parts = array_replace([
            'host' => null,
            'user' => null,
            'pass' => null
        ], parse_url('//'.ltrim($string, '/')));

        return new static($parts['user'], $parts['pass'], $parts['host']);
    }
}