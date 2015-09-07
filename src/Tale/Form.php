<?php

namespace Tale;

use Tale\Dom\Html\Element;
use Tale\Net\Http\Method;
use Tale\Net\Mime\Type;

class Form
{

    private $_fields;
    private $_fieldFactory;

    public function __construct(array $fieldDefitions = null)
    {

        $this->_fields = [];
        $this->_fieldFactory = new Factory('Tale\\Form\\FieldBase', [
            'text' => 'Tale\\Form\\Field\\Text',
            'password' => 'Tale\\Form\\Field\\Password',
            'select' => 'Tale\\Form\\Field\\Select',
            'checkbox' => 'Tale\\Form\\Field\\Checkbox',
            'radio-group' => 'Tale\\Form\\Field\\RadioGroup'
        ]);

        if ($fieldDefitions) {

            foreach ($fieldDefitions as $name => $def) {
                $this->addFieldDefinition($name, $def);
            }
        }
    }

    public function getFields()
    {

        return $this->_fields;
    }

    public function hasField($name)
    {

        return isset($this->_fields[$name]);
    }

    public function getField($name)
    {

        return $this->_fields[$name];
    }

    public function setField($name, $type, $value = null, array $options = null)
    {

        $this->_fields[$name] = $this->_fieldFactory->createInstance($type, [$this, $name, $value, $options]);

        return $this;
    }

    public function addFieldDefinition($name, $definition)
    {

        if (is_string($definition))
            return $this->setField($name, $definition);

        if (!is_array($definition))
            throw new \InvalidArgumentException(
                "Failed to add field definition: "
                ."Argument 2 needs to be an array or string"
            );

        $definition = array_replace([
            'type' => 'text',
            'value' => null,
            'options' => []
        ], $definition);

        return $this->setField($name, $definition['type'], $definition['value'], $definition['options']);
    }

    public function getErrors($withFields = false)
    {

        $errors = [];
        foreach ($this->_fields as $name => $field)
            if ($withFields)
                $errors[$name] = $field->getErrors();
            else
                $errors = array_merge($errors, $field->getErrors());

        return $errors;
    }

    public function getHtmlElement(array $attributes = null, $fieldCallback = null)
    {

        if ($fieldCallback !== null && !is_callable($fieldCallback))
            throw new \InvalidArgumentException(
                "Argument 1 passed to buildHtml needs to be valid callback"
                ." or null"
            );

        $attributes = array_replace([
            'action' => '#',
            'method' => Method::GET,
            'enctype' => Type::X_WWW_FORM_URLENCODED
        ], $attributes ? $attributes : []);

        $form = new Element('form', $attributes);
        foreach ($this->_fields as $field) {

            $el = $field->getHtmlElement();

            if ($fieldCallback)
                $el = call_user_func($fieldCallback, $el);

            $form->appendChild($el);
        }

        return $form;
    }

    public function __isset($fieldName)
    {

        return $this->hasField($fieldName);
    }

    public function __get($fieldName)
    {

        return $this->getField($fieldName);
    }
}