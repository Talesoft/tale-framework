<?php

namespace Tale\Db\Index;

use Tale\Db\IndexBase;

class Unique extends IndexBase {

    private $_columns;

    public function __construct( $name, array $columns = null ) {
        parent::__construct( $name );

        $this->_columns = $columns ? $columns : [];
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
     * @return $this
     */
    public function setColumns( array $columns ) {

        $this->_columns = $columns;

        return $this;
    }


}