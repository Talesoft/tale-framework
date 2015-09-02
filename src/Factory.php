<?php

namespace Tale;

/**
 * Dynamic class factory pattern
 *
 * Instanciates a class based on a Base-Class and Aliases given
 *
 * Does automatic subclass-checks and alias-conversion
 *
 * @version 1.0
 * @featureState Stable
 *
 * @package Tale
 */
class Factory {

    /**
     * The FQCN of the base class
     *
     * @var string
     */
    private $_baseClassName;

    /**
     * An associative array of aliases
     *
     * @var array
     */
    private $_aliases;

    /**
     * Creates a new factory instance
     *
     * @param string     $baseClassName The base class child-classes should extend from
     * @param array|null $aliases The aliases that can be used in favor of the FQCN (associative)
     */
    public function __construct( $baseClassName = null, array $aliases = null ) {

        $this->_baseClassName = $baseClassName ? $baseClassName : null;
        $this->_aliases = $aliases ? $aliases : [];
    }

    /**
     * Returns the base-class that all children need to extend from
     *
     * @return string
     */
    public function getBaseClassName() {

        return $this->_baseClassName;
    }

    /**
     * Returns the aliases that are currently registered
     *
     * @return array
     */
    public function getAliases() {

        return $this->_aliases;
    }

    /**
     * Registers a new alias with a specific FQCN
     *
     * @param string $alias     The alias the FQDN can be found under
     * @param string $className The FQCN the given alias should map to
     *
     * @return $this
     */
    public function registerAlias( $alias, $className ) {

        $this->_aliases[ $alias ] = $className;

        return $this;
    }

    /**
     * Registers an array of aliases.
     * The aliases should be the keys, the FQCNs the values of the associative array
     *
     * @param array $aliases Associative array of aliases => FQCNs
     *
     * @return $this
     */
    public function registerAliases( array $aliases ) {

        foreach( $aliases as $alias => $className )
            $this->registerAlias( $alias, $className );

        return $this;
    }


    /**
     * Resolves a a class-name or an alias to a FQCN
     *
     * If no alias is found, it returns the class name given
     *
     * @param string $className The class-name to be converted
     *
     * @return string The usable FQCN of the class
     */
    public function resolveClassName( $className ) {

        if( isset( $this->_aliases[ $className ] ) )
            $className = $this->_aliases[ $className ];

        return $className;
    }

    /**
     * Creates a new instance of a class based on a class name or alias given.
     *
     * If the class doesnt exist or doesnt extend the base-class of this factory,
     * a RuntimeException is thrown
     *
     * Uses Reflection internally
     *
     * @see ReflectionClass->newInstanceArgs
     *
     * @param string     $className The alias or FQCN to instanciate
     * @param array|null $args      The arguments that should be passed to the constructor
     *
     * @return object The newly created child-class instance
     */
    public function createInstance( $className, array $args = null ) {

        $args = $args ? $args : [];
        $className = $this->resolveClassName( $className );

        if( !class_exists( $className ) || ( $this->_baseClassName && !is_subclass_of( $className, $this->_baseClassName ) ) )
            throw new \RuntimeException(
                "Failed to create factory instance: "
                . "$className does not exist or is not a valid {$this->_baseClassName}"
            );

        $ref = new \ReflectionClass( $className );
        return $ref->newInstanceArgs( $args );
    }
}