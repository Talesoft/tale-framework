<?php

namespace Tale\Crud\Entity;

class Change
{

    private $_name;
    private $_oldValue;
    private $_newValue;

    public function __construct($name, $oldValue, $newValue)
    {

        $this->_name = $name;
        $this->_oldValue = $oldValue;
        $this->_newValue = $newValue;
    }
}