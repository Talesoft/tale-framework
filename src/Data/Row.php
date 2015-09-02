<?php

namespace Tale\Data;

use Exception,
    BadMethodCallException,
    Tale\StringUtil;

class Row extends EntityBase {

    private $_data;

    private $_table;
    private $_synced;
    private $_indexColumn;

    public function __construct( Table $table, array $data = null ) {
        parent::__construct();

        $this->_data = $data ? $data : [];
        $this->_table = $table;
        $this->_synced = false;
        $this->_indexColumn = null;
    }

    public function getTable() {

        return $this->_table;
    }

    public function getDatabase() {

        return $this->_table->getDatabase();
    }

    public function getSource() {

        return $this->_table->getSource();
    }

    public function exists() {

        $pk = $this->getTable()->getPrimaryKeyName( true );

        if( !$pk || !isset( $this->_data[ $pk ] ) )
            throw new Exception( "Failed to check row existence: Primary column $pk has no value" );

        return $this->_table->selectOne( [ $pk => $this[ $pk ] ] ) ? true : false;
    }

    public function load() {

        $pk = $this->getTable()->getPrimaryKeyName( true );

        if( !$pk || !isset( $this->_data[ $pk ] ) )
            throw new Exception( "Failed to check row existence: Primary column $pk has no value" );

        $this->_data = $this->_table->where( [
            $pk => $this->_data[ $pk ]
        ] )->selectOne( null, false );

        return $this->sync();
    }

    public function save() {

        $pk = $this->getTable()->getPrimaryKeyName( true );

        if( !$pk || !isset( $this->_data[ $pk ] ) )
            throw new Exception( "Failed to check row existence: Primary column $pk has no value" );

        $data = $this->_data;

        //Unset the primary key in the passed data, we don't want it to end up in setting the ID
        unset( $data[ $pk ] );

        $this->_table->where( [
            $pk => $this->_data[ $pk ]
        ] )->update( $data );

        return $this->sync();
    }

    public function remove() {

        $pk = $this->getTable()->getPrimaryKeyName( true );

        if( !$pk || !isset( $this->_data[ $pk ] ) )
            throw new Exception( "Failed to check row existence: Primary column $pk has no value" );

        $this->_table->where( [
            $pk => $this->_data[ $pk ]
        ] )->remove();

        return $this->unsync();
    }

    public function create( array $data = null ) {

        $pk = $this->getTable()->getPrimaryKeyName( true );

        $this->_data[ $pk ] = null;
        $id = $this->_table->insert( $this->_data );
        $this->_data[ $pk ] = $id;

        return $this->sync();
    }

    public function getOneQuery( Table $table, $columnName = null ) {

        list( $thisKey, $key ) = $this->getTable()->getReferenceKeyNames( $table, $columnName );

        return $table->where( [
            $key => $this->_data[ $thisKey ]
        ] );
    }

    public function setOne( Row $row, $columnName = null ) {

        $table = $row->getTable();

        list( $thisKey, $key ) = $this->getTable()->getReferenceKeyNames( $table, $columnName );

        if( !isset( $row[ $key ] ) )
            throw new Exception( "Failed to setOne( $table, row ): Column $key is not loaded on row. Try load() or a full select?" );

        $this[ $thisKey ] = $row[ $key ];

        return $this;
    }

    public function countOne( Table $table, $columnName = null, $field = null, $distinct = false ) {

        return $this->getOneQuery( $table, $columnName )->count( $field, $distinct );
    }

    public function selectOne( Table $table, $columnName = null, array $fields = null, $as = null ) {

        return $this->getOneQuery( $table, $columnName )->selectOne( $fields, $as );
    }

    public function updateOne( Table $table, $columnName = null, array $data ) {

        return $this->getOneQuery( $table, $columnName )->update( $data );
    }

    public function removeOne( Table $table, $columnName = null) {

        return $this->getOneQuery( $table, $columnName )->remove();
    }

    public function getManyQuery( Table $table, $otherColumnName = null ) {

        list( $key, $thisKey ) = $table->getReferenceKeyNames( $this->getTable(), $otherColumnName );

        return $table->where( [
            $key => $this->_data[ $thisKey ]
        ] );
    }

    public function setMany( Table $table, $rows, $otherColumnName = null ) {

        list( $thisKey, $key ) = $this->getManyKeys( $table, $otherColumnName );

        foreach( $rows as $row ) {

            if( !( $table->equals( $row->getTable() ) ) )
                throw new Exception( "Failed to setMany( $table, row ): Passed row's table doesnt match expected table" );

            $row[ $key ] = $this->_data[ $thisKey ];
            yield $row;
        }
    }

    public function setManyArray( Table $table, $rows, $otherColumnName = null ) {

        return iterator_to_array( $this->setMany( $table, $rows, $otherColumnName ) );
    }

    public function countMany( Table $table, $otherColumnName = null, $field = null, $distinct = false ) {

        return $this->getManyQuery( $table, $otherColumnName )->count( $field, $distinct );
    }

    public function selectMany( Table $table, $otherColumnName = null, array $fields = null, $as = null ) {

        return $this->getManyQuery( $table, $otherColumnName )->select( $fields, $as );
    }

    public function selectManyArray( Table $table, $otherColumnName = null, array $fields = null, $as = null ) {

        return $this->getManyQuery( $table, $otherColumnName )->selectArray( $fields, $as );
    }

    public function updateMany( Table $table, $otherColumnName = null, array $data ) {

        return $this->getManyQuery( $table, $otherColumnName )->update( $data );
    }

    public function removeMany( Table $table, $otherColumnName = null ) {

        return $this->getManyQuery( $table, $otherColumnName )->remove();
    }

    public function getManyOneQueries( Table $table, Table $glueTable, $columnName = null, $otherColumnName = null) {

        foreach( $this->selectMany( $glueTable, $columnName ) as $glueRow )
            yield $glueRow->getOneQuery( $table, $otherColumnName );
    }

    public function getManyManyQueries( Table $table, Table $glueTable, $columnName = null, $otherColumnName = null) {

        foreach( $this->selectMany( $glueTable, $columnName ) as $glueRow )
            yield $glueRow->getManyQuery( $table, $otherColumnName );
    }

    public function addManyOne( Table $table, Table $glueTable, Row $row, $columnName = null, $otherColumnName = null ) {

        list( $thisKey, $thisGlueKey ) = $this->getTable()->getReferenceKeyNames( $glueTable, $columnName );
        list( $otherKey, $otherGlueKey ) = $table->getReferenceKeyNames( $glueTable, $otherColumnName );

        if( !$table->equals( $row->getTable() ) )
            throw new Exception( "Failed to addManyOne( $table, row ): Passed row's table doesnt match expected table $table" );

        if( !isset( $this->_data[ $thisKey ] ) )
            throw new Exception( "Failed to addManyOne: Row has no $thisKey value" );

        if( !isset( $row->_data[ $otherKey ] ) )
            throw new Exception( "Failed to addManyOne: Row has no $otherKey value" );

        return $glueTable->insert( [
            $thisGlueKey => $this->_data[ $thisKey ],
            $otherGlueKey => $this->_data[ $otherKey ]
        ] );
    }

    public function countManyOne( Table $table, Table $glueTable, $columnName = null, $otherColumnName = null, $field = null, $distinct = false ) {

        foreach( $this->getManyOneQueries( $table, $glueTable, $columnName, $otherColumnName ) as $qry )
            yield $qry->count( $field, $distinct );
    }

    public function countManyOneArray( Table $table, Table $glueTable, $columnName = null, $otherColumnName = null, $field = null, $distinct = false ) {

        return iterator_to_array( $this->countManyOne( $table, $glueTable, $columnName, $otherColumnName, $field, $distinct ) );
    }

    public function countManyMany( Table $table, Table $glueTable, $columnName = null, $otherColumnName = null, $field = null, $distinct = false ) {

        foreach( $this->getManyManyQueries( $table, $glueTable, $columnName, $otherColumnName ) as $qry )
            yield $qry->count( $field, $distinct );
    }

    public function countManyManyArray( Table $table, Table $glueTable, $columnName = null, $otherColumnName = null, $field = null, $distinct = false ) {

        return iterator_to_array( $this->countManyMany( $table, $glueTable, $columnName, $otherColumnName, $field, $distinct ) );
    }

    public function selectManyOne( Table $table, Table $glueTable, $columnName = null, $otherColumnName = null, array $fields = null, $as = null ) {

        foreach( $this->getManyOneQueries( $table, $glueTable, $columnName, $otherColumnName ) as $qry )
            yield $qry->selectOne( $fields, $as );
    }

    public function selectManyOneArray( Table $table, Table $glueTable, $columnName = null, $otherColumnName = null, array $fields = null, $as = null ) {

        return iterator_to_array( $this->selectManyOne( $table, $glueTable, $columnName, $otherColumnName, $fields, $as ) );
    }

    public function selectManyMany( Table $table, Table $glueTable, $columnName = null, $otherColumnName = null, array $fields = null, $as = null ) {

        foreach( $this->getManyManyQueries( $table, $glueTable, $columnName, $otherColumnName ) as $qry )
            yield $qry->selectOne( $fields, $as );
    }

    public function selectManyManyArray( Table $table, Table $glueTable, $columnName = null, $otherColumnName = null, array $fields = null, $as = null ) {

        return iterator_to_array( $this->selectManyMany( $table, $glueTable, $columnName, $otherColumnName, $fields, $as ) );
    }

    public function updateManyOne( Table $table, Table $glueTable, $columnName = null, $otherColumnName = null, array $data = null ) {

        foreach( $this->getManyOneQueries( $table, $glueTable, $columnName, $otherColumnName ) as $qry )
            $qry->update( $data );

        return $this;
    }

    public function updateManyMany( Table $table, Table $glueTable, $columnName = null, $otherColumnName = null, array $data = null ) {

        foreach( $this->getManyManyQueries( $table, $glueTable, $columnName, $otherColumnName ) as $qry )
            $qry->update( $data );

        return $this;
    }

    public function removeManyOne( Table $table, Table $glueTable, $columnName = null, $otherColumnName = null ) {

        foreach( $this->getManyOneQueries( $table, $glueTable, $columnName, $otherColumnName ) as $qry )
            $qry->remove();

        return $this;
    }

    public function removeManyMany( Table $table, Table $glueTable, $columnName = null, $otherColumnName = null ) {

        foreach( $this->getManyManyQueries( $table, $glueTable, $columnName, $otherColumnName ) as $qry )
            $qry->remove();

        return $this;
    }

    public function __call( $method, array $args = null ) {

        if( !strlen( $method ) < 4 ) {

            $args = $args ? $args : [];

            $token = substr( $method, 0, 3 );

            if( in_array( $token, [ 'has', 'get', 'set', 'add' ] ) ) {

                $name = substr( $method, 3 );
                $suffix = '';

                if( strlen( $name ) > 5 && substr( $name, -5 ) === 'Array' ) {

                    $name = substr( $name, 0, -5 );
                    $suffix = 'Array';
                }

                $plural = StringUtil::pluralize( $name );
                $singular = $plural !== $name;
                $table = $this->getDatabase()->{$plural};

                array_unshift( $args, $table );
                switch( substr( $method, 0, 3 ) ) {
                    case 'has':

                        if( $singular )
                            return call_user_func_array( [ $this, 'countOne' ], $args ) ? true : false;

                        return call_user_func_array( [ $this, 'countMany' ], $args ) ? true : false;
                    case 'get':

                        if( $singular )
                            return call_user_func_array( [ $this, 'selectOne' ], $args );

                        return call_user_func_array( [ $this, 'selectMany' ], $args );
                    case 'set':

                        if( $singular )
                            return call_user_func_array( [ $this, 'setOne' ], $args );

                        return call_user_func_array( [ $this, 'setMany'.$suffix ], $args );
                    case 'add':

                        if( $singular )
                            return call_user_func_array( [ $this, 'addManyOne' ], $args );

                        throw new Exception( "Failed to add $table: No plural action available" );
                }
            }
            
            if( strlen( $method ) > 5 ) {

                $token = substr( $method, 0, 5 );

                if( in_array( $token, [ 'count' ] ) ) {

                    $name = substr( $method, 5 );
                    $plural = StringUtil::pluralize( $name );
                    $singular = $plural !== $name;
                    $table = $this->getDatabase()->{$plural};

                    array_unshift( $args, $table );

                    if( $singular )
                        return call_user_func_array( [ $this, 'countOne' ], $args );

                    return call_user_func_array( [ $this, 'countMany' ], $args );
                }
            }
        }

        throw new BadMethodCallException( "Failed to call method $method: Method doesnt exist" );
    }
}
