<?php

namespace Tale\Form\Field;

use Tale\Dom\Html\Element;
use Tale\Form\FieldBase;

class Number extends FieldBase
{

    public function getHtmlElement()
    {

        $el = new Element('input', [
            'type' => 'number',
            'name' => $this->getName(),
            'value' => $this->getValue()
        ]);

        if ($this->hasOption('min'))
            $el->setAttribute('min', $this->getOption('min'));

        if ($this->hasOption('max'))
            $el->setAttribute('max', $this->getOption('max'));

        if ($this->hasOption('step'))
            $el->setAttribute('step', $this->getOption('step'));

        return $el;
    }

    public function setValue($value)
    {

        return parent::setValue($value ? intval($value) : null);
    }

    public function getValue()
    {

        $value = parent::getValue();

        if ($value)
            return intval($value);

        return null;
    }
}