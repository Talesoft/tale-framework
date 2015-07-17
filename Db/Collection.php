<?php

namespace Tale\Db;

class Collection {

    private $_name;

    public function __construct( $name ) {

        $this->_name = $name;
    }
}