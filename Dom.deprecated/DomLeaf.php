<?php

namespace Tale\Dom;

class DomLeaf {

    private $_parent;

    public function __construct( DomNode $parent = null ) {

        $this->_parent = null;

        if( !is_null( $parent ) )
            $this->setParent( $parent );
    }

    public function hasParent() {

        return !is_null( $this->_parent );
    }

    public function getParent() {

        return $this->_parent;
    }

    public function setParent( DomNode $parent = null ) {

        if( $this->_parent === $parent )
            return $this;

        if( is_null( $parent ) && $this->hasParent() && $this->getParent()->hasChild( $this ) )
            $this->getParent()->removeChild( $this );

        $this->_parent = $parent;

        if( !is_null( $parent ) && !$parent->hasChild( $this ) )
            $parent->appendChild( $this );

        return $this;
    }

    public function __clone() {

        $this->_parent = null;
    }
}