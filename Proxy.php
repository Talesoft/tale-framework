<?php

namespace Tale;

class Proxy {

    const FORWARD_PROPERTIES = 1;
    const FORWARD_CALLS      = 2;

    private $_forwardTarget;
    private $_forwardStyle;

    public function __construct( $forwardTarget, $forwardStyle = null ) {

        if( !is_object( $forwardTarget ) )
            throw new \InvalidArgumentException( "Failed to set proxy target instance: Forward target is not an object" );

        $this->_forwardTarget = $forwardTarget;
        $this->_forwardStyle = $forwardStyle ? $forwardStyle : 0;
    }

    public function getForwardTarget() {

        return $this->_forwardTarget;
    }

    public function getForwardStyle() {

        return $this->_forwardStyle;
    }

    public function isForwardingProperties() {

        return ( $this->_forwardStyle & self::FORWARD_PROPERTIES ) !== 0;
    }

    public function isForwardingCalls() {

        return ( $this->_forwardStyle & self::FORWARD_CALLS ) !== 0;
    }

    function __get( $name ) {

        if( !$this->isForwardingProperties() )
            throw new \Exception( "Failed to get property $name: Proxy doesnt forward properties" );

        return $this->_forwardTarget->{$name};
    }

    function __set( $name, $value ) {

        if( !$this->isForwardingProperties() )
            throw new \Exception( "Failed to set property $name: Proxy doesnt forward properties" );

        $this->_forwardTarget->{$name} = $value;
    }

    function __isset( $name ) {

        if( !$this->isForwardingProperties() )
            throw new \Exception( "Failed to check property $name: Proxy doesnt forward properties" );

        return isset( $this->_forwardTarget->{$name} );
    }


    function __unset( $name ) {

        if( !$this->isForwardingProperties() )
            throw new \Exception( "Failed to access property $name: Proxy doesnt forward properties" );

        unset( $this->_forwardTarget->{$name} );
    }

    function __call( $name, $arguments ) {

        if( !$this->isForwardingCalls() )
            throw new \BadMethodCallException( "Failed to call method on forward target: Proxy doesnt forward calls" );

        return call_user_func_array( [ $this->_forwardTarget, $name ], $arguments );
    }
}