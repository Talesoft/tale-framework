<?php

namespace Tale\Crud\Type;


class LongType extends IntType
{

    const MIN = -2147483648;
    const MAX = 2147483647;
    const UNSIGNED_MIN = 0;
    const UNSIGNED_MAX = 4294967295;
}