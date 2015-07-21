<?php

namespace Tale\Dom;

use Exception;

class Node extends Leaf {

    private $_children;
    private $_textClassName;
    private $_elementClassName;

    public function __construct( Node $parent = null, array $children = null ) {
        parent::__construct( $parent );

        $this->_children = [];

        if( !is_null( $children ) )
            $this->setChildren( $children );
    }

    public function hasChildren() {

        return !empty( $this->_children );
    }

    public function getChildIndex( Leaf $child ) {

        return array_search( $child, $this->_children, true );
    }

    public function getIndex() {

        if( !$this->hasParent() )
            return null;

        return $this->getParent()->getChildIndex( $this );
    }

    public function getChildren() {

        return $this->_children;
    }

    public function setChildren( array $children ) {

        $this->_children = $children;

        foreach( $this->_children as $child )
            if( $child->getParent() !== $this )
                $child->setParent( $this );

        return $this;
    }

    public function hasChild( Leaf $child ) {

        return in_array( $child, $this->_children, true );
    }

    public function getChildAt( $index ) {

        return $this->_children[ $index ];
    }

    public function getPreviousChild() {

        $idx = $this->getIndex();

        if( !$idx )
            return null;

        return $this->getParent()->getChildAt( $idx - 1 );
    }

    public function getNextChild() {

        $idx = $this->getIndex();

        if( is_null( $idx ) || $idx >= count( $this->getParent() ) )
            return null;

        return $this->getParent()->getChildAt( $idx + 1 );
    }

    private function _prepareChild( Leaf $child ) {

        if( $this->hasChild( $child ) )
            $this->removeChild( $child );
    }

    private function _finishChild( Leaf $child ) {

        if( $child->getParent() !== $this )
            $child->setParent( $this );
    }

    public function appendChild( Leaf $child ) {

        $this->_prepareChild( $child );
        $this->_children[] = $child;
        $this->_finishChild( $child );

        return $this;
    }

    public function prependChild( Leaf $child ) {

        $this->_prepareChild( $child );
        array_unshift( $this->_children, $child );
        $this->_finishChild( $child );

        return $this;
    }

    public function removeChild( Leaf $child ) {

        $idx = array_search( $child, $this->_children, true );

        if( $idx !== false ) {

            unset( $this->_children[ $idx ] );
            $child->setParent( null );
        }

        return $this;
    }

    public function insertBefore( Leaf $newChild, Leaf $child = null ) {

        if( !$child ) {

            if( !$this->hasParent() )
                throw new Exception( "Failed to insert before: Current child has no parent and thus cant have siblings" );

            return $this->getParent()->insertBefore( $newChild, $this );
        }

        if( !$this->hasChild( $child ) )
            throw new Exception( "Failed to insert before: Passed child is not a child of element to insert in" );

        $this->_prepareChild( $newChild );
        array_splice( $this->_children , $child->getIndex(), 0, [ $newChild ] );
        $this->_finishChild( $newChild );

        return $this;
    }

    public function insertAfter( Leaf $newChild, Leaf $child = null ) {

        if( !$child ) {

            if( !$this->hasParent() )
                throw new Exception( "Failed to insert after: Current child has no parent and thus cant have siblings" );

            return $this->getParent()->insertAfter( $newChild, $this );
        }

        if( !$this->hasChild( $child ) )
            throw new Exception( "Failed to insert after: Passed child is not a child of element to insert in" );

        $this->_prepareChild( $newChild );
        array_splice( $this->_children, $child->getIndex() + 1, 0, [ $newChild ] );
        $this->_finishChild( $newChild );

        return $this;
    }

    public function removeChildren() {

        foreach( $this->_children as $child )
            $child->setParent( null );

        $this->_children = [];

        return $this;
    }

    public function setTextClassName( $className ) {

        $this->_textClassName = $className;

        return $this;
    }

    public function setElementClassName( $className ) {

        $this->_elementClassName = $className;

        return $this;
    }

    public function getTexts() {

        foreach( $this->_children as $child )
            if( $child instanceof Text )
                yield $child;
    }

    public function getTextArray() {

        return iterator_to_array( $this->getTexts() );
    }

    public function getElements() {

        foreach( $this->_children as $child )
            if( $child instanceof Element )
                yield $child;
    }

    public function getElementArray() {

        return iterator_to_array( $this->getElements() );
    }

    public function __clone() {
        parent::__clone();

        foreach( $this->_children as $i => $child )
            $this->_children[ $i ] = clone $child;
    }
}
