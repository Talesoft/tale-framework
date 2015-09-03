<?php

namespace Tale\Data;

abstract class NamedEntityBase extends EntityBase
{

    private $_name;

    public function __construct($name)
    {
        parent::__construct();

        $this->_name = $name;
    }

    public function getName()
    {

        return $this->_name;
    }

    public function __toString()
    {

        return $this->getName();
    }
}