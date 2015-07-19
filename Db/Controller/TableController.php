<?php

namespace Tale\Db\Controller;

use Tale\Db\ControllerBase,
    Tale\Db\AdapterBase,
    Tale\Db\Table;

class TableController extends ControllerBase {

    private $_databaseController;
    private $_database;
    private $_table;

    public function __construct( AdapterBase $adapter, DatabaseController $databaseController, Table $table ) {
        parent::__construct( $adapter );

        $this->_databaseController = $databaseController;
        $this->_database = $databaseController->getDatabase();
        $this->_table = $table;
    }

    public function getDatabaseController() {

        return $this->_databaseController;
    }

    public function getDatabase() {

        return $this->_database;
    }

    public function getTable() {

        return $this->_table;
    }

    public function create() {

        $this->getAdapter()->createTable( $this->_database, $this->_table );

        return $this;
    }

    public function exists() {

        return $this->getAdapter()->hasTable( $this->_database, $this->_table );
    }

    public function load() {

        $this->getAdapter()->loadTable( $this->_database, $this->_table );

        return $this;
    }

    public function save() {

        $this->getAdapter()->saveTable( $this->_database, $this->_table );

        return $this;
    }

    public function remove() {

        $this->getAdapter()->removeTable( $this->_database, $this->_table );

        return $this;
    }

    public function __get( $columnName ) {


    }
}