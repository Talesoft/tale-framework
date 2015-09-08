<?php

namespace Tale\Crud;

abstract class UnsignedTypeBase extends TypeBase
{

    private $_unsigned;

    public function __construct($value, $unsigned = false)
    {

        $this->_unsigned = $unsigned;
    }

    public function isUnsigned()
    {

        return $this->_unsigned;
    }

    public function isSigned()
    {

        return !$this->_unsigned;
    }
}