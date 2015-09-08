<?php

namespace Tale\Crud\Type;

use Tale\Crud\TypeBase;
use Tale\Crud\Validator;

class DateTimeType extends TypeBase
{

    protected function sanitize($value)
    {

        if (empty($value))
            return null;

        $value = \DateTime::createFromFormat(\DateTime::DATE_ATOM, $value);

        return $value ? $value : null;
    }

    protected function validate(Validator $v)
    {

        $v->whenSet(function(Validator $v) {

            $v->notDateTime('The value is not a valid Date and Time string');
        });

        return parent::validate($v);
    }
}