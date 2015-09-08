<?php

namespace Tale\Crud\Type;

use Tale\Crud\TypeBase;
use Tale\Crud\Validator;

class CharType extends TypeBase
{

    protected function sanitize($value)
    {

        if (empty($value) || !$this->isScalar())
            return null;

        return strval($value);
    }

    protected function validate(Validator $v)
    {

        $v->whenSet(function(Validator $v) {

            $v->smallerThan(1,1, 'The value needs to be exatly one character long');
        });

        return parent::validate($v);
    }
}