<?php

namespace Tale;

/**
 * A simple configuration wrapper respresenting a PHP config array
 *
 * @version 1.0
 * @featureState Pending
 *
 * @package Tale
 */
class Config extends Collection {

    /**
     * Create a new Config instance
     *
     * @param array|null $options The initial configuration (e.g. default values)
     */
    public function __construct( array $options = null, $flags = null ) {
        //Config is always mutable and always has property access for consistency reasons
        //Configs can be read-only though, which still makes them mutable, but you can't simply set single keys
        parent::__construct( $options, ( $flags ? $flags : 0 ) | self::FLAG_MUTABLE | self::FLAG_PROPERTY_ACCESS );
    }

    public function setDefaults( array $defaults, $recursive = false ) {

        $this->mergeArray( $defaults, $recursive, true )
             ->interpolate();

        return $this;
    }
}