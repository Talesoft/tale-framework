<?php

namespace Tale\Db;

use Tale\Data;

class Table extends NamedEntity {

    private $_columns;
    private $_indices;

    public function __construct( $name, array $columns = null, array $indices = null ) {
        parent::__construct( $name );

        $this->_columns = $columns ? $columns : [];
        $this->_indices = $indices ? $indices : [];
    }

    /**
     * @return array
     */
    public function getColumns() {

        return $this->_columns;
    }

    /**
     * @param array $columns
     *
     * @return Table
     */
    public function setColumns( $columns ) {

        $this->_columns = $columns;

        return $this;
    }

    /**
     * @return array
     */
    public function getIndices() {

        return $this->_indices;
    }

    /**
     * @param array $indices
     *
     * @return Table
     */
    public function setIndices( $indices ) {

        $this->_indices = $indices;

        return $this;
    }
}