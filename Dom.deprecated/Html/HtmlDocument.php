<?php

namespace Tale\Dom\Html;

use Tale\Dom\DomNode;

class HtmlDocument extends HtmlElement {

    private $_docType;

    public function __construct( $docType = null, array $attributes = null, DomNode $parent = null, array $children = null ) {
        parent::__construct( 'html', $attributes, $parent, $children );

        $this->_docType = $docType ? $docType : HtmlDocumentType::HTML5;
    }

    public function getDocumentType() {

        return $this->_docType;
    }
}