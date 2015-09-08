<?php

namespace Tale\Crud\Type;


class ShortType extends IntType
{

    const MIN = -32768;
    const MAX = 32767;
    const UNSIGNED_MIN = 0;
    const UNSIGNED_MAX = 65535;
}