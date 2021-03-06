<?php

namespace Tale\Crud\Type;

use Tale\Crud\TypeBase;

class StringType extends TypeBase
{

    protected function sanitize($value)
    {

        if (empty($value) || !$this->isScalar())
            return null;

        return strval($value);
    }
}