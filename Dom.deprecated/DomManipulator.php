<?php

namespace Tale\Dom;

use Tale\System\Exception,
    IteratorAggregate,
    ArrayIterator,
    Countable,
    Traversable;

class DomManipulator implements IteratorAggregate, Countable {

    private $_elements;

    public function __construct( $elements = null ) {

        $this->_elements = [];

        if( !is_null( $elements ) )
            $this->_elements = $this->parseElements( $elements );
    }

    public function getElements() {

        return $this->_elements;
    }

    public function parseElements( $elements ) {

        if( is_string( $elements ) ) {

            if( strpos( $elements, '<' ) !== false ) {

                $className = static::getElementClassName();
                return [ call_user_func( [ $className, 'fromString' ], $elements ) ];
            }

            return $this->parseElements( DomSelector::fromString( $elements ) );
        }

        if( $elements instanceof DomSelector ) {

            $className = static::getElementClassName();
            return [ call_user_func( [ $className, 'fromSelector' ], $elements ) ];
        }

        if( $elements instanceof DomElement ) {

            return [ $elements ];
        }

        if( $elements instanceof Traversable ) {

            $elements = iterator_to_array( $elements );
        }

        if( is_array( $elements ) )
            return array_filter( $elements, function( $val ) {

                return $val instanceof DomElement;
            } );

        throw new Exception( "Invalid argument $elements passed to DomManipulator->add" );
    }

    public function add( $elements ) {

        return new static( array_merge( $this->_elements, $this->parseElements( $elements ) ) );
    }

    public function addOrAppend( $elements ) {

        return !count( $this ) ? $this->add( $elements ) : $this->append( $elements );
    }

    public function find( $selector ) {

        $result = [];
        foreach( $this->_elements as $el )
            foreach( $el->find( $selector ) as $foundEl )
                $result[] = $foundEl;

        return new static( $result );
    }

    public function parent( $selector = null ) {

        if( is_int( $selector ) ) {

            $result = $this;
            while( $selector-- )
                $result = $result->parent();

            return $result;
        }

        $result = [];
        foreach( $this->_elements as $el ) {

            if( $el->hasParent() ) {

                $p = $el->getParent();
                if( !is_string( $selector ) || $el->matches( $selector ) ) {

                    $result[] = $p;
                }
            }
        }

        return new static( $result );
    }

    public function parents( $selector = null ) {

        $result = $this;
        $parents = $this;
        while( count( $parents = $parents->parent() ) > 0 ) {

            $result = $result->add( !$selector ? $parents : $parents->filter( $selector ) );
        }

        return $result;
    }

    public function root() {

        $current = $this;
        $result = $this;
        while( count( $current = $current->parent() ) )
            $result = $current;

        return $result;
    }

    public function is( $selector = null ) {

        foreach( $this->_elements as $el )
            if( !$el->matches( $selector ) )
                return false;

        return true;
    }

    public function append( $elements ) {

        $elements = $this->parseElements( $elements );
        $result = [];
        foreach( $this->_elements as $el ) {

            foreach( $elements as $appendEl ) {

                $el->appendChild( $result[] = ( clone $appendEl ) );
            }
        }

        return new static( $result );
    }

    public function prepend( $elements ) {

        $elements = $this->parseElements( $elements );
        $result = [];
        foreach( $this->_elements as $el ) {

            foreach( $elements as $prependEl ) {

                $el->prependChild( $result[] = ( clone $prependEl ) );
            }
        }

        return new static( $result );
    }

    public function before( $elements ) {

        $elements = $this->parseElements( $elements );
        $result = [];
        foreach( $this->_elements as $el ) {

            foreach( $elements as $prependEl ) {

                $el->insertBefore( $result[] = ( clone $prependEl ) );
            }
        }

        return new static( $result );
    }

    public function after( $elements ) {

        $elements = $this->parseElements( $elements );
        $result = [];
        foreach( $this->_elements as $el ) {

            foreach( $elements as $prependEl ) {

                $el->insertAfter( $result[] = ( clone $prependEl ) );
            }
        }

        return new static( $result );
    }

    

    public function appendTo( $elements ) {

        $mp = new static( $elements );
        $mp->append( $this );

        return $this;
    }

    public function prependTo( $elements ) {

        $mp = new static( $elements );
        $mp->prepend( $this );

        return $this;
    }

    public function filter( $selector ) {

        $handler = is_callable( $selector ) ? (
            $selector instanceof DomSelector ? $selector : DomSelector::fromString( $selector )
        ) : function( $el ) use( $selector ) {

            return $el->matches( $selector );
        };

        return new static( array_filter( $this->_elements, $handler ) );
    }

    public function map( callable $handler ) {

        return new static( array_map( $handler, $this->_elements, array_keys( $this->_elements ) ) );
    }

    public function clear() {

        foreach( $this->_elements as $el ) {

            $el->removeChildren();
        }

        return $this;
    }

    public function count() {

        return count( $this->_elements );
    }

    public function getIterator() {

        return new ArrayIterator( array_map( function( $el ) {

            return new static( $el );
        }, $this->_elements ) );
    }

    public function __clone() {

        foreach( $this->_elements as $i => $el )
            $this->_elements[ $i ] = $el;
    }

    public function __toString() {

        return $this->getString();
    }

    public function __call( $method, array $args ) {

        if( !method_exists( static::getElementClassName(), $method ) )
            return $this->addOrAppend( $method.( count( $args ) ? $args[ 0 ] : '' ) );

        $result = [];
        foreach( $this->_elements as $el ) {

            $result[] = call_user_func_array( [ $el, $method ], $args );
        }

        if( in_array( $method, [ 'getText', 'getString', 'getAttribute' ] ) )
            return implode( '', $result );

        return $this;
    }

    public static function getElementClassName() {

        return __NAMESPACE__.'\\DomElement';
    }
}