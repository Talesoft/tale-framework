<?php

namespace Tale;

class Factory {

    private $_baseClassName;
    private $_aliases;

    public function __construct( $baseClassName, array $aliases = null ) {

        $this->_baseClassName = $baseClassName;
        $this->_aliases = $aliases ? $aliases : [];
    }

    public function getBaseClassName() {

        return $this->_baseClassName;
    }

    public function getTypes() {

        return $this->_aliases;
    }

    public function resolveClassName( $className ) {

        if( isset( $this->_aliases[ $className ] ) )
            $className = $this->_aliases[ $className ];

        return $className;
    }

    public function registerAlias( $alias, $className ) {

        $this->_aliases[ $alias ] = $className;

        return $this;
    }

    public function registerAliases( array $aliases ) {

        foreach( $aliases as $alias => $className )
            $this->registerAlias( $alias, $className );

        return $this;
    }

    public function createInstance( $className, array $args = null ) {

        $args = $args ? $args : [];
        $className = $this->resolveClassName( $className );

        if( !class_exists( $className ) || !is_subclass_of( $className, $this->_baseClassName ) )
            throw new \RuntimeException(
                "Failed to create factory instance: "
                . "$className does not exist or is not a valid {$this->_baseClassName}"
            );

        $ref = new \ReflectionClass( $className );
        return $ref->newInstanceArgs( $args );
    }
}