<?php

namespace Tale\Crud\Type;

class ULongType extends LongType
{

    public function __construct($value)
    {
        parent::__construct($value, true);
    }
}