<?php

namespace Tale\Dom;

use Tale\System\Exception;

class DomText extends DomLeaf {

    private $_text;

    public function __construct( $text = null, DomNode $parent = null ) {
        parent::__construct( $parent );

        $this->_text = $text;
    }

    public function getText() {

        return $this->_text;
    }

    public function __toString() {

        return $this->_text;
    }
}