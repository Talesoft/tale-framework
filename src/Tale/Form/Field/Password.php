<?php

namespace Tale\Form\Field;

use Tale\Dom\Html\Element;
use Tale\Form\FieldBase;

class Password extends FieldBase
{

    public function getHtmlElement()
    {

        $el = new Element('input', [
            'type' => 'password',
            'name' => $this->getName(),
            'value' => $this->getValue()
        ]);

        return $el;
    }
}