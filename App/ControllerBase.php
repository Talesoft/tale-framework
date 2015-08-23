<?php

namespace Tale\App;

use Tale\App;

class ControllerBase {

    private $_args;
    private $_helpers;

    public function __construct( array $args = null, array $helpers = null ) {

        $this->_args = $args ? $args : [];
        $this->_helpers = [];

        if( $helpers ) {

            foreach( $helpers as $name => $callback )
                $this->registerHelper( $name, $callback );
        }
    }

    public function getArgs() {

        return $this->_args;
    }

    public function setArgs( array $args ) {

        foreach( $args as $key => $value )
            $this->setArg( $key, $value );

        return $this;
    }

    public function hasArg( $key ) {

        return isset( $this->_args[ $key ] );
    }

    public function getArg( $key ) {

        return $this->_args[ $key ];
    }

    public function setArg( $key, $value ) {

        $this->_args[ $key ] = $value;

        return $this;
    }

    public function removeArg( $key ) {

        unset( $this->_args[ $key ] );

        return $this;
    }

    public function registerHelper( $name, callable $callback ) {

        if( $callback instanceof \Closure )
            $callback = $callback->bindTo( $this, $this );

        $this->_helpers[ $name ] = $callback;

        return $this;
    }

    public function __isset( $key ) {

        return $this->hasArg( $key );
    }

    public function __unset( $key ) {

        $this->removeArg( $key );
    }

    public function __get( $key ) {

        return $this->getArg( $key );
    }

    public function __set( $key, $value ) {

        $this->setArg( $key, $value );
    }

    public function __call( $method, array $args = null ) {

        if( !isset( $this->_helpers[ $method ] ) )
            throw new \BadMethodCallException( "Invalid helper $method called" );

        return call_user_func_array( $this->_helpers[ $method ], $args ? $args : [] );
    }
}