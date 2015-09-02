<?php

namespace Tale\Dom\Html;

use Tale\Dom\Node;

class Document extends Element {

    private $_documentType;

    public function __construct( $documentType = null, array $attributes = null, Node $parent = null, array $children = null ) {
        parent::__construct( 'html', $attributes, $parent, $children );

        $this->_documentType = $documentType ? $documentType : DocumentType::HTML5;
    }

    public function getDocumentType() {

        return $this->_documentType;
    }
}