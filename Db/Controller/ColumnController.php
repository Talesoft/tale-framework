<?php

namespace Tale\Db\Controller;

use Tale\Db\ControllerBase,
    Tale\Db\AdapterBase,
    Tale\Db\Column;

class ColumnController extends ControllerBase {

    private $_database;
    private $_tableController;
    private $_table;
    private $_column;

    public function __construct( AdapterBase $adapter, TableController $tableController, Column $column ) {
        parent::__construct( $adapter );

        $this->_database = $tableController->getDatabaseController()->getDatabase();
        $this->_tableController = $tableController;
        $this->_table = $tableController->getTable();
        $this->_column = $column;
    }

    public function getDatabase() {

        return $this->_database;
    }

    public function getTableController() {

        return $this->_tableController;
    }

    public function getTable() {

        return $this->_table;
    }

    public function getColumn() {

        return $this->_column;
    }

    public function create() {

        $this->getAdapter()->createColumn( $this->_database, $this->_table, $this->_column );

        return $this;
    }

    public function exists() {

        return $this->getAdapter()->hasColumn( $this->_database, $this->_table, $this->_column );
    }

    public function load() {

        $this->getAdapter()->loadColumn( $this->_database, $this->_table, $this->_column );

        return $this;
    }

    public function save() {

        $this->getAdapter()->saveColumn( $this->_database, $this->_table, $this->_column );

        return $this;
    }

    public function remove() {

        $this->getAdapter()->removeColumn( $this->_database, $this->_table, $this->_column );

        return $this;
    }
}