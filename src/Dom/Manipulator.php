<?php

namespace Tale\Dom;

use Exception,
    IteratorAggregate,
    ArrayIterator,
    Countable,
    Traversable;

//Just try this: var_dump( $m->html( '[lang="de"]' )->html->parent->body->parent )      :)
class Manipulator implements IteratorAggregate, Countable {

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

            return $this->parseElements( Selector::fromString( $elements ) );
        }

        if( is_array( $elements ) )
            return array_filter( $elements, function( $val ) {

                return $val instanceof Element;
            } );

        if( $elements instanceof self ) {

            return $elements->getElements();
        }

        if( $elements instanceof Traversable ) {

            return iterator_to_array( $elements );
        }

        if( $elements instanceof Selector ) {

            $className = static::getElementClassName();
            return [ call_user_func( [ $className, 'fromSelector' ], $elements ) ];
        }

        if( $elements instanceof Element ) {

            return [ $elements ];
        }

        throw new Exception( "Invalid argument $elements passed to Manipulator->parseElements" );
    }

    public function add( $elements ) {

        return new static( array_merge( $this->_elements, $this->parseElements( $elements ) ) );
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

    public function appendOrAdd( $elements ) {

        return count( $this ) ? $this->append( $elements ) : $this->add( $elements );
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

    public function prependOrAdd( $elements ) {

        return count( $this ) ? $this->prepend( $elements ) : $this->add( $elements );
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

        $selector = $selector instanceof Selector || is_callable( $selector ) ? $selector : Selector::fromString( $selector );
        $filter = is_callable( $selector ) ? $selector : function( $el ) use( $selector ) {

            return $el->matches( $selector );
        };

        return new static( array_filter( $this->_elements, $filter ) );
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

        foreach( $this->_elements as $el )
            yield new static( $el );
    }

    public function getString( array $options = null ) {

        $str = '';
        foreach( $this->_elements as $el )
            $str .= $el->getString( $options );

        return $str;
    }

    public function __toString() {

        return $this->getString();
    }

    public function __clone() {

        foreach( $this->_elements as $i => $el )
            $this->_elements[ $i ] = clone $el;
    }

    public function __call( $method, array $args ) {

        //In case you call $m->parent->parent (without ())
        if( method_exists( $this, $method ) ) {

            //Just proxy dat shit
            return call_user_func_array( [ $this, $method ], $args );
        }

        //In case you enter an element ($m->div->table->thead or $m->div()->table()->thead())
        if( !method_exists( static::getElementClassName(), $method ) ) {

            //First we check if there's a direct descendant of this selector already
            $els = $this->find( ">$method" );
            if( count( $els ) ) {

                //Found some sub-elements for this selector, access these
                return $els;
            }

            //This is some kind of auto-creation ($m->html( '[lang="de"]' )->head->parent->body)
            return $this->appendOrAdd( $method.( count( $args ) ? $args[ 0 ] : '' ) );
        }

        //In case you want to act on the elements ($m->getText(), $m->setCss() etc.)
        $result = [];
        foreach( $this->_elements as $el ) {

            $result[] = call_user_func_array( [ $el, $method ], $args );
        }

        if( in_array( $method, [ 'getText', 'getString', 'getAttribute' ] ) )
            return implode( '', $result );

        return $this;
    }

    public function __get( $method ) {

        return $this->__call( $method, [] );
    }

    public function __invoke( $selector ) {

        return $this->find( $selector );
    }

    public static function getElementClassName() {

        return __NAMESPACE__.'\\Element';
    }
}