<?php

namespace Tale\Config;

use Tale\Config;
use Traversable;

class Container {

    private $_config;

    public function __construct( $config = null, $readOnly = false ) {

        if( $config instanceof Traversable )
            $config = iterator_to_array( $config );

        if( !is_null( $config ) && !is_array( $config ) )
            throw new \InvalidArgumentException( "Failed to create config container: Argument passed must be null, traversable or an array" );

        $flags = Config::FLAG_MUTABLE | Config::FLAG_PROPERTY_ACCESS;

        if( $readOnly )
            $flags |= Config::FLAG_READ_ONLY;

        $this->_config = new Config( $config ? $config : [], $flags );
    }

    public function getConfig() {

        return $this->_config;
    }

    public function setDefaultOptions( array $defaults, $recursive = false ) {

        $this->_config->mergeArray( $defaults, $recursive, true );
        $this->_config->interpolate();

        return $this;
    }

    public function loadConfigFile( $path ) {

        $this->_config->merge( Config::fromFile( $path ), true );
        $this->_config->interpolate();

        return $this;
    }

    public function getOption( $key ) {

        return $this->_config[ $key ];
    }
}