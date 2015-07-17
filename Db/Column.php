<?php

namespace Tale\Db;

use Tale\Db\Column\TypeBase,
    Tale\Factory;

class Column extends NamedEntity {

    private $_type;
    private $_typeFactory;
    private $_defaultValue;
    private $_nullable;

    public function __construct( $name ) {
        parent::__construct( $name );

        $this->_type = null;
        $this->_typeFactory = new Factory( 'Tale\\Db\\Column\\TypeBase', [
            'string' => 'Tale\\Db\\Column\\Type\\StringType',
            'int' => 'Tale\\Db\\Column\\Type\\IntType'
        ] );
        $this->_defaultValue = null;
        $this->_nullable = false;
    }

    public function getTable() {

        return $this->_table;
    }

    public function getType() {

        return $this->_type;
    }

    public function setType( $type ) {

        $this->_type = $type;
        return $this;
    }

    public function getDefaultValue() {

        return $this->_defaultValue;
    }

    public function setDefaultValue( $defaultValue ) {

        $this->_defaultValue = $defaultValue;
        return $this;
    }

    public function setNullable() {

        $this->_nullable = true;
        return $this;
    }

    public function setNotNullable( $nullable ) {

        $this->_nullable = false;
        return $this;
    }


}