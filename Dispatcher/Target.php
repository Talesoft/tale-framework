<?php

namespace Tale\Dispatcher;

use Tale\Dispatcher;

class Target {

    const INSTANCE_STATIC = 0;
    const INSTANCE_DYNAMIC = 1;

    private $_dispatcher;
    private $_className;
    private $_args;
    private $_instance;
    private $_instanceStyle;

    public function __construct( Dispatcher $dispatcher, $className, array $args = null ) {

        $this->_dispatcher = $dispatcher;
        $this->_className = $className;
        $this->_args = $args;
        $this->_instance = null;
        $this->_instanceStyle = self::INSTANCE_STATIC;
    }

    public function __destruct() {

        $this->_instance = null;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher() {

        return $this->_dispatcher;
    }

    /**
     * @return string
     */
    public function getClassName() {

        return $this->_className;
    }

    /**
     * @return array
     */
    public function getArgs() {

        return $this->_args;
    }

    /**
     * @return int
     */
    public function getInstanceStyle() {

        return $this->_instanceStyle;
    }

    public function setStatic() {

        $this->_instanceStyle = self::INSTANCE_STATIC;

        return $this;
    }

    public function setDynamic() {

        $this->_instanceStyle = self::INSTANCE_DYNAMIC;

        return $this;
    }


    public function getInstance() {

        if( !$this->_instance || $this->_instanceStyle === self::INSTANCE_DYNAMIC )
            $this->_instance = $this->_dispatcher->createInstance( $this->_className, $this->_args );

        return $this->_instance;
    }

    public function call( $method, array $args = null, $resolve = true ) {

        $args = $args ? $args : [];
        $method = $resolve ? $this->_dispatcher->resolveMethodName( $method ) : $method;

        $instance = $this->getInstance();
        if( !method_exists( $instance, $method ) )
            throw new \RuntimeException( "Failed to dispatch method $method: Method not found in ".get_class( $instance ) );

        return call_user_func_array( [ $instance, $method ], $args );
    }

    public function callPattern( $pattern, array $args = null ) {

        $args = $args ? $args : [];

        $results = [];
        $instance = $this->getInstance();
        $ref = new \ReflectionClass( get_class( $instance ) );
        foreach( $ref->getMethods( \ReflectionMethod::IS_PUBLIC ) as $method ) {

            if( $method->isStatic() )
                continue;

            $name = $method->getName();
            if( preg_match( "/($pattern)/", $name, $matches ) )
                $results[ isset( $matches[ 2 ] ) ? $matches[ 2 ] : $matches[ 1 ] ] = call_user_func_array( [ $instance, $name ], $args );
        }

        return $results;
    }

    public function __call( $pattern, array $args = null ) {

        return $this->callPattern( $pattern, $args );
    }
}