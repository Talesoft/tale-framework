<?php

namespace Tale\Db;

class NamedEntity {

    private $_name;

    public function __construct( $name ) {

        $this->_name = $name;
    }

    public function getName() {

        return $this->_name;
    }
}