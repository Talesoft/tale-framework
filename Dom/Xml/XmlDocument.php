<?php

namespace Tale\Dom\Xml;

use Tale\Dom\DomElement,
    Tale\Dom\DomNodeInterface;

class XmlDocument extends XmlElement {

    const DEFAULT_VERSION = '1.0';
    const DEFAULT_ENCODING = 'utf-8';

    private $_version;
    private $_encoding;

    public function __construct( $name, array $attributes = null, $version = null, $encoding = null, DomNodeInterface $parent = null, array $children = null ) {
        parent::__construct( $name, $attributes, $parent, $children );

        $this->_version = $version ? $version : self::DEFAULT_VERSION;
        $this->_encoding = $encoding ? $encoding : self::DEFAULT_ENCODING;
    }

    public function getVersion() {

        return $this->_version;
    }

    public function getEncoding() {

        return $this->_encoding;
    }
}