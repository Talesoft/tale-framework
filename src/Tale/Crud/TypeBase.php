<?php

namespace Tale\Crud;

abstract class TypeBase
{

    private $_value;
    private $_valueType;
    private $_validator;

    public function __construct($value = null)
    {

        $this->_value = $value;
        $this->_valueType = gettype($value);
        $this->_validator = new Validator($this->_value);
    }

    public function getRawValue()
    {

        return $this->_value;
    }

    public function getValue()
    {

        if ($this->isNull())
            return null;

        return $this->sanitize($this->_value);
    }

    public function getValueType()
    {

        return $this->_valueType;
    }

    public function getValidator()
    {

        return $this->_validator;
    }

    public function isBool()
    {

        return $this->_valueType === 'boolean';
    }

    public function isInt()
    {

        return $this->_valueType === 'integer';
    }

    public function isDouble()
    {

        return $this->_valueType === 'double';
    }

    public function isString()
    {

        return $this->_valueType === 'string';
    }

    public function isScalar()
    {

        return !$this->isArray() && !$this->isObject() && !$this->isResource();
    }

    public function isArray()
    {

        return $this->_valueType === 'array';
    }

    public function isObject()
    {

        return $this->_valueType === 'object';
    }

    public function isResource()
    {

        return $this->_valueType === 'resource';
    }

    public function isNull()
    {

        return $this->_valueType === 'NULL';
    }

    public function validates()
    {

        $this->_validator->reset();
        $this->validate($this->_validator);

        return $this->_validator->hasErrors();
    }

    public function getValidationErrors()
    {

        return $this->_validator->getErrors();
    }

    public function __toString()
    {

        $value = $this->getValue();
        return $value ? (string)$value : '';
    }


    protected function sanitize($value)
    {

        return $value;
    }

    protected function validate(Validator $v) {

        return $v;
    }
}