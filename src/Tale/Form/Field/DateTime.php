<?php

namespace Tale\Form\Field;

use Tale\Dom\Html\Element;
use Tale\Form\FieldBase;

class DateTime extends FieldBase
{

    public function setValue($value)
    {

        return parent::setValue($value ? strtotime($value) : null);
    }

    public function getValue()
    {

        $value = parent::getValue();

        if ($value)
            return new \DateTime("@$value");

        return null;
    }

    public function getHtmlElement()
    {

        $el = new Element('input', [
            'type'  => 'datetime',
            'name'  => $this->getName(),
            'value' => $this->hasValue() ? $this->getValue()->format(\DateTime::RFC3339) : null
        ]);

        return $el;
    }
}