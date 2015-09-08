<?php

namespace Tale\Net\Http;

use Tale\StringUtil;

//Headers work a little bit like this:
//You can get a header in ANY casing, hasHeader and getHeader would find User-Agent under user-agent as well as USER-AGENT
//Setting a header (setHeader) in contrast PRESERVES casing. Never mix casing with set right now, keep it constant
//i.e.: getHeader( 'user-agent' ), hasHeader( 'user-agent' ), setHeader( 'User-Agent' )
//The casing you pass to setHeader will be in the final request
//TODO: Check same-header-with-different-casing-existence in setHeader()
class Message
{

    const DEFAULT_PROTOCOL = 'HTTP';
    const DEFAULT_PROTOCOL_VERSION = 1.1;

    private $_headers;
    private $_headerNames;
    private $_body;
    private $_protocol;
    private $_protocolVersion;

    public function __construct(array $headers = null, Body $body = null, $protocol = null, $protocolVersion = null)
    {

        $this->_headers = $headers ? $headers : [];
        $this->_headerNames = [];
        $this->_body = $body ? $body : new Body();
        $this->_protocol = $protocol ? strtoupper($protocol) : self::DEFAULT_PROTOCOL;
        $this->_protocolVersion = $protocolVersion ? floatval($protocolVersion) : self::DEFAULT_PROTOCOL_VERSION;

        $keys = array_keys($this->_headers);
        $this->_headerNames = array_combine(array_map('strtolower', $keys), $keys);
    }

    public function getHeaders()
    {

        return $this->_headers;
    }

    public function getHeaderName($key)
    {

        $key = strtolower($key);

        if (!array_key_exists($key, $this->_headerNames))
            return $key;

        return $this->_headerNames[$key];
    }

    public function setHeaders(array $headers)
    {

        $this->_headers = $headers;

        return $this;
    }

    public function hasHeader($name)
    {

        return array_key_exists($this->getHeaderName($name), $this->_headers);
    }

    public function getHeader($name)
    {

        return $this->_headers[$this->getHeaderName($name)];
    }

    public function getHeaderLine($name)
    {

        $name = $this->getHeaderName($name);
        $value = $this->_headers[$name];

        return "$name: $value";
    }

    public function getHeaderLines()
    {

        $lines = [];
        foreach ($this->_headers as $name => $value)
            $lines[] = $this->getHeaderLine($name);

        return $lines;
    }

    public function setHeader($name, $value)
    {

        $this->_headerNames[strtolower($name)] = $name;
        $this->_headers[$name] = $value;

        return $this;
    }

    public function setHeaderLine($line)
    {

        $parts = StringUtil::map($line, ':', ['name', 'value']);

        return $this->setHeader(trim($parts['name']), trim($parts['value']));
    }

    public function removeHeader($name)
    {

        unset($this->_headers[$this->getHeaderName($name)]);
        unset($this->_headerNames[strtolower($name)]);

        return $this;
    }

    public function getBody()
    {

        return $this->_body;
    }

    public function setBody(Body $body)
    {

        $this->_body = $body;

        return $this;
    }

    public function getProtocol()
    {

        return $this->_protocol;
    }

    public function setProtocol($protocol)
    {

        $this->_protocol = strtoupper($protocol);

        return $this;
    }

    public function getProtocolVersion()
    {

        return $this->_protocolVersion;
    }

    public function setProtocolVersion($protocolVersion)
    {

        $this->_protocolVersion = floatval($protocolVersion);

        return $this;
    }

    public function getString()
    {

        $body = $this->_body;

        $parts = [];
        $headerLines = $this->getHeaderLines();
        foreach ($headerLines as $line) {
            $parts[] = $line;
        }

        if ($body->hasContent()) {

            if (!$this->hasHeader('content-type'))
                $parts[] = 'Content-Type: '.$body->getContentType().'; encoding='.$body->getContentEncoding();

            if (!$this->hasHeader('content-length'))
                $parts[] = 'Content-Length: '.$body->getContentLength();

            $parts[] = '';
            $parts[] = $body->getContent();
        }

        $parts[] = '';

        return implode("\r\n", $parts);
    }

    public function __toString()
    {

        return $this->getString();
    }
}
