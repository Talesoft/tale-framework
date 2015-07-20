<?php

namespace Tale\Dom;

class DomElement extends DomNode {

    const ATTRIBUTE_ID = 'id';
    const ATTRIBUTE_CLASS = 'class';

    private $_tag;
    private $_attributes;

    public function __construct( $tag, array $attributes = null, DomNode $parent = null, array $children = null ) {
        parent::__construct( $parent, $children );

        $this->_tag = $tag;
        $this->_attributes = new DomAttributeSet( $attributes );
    }

    public function getTag() {

        return $this->_tag;
    }

    public function setTag( $name ) {

        $this->_tag = $name;

        return $this;
    }

    public function hasAttributes() {

        return count( $this->_attributes ) > 0;
    }

    public function getAttributes() {

        return $this->_attributes;
    }

    public function setAttributes( DomAttributeSet $attributes ) {

        $this->_attributes = $attributes;

        return $this;
    }

    public function hasAttribute( $name ) {

        return isset( $this->_attributes[ $name ] );
    }

    public function getAttribute( $name ) {

        return $this->_attributes[ $name ];
    }

    public function setAttribute( $name, $value ) {

        $this->_attributes[ $name ] = $value;

        return $this;
    }

    public function removeAttribute( $name ) {

        unset( $this->_attributes[ $name ] );

        return $this;
    }

    public function hasId() {

        return $this->hasAttribute( self::ATTRIBUTE_ID );
    }

    public function getId() {

        return $this->getAttribute( self::ATTRIBUTE_ID );
    }

    public function setId( $id ) {

        return $this->setAttribute( self::ATTRIBUTE_ID, $id );
    }

    public function hasClasses() {

        return $this->hasAttribute( self::ATTRIBUTE_CLASS );
    }

    public function getClasses() {

        return $this->getAttribute( self::ATTRIBUTE_CLASS );
    }

    public function setClasses( $classes ) {

        return $this->setAttribute( self::ATTRIBUTE_CLASS, $classes );
    }

    public function getClassesArray() {

        if( !$this->hasClasses() )
            return [];

        return explode( ' ', $this->getClasses() );
    }

    public function setClassesArray( array $classes ) {

        return $this->setClasses( implode( ' ', $classes ) );
    }

    public function hasClass( $class ) {

        if( !$this->hasClasses() )
            return false;

        return in_array( $class, $this->getClassesArray() );
    }

    public function appendClass( $class ) {

        $classes = $this->getClassesArray();
        $classes[] = $class;

        return $this->setClassesArray( $classes );
    }

    public function prependClass( $class ) {

        $classes = $this->getClassesArray();
        array_unshift( $classes, $class );

        return $this->setClassesArray( $classes );
    }

    public function removeClass( $class ) {

        $classes = $this->getClassesArray();
        $idx = array_search( $class, $classes );

        if( $idx === false )
            return $this;

        unset( $classes[ $idx ] );

        return $this->setClassesArray( $classes );
    }

    public function getText() {

        return trim( implode( '',$this->getTextArray() ) );
    }

    public function setText( $text ) {

        return $this->removeChildren()->appendChild( new DomText( $text ) );
    }

    public function matches( $selector ) {

        $selector = $selector instanceof DomSelector ? $selector : DomSelector::fromString( $selector );

        return $selector->matches( $this );
    }

    public function findElements( $selector, $recursive = false ) {

        $selector = $selector instanceof DomSelector ? $selector : DomSelector::fromString( $selector );

        foreach( $this->getElements() as $child ) {

            if( $child instanceof DomElement ) {

                if( $child->matches( $selector ) )
                    yield $child;

                if( $recursive )
                    foreach( $child->findElements( $selector, $recursive ) as $subChild )
                        yield $subChild;
            }
        }
    }

    public function findElementArray( $selector, $recursive = false ) {

        return iterator_to_array( $this->findElements( $selector, $recursive ) );
    }

    public function find( $selectors ) {

        //We add a , to the selector to trigger the "," selector below and flush the results
        $selectors = preg_split( '/(,| |>)/', "$selectors,", -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY );

        $recursive = true;
        $currentSet = [ $this ];
        foreach( $selectors as $selector ) {

            $selector = trim( $selector );

            if( empty( $selector ) )
                continue;

            if( $selector === '>' ) {

                $recursive = false;
                continue;
            }

            if( $selector === ',' ) {

                foreach( $currentSet as $child )
                    yield $child;

                $recursive = true;
                $currentSet = [ $this ];
                continue;
            }

            foreach( $currentSet as $child )
                $currentSet = $child->findElementArray( $selector, $recursive );

            $recursive = true;
        }
    }

    public function findArray( $selectors ) {

        return iterator_to_array( $this->find( $selectors ) );
    }

    public function getString( $pretty = null, $bufferSize = 8192 ) {

        $writerClassName = static::getWriterClassName();
        $writer = call_user_func( [ $writerClassName, 'createOnMemory' ] );
        $writer->writeElement( $this, $pretty );

        $writer->seekStart();

        $str = '';
        while( $buf = $writer->read( $bufferSize ) )
            $str .= $buf;

        return $str;
    }

    public function __toString() {

        return $this->getString();
    }

    public function __clone() {
        parent::__clone();
        
        $this->_attributes = clone $this->_attributes;
    }

    public static function fromSelector( $selector, DomNode $parent = null, array $children = null ) {

        $selector = $selector instanceof DomSelector ? $selector : DomSelector::fromString( $selector );

        $tag = $selector->getTag();
        $el = new static( $tag ? $tag : 'div', $selector->getAttributes(), $parent, $children );

        if( $id = $selector->getId() )
            $el->setId( $id );

        if( $classes = $selector->getClasses() )
            foreach( $classes as $class )
                $el->appendClass( $class );

        return $el;
    }

    public static function fromString( $string, DomNode $parent = null, array $children = null ) {

        $readerClassName = static::getReaderClassName();
        $reader = call_user_func( [ $readerClassName, 'createOnMemory' ] );
        $reader->write( $string, strlen( $string ) );
        $reader->seekStart();

        return $reader->readElement();
    }

    public static function getReaderClassName() {

        return __NAMESPACE__.'\\DomReader';
    }

    public static function getWriterClassName() {

        return __NAMESPACE__.'\\DomWriter';
    }
}