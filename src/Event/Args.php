<?php

namespace Tale\Event;

class Args {

    private $_data;
    private $_defaultPrevented;

    public function __construct( array $data = null, $defaultPrevented = false ) {

        $this->_data = $data ? $data : [];
        $this->_defaultPrevented = $defaultPrevented;
    }

    public function getData() {

        return $this->_data;
    }

    public function isDefaultPrevented() {

        return $this->_defaultPrevented;
    }

    public function __isset( $key ) {

        return isset( $this->_data[ $key ] );
    }

    public function __get( $key ) {

        return $this->_data[ $key ];
    }
}