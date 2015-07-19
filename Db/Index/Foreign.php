<?php

namespace Tale\Db\Index;

use Tale\Db\Column;

class Foreign extends Primary {

    private $_foreignColumn;

    public function __construct( $name, Column $column = null, Column $foreignColumn = null ) {
        parent::__construct( $name, $column );

        $this->_foreignColumn = $foreignColumn;
    }

    /**
     * @return mixed
     */
    public function getForeignColumn() {

        return $this->_foreignColumn;
    }

    /**
     * @param mixed $foreignColumn
     *
     * @return $this
     */
    public function setForeignColumn( Column $foreignColumn ) {

        $this->_foreignColumn = $foreignColumn;

        return $this;
    }
}