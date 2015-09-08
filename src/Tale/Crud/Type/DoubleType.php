<?php

namespace Tale\Crud\Type;

use Tale\Crud\TypeBase;
use Tale\Crud\Validator;

class DoubleType extends TypeBase
{

    protected function convert($value)
    {

        if (empty($value) || !$this->isScalar())
            return null;

        return floatval($value);
    }

    protected function validate(Validator $v)
    {

        $v->whenSet(function(Validator $v) {

            $v->notFloat('Value has to be a floating number');
        });

        return parent::validate($v);
    }
}