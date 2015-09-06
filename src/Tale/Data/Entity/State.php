<?php

namespace Tale\Data\Entity;

use Tale\Enum;

class State extends Enum
{

    const INITIALIZED = 1;
    const CREATED     = 2;
    const LOADED      = 3;
    const SAVED       = 4;
    const REMOVED     = 5;
    const CHANGED     = 6;
}