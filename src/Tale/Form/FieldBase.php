<?php

namespace Tale\Form;

use Tale\Config;
use Tale\Form;

abstract class FieldBase
{
    use Config\OptionalTrait;

    private $_form;
    private $_name;
    private $_value;

    /**
     * @var \Tale\Form\Validator
     */
    private $_validator;

    public function __construct(Form $form, $name, $value = null, array $options = null)
    {

        $this->_form = $form;
        $this->_name = $name;
        $this->_value = $value;

        if ($options)
            $this->appendOptions($options);
        $this->init();
    }

    /**
     * @return \Tale\Form
     */
    public function getForm()
    {

        return $this->_form;
    }

    /**
     * @return mixed
     */
    public function getName()
    {

        return $this->_name;
    }

    public function hasValue()
    {

        return $this->_value !== null;
    }

    public function getValue()
    {

        return $this->_value;
    }

    public function setValue($value)
    {

        $this->_value = $value;

        return $this;
    }

    protected function init() {}

    public function validate($reset = false)
    {

        if (!isset($this->_validator) || $reset)
            $this->_validator = new Validator($this->_value);

        return $this->_validator;
    }

    public function getErrors()
    {

        if (!isset($this->_validator))
            return [];

        return $this->_validator->getErrors();
    }

    abstract public function getHtmlElement();
}