<?php

namespace Tale\Form\Field;

use Tale\Dom\Html\Element;
use Tale\Form\FieldBase;

class Text extends FieldBase
{

    public function getHtmlElement()
    {

        $el = new Element('input', [
            'type' => 'text',
            'name' => $this->getName(),
            'value' => $this->getValue()
        ]);

        return $el;
    }
}