<?php

namespace Tale\Crud\Type;


class ByteType extends IntType
{

    const MIN = -128;
    const MAX = 127;
    const UNSIGNED_MIN = 0;
    const UNSIGNED_MAX = 255;
}