<?php

namespace Tale\Db;

use Tale\Data;

class Database extends NamedEntity {

    private $_tables;

    public function __construct( $name, array $tables = null ) {
        parent::__construct( $name );

        $this->_tables = $tables ? $tables : [];
    }

    public function getTables() {

        return $this->_tables;
    }

    public function setTables( array $tables ) {

        $this->_tables = $tables;

        return $this;
    }
}