<?php

namespace Tale\Config;

use Tale\Collection;
use Tale\Config;

trait OptionalTrait {

    private $_config;

    public function getConfigClassName() {

        return 'Tale\\Config';
    }

    /**
     * @return Config
     */
    public function getConfig() {

        if( !isset( $this->_config ) ) {

            $className = $this->getConfigClassName();
            $this->_config = new $className();
        }

        return $this->_config;
    }

    public function addOptions( array $options, $recursive = false ) {

        $this->getConfig()->mergeArray( $options, $recursive )->interpolate();

        return $this;
    }

    public function addOptionFile( $path, $recursive = false ) {

        $this->getConfig()->merge( Collection::fromFile( $path ), $recursive );

        return $this;
    }

    public function addDefaultOptions( array $options, $recursive = false ) {

        $this->getConfig()->mergeArray( $options, $recursive, true );

        return $this;
    }

    public function hasOption( $key ) {

        return $this->getConfig()->hasItem( $key );
    }

    public function getOption( $key ) {

        return $this->getConfig()->getItem( $key );
    }
}