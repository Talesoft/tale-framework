<?php

namespace Tale;

class Dispatcher {

    private $_factory;
    private $_nameSpace;
    private $_classNamePattern;
    private $_methodNamePattern;

    public function __construct( Factory $factory, $nameSpace = null, $classNamePattern = null, $methodNamePattern = null ) {

        $this->_factory = $factory;
        $this->_nameSpace = $nameSpace;
        $this->_classNamePattern = $classNamePattern ? $classNamePattern : '%s';
        $this->_methodNamePattern = $methodNamePattern ? $methodNamePattern : '%s';
    }

    /**
     * @return Factory
     */
    public function getFactory() {

        return $this->_factory;
    }

    /**
     * @return null
     */
    public function getNameSpace() {

        return $this->_nameSpace;
    }

    /**
     * @return null|string
     */
    public function getClassNamePattern() {

        return $this->_classNamePattern;
    }

    /**
     * @return string
     */
    public function getMethodNamePattern() {

        return $this->_methodNamePattern;
    }

    public function resolveClassName( $className ) {

        $ns = $this->_nameSpace ? rtrim( $this->_nameSpace, '\\' ).'\\' : '';

        return $ns.sprintf( $this->_classNamePattern, implode( '\\', array_map( function( $name ) {

            return StringUtils::camelize( $name );
        }, explode( '.', $this->_factory->resolveClassName( $className ) ) ) ) );
    }

    public function resolveMethodName( $methodName ) {

        return sprintf(
            $this->_methodNamePattern,
            strpos( $this->_methodNamePattern, '%s' ) === 0
                ? StringUtils::variablize( $methodName )
                : StringUtils::camelize( $methodName )
        );
    }

    public function createInstance( $className, array $args = null ) {

        return new Dispatcher\Instance(
            $this,
            $this->_factory->createInstance( $this->resolveClassName( $className ), $args )
        );
    }
}