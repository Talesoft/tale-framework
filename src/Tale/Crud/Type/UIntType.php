<?php

namespace Tale\Crud\Type;

class UIntType extends IntType
{

    public function __construct($value)
    {
        parent::__construct($value, true);
    }
}