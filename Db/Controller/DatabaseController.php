<?php

namespace Tale\Db\Controller;

use Tale\Db\ControllerBase,
    Tale\Db\AdapterBase,
    Tale\Db\Database,
    Tale\StringUtils;

class DatabaseController extends ControllerBase {

    private $_database;

    public function __construct( AdapterBase $adapter, Database $database ) {
        parent::__construct( $adapter );

        $this->_database = $database;
    }

    public function getDatabase() {

        return $this->_database;
    }

    public function getTables( $force = false ) {

        if( !$this->_tables || $force ) {

            if( $cache = $this->getCache() ) {

                $this->_tables = $cache->load( 'tables', function() {

                    return $this->getAdapter()->getTables( $this->_database );
                } );
            } else {

                $this->_tables = $this->getAdapter()->getTables( $this->_database );
            }
        }

        return $this->_tables;
    }

    public function create() {

        $this->getAdapter()->createDatabase( $this->_database );

        return $this;
    }

    public function exists() {

        return $this->getAdapter()->hasDatabase( $this->_database );
    }

    public function load() {

        $this->getAdapter()->loadDatabase( $this->_database );

        return $this;
    }

    public function save() {

        $this->getAdapter()->saveDatabase( $this->_database );

        return $this;
    }

    public function remove() {

        $this->getAdapter()->removeDatabase( $this->_database );

        return $this;
    }

    public function __get( $tableName ) {

        $cache = null;
        if( $cache = $this->getCache() ) {

            $cache = $cache->getSubCache( 'tables.'.StringUtils::canonicalize( $tableName ) );
        }

        $tableController = $this->getAdapter()->getTableController( $this, $tableName );

        if( $cache ) {

            $tableController->setCache( $cache );
        }

        return $tableController;
    }
}