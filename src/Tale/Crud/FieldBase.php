<?php

namespace Tale\Crud;


abstract class FieldBase
{

    /** @var string */
    private $_name;

    /** @var \Tale\Crud\TypeBase */
    private $_type;

    public function __construct($name, TypeBase $type)
    {

        $this->_name = $name;
        $this->_type = $type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return \Tale\Crud\TypeBase
     */
    public function getType()
    {
        return $this->_type;
    }

    public function getValidator()
    {

        return $this->_type->getValidator();
    }

    public function validates()
    {
        $v = $this->_type->getValidator();
        $this->validate($v);

        return $v->hasErrors();
    }

    protected function validate(Validator $v)
    {

        return $v;
    }
}