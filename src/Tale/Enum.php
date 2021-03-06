<?php

namespace Tale;

use ReflectionClass,
    InvalidArgumentException;

/**
 * Static class for enum-style classes to extend
 * Provides a few utilities to work with such enum-style classes easier
 *
 * @version 1.0
 * @stability Stable
 *
 * @package Tale
 */
class Enum {

    /**
     * The constructor is blocked, we don't want enum instances
     */
    private final function __construct() {}

    /**
     * Returns the value of an enum constant
     *
     * @param string $name The name of the enum constant
     *
     * @return mixed The value of the enum constant
     */
    public static function getValue( $name ) {

        return constant( get_called_class()."::$name" );
    }

    /**
     * Returns all defined enum values
     * Names are the keys, values are the values
     *
     * @return array
     */
    public static function getValues() {

        //It doesn't matter. ReflectionClass is cached internally.
        $ref = new ReflectionClass( get_called_class() );
        return $ref->getConstants();
    }

    /**
     * Returns the name for a specific enum value.
     * Notice that it find's the first constant having that value, all constants with the same
     * value after that will be ignored
     *
     * @param mixed $value The value for the constant name to find
     *
     * @return string The name of the constant with the given value
     */
    public static function getName( $value ) {

        $constants = array_flip( static::getValues() );

        if( !isset( $constants[ $value ] ) )
            throw new InvalidArgumentException( "Invalid argument passed to Enum::getName: $value is not a valid value in this enum" );

        return $constants[ $value ];
    }
}