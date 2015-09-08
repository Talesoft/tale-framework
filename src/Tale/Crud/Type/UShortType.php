<?php

namespace Tale\Crud\Type;

class UShortType extends ShortType
{

    public function __construct($value)
    {
        parent::__construct($value, true);
    }
}