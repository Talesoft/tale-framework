<?php

namespace Tale\Crud\Type;

use Tale\Crud\TypeBase;
use Tale\Crud\Validator;

class EnumType extends StringType
{

    private $_options;

    public function __construct($value, array $options = null)
    {

        $this->_options = $options ? $options : [];
    }

    public function getOptions()
    {

        return $this->_options;
    }

    public function addOptions($option, $label = null)
    {

        if (!$label)
            $this->_options[] = $option;
        else
            $this->_options[$label] = $option;

        return $this;
    }

    protected function validate(Validator $v)
    {

        $v->whenSet(function(Validator $v) {

            $v->notIn($this->_options, 'The value needs to be one of '.implode( ', ', $this->_options));
        });

        return parent::validate($v);
    }
}