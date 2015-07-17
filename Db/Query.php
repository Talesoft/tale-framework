<?php

namespace Tale\Db;

class Query {

    const SORT_ASC = 'ASC';
    const SORT_DESC = 'DESC';

    private $_clauses;
    private $_sortings;
    private $_limit;
    private $_limitStart;

    public function __construct( array $clauses = null, array $sortings = null, $limit = null, $limitStart = null ) {

        $this->_clauses = $clauses ? $clauses : [];
        $this->_sortings = $sortings ? $sortings : [];
        $this->_limit = $limit;
        $this->_limitStart = $limit;
    }

    public function getClauses() {

        return $this->_clauses;
    }

    public function getSortings() {

        return $this->_sortings;
    }

    public function getLimit() {

        return $this->_limit;
    }

    public function getLimitStart() {

        return $this->_limitStart;
    }

    public function where( array $clauses, $recursive = false ) {

        $this->_clauses = $recursive
                        ? array_replace_recursive( $this->_clauses, $clauses )
                        : array_replace( $this->_clauses, $clauses );

        return $this;
    }

    public function orderBy( array $sortings ) {

        $this->_sortings = array_replace( $this->_sortings, $sortings );

        return $this;
    }

    public function limit( $limit, $start = null ) {

        $this->_limit = $limit;
        $this->_limitStart = $start;

        return $this;
    }
}