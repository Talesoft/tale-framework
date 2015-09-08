<?php

namespace Tale\Crud\Type;

class UByteType extends ByteType
{

    public function __construct($value)
    {
        parent::__construct($value, true);
    }
}