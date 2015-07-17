<?php

namespace Tale\Db;

use Tale\Db;

class Table extends NamedEntity {

    private $_columns;

    public function __construct( $name, array $columns = null ) {
        parent::__construct( $name );

        $this->_columns = $columns ? $columns : [];
    }

    public function getColumns() {

        return $this->_columns;
    }
}