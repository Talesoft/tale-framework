<?php

namespace Tale\Db\Index;

use Tale\Db\IndexBase,
    Tale\Db\Column;

class Primary extends IndexBase {

    private $_column;

    public function __construct( $name, Column $column = null ) {
        parent::__construct( $name );

        $this->_column = $column;
    }

    /**
     * @return mixed
     */
    public function getColumn() {

        return $this->_column;
    }

    /**
     * @param mixed $column
     *
     * @return $this
     */
    public function setColumn( $column ) {

        $this->_column = $column;

        return $this;
    }
}