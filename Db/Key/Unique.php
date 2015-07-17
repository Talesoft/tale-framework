<?php

namespace Tale\Db\Key;

use Tale\Db\Key,
    Tale\Db\Column;

class Unique extends Key {

    private $_columns;

    public function __construct( $name, array $columns ) {

        $this->_columns = $columns ? $columns
    }
}