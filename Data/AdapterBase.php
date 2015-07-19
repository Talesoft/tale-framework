<?php

namespace Tale\Data;

use Tale\Config;

abstract class AdapterBase {

    private $_config;

    public function __construct( array $options = null ) {

        $this->_config = new Config( array_replace_recursive( [
        	'inflections' => [
                'databases' => null,
                'tables' => null,
                'columns' => null,
                'inputColumns' => null,
                'outputColumns' => null
            ]
        ], $options ? $options : [] ) );
    }

    public function __destruct() {

    	$this->close();
    }

    public function getConfig() {

        return $this->_config;
    }

    public function inflectDatabaseName( $string ) {

        if( !$this->_config->inflections->databases )
            return $string;

        return call_user_func( $this->_config->inflections->databases, $string );
    }

    public function inflectTableName( $string ) {

        if( !$this->_config->inflections->tables )
            return $string;

        return call_user_func( $this->_config->inflections->tables, $string );
    }

    public function inflectColumnName( $string ) {

        if( !$this->_config->inflections->columns )
            return $string;

        return call_user_func( $this->_config->inflections->columns, $string );
    }

    public function inflectInputColumnName( $string ) {

        if( !$this->_config->inflections->inputColumns )
            return $string;

        return call_user_func( $this->_config->inflections->inputColumns, $string );
    }

    public function inflectOutputColumnName( $string ) {

        if( !$this->_config->inflections->outputColumns )
            return $string;

        return call_user_func( $this->_config->inflections->outputColumns, $string );
    }


    abstract public function open();
    abstract public function close();
    abstract public function isOpen();

    abstract public function encode( $value );
    abstract public function decode( $value );

    abstract public function getDatabaseNames();

    abstract public function hasDatabase( Database $database );
    abstract public function loadDatabase( Database $database );
    abstract public function saveDatabase( Database $database );
    abstract public function createDatabase( Database $database );
    abstract public function removeDatabase( Database $database );




    abstract public function getTableNames( Database $database );

    abstract public function hasTable( Table $table );
    abstract public function loadTable( Table $table );
    abstract public function saveTable( Table $table );
    abstract public function createTable( Table $table, array $columns );
    abstract public function removeTable( Table $table );




    abstract public function getColumnNames( Table $table );

    abstract public function hasColumn( Column $column );
    abstract public function loadColumn( Column $column );
    abstract public function saveColumn( Column $column );
    abstract public function createColumn( Column $column );
    abstract public function removeColumn( Column $column );




    abstract public function countRows( Query $query, $field = null, $distinct = false );
    abstract public function loadRows( Query $query, array $fields = null, $as = null );
    abstract public function saveRows( Query $query, array $data );
    abstract public function createRow( Table $table, array $data );
    abstract public function removeRows( Query $query );

    abstract public function getLastId();
}