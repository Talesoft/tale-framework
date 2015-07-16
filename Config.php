<?php

namespace Tale;

use IteratorAggregate,
    ArrayIterator,
    Countable;

class Config implements IteratorAggregate, Countable {

    private $_options;

    public function __construct( array $options = null ) {

        $this->_options = $options ? $options : [];
    }

    public function getOptions() {

        return $this->_options;
    }

    public function merge( array $options = null, $recursive = true ) {

        $options = $recursive
                 ? array_replace_recursive( $this->_options, $options )
                 : array_replace( $this->_options, $options );

        return new static( $options );
    }

    public function mergeConfig( self $config, $recursive = true ) {

        return $this->merge( $config->getOptions(), $recursive );
    }

    public function interpolate() {

        StringUtils::interpolateArray( $this->_options );

        return $this;
    }

    public function getIterator() {

        return new ArrayIterator( $this->_options );
    }

    public function count() {

        return count( $this->_options );
    }

    public function __isset( $key ) {

        return isset( $this->_options[ $key ] );
    }

    public function __unset( $key ) {

        unset( $this->_options[ $key ] );
    }

    public function __get( $key ) {

        $value = $this->_options[ $key ];

        if( is_array( $value ) )
            return new static( $value );

        return $value;
    }

    public function __set( $key, $value ) {

        $this->_options[ $key ] = $value;
    }

    public static function fromFile( $path ) {

        $json = file_get_contents( $path );
        return new static( json_decode( $json, true ) );
    }
}