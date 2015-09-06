<?php

namespace Tale\Form\Field;

use Tale\Dom\Html\Element;
use Tale\Form\FieldBase;

class Select extends FieldBase
{

    public function getOptions()
    {

        return $this->getConfig()->getItems();
    }

    public function getHtmlElement()
    {

        $el = new Element('select', [
            'name' => $this->getName()
        ]);

        foreach ($this->getConfig() as $value => $label) {

            $option = new Element('option', [
                'value' => $value
            ]);
            $option->setText($label);

            if (strval($value) === strval($this->getValue()))
                $option->setAttribute('selected', true);

            $el->appendChild($option);
        }

        return $el;
    }
}