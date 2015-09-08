<?php

namespace Tale\Net\Http;

class Response extends Message
{

    private $_statusCode;
    private $_reasonPhrase;

    public function __construct($statusCode = null, $reasonPhrase = null, array $headers = null, Body $body = null)
    {
        parent::__construct($headers, $body);

        $this->_statusCode = $statusCode ? $statusCode : StatusCode::OK;
        $this->_reasonPhrase = $reasonPhrase;
    }

    public function getStatusCode()
    {

        return $this->_statusCode;
    }

    public function setStatusCode($statusCode)
    {

        $this->_statusCode = $statusCode;

        return $this;
    }

    public function hasReasonPhrase($key)
    {

        return !is_null($this->_reasonPhrase);
    }

    public function getReasonPhrase()
    {

        return $this->_reasonPhrase;
    }

    public function setReasonPhrase($reasonPhrase)
    {

        $this->_reasonPhrase = $reasonPhrase;

        return $this;
    }

    public function setLocation($location)
    {

        return $this->setHeader('Location', (string)$location);
    }

    public function getHeadLine()
    {

        $rp = $this->_reasonPhrase;
        if (!$rp)
            $rp = StatusCode::getReasonPhrase($this->_statusCode);

        return implode(' ', [
            $this->getProtocol().'/'.$this->getProtocolVersion(),
            $this->_statusCode,
            $rp
        ]);
    }

    public function applyHeadLine()
    {

        header($this->getHeadLine());

        return $this;
    }

    public function applyHeaders()
    {

        $body = $this->getBody();
        if ($body->hasContent()) {

            if (!$this->hasHeader('content-type'))
                header('Content-Type: '.$body->getContentType().'; encoding='.$body->getContentEncoding());

            if (!$this->hasHeader('content-length'))
                header('Content-Length: '.$body->getContentLength());
        }

        $headers = $this->getHeaderLines();
        foreach ($headers as $line)
            header($line);

        return $this;
    }

    public function applyBody()
    {

        $body = $this->getBody();
        if ($body->hasContent()) {

            echo $body->getContent();
        }

        return $this;
    }

    public function apply()
    {

        if (function_exists('headers_sent') && headers_sent())
            throw new \RuntimeException(
                "Failed to apply response: The headers have already "
                ."been sent out. You made some kind of output "
                ."before apply() has been called on the HTTP response"
            );

        if (function_exists('mb_http_output')) {

            $encoding = $this->getBody()->getContentEncoding();
            mb_http_output(strtoupper($encoding ? $encoding : 'UTF-8'));
            ob_start('mb_output_handler');
        }

        $this->applyHeadLine();
        $this->applyHeaders();
        $this->applyBody();

        return $this;
    }

    public function getString()
    {

        return $this->getHeadLine()."\r\n".parent::getString();
    }
}
