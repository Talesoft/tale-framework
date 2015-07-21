<?php

namespace Tale\Dom;

class Text extends Leaf {

    /**
     * @var string
     */
    private $_text;

    public function __construct( $text, Node $parent = null ) {
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