<?php

namespace Tale;

use IteratorAggregate,
    Countable,
    ArrayAccess,
    Serializable,
    Traversable;

class Collection implements IteratorAggregate, Countable, ArrayAccess, Serializable {

    //Mutability acts on the internal array as a whole, operations directly on array indices are always mutable
    const FLAG_MUTABLE = 1;
    const FLAG_READ_ONLY = 2;
    const FLAG_PROPERTY_ACCESS = 4;

    private $_items;
    private $_flags;

    public function __construct( array $items = null, $flags = null ) {

        $this->_items = $items ? $items : [];
        $this->_flags = $flags ? $flags : self::FLAG_PROPERTY_ACCESS;
    }

    public function isMutable() {

        return ( $this->_flags & self::FLAG_MUTABLE ) !== 0;
    }

    public function isReadOnly() {

        return ( $this->_flags & self::FLAG_READ_ONLY ) !== 0;
    }

    public function hasPropertyAccess() {

        return ( $this->_flags & self::FLAG_PROPERTY_ACCESS ) !== 0;
    }

    public function getItems() {

        return $this->_items;
    }

    public function setItems( array $items = null ) {

        //This shouldn't be affected by ReadOnly, you mostly have a really good reason to replace the whole array
        $this->_items = $items;

        return $this;
    }

    public function getFlags() {

        return $this->_flags;
    }

    public function hasItem( $key ) {

        return isset( $this->_items[ $key ] );
    }

    public function &getItem( $key ) {

        return $this->_items[ $key ];
    }

    public function setItem( $key, $value ) {

        if( $this->isReadOnly() )
            throw new \Exception( "Failed to access key $key: ArrayObject is read-only" );

        if( $key === null )
            $this->_items[] = $value;
        else
            $this->_items[ $key ] = $value;

        return $this;
    }

    public function removeItem( $key ) {

        if( $this->isReadOnly() )
            throw new \Exception( "Failed to access key $key: ArrayObject is read-only" );

        unset( $this->_items[ $key ] );

        return $this;
    }

    public function merge( Traversable $items, $recursive = false, $reverse = false ) {

        return $this->mergeArray( iterator_to_array( $items ), $recursive, $reverse );
    }

    public function mergeArray( array $items, $recursive = false, $reverse = false ) {

        $func = $recursive ? 'array_replace_recursive': 'array_replace';

        $result = $reverse ? $func( $items, $this->_items ) : $func( $this->_items, $items );

        if( $this->isMutable() ) {

            $this->_items = $result;
            return $this;
        }

        return new static( $result, $this->_flags );
    }

    /**
     * Interpolates a multi-dimensional array with another array recursively
     *
     * If no source is given, you get a live interpolation where you can directly interpolate
     * variables that have just been interpolated before
     *
     * This is mostly used for option arrays, e.g. config-files
     *
     * @param array|null $source        The source array for variables. If none given, the input array is taken
     * @param null       $defaultValue  The default value for indices that couldnt be resolved
     * @param string     $delimeter     The delimeter used for multi-dimension access (Default: Dot (.))
     *
     * @return Collection
     */
    public function interpolate( array &$source = null, $defaultValue = null, $delimeter = null ) {

        if( !$this->isMutable() )
            return new static( ArrayUtil::interpolate( $this->_items, $source, $defaultValue, $delimeter ) );

        ArrayUtil::interpolateMutable( $this->_items, $source, $defaultValue, $delimeter );

        return $this;
    }

    public function getIterator() {

        $keys = array_keys( $this->_items );
        foreach( $keys as $key )
            yield $key => $this->getItem( $key );
    }

    public function getCallbackIterator( callable $callback ) {

        foreach( $this as $key => $value )
            yield $key => call_user_func( $callback, $value, $key );
    }

    public function offsetExists( $offset ) {

        return $this->hasItem( $offset );
    }

    public function &offsetGet( $offset ) {

        return $this->getItem( $offset );
    }

    public function offsetSet( $offset, $value ) {

        $this->setItem( $offset, $value );
    }

    public function offsetUnset( $offset ) {

        $this->removeItem( $offset );
    }


    public function serialize() {

        return serialize( $this->_items );
    }

    public function unserialize( $serialized ) {

        $this->_items = unserialize( $serialized );
    }

    public function count() {

        return count( $this->_items );
    }

    function &__get( $name ) {

        if( !$this->hasPropertyAccess() )
            throw new \Exception( "Failed to get property $name: Property not found" );

        return $this->getItem( $name );
    }

    function __set( $name, $value ) {

        if( !$this->hasPropertyAccess() )
            throw new \Exception( "Failed to set property $name: Property not found" );

        $this->setItem( $name, $value );
    }

    function __isset( $name ) {

        if( !$this->hasPropertyAccess() )
            throw new \Exception( "Failed to check property $name: Property not found" );

        return $this->hasItem( $name );
    }

    function __unset( $name ) {

        if( !$this->hasPropertyAccess() )
            throw new \Exception( "Failed to unset property $name: Property not found" );

        $this->removeItem( $name );
    }
}