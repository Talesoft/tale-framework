<?php

namespace Tale\Data;

/**
* Clause Syntax:
* 'field' => 'value'        => `field` = 'value'
* 'field!' => 'value'       => `field` != 'value'
* 'field~' => 'value'       => `field` LIKE '%value%'
* 'field^' => 'value'       => `field` LIKE '%value'
* 'field$' => 'value'       => `field` LIKE 'value%'
* 'field>' => 'value'       => `field` > 'value'
* 'field<' => 'value'       => `field` < 'value'
* 'field>=' => 'value'      => `field` >= 'value'
* 'field<=' => 'value'      => `field` <= 'value'
* 'field' => [ 'a', 'b' ]   => `field` IN( 'a', 'b' )
* 'field!' => [ 'a', 'b' ]  => `field` NOT IN( 'a', 'b' )
* 'and' => []               => AND ( .... )
* 'or' => []                => OR ( .... )
* 'any........*'            => `any` (Can also be used for multiple 'ands' or 'ors' ('and.', 'and..', 'and...', 'and....')
*/
class Query {

    const DEFAULT_ROW_TYPE = 'Tale\\Data\\Row';

    private $_table;
    private $_clauses;
    private $_sortings;
    private $_random;
    private $_limit;
    private $_limitStart;

    public function __construct( Table $table, array $clauses = null, array $sortings = null, $limit = null, $limitStart = null ) {

        $this->_table = $table;
        $this->_clauses = $clauses ? $clauses : [];
        $this->_sortings = $sortings ? $sortings : [];
        $this->_random = false;
        $this->_limit = null;
        $this->_limitStart = null;
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

    public function getClauses() {

        return $this->_clauses;
    }

    public function setClauses( array $clauses ) {

        $this->_clauses = $clauses;

        return $this;
    }

    public function getSortings() {

        return $this->_sortings;
    }

    public function setSortings( array $sortings ) {

        $this->_random = false;
        $this->_sortings = $sortings;

        return $this;
    }

    public function getLimit() {

        return $this->_limit;
    }

    public function setLimit( $limit ) {

        $this->_limit = $limit;

        return $this;
    }

    public function getLimitStart() {

        return $this->_limitStart;
    }

    public function setLimitStart( $limitStart ) {

        $this->_limitStart = $limitStart;

        return $this;
    }



    public function where( array $clauses ) {

        $this->_clauses = array_replace_recursive( $this->_clauses, $clauses );

        return $this;
    }

    public function sortBy( array $sortings ) {

        $this->_random = false;
        $this->_sortings = array_replace_recursive( $this->_sortings, $sortings );

        return $this;
    }

    public function isRandomSorted() {

        return $this->_random;
    }

    public function sortRandom() {

        $this->_random = true;

        return $this;
    }

    public function limit( $limit, $start = null ) {

        $this->_limit = $limit;
        $this->_limitStart = $start;

        return $this;
    }

    public function count( $field = null, $distinct = false ) {

        return $this->getSource()->countRows( $this, $field, $distinct );
    }

    public function select( array $fields = null, $as = null ) {

        if( $as === null )
            $as = $this->_table->getRowClassName();

        return $this->getSource()->loadRows( $this, $fields, $as );
    }

    public function selectArray( array $fields = null, $as = null ) {

        return iterator_to_array( $this->select( $fields ) );
    }

    public function selectOne( array $fields = null, $as = null ) {

        $result = $this->limit( 1 )->selectArray();

        return count( $result ) ? $result[ 0 ] : $result;
    }

    public function update( array $data ) {

        $this->getSource()->saveRows( $this, $data );

        return $this;
    }

    public function remove() {

        $this->getSource()->removeRows( $this );

        return $this;
    }
}
