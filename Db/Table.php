<?php

namespace Tale\Db;

class Table {

    private $_name;

    public function __construct( $name ) {

        $this->_name = $name;
    }
}