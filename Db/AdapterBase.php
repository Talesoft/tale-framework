<?php

namespace Tale\Db;

use Tale\Config;

abstract class AdapterBase {

    private $_config;

    public function __construct( array $options = null ) {

        $this->_config = new Config( $options );
        $this->init();
    }

    public function getConfig() {

        return $this->_config;
    }

    abstract protected function init();

    abstract public function getDatabase( $key, $lifeTime );
    abstract public function get( $key );
    abstract public function set( $key, $value );
    abstract public function remove( $key );
}