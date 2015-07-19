<?php

namespace Tale\Db;

use Tale\Config,
    Tale\Factory,
    Tale\Db\Controller\DatabaseController,
    Tale\Db\Controller\TableController,
    Tale\Db\Controller\ColumnController;

abstract class AdapterBase {

    private $_config;

    /**
     * @var Factory
     */
    private $_valueFactory;
    private $_databases;

    public function __construct( array $options = null ) {

        $this->_config = new Config( $options );
        $this->_databases = [];

        if( isset( $this->_config->valueAliases ) )
            $this->_valueFactory->registerAliases( $this->_config->valueAliases->getOptions() );
    }

    public function getConfig() {

        return $this->_config;
    }

    /* Databases */
    abstract public function getDatabases();
    abstract public function createDatabase( Database $database );
    abstract public function hasDatabase( Database $database );
    abstract public function loadDatabase( Database $database );
    abstract public function saveDatabase( Database $database );
    abstract public function removeDatabase( Database $database );

    /* Tables */
    abstract public function getTables( Database $database );
    abstract public function createTable( Database $database, Table $table );
    abstract public function hasTable( Database $database, Table $table );
    abstract public function loadTable( Database $database, $name );
    abstract public function saveTable( Database $database, Table $table );
    abstract public function removeTable( Database $database, Table $table );

    /* Columns */
    abstract public function getColumns( Database $database, Table $table );
    abstract public function createColumn( Database $database, Table $table, Column $column );
    abstract public function hasColumn( Database $database, Table $table, Column $column );
    abstract public function loadColumn( Database $database, Table $table, Column $column );
    abstract public function saveColumn( Database $database, Table $table, Column $column );
    abstract public function removeColumn( Database $database, Table $table, Column $column );

    /* Rows */
    abstract public function createRow( Database $database, Table $table, $data );
    abstract public function countRows( Database $database, Table $table, Query $query = null );
    abstract public function getRows( Database $database, Table $table, $what, Query $query = null );
    abstract public function saveRows( Database $database, Table $table, $data, Query $query = null );
    abstract public function removeRows( Database $database, Table $table, Query $query = null );


    public function getDatabaseController( $databaseName ) {

        return new DatabaseController( $this, new Database( $databaseName ) );
    }

    public function getTableController( DatabaseController $databaseController, $tableName ) {

        return new TableController( $this, $databaseController, new Table( $tableName ) );
    }

    public function getColumnController( TableController $tableController, $columnName ) {

        return new ColumnController( $this, $tableController, new Column( $columnName ) );
    }

    public function getTypes() {

        return [ 'string', 'int', 'float', 'bool', 'array', 'enum' ];
    }
}