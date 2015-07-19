<?php

namespace Tale\Db;

class ScalarTypeBase extends TypeBase {

    private $_maxLength;

    public function __construct( $value, $maxLength = null ) {
        parent::__construct( $value );

        $this->_maxLength = $maxLength;
    }

    public function hasMaxLength() {

        return !is_null( $this->_maxLength );
    }

    public function getMaxLength() {

        return $this->_maxLength;
    }

    public function setMaxLength( $maxLength ) {

        $this->_maxLength = $maxLength;

        return $this;
    }
}