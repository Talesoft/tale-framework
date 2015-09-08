<?php

namespace Tale\Net\Http;

use Tale\Net\Mime\Type;

class Body
{

    const DEFAULT_ENCODING = 'utf-8';

    private $_content;
    private $_contentType;
    private $_contentEncoding;

    public function __construct($content = null, $contentType = null, $contentEncoding = null)
    {

        $this->_content = $content ? $content : '';
        $this->_contentType = $contentType ? $contentType : Type::PLAIN;
        $this->_contentEncoding = $contentEncoding ? $contentEncoding : self::DEFAULT_ENCODING;
    }

    public function hasContent()
    {

        return !empty($this->_content);
    }

    public function getContent()
    {

        return $this->_content;
    }

    public function setContent($content)
    {

        $this->_content = $content;

        return $this;
    }

    public function getContentType()
    {

        return $this->_contentType;
    }

    public function setContentType($contentType)
    {

        $this->_contentType = $contentType;

        return $this;
    }

    public function getContentEncoding()
    {

        return $this->_contentEncoding;
    }

    public function setContentEncoding($contentEncoding)
    {

        $this->_contentEncoding = $contentEncoding;

        return $this;
    }

    public function getContentLength()
    {

        return strlen($this->_content);
    }

    public function getContentArray()
    {

        parse_str($this->_content, $result);

        return $result;
    }

    public function setContentArray(array $items)
    {

        $this->_content = http_build_query($items);

        return $this;
    }

    public function clearContent()
    {

        $this->_content = '';

        return $this;
    }

    public function appendContent($content)
    {

        $this->_content .= $content;

        return $this;
    }

    public function prependContent($content)
    {

        $this->_content = $content.$this->_content;

        return $this;
    }

    public function getString()
    {

        return $this->_content;
    }

    public function __toString()
    {

        return $this->getString();
    }
}