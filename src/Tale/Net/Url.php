<?php

namespace Tale\Net;

use Tale\Environment;
use Tale\StringUtil,
    Exception;

class Url extends Uri
{

    private static $_schemePorts = [
        'ssh'   => Ip\Port::SSH,
        'ftp'   => Ip\Port::FTP,
        'ftps'  => Ip\Port::FTPS,
        'http'  => Ip\Port::HTTP,
        'https' => Ip\Port::HTTPS,
        'ldap'  => Ip\Port::LDAP
    ];

    private static $_parts = [
        'scheme'      => null,
        'user'        => null,
        'pass'        => null,
        'host'        => null,
        'port'        => null,
        'path'        => null,
        'query'       => null,
        'fragment'    => null,

        //Custom (Alias for compat. We call it "password". They call it "pass". I don't care.)
        'domain'      => null,
        'userName'    => null,
        'password'    => null,
        'queryString' => null
    ];

    private $_userName;
    private $_password;
    private $_domain;
    private $_port;
    private $_queryString;
    private $_fragment;

    public function __construct(array $items = null)
    {
        parent::__construct($items = array_replace(self::$_parts, $items ? $items : []));

        $this->_userName = $items['user'];
        $this->_password = $items['pass'] ? $items['pass'] : $items['password'];
        $this->_domain = $items['host'] ? $items['host'] : $items['domain'];
        $this->_port = is_null($items['port']) ? null : intval($items['port']);
        $this->_queryString = $items['query'] ? $items['query'] : $items['queryString'];
        $this->_fragment = $items['fragment'];
    }

    public function hasUserName()
    {

        return !is_null($this->_userName);
    }

    public function getUserName()
    {

        return $this->_userName;
    }

    public function setUserName($userName)
    {

        $this->_userName = $userName;

        return $this;
    }

    public function hasPassword()
    {

        return !is_null($this->_password);
    }

    public function getPassword()
    {

        return $this->_password;
    }

    public function setPassword($password)
    {

        $this->_password = $password;

        return $this;
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

    public function getCredential()
    {

        return new Credential($this->_userName, $this->_password);
    }

    public function getDomainCredential()
    {

        return new Credential\Domain($this->_userName, $this->_password, $this->_domain);
    }

    public function hasPort()
    {

        return !is_null($this->_port);
    }

    public function getPort()
    {

        return $this->_port;
    }

    public function setPort($port)
    {

        $this->_port = is_null($port) ? null : intval($port);

        return $this;
    }

    public function getRequiredPort()
    {

        $port = $this->_port;
        if (!$port) {

            $scheme = $this->getScheme();

            if (!$scheme)
                throw new Exception("Failed to get required port on $this: No port or scheme available");

            if (!array_key_exists($scheme, self::$_schemePorts))
                throw new Exception("Failed to get required port on $this: Scheme $scheme has no default port. Maybe add one with Url::addSchemePort?");

            $port = self::$_schemePorts[$scheme];
        }

        return $port;
    }

    public function getEndPoint()
    {

        if (!$this->_domain)
            throw new Exception("Failed to get endpoint on $this: No domain specified. Use the host-name, ipv4 or [ipv6]");

        return new Ip\EndPoint(Ip\Address::fromDomain($this->_domain), $this->getRequiredPort());
    }

    public function hasQueryString()
    {

        return !is_null($this->_queryString);
    }

    public function getQueryString()
    {

        return $this->_queryString;
    }

    public function setQueryString($queryString)
    {

        $this->_queryString = $queryString;

        return $this;
    }

    public function getQueryArray()
    {

        if (is_null($this->_queryString))
            return [];

        parse_str($this->_queryString, $result);

        return $result;
    }

    public function setQueryArray(array $items)
    {

        $this->_queryString = http_build_query($items);

        return $this;
    }

    public function hasFragment()
    {

        return !is_null($this->_fragment);
    }

    public function getFragment()
    {

        return $this->_fragment;
    }

    public function setFragment($fragment)
    {

        $this->_fragment = $fragment;

        return $this;
    }

    public function getString()
    {

        $scheme = $this->getScheme();
        $parts = [];
        if ($scheme)
            $parts[] = "$scheme:";

        if ($this->_domain)
            // Tim Berners lee apologized for the // in the HTTP url, even though, parse_url parses everything as a path without them :)
            // I guess this way unit tests asserting equal will success
            // Update: I've read that the // was to select a different internet, but it was never needed
            // e.g. http:/some-network-name/some-host.com/some-path
            $parts[] = '//'.(string)$this->getDomainCredential();

        //We don't want to print the port, if it matches the scheme-port above.
        //e.g. when you call http://example.com/some-path, you don't want your URL to end up as
        //http://example.com:80/some-path via conversion. The scheme implies the used port.
        if ($this->_domain
            && $this->_port
            && (!$scheme || !array_key_exists($scheme, self::$_schemePorts) || self::$_schemePorts[$scheme] !== $this->_port)
        )
            $parts[] = ":{$this->_port}";

        if ($this->hasPath())
            $parts[] = $this->getPath();

        if ($this->_queryString)
            $parts[] = "?{$this->_queryString}";

        if ($this->_fragment)
            $parts[] = "#{$this->_fragment}";

        return implode('', $parts);
    }

    public function __toString()
    {

        return $this->getString();
    }

    public static function fromString($string)
    {

        $parts = parse_url($string);

        if (!$parts)
            throw new Exception("Failed to parse URL $string, it seems it's not a valid URL");

        return new static(array_replace(self::$_parts, $parts));
    }

    private static function _validateWebEnvironment()
    {

        if (!Environment::isWeb())
            throw new \Exception(
                "Failed to get URL data: "
                ."The current environment is not a web environment"
            );
    }

    public static function getSchemeFromEnvironment()
    {

        self::_validateWebEnvironment();

        return Environment::getClientOption('REQUEST_SCHEME',
            Environment::getClientOption('HTTPS', '') == 'on' ? 'https' : 'http'
        );
    }

    public static function getHostFromEnvironment()
    {

        self::_validateWebEnvironment();

        return Environment::getClientOption('HTTP_HOST',
            Environment::getClientOption('SERVER_NAME', 'localhost')
        );
    }

    public static function getPortFromEnvironment()
    {

        self::_validateWebEnvironment();

        return Environment::getClientOption('SERVER_PORT');
    }

    public static function getPathFromEnvironment()
    {

        self::_validateWebEnvironment();

        $path = Environment::getClientOption('PATH_INFO');
        if (empty($path)) {

            $path = Environment::getClientOption(
                'REDIRECT_REQUEST_URI',
                Environment::getClientOption('REQUEST_URI', '/')
            );
        }

        return $path;
    }

    public static function getQueryStringFromEnvironment()
    {

        self::_validateWebEnvironment();

        return Environment::getClientOption('redirectQueryString', Environment::getClientOption('queryString'));
    }

    public static function fromEnvironment()
    {

        return new Url([
            'scheme' => self::getSchemeFromEnvironment(),
            'host' => self::getHostFromEnvironment(),
            'port' => self::getPortFromEnvironment(),
            'path' => self::getPathFromEnvironment(),
            'queryString' => self::getQueryStringFromEnvironment()
        ]);
    }
}