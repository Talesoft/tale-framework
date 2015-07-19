<?php

namespace Tale\Db;

use Tale\Db\Column\TypeBase,
    Tale\Factory;

class Column extends NamedEntity {

    private $_type;
    private $_maxLength;
    private $_allowedValues;
    private $_defaultValue;
    private $_optional;

    public function __construct( $name ) {
        parent::__construct( $name );

        $this->_type = null;
        $this->_maxLength = null;
        $this->_allowedValues = null;
        $this->_defaultValue = null;
        $this->_optional = false;
    }

    public function getType() {

        return $this->_type;
    }

    public function setType( $type ) {

        $this->_type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxLength() {

        return $this->_maxLength;
    }

    /**
     * @param int $maxLength
     *
     * @return $this
     */
    public function setMaxLength( $maxLength ) {

        $this->_maxLength = $maxLength;

        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedValues() {

        return $this->_allowedValues;
    }

    /**
     * @param array|null $allowedValues
     *
     * @return $this
     */
    public function setAllowedValues( array $allowedValues ) {

        $this->_allowedValues = $allowedValues;

        return $this;
    }



    public function getDefaultValue() {

        return $this->_defaultValue;
    }

    public function setDefaultValue( $defaultValue ) {

        $this->_defaultValue = $defaultValue;

        return $this;
    }

    public function setOptional() {

        $this->_optional = true;

        return $this;
    }

    public function setRequired() {

        $this->_optional = false;

        return $this;
    }

    public function isOptional() {

        return $this->_optional;
    }

    public function isRequired() {

        return !$this->_optional;
    }
}